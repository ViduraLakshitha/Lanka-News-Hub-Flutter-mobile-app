<?php
namespace App\Controllers;
use App\Core\Portal;
use App\Core\Cache;

class NewsController extends Portal {
    private function feed(string $where='', array $params=[], int $limit=13, int $offset=0, bool $fullContent=false, string $orderBy='a.published_at DESC,a.id DESC'): array {
        $language=lang();
        $columns = $fullContent 
            ? "a.*" 
            : "a.id, a.author_id, a.category_id, a.featured_image_id, a.video_media_id, a.slug, a.title_si, a.title_en, a.title_ta, a.summary_si, a.summary_en, a.summary_ta, a.status, a.is_breaking, a.is_featured, a.views, a.published_at, a.created_at, a.updated_at, a.gallery_ids, a.video_url, a.tags";
        return $this->rows("SELECT $columns, c.name_$language AS category_name, c.slug AS category_slug, u.name AS author_name, m.file_path AS image_path FROM articles a JOIN categories c ON c.id=a.category_id JOIN users u ON u.id=a.author_id LEFT JOIN media m ON m.id=a.featured_image_id WHERE a.status IN ('published','scheduled') AND a.published_at<=? AND c.status='active' AND COALESCE(a.title_$language,'')<>'' AND COALESCE(a.content_$language,'')<>'' $where ORDER BY $orderBy LIMIT $limit OFFSET $offset",[date('Y-m-d H:i:s'),...$params]);
    }
    public function index(): void {
        $language = lang();
        $cacheKey = "homepage_payload_$language";
        $data = Cache::remember($cacheKey, 60, function() {
            $categorySections = [];
            foreach ($this->publicCategories() as $desk) {
                $catIds = $desk['category_ids'] ?? [(int)$desk['id']];
                $inList = implode(',', array_fill(0, count($catIds), '?'));
                $stories = $this->feed("AND a.category_id IN ($inList)", $catIds, 8);
                if ($stories) {
                    $categorySections[] = [
                        'category' => $desk,
                        'stories' => $stories
                    ];
                }
            }
            return [
                'latest' => $this->feed('', [], 30),
                'featured' => $this->feed('AND a.is_featured=1', [], 5),
                'breaking' => $this->feed('AND a.is_breaking=1', [], 6),
                'videos' => $this->feed("AND (COALESCE(a.video_url,'')<>'' OR a.video_media_id IS NOT NULL)", [], 12),
                'pictures' => $this->feed("AND COALESCE(a.gallery_ids,'[]') NOT IN ('','[]')", [], 8),
                'categorySections' => $categorySections
            ];
        });
        $this->page('home', [
            'title' => setting('site_name', 'Lanka News Hub'),
            ...$data
        ]);
    }
    public function listing(string $section='latest'): void {
        $where=''; $params=[]; $heading=t('Latest news');
        if ($section==='breaking') { $where='AND a.is_breaking=1'; $heading=t('Breaking news'); }
        elseif ($section==='videos') { $where="AND (COALESCE(a.video_url,'')<>'' OR a.video_media_id IS NOT NULL)"; $heading=t('Videos'); }
        elseif ($section==='gallery') { $where="AND COALESCE(a.gallery_ids,'[]') NOT IN ('','[]')"; $heading=t('Photo gallery'); }
        elseif ($section==='search') {
            $q=mb_substr(trim($_GET['q'] ?? ''),0,150); $language=lang();
            $heading=t('Search').': '.$q;
            $where="AND (a.title_$language LIKE ? OR a.summary_$language LIKE ? OR a.content_$language LIKE ? OR a.tags LIKE ?)";
            $params=array_fill(0,4,'%'.$q.'%');
            if ($q==='') $where='AND 1=0';
            if ($q==='') $params=[];
        } elseif ($section!=='latest') {
            $slugAliases = [
                'sri-lanka' => 'local',
                'world' => 'international',
                'life' => 'entertainment',
                'tech' => 'technology',
                'biz' => 'business',
            ];
            if (isset($slugAliases[$section])) {
                $section = $slugAliases[$section];
            }
            $category=$this->one("SELECT * FROM categories WHERE slug=? AND status='active'",[$section]);
            if (!$category) $this->abort(404,'Category not found');
            $heading=localized($category,'name'); $params=[(int)$category['id']];
            foreach ($this->publicCategories() as $desk) {
                if (in_array($section,$desk['category_slugs'],true)) { $params=$desk['category_ids']; break; }
            }
            $where='AND a.category_id IN ('.implode(',',array_fill(0,count($params),'?')).')';
        }
        $page=max(1,min(100000,(int)($_GET['page'] ?? 1)));
        $perPage=18;
        $language=lang();
        $countSql="SELECT COUNT(*) as cnt FROM articles a JOIN categories c ON c.id=a.category_id WHERE a.status IN ('published','scheduled') AND a.published_at<=? AND c.status='active' AND COALESCE(a.title_$language,'')<>'' AND COALESCE(a.content_$language,'')<>'' $where";
        $totalCount=(int)($this->one($countSql, [date('Y-m-d H:i:s'), ...$params])['cnt'] ?? 0);
        $totalPages=max(1, (int)ceil($totalCount / $perPage));
        $items=$this->feed($where,$params,$perPage,($page-1)*$perPage);
        $videoStories=$this->feed("AND (COALESCE(a.video_url,'')<>'' OR a.video_media_id IS NOT NULL)", [], 6);
        if (empty($videoStories)) {
            $videoStories=$this->feed('AND a.is_featured=1', [], 6);
        }
        if (empty($videoStories)) {
            $videoStories=$this->feed('', [], 6);
        }
        $this->page('listing',[
            'title'=>$heading,
            'section'=>$section,
            'items'=>$items,
            'page'=>$page,
            'totalPages'=>$totalPages,
            'totalCount'=>$totalCount,
            'more'=>$page < $totalPages,
            'videoStories'=>$videoStories
        ]);
    }
    public function search(): void { $this->listing('search'); }
    public function article(string $slug): void {
        $items=$this->feed('AND a.slug=?',[$slug],1,0,true);
        if (!$items) $this->abort(404,'This story is not available in this edition. Choose another language or return to the latest news.');
        $article=$items[0];
        $_SESSION['viewed_stories'] ??= [];
        if (!in_array($article['id'], $_SESSION['viewed_stories'], true)) {
            $_SESSION['viewed_stories'][] = $article['id'];
            $this->run('UPDATE articles SET views=views+1 WHERE id=?',[$article['id']]);
        }
        $gallery=[];
        $galleryIds = array_filter(array_map('intval', json_decode($article['gallery_ids'] ?? '[]', true) ?: []));
        if ($galleryIds) {
            $in = implode(',', array_fill(0, count($galleryIds), '?'));
            $gallery = $this->rows("SELECT * FROM media WHERE id IN ($in) AND file_type LIKE 'image/%'", $galleryIds);
        }
        $video=$article['video_media_id'] ? $this->one("SELECT * FROM media WHERE id=? AND file_type LIKE 'video/%'",[$article['video_media_id']]) : false;
        
        $mostRead = $this->feed('AND a.id<>?', [$article['id']], 10, 0, false, 'a.views DESC, a.published_at DESC');
        if (count($mostRead) < 5) {
            $mostRead = $this->feed('AND a.id<>?', [$article['id']], 10);
        }
        $videoStories = $this->feed("AND (COALESCE(a.video_url,'')<>'' OR a.video_media_id IS NOT NULL)", [], 5);
        if (empty($videoStories)) {
            $videoStories = $this->feed('AND a.is_featured=1', [], 5);
        }
        if (empty($videoStories)) {
            $videoStories = $this->feed('', [], 5);
        }
        $related = $this->feed('AND a.category_id=? AND a.id<>?', [$article['category_id'], $article['id']], 4);

        $this->page('article', [
            'title' => localized($article, 'title'),
            'article' => $article,
            'gallery' => $gallery,
            'video' => $video,
            'related' => $related,
            'mostRead' => $mostRead,
            'videoStories' => $videoStories,
        ]);
    }

    public function rss(): void {
        header('Content-Type: application/rss+xml; charset=UTF-8');
        $siteName = setting('site_name', 'Lanka News Hub');
        $siteUrl = url();
        $lang = lang();
        $stories = $this->feed('', [], 25);
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        echo '<channel>' . "\n";
        echo '<title>' . htmlspecialchars($siteName, ENT_XML1) . '</title>' . "\n";
        echo '<link>' . htmlspecialchars($siteUrl, ENT_XML1) . '</link>' . "\n";
        echo '<description>' . htmlspecialchars(setting('site_tagline', 'TRUTH FIRST · NATIONAL PERSPECTIVE'), ENT_XML1) . '</description>' . "\n";
        echo '<language>' . htmlspecialchars($lang, ENT_XML1) . '</language>' . "\n";
        foreach ($stories as $item) {
            $title = localized($item, 'title') ?: $item['title_en'];
            $summary = localized($item, 'summary') ?: $item['summary_en'];
            $link = url('news/' . $item['slug']);
            $pubDate = date(DATE_RSS, strtotime($item['published_at']));
            echo '<item>' . "\n";
            echo '<title>' . htmlspecialchars($title, ENT_XML1) . '</title>' . "\n";
            echo '<link>' . htmlspecialchars($link, ENT_XML1) . '</link>' . "\n";
            echo '<guid isPermaLink="true">' . htmlspecialchars($link, ENT_XML1) . '</guid>' . "\n";
            echo '<pubDate>' . $pubDate . '</pubDate>' . "\n";
            echo '<description>' . htmlspecialchars($summary, ENT_XML1) . '</description>' . "\n";
            echo '</item>' . "\n";
        }
        echo '</channel>' . "\n";
        echo '</rss>' . "\n";
        exit;
    }
}

