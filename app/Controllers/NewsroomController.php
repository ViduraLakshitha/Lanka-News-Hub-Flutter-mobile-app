<?php
namespace App\Controllers;
use App\Core\Portal;
use App\Core\Upload;
class NewsroomController extends Portal
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(['super_admin', 'editor', 'reporter']);
    }
    private function manager(): void
    {
        $this->requireRole(['super_admin', 'editor']);
    }
    private function admin(): void
    {
        $this->requireRole(['super_admin']);
    }
    private function screen(string $section, array $data = []): void
    {
        $this->page('admin', ['section' => $section, 'title' => ucfirst(str_replace('-', ' ', $section)), ...$data]);
    }
    private function articleOwner(array $article): void
    {
        if ($this->user['role'] === 'reporter' && $article['author_id'] != $this->user['id'])
            $this->abort(403, 'You can only edit your own stories.');
    }
    private function clearPublicCaches(): void
    {
        \App\Core\Cache::delete('portal_categories');
        \App\Core\Cache::delete('homepage_payload_en');
        \App\Core\Cache::delete('homepage_payload_si');
        \App\Core\Cache::delete('homepage_payload_ta');
        \App\Helpers\AdHelper::clearCache();
    }
    public function index(): void
    {
        $mine = $this->user['role'] === 'reporter' ? ' WHERE author_id=' . (int) $this->user['id'] : '';
        $stats = [
            'Stories' => $this->one('SELECT COUNT(*) n FROM articles' . $mine)['n'],
            'Published' => $this->one("SELECT COUNT(*) n FROM articles WHERE status IN ('published','scheduled') AND published_at<=?" . ($mine ? ' AND author_id=' . (int) $this->user['id'] : ''), [date('Y-m-d H:i:s')])['n'],
            'Article views' => $this->one('SELECT COALESCE(SUM(views),0) n FROM articles' . $mine)['n'],
            'Media files' => $this->one('SELECT COUNT(*) n FROM media')['n']
        ];
        if ($this->user['role'] !== 'reporter') {
            $stats['Pending review'] = $this->one("SELECT COUNT(*) n FROM articles WHERE status='pending'")['n'];
        }
        $recent = $this->rows('SELECT * FROM articles' . $mine . ' ORDER BY updated_at DESC,id DESC LIMIT 8');
        $this->screen('dashboard', ['stats' => $stats, 'recent' => $recent]);
    }
    public function articles(): void
    {
        $where = '1=1';
        $params = [];
        $whereAuthor = '';
        $paramsAuthor = [];
        if ($this->user['role'] === 'reporter') {
            $where .= ' AND a.author_id=?';
            $params[] = $this->user['id'];
            $whereAuthor = 'WHERE author_id=?';
            $paramsAuthor = [$this->user['id']];
        }
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $where .= ' AND (a.title_en LIKE ? OR a.title_si LIKE ? OR a.title_ta LIKE ?)';
            $params = [...$params, ...array_fill(0, 3, '%' . mb_substr($q, 0, 150) . '%')];
        }
        $activeStatus = trim($_GET['status'] ?? '');
        if (in_array($activeStatus, ['draft', 'pending', 'scheduled', 'published', 'archived'], true)) {
            $where .= ' AND a.status=?';
            $params[] = $activeStatus;
        }
        $activeEdition = trim($_GET['edition'] ?? '');
        if (in_array($activeEdition, ['en', 'si', 'ta'], true)) {
            $where .= " AND COALESCE(a.title_$activeEdition,'')<>'' AND COALESCE(a.content_$activeEdition,'')<>''";
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * 30;
        $items = $this->rows("SELECT a.*, u.name author, c.name_en category_name FROM articles a JOIN users u ON u.id=a.author_id LEFT JOIN categories c ON c.id=a.category_id WHERE $where ORDER BY a.id DESC LIMIT 31 OFFSET $offset", $params);

        $counts = [
            'total' => (int)$this->one("SELECT COUNT(*) AS n FROM articles $whereAuthor", $paramsAuthor)['n'],
            'published' => (int)$this->one("SELECT COUNT(*) AS n FROM articles " . ($whereAuthor ? "$whereAuthor AND status='published'" : "WHERE status='published'"), $paramsAuthor)['n'],
            'draft' => (int)$this->one("SELECT COUNT(*) AS n FROM articles " . ($whereAuthor ? "$whereAuthor AND status='draft'" : "WHERE status='draft'"), $paramsAuthor)['n'],
            'pending' => (int)$this->one("SELECT COUNT(*) AS n FROM articles " . ($whereAuthor ? "$whereAuthor AND status='pending'" : "WHERE status='pending'"), $paramsAuthor)['n'],
            'scheduled' => (int)$this->one("SELECT COUNT(*) AS n FROM articles " . ($whereAuthor ? "$whereAuthor AND status='scheduled'" : "WHERE status='scheduled'"), $paramsAuthor)['n'],
            'en' => (int)$this->one("SELECT COUNT(*) AS n FROM articles " . ($whereAuthor ? "$whereAuthor AND COALESCE(title_en,'')<>''" : "WHERE COALESCE(title_en,'')<>''"), $paramsAuthor)['n'],
            'si' => (int)$this->one("SELECT COUNT(*) AS n FROM articles " . ($whereAuthor ? "$whereAuthor AND COALESCE(title_si,'')<>''" : "WHERE COALESCE(title_si,'')<>''"), $paramsAuthor)['n'],
            'ta' => (int)$this->one("SELECT COUNT(*) AS n FROM articles " . ($whereAuthor ? "$whereAuthor AND COALESCE(title_ta,'')<>''" : "WHERE COALESCE(title_ta,'')<>''"), $paramsAuthor)['n'],
        ];

        $this->screen('articles', [
            'items' => array_slice($items, 0, 30),
            'page' => $page,
            'more' => count($items) > 30,
            'counts' => $counts,
            'activeStatus' => $activeStatus,
            'activeEdition' => $activeEdition,
            'searchQuery' => $q
        ]);
    }
    public function editArticle(?string $id = null): void
    {
        $article = [];
        $error = '';
        if ($id) {
            $article = $this->one('SELECT * FROM articles WHERE id=?', [$id]);
            if (!$article)
                $this->abort(404, 'Story not found');
            $this->articleOwner($article);
        }
        $userLangs = \App\Helpers\DomainHelper::userLanguages($this->user);
        if ($this->post()) {
            $data = [];
            $complete = false;
            foreach (['en', 'si', 'ta'] as $language) {
                if (in_array($language, $userLangs, true)) {
                    $data['title_' . $language] = $this->text('title_' . $language);
                    $data['summary_' . $language] = $this->text('summary_' . $language, 2000);
                    $data['content_' . $language] = rich_html($this->text('content_' . $language, 200000));
                    if ($data['title_' . $language] && trim(strip_tags($data['content_' . $language])))
                        $complete = true;
                } else {
                    // Preserve existing translations if user cannot edit this language
                    $data['title_' . $language] = $article['title_' . $language] ?? '';
                    $data['summary_' . $language] = $article['summary_' . $language] ?? '';
                    $data['content_' . $language] = $article['content_' . $language] ?? '';
                    if ($data['title_' . $language] && trim(strip_tags($data['content_' . $language])))
                        $complete = true;
                }
            }
            $status = $this->text('status', 20);
            if ($this->user['role'] === 'reporter' && !in_array($status, ['draft', 'pending'], true))
                $status = 'pending';
            if (!$complete) {
                $langLabels = [
                    'en' => 'English (news.liontech.lk)',
                    'si' => 'Sinhala (sinhala.news.liontech.lk)',
                    'ta' => 'Tamil (tamil.news.liontech.lk)'
                ];
                $allowedNames = array_map(fn($l) => $langLabels[$l] ?? strtoupper($l), $userLangs);
                $error = 'Provide a headline and story content in your assigned edition (' . implode(' or ', $allowedNames) . ').';
            }
            if (!in_array($status, ['draft', 'pending', 'scheduled', 'published', 'archived'], true))
                $error = 'Choose a valid publication status.';
            $category = $this->one("SELECT id FROM categories WHERE id=? AND status='active'", [(int) ($_POST['category_id'] ?? 0)]);
            if (!$category)
                $error = 'Choose an active news category.';
            $date = $this->text('published_at', 30);
            $parsed = $date ? strtotime($date) : false;
            if ($status === 'scheduled' && (!$parsed || $parsed <= time()))
                $error = 'Scheduled stories need a future publication date (Sri Lanka time).';
            if ($status === 'published' && $date && (!$parsed || $parsed > time()))
                $error = 'Use Scheduled for a future publication date.';
            $video = $this->text('video_url');
            if ($video && !youtube_id($video))
                $error = 'Enter a valid YouTube watch, short or embed URL.';
            $gallery = array_values(array_unique(array_map('intval', (array) ($_POST['gallery_ids'] ?? []))));
            $featured = (int) ($_POST['featured_image_id'] ?? 0);
            $videoMedia = (int) ($_POST['video_media_id'] ?? 0);
            foreach (array_filter([...$gallery, $featured]) as $mediaId)
                if (!$this->one("SELECT id FROM media WHERE id=? AND file_type LIKE 'image/%'", [$mediaId]))
                    $error = 'Choose valid images from the media library.';
            if ($videoMedia && !$this->one("SELECT id FROM media WHERE id=? AND file_type LIKE 'video/%'", [$videoMedia]))
                $error = 'Choose a valid video from the media library.';
            $titleCandidate = $data['title_en'] ?: ($data['title_si'] ?: ($data['title_ta'] ?: 'story'));
            $data += ['category_id' => $category['id'] ?? 0, 'slug' => $this->uniqueSlug('articles', $this->text('slug') ?: $titleCandidate, $id ? (int) $id : null), 'status' => $status, 'published_at' => in_array($status, ['published', 'scheduled'], true) ? date('Y-m-d H:i:s', $parsed ?: time()) : null, 'is_breaking' => isset($_POST['is_breaking']) ? 1 : 0, 'is_featured' => isset($_POST['is_featured']) ? 1 : 0, 'featured_image_id' => $featured ?: null, 'video_media_id' => $videoMedia ?: null, 'tags' => $this->text('tags', 2000), 'gallery_ids' => json_encode($gallery), 'video_url' => $video, 'updated_at' => date('Y-m-d H:i:s')];
            if ($this->user['role'] !== 'reporter' && !empty($_POST['author_id'])) {
                $authorId = (int) $_POST['author_id'];
                $validAuthor = $this->one("SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND r.slug IN ('super_admin','editor','reporter')", [$authorId]);
                if ($validAuthor)
                    $data['author_id'] = $authorId;
            }
            if (!$error) {
                $this->db->beginTransaction();
                try {
                    if ($id)
                        $this->save('article_revisions', ['article_id' => $id, 'user_id' => $this->user['id'], 'snapshot' => json_encode($article, JSON_UNESCAPED_UNICODE), 'created_at' => date('Y-m-d H:i:s')]);
                    elseif (!isset($data['author_id']))
                        $data['author_id'] = $this->user['id'];
                    $saved = $this->save('articles', $data, $id ? (int) $id : null);
                    if ($id) {
                        if ($status === 'published' && ($article['status'] ?? '') !== 'published')
                            $auditAction = 'Published';
                        elseif ($status === 'scheduled' && ($article['status'] ?? '') !== 'scheduled')
                            $auditAction = 'Scheduled';
                        elseif ($status === 'archived' && ($article['status'] ?? '') !== 'archived')
                            $auditAction = 'Archived';
                        elseif ($status === 'pending' && ($article['status'] ?? '') !== 'pending')
                            $auditAction = 'Submitted for review';
                        else
                            $auditAction = 'Edited';
                    } else {
                        if ($status === 'published')
                            $auditAction = 'Published';
                        elseif ($status === 'scheduled')
                            $auditAction = 'Scheduled';
                        elseif ($status === 'pending')
                            $auditAction = 'Submitted for review';
                        else
                            $auditAction = 'Created';
                    }
                    $this->audit($auditAction, 'articles', $saved);
                    $this->db->commit();
                    $this->clearPublicCaches();
                } catch (\Throwable $ex) {
                    $this->db->rollBack();
                    throw $ex;
                }
                flash('Story saved.');
                $this->redirect('/admin/articles/edit/' . $saved);
            }
            $article = array_merge($article, $data);
        }
        $revisions = $id ? $this->rows('SELECT r.*,u.name author FROM article_revisions r JOIN users u ON u.id=r.user_id WHERE r.article_id=? ORDER BY r.id DESC', [$id]) : [];
        $authors = $this->rows("SELECT u.id, u.name, r.name role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug IN ('super_admin','editor','reporter') ORDER BY u.name");
        $this->screen('article-editor', ['title' => $id ? 'Edit story' : 'New story', 'article' => $article, 'error' => $error, 'media' => $this->rows('SELECT * FROM media ORDER BY id DESC'), 'revisions' => $revisions, 'authors' => $authors, 'userLangs' => $userLangs]);
    }
    public function restore(string $id): void
    {
        $revision = $this->one('SELECT * FROM article_revisions WHERE id=?', [$id]);
        if (!$revision)
            $this->abort(404, 'Revision not found');
        $current = $this->one('SELECT * FROM articles WHERE id=?', [$revision['article_id']]);
        if (!$current)
            $this->abort(404, 'Story not found');
        $this->articleOwner($current);
        $snapshot = json_decode($revision['snapshot'], true);
        $data = [];
        foreach (['title_en', 'title_si', 'title_ta', 'summary_en', 'summary_si', 'summary_ta', 'content_en', 'content_si', 'content_ta', 'tags', 'video_url', 'gallery_ids'] as $key)
            if (array_key_exists($key, $snapshot))
                $data[$key] = str_starts_with($key, 'content_') ? rich_html($snapshot[$key] ?? '') : $snapshot[$key];
        if (!empty($snapshot['category_id']) && $this->one("SELECT id FROM categories WHERE id=? AND status='active'", [(int) $snapshot['category_id']])) {
            $data['category_id'] = (int) $snapshot['category_id'];
        }
        if (!empty($snapshot['featured_image_id']) && $this->one("SELECT id FROM media WHERE id=?", [(int) $snapshot['featured_image_id']])) {
            $data['featured_image_id'] = (int) $snapshot['featured_image_id'];
        }
        if (!empty($snapshot['video_media_id']) && $this->one("SELECT id FROM media WHERE id=?", [(int) $snapshot['video_media_id']])) {
            $data['video_media_id'] = (int) $snapshot['video_media_id'];
        }
        // Restore to draft; preserve identifiers, author, current media references and metrics.
        $data += ['status' => 'draft', 'published_at' => null, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->beginTransaction();
        try {
            $this->save('article_revisions', ['article_id' => $current['id'], 'user_id' => $this->user['id'], 'snapshot' => json_encode($current, JSON_UNESCAPED_UNICODE), 'created_at' => date('Y-m-d H:i:s')]);
            $this->save('articles', $data, (int) $current['id']);
            $this->audit('Restored revision ' . $id, 'articles', (int) $current['id']);
            $this->db->commit();
            $this->clearPublicCaches();
        } catch (\Throwable $ex) {
            $this->db->rollBack();
            throw $ex;
        }
        flash('Revision restored as a draft. Review it before publishing.');
        $this->redirect('/admin/articles/edit/' . $current['id']);
    }
    public function archive(string $id): void
    {
        $article = $this->one('SELECT * FROM articles WHERE id=?', [$id]);
        if (!$article)
            $this->abort(404, 'Story not found');
        $this->articleOwner($article);
        $this->save('articles', ['status' => 'archived'], (int) $id);
        $this->audit('Archived', 'articles', (int) $id);
        $this->clearPublicCaches();
        flash('Story archived.');
        $this->redirect('/admin/articles');
    }
    public function deleteArticle(string $id): void
    {
        $this->manager();
        $article = $this->one('SELECT * FROM articles WHERE id=?', [$id]);
        if (!$article)
            $this->abort(404, 'Story not found');
        $this->db->beginTransaction();
        try {
            $this->run('DELETE FROM article_revisions WHERE article_id=?', [$id]);
            try {
                $this->run('DELETE FROM comments WHERE article_id=?', [$id]);
            } catch (\Throwable) {
            }
            $this->run('DELETE FROM articles WHERE id=?', [$id]);
            $this->audit('Deleted', 'articles', (int) $id);
            $this->db->commit();
            $this->clearPublicCaches();
        } catch (\Throwable $ex) {
            $this->db->rollBack();
            throw $ex;
        }
        flash('Story permanently deleted.');
        $this->redirect('/admin/articles');
    }
    public function media(): void
    {
        $error = '';
        if ($this->post() && ($_POST['action'] ?? '') === 'seed_sample_media') {
            $inWebApp = true;
            ob_start();
            require dirname(__DIR__, 2) . '/bin/demo-media.php';
            ob_end_clean();
            $this->audit('Populated editorial media samples', 'media', 0);
            flash('Successfully seeded high-resolution editorial photography and whitepaper media assets!');
            $this->redirect('/admin/media');
            return;
        }
        if ($this->post()) {
            try {
                $file = Upload::store($_FILES['file'] ?? []);
                $id = $this->save('media', $file + ['uploader_id' => $this->user['id'], 'alt_text' => $this->text('alt_text'), 'caption' => $this->text('caption', 2000)]);
                $this->audit('Uploaded', 'media', $id);
                flash('Media uploaded successfully. Images are automatically optimized and converted to WebP.');
                $this->redirect('/admin/media');
                return;
            } catch (\InvalidArgumentException $ex) {
                $error = $ex->getMessage();
            }
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * 24;

        $where = "1=1";
        $params = [];
        $type = trim($_GET['type'] ?? '');
        if ($type === 'image') {
            $where .= " AND m.file_type LIKE 'image/%'";
        } elseif ($type === 'video') {
            $where .= " AND m.file_type LIKE 'video/%'";
        } elseif ($type === 'pdf' || $type === 'document') {
            $where .= " AND (m.file_type LIKE 'application/%' OR (m.file_type NOT LIKE 'image/%' AND m.file_type NOT LIKE 'video/%'))";
        }

        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $where .= " AND (m.file_name LIKE ? OR m.alt_text LIKE ? OR m.caption LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        $items = $this->rows("SELECT m.*, u.name as uploader_name, u.email as uploader_email FROM media m LEFT JOIN users u ON u.id = m.uploader_id WHERE $where ORDER BY m.id DESC LIMIT 25 OFFSET $offset", $params);

        $stats = [
            'total' => (int)$this->one("SELECT COUNT(*) as n FROM media")['n'],
            'images' => (int)$this->one("SELECT COUNT(*) as n FROM media WHERE file_type LIKE 'image/%'")['n'],
            'videos' => (int)$this->one("SELECT COUNT(*) as n FROM media WHERE file_type LIKE 'video/%'")['n'],
            'documents' => (int)$this->one("SELECT COUNT(*) as n FROM media WHERE file_type LIKE 'application/%' OR (file_type NOT LIKE 'image/%' AND file_type NOT LIKE 'video/%')")['n'],
        ];

        $this->screen('media', [
            'items' => array_slice($items, 0, 24),
            'page' => $page,
            'more' => count($items) > 24,
            'error' => $error,
            'stats' => $stats,
            'filterType' => $type,
            'searchQuery' => $q
        ]);
    }
    public function deleteMedia(string $id): void
    {
        $this->manager();
        $media = $this->one('SELECT * FROM media WHERE id=?', [$id]);
        if (!$media)
            $this->abort(404, 'Media file not found');
        $usedAsFeatured = $this->one('SELECT id FROM articles WHERE featured_image_id=?', [$id]);
        $usedAsVideo = $this->one('SELECT id FROM articles WHERE video_media_id=?', [$id]);
        $usedInGallery = $this->one("SELECT id FROM articles WHERE gallery_ids LIKE ?", ['%' . $id . '%']);
        $usedInAds = $this->one("SELECT id FROM advertisements WHERE image_path=?", [$media['file_path']]);
        if ($usedAsFeatured || $usedAsVideo || $usedInGallery || $usedInAds) {
            flash('Cannot delete media: file is currently referenced in an article or advertisement.');
            $this->redirect('/admin/media');
            return;
        }
        $this->db->beginTransaction();
        try {
            $this->run('DELETE FROM media WHERE id=?', [$id]);
            $this->audit('Deleted', 'media', (int) $id);
            $this->db->commit();
            $absPath = dirname(__DIR__, 2) . '/public/' . ltrim($media['file_path'], '/');
            if (str_starts_with($media['file_path'], 'uploads/') && is_file($absPath))
                @unlink($absPath);
        } catch (\Throwable $ex) {
            $this->db->rollBack();
            throw $ex;
        }
        flash('Media file deleted.');
        $this->redirect('/admin/media');
    }
    public function categories(): void
    {
        $this->manager();
        $error = '';
        $edit = isset($_GET['edit']) ? $this->one('SELECT * FROM categories WHERE id=?', [(int) $_GET['edit']]) : [];
        if ($this->post()) {
            $id = (int) ($_POST['id'] ?? 0);
            $data = [];
            foreach (['en', 'si', 'ta'] as $language)
                $data['name_' . $language] = $this->text('name_' . $language);
            if (!$data['name_en'] || !$data['name_si'] || !$data['name_ta'])
                $error = 'Enter the category name in all three languages.';
            if ($id && !$this->one('SELECT id FROM categories WHERE id=?', [$id]))
                $error = 'Category not found.';
            $data += ['slug' => $this->uniqueSlug('categories', $this->text('slug') ?: $data['name_en'], $id ?: null), 'order_index' => (int) ($_POST['order_index'] ?? 0), 'status' => isset($_POST['active']) ? 'active' : 'inactive'];
            if (!$error) {
                $saved = $this->save('categories', $data, $id ?: null);
                $this->audit('Saved', 'categories', $saved);
                $this->clearPublicCaches();
                flash('Category saved.');
                $this->redirect('/admin/categories');
            }
            $edit = $data + ['id' => $id];
        }
        $this->screen('categories', ['items' => $this->rows('SELECT * FROM categories ORDER BY order_index,id'), 'edit' => $edit ?: [], 'error' => $error]);
    }
    public function deleteCategory(string $id): void
    {
        $this->manager();
        $category = $this->one('SELECT * FROM categories WHERE id=?', [$id]);
        if (!$category)
            $this->abort(404, 'Category not found');
        $hasArticles = $this->one('SELECT id FROM articles WHERE category_id=? LIMIT 1', [$id]);
        if ($hasArticles) {
            flash('Cannot delete category: it is assigned to existing articles. Move them or set status to inactive.');
            $this->redirect('/admin/categories');
            return;
        }
        $this->run('DELETE FROM categories WHERE id=?', [$id]);
        $this->audit('Deleted', 'categories', (int) $id);
        $this->clearPublicCaches();
        flash('Category deleted.');
        $this->redirect('/admin/categories');
    }
    public function classifieds(): void
    {
        $this->manager();
        $error = '';
        if ($this->post()) {
            $id = (int) ($_POST['id'] ?? 0);
            $item = $this->one('SELECT * FROM classifieds WHERE id=?', [$id]);
            $status = $this->text('status', 20);
            if (!$item || !in_array($status, ['approved', 'rejected', 'suspended', 'withdrawn', 'pending'], true))
                $error = 'Choose an advertisement and valid review decision.';
            else {
                $paid = $this->one("SELECT id FROM payments WHERE classified_id=? AND status='paid'", [$id]);
                $premium = isset($_POST['is_premium']) && $paid && $status === 'approved';
                if (isset($_POST['is_premium']) && !$paid)
                    $error = 'Record a confirmed payment before enabling premium placement.';
                else {
                    $expiresAt = $status === 'approved' ? date('Y-m-d H:i:s', time() + 30 * 86400) : $item['expires_at'];
                    $this->save('classifieds', ['status' => $status, 'moderation_note' => $this->text('moderation_note', 2000), 'is_premium' => $premium ? 1 : 0, 'expires_at' => $expiresAt, 'updated_at' => date('Y-m-d H:i:s')], $id);
                    $this->audit('Review: ' . $status, 'classifieds', $id);
                    flash('Review saved. The owner can see the decision in their account.');
                    $this->redirect('/admin/classifieds');
                }
            }
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * 20;
        $items = $this->rows("SELECT c.*,u.name owner_name,u.email owner_email,u.status owner_status,u.created_at owner_registered_at FROM classifieds c JOIN users u ON u.id=c.user_id ORDER BY CASE WHEN c.status='pending' THEN 0 ELSE 1 END,c.id DESC LIMIT 21 OFFSET $offset");
        $this->screen('classifieds', ['items' => array_slice($items, 0, 20), 'page' => $page, 'more' => count($items) > 20, 'error' => $error]);
    }
    public function advertisements(): void
    {
        $this->manager();
        $error = '';
        $edit = isset($_GET['edit']) ? $this->one('SELECT * FROM advertisements WHERE id=?', [(int) $_GET['edit']]) : [];
        $positions = \App\Helpers\AdHelper::placements();
        $formats = \App\Helpers\AdHelper::formats();
        if ($this->post() && ($_POST['action'] ?? '') === 'seed_sample_ads') {
            $count = \App\Helpers\AdHelper::seedSampleAds();
            $this->audit('Populated sample advertisements', 'advertisements', 0);
            flash("Populated sample advertisements for all 11 placements! ($count new ads added)");
            $this->redirect('/admin/advertisements');
        }
        if ($this->post() && ($_POST['action'] ?? '') === 'toggle') {
            $id = (int) ($_POST['id'] ?? 0);
            $campaign = $this->one('SELECT * FROM advertisements WHERE id=?', [$id]);
            if (!$campaign)
                $this->abort(404, 'Advertisement not found');
            $this->save('advertisements', ['is_active' => $campaign['is_active'] ? 0 : 1], $id);
            $this->audit($campaign['is_active'] ? 'Paused advertisement' : 'Enabled advertisement', 'advertisements', $id);
            \App\Helpers\AdHelper::clearCache();
            flash($campaign['is_active'] ? 'Advertisement paused.' : 'Advertisement enabled. Its publication dates still apply.');
            $this->redirect('/admin/advertisements');
        }
        $presetPlacement = $_GET['placement'] ?? '';
        if (!$edit && $presetPlacement && isset($positions[$presetPlacement])) {
            $edit = ['position' => $presetPlacement];
        }
        if ($this->post()) {
            $id = (int) ($_POST['id'] ?? 0);
            $old = $id ? $this->one('SELECT * FROM advertisements WHERE id=?', [$id]) : [];
            $format = $this->text('ad_format', 20) ?: 'image';
            $data = ['title' => $this->text('title', 100), 'position' => $this->text('position', 50), 'ad_format' => $format, 'advertiser' => $this->text('advertiser', 150) ?: null, 'ad_text' => $this->text('ad_text', 3000) ?: null, 'cta_label' => $this->text('cta_label', 80) ?: null, 'url' => $this->text('url'), 'start_date' => $this->text('start_date', 10) ?: null, 'end_date' => $this->text('end_date', 10) ?: null, 'priority' => max(1, min(100, (int) ($_POST['priority'] ?? 1))), 'is_active' => isset($_POST['is_active']) ? 1 : 0, 'image_path' => $old['image_path'] ?? null, 'ad_code' => null];
            if ($format === 'html' && $this->user['role'] === 'super_admin')
                $data['ad_code'] = $this->text('ad_code', 25000) ?: null;
            if (!$data['title'] || !isset($positions[$data['position']]) || ($id && !$old))
                $error = 'Enter a title and valid placement.';
            elseif (!isset($formats[$format]))
                $error = 'Choose a valid advertisement format.';
            elseif ($format === 'html' && $this->user['role'] !== 'super_admin')
                $error = 'Only a super administrator can publish advertisement code.';
            if ($data['url'] && !safe_link($data['url']))
                $error = 'Advertisement links must be valid HTTP or HTTPS URLs.';
            foreach (['start_date', 'end_date'] as $key)
                if ($data[$key] && (!strtotime($data[$key]) || date('Y-m-d', strtotime($data[$key])) !== $data[$key]))
                    $error = 'Enter valid dates.';
            if ($data['start_date'] && $data['end_date'] && $data['end_date'] < $data['start_date'])
                $error = 'End date must be after the start date.';
            if (!$error) {
                try {
                    $uploaded = null;
                    if (($_FILES['banner']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        if (!in_array($format, ['image', 'native', 'video'], true))
                            throw new \InvalidArgumentException('This format does not use an uploaded file.');
                        $uploaded = Upload::store($_FILES['banner'], $format !== 'video', true);
                        $path = $uploaded['file_path'];
                        $isVideo = (bool) preg_match('/\.(mp4|webm)$/i', $path);
                        if (($format === 'video') !== $isVideo) {
                            Upload::discardNew([$path]);
                            throw new \InvalidArgumentException($format === 'video' ? 'Upload an MP4 or WebM video.' : 'Upload a JPG, PNG, WebP or GIF image.');
                        }
                        $data['image_path'] = $path;
                    }
                    if ($format === 'html')
                        $data['image_path'] = null;
                    if ($format === 'text')
                        $data['image_path'] = null;
                    if ($format === 'video' && (!$data['image_path'] || !preg_match('/\.(mp4|webm)$/i', $data['image_path'])))
                        $error = 'Upload an MP4 or WebM video.';
                    if ($format === 'image' && (!$data['image_path'] || !preg_match('/\.(jpe?g|png|webp|gif)$/i', $data['image_path'])))
                        $error = 'Upload a JPG, PNG, WebP or GIF image.';
                    if ($format === 'native' && $data['image_path'] && !preg_match('/\.(jpe?g|png|webp|gif)$/i', $data['image_path']))
                        $error = 'Upload a JPG, PNG, WebP or GIF image.';
                    if (in_array($format, ['text', 'native'], true) && !$data['ad_text'])
                        $error = 'Enter advertisement text.';
                    if ($format === 'html' && !$data['ad_code'])
                        $error = 'Enter AdSense or trusted advertisement code.';
                    if ($error && $uploaded)
                        Upload::discardNew([$uploaded['file_path']]);
                    if (!$error) {
                        $saved = $this->save('advertisements', $data, $id ?: null);
                        $this->audit($id ? 'Updated advertisement' : 'Created advertisement', 'advertisements', $saved);
                        \App\Helpers\AdHelper::clearCache();
                        flash('Advertisement saved. Active campaigns rotate within each placement.');
                        $this->redirect('/admin/advertisements');
                    }
                } catch (\InvalidArgumentException $ex) {
                    $error = $ex->getMessage();
                }
            }
            $edit = $data + ['id' => $id];
        }
        $placementCounts = \App\Helpers\AdHelper::activeCounts();
        $placementSpecs = \App\Helpers\AdHelper::placementSpecs();
        $this->screen('advertisements', ['items' => $this->rows('SELECT * FROM advertisements ORDER BY id DESC'), 'edit' => $edit ?: [], 'positions' => $positions, 'formats' => $formats, 'placementCounts' => $placementCounts, 'placementSpecs' => $placementSpecs, 'error' => $error]);
    }
    public function users(): void
    {
        $this->admin();
        $error = '';
        $edit = isset($_GET['edit']) ? $this->one('SELECT * FROM users WHERE id=?', [(int) $_GET['edit']]) : [];
        $roles = $this->rows('SELECT * FROM roles ORDER BY id');
        if ($this->post()) {
            $id = (int) ($_POST['id'] ?? 0);
            $oldUser = $id ? $this->one('SELECT * FROM users WHERE id=?', [$id]) : null;
            $roleId = (int) ($_POST['role_id'] ?? 0);
            $selectedLangs = (array) ($_POST['allowed_languages'] ?? []);
            $validLangs = array_values(array_intersect($selectedLangs, ['en', 'si', 'ta']));

            $data = [
                'name' => $this->text('name', 100),
                'email' => strtolower($this->text('email')),
                'role_id' => $roleId,
                'status' => isset($_POST['active']) ? 'active' : 'inactive',
                'allowed_languages' => !empty($validLangs) ? implode(',', $validLangs) : 'en,si,ta'
            ];
            $password = (string) ($_POST['password'] ?? '');

            // For journalists/writers, require at least one language selection
            $roleRow = $this->one('SELECT slug FROM roles WHERE id=?', [$roleId]);
            $isReporter = ($roleRow['slug'] ?? '') === 'reporter';
            if ($isReporter && empty($validLangs)) {
                $error = 'Select at least one writing language for this article writer.';
            }

            if (!$error) {
                if (!$data['name'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || !in_array($data['role_id'], array_column($roles, 'id')))
                    $error = 'Enter a name, email and valid role.';
                elseif ($id && !$oldUser)
                    $error = 'User not found.';
                elseif ($this->one('SELECT id FROM users WHERE email=? AND id<>?', [$data['email'], $id]))
                    $error = 'This email is already registered.';
                elseif ((!$id || $password) && (strlen($password) < 12 || strlen($password) > 72))
                    $error = 'Passwords must contain 12–72 characters.';
                elseif ($id == $this->user['id'] && ($data['role_id'] != $this->user['role_id'] || $data['status'] !== 'active'))
                    $error = 'You cannot disable or demote your own administrator account.';
            }

            if (!$error) {
                if ($password)
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                $saved = $this->save('users', $data, $id ?: null);
                if (!$id) {
                    $auditAction = 'Created user';
                } else {
                    $changes = [];
                    if ($oldUser['role_id'] != $data['role_id']) {
                        $oldRoleName = $this->one('SELECT name FROM roles WHERE id=?', [$oldUser['role_id']])['name'] ?? 'Role ' . $oldUser['role_id'];
                        $newRoleName = $this->one('SELECT name FROM roles WHERE id=?', [$data['role_id']])['name'] ?? 'Role ' . $data['role_id'];
                        $changes[] = "Role changed ({$oldRoleName} -> {$newRoleName})";
                    }
                    if ($oldUser['status'] !== $data['status']) {
                        $changes[] = "Account status changed ({$oldUser['status']} -> {$data['status']})";
                    }
                    if (($oldUser['allowed_languages'] ?? '') !== $data['allowed_languages']) {
                        $changes[] = "Languages changed ({$oldUser['allowed_languages']} -> {$data['allowed_languages']})";
                    }
                    $auditAction = $changes ? implode('; ', $changes) : 'Updated user';
                }
                $this->audit($auditAction, 'users', $saved);
                flash('User saved.');
                $this->redirect('/admin/users');
            }
            $edit = $data + ['id' => $id];
        }
        $this->screen('users', ['items' => $this->rows('SELECT u.*,r.name role_name, r.slug role_slug FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC'), 'edit' => $edit ?: [], 'roles' => $roles, 'error' => $error]);
    }
    public function settings(): void
    {
        $this->admin();
        $error = '';
        $edit = [];
        if ($this->post()) {
            $data = ['site_name' => $this->text('site_name', 100), 'contact_email' => $this->text('contact_email'), 'adsense_id' => $this->text('adsense_id', 40), 'ga_id' => $this->text('ga_id', 40), 'gtm_id' => $this->text('gtm_id', 40)];
            foreach (['en', 'si', 'ta'] as $language)
                $data['language_' . $language] = isset($_POST['language_' . $language]) ? '1' : '0';
            $edit = $data;
            if (!$data['site_name'])
                $error = 'Enter a publication name.';
            elseif ($data['contact_email'] && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL))
                $error = 'Enter a valid contact email.';
            elseif (!in_array('1', [$data['language_en'], $data['language_si'], $data['language_ta']], true))
                $error = 'Keep at least one language enabled.';
            elseif ($data['adsense_id'] && !preg_match('/^ca-pub-\d{10,20}$/D', $data['adsense_id']))
                $error = 'Google AdSense Publisher IDs start with ca-pub- followed by numbers.';
            elseif ($data['ga_id'] && !preg_match('/^G-[A-Z0-9]+$/D', $data['ga_id']))
                $error = 'Google Analytics IDs start with G-.';
            elseif ($data['gtm_id'] && !preg_match('/^GTM-[A-Z0-9]+$/D', $data['gtm_id']))
                $error = 'Google Tag Manager IDs start with GTM-.';
            else {
                foreach ($data as $key => $value) {
                    $exists = $this->one('SELECT id FROM settings WHERE setting_key=?', [$key]);
                    if ($exists) {
                        $this->run('UPDATE settings SET setting_value=? WHERE setting_key=?', [$value, $key]);
                    } else {
                        $this->run('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)', [$key, $value]);
                    }
                }
                $this->audit('Updated site settings', 'settings');
                \App\Core\Cache::delete('portal_settings');
                flash('Settings saved.');
                $this->redirect('/admin/settings');
            }
        }
        $this->screen('settings', ['error' => $error, 'edit' => $edit]);
    }
    public function menus(): void
    {
        $this->admin();
        $error = '';
        $edit = isset($_GET['edit']) ? $this->one('SELECT * FROM menus WHERE id=?', [(int) $_GET['edit']]) : [];
        if ($this->post()) {
            $id = (int) ($_POST['id'] ?? 0);
            $data = [];
            foreach (['en', 'si', 'ta'] as $language)
                $data['name_' . $language] = $this->text('name_' . $language, 100);
            $data += ['url' => $this->text('url'), 'order_index' => (int) ($_POST['order_index'] ?? 0), 'is_active' => isset($_POST['active']) ? 1 : 0];
            if (!safe_link($data['url'], true) || !$data['name_en'] || !$data['name_si'] || !$data['name_ta'])
                $error = 'Enter three language labels and a relative path or HTTP(S) link.';
            elseif ($id && !$this->one('SELECT id FROM menus WHERE id=?', [$id]))
                $error = 'Menu item not found.';
            else {
                $saved = $this->save('menus', $data, $id ?: null);
                $this->audit('Saved', 'menus', $saved);
                \App\Core\Cache::delete('portal_menus');
                flash('Menu saved.');
                $this->redirect('/admin/menus');
            }
            $edit = $data + ['id' => $id];
        }
        $this->screen('menus', ['items' => $this->rows('SELECT * FROM menus ORDER BY order_index,id'), 'edit' => $edit ?: [], 'error' => $error]);
    }
    public function payments(): void
    {
        $this->admin();
        $error = '';
        if ($this->post()) {
            $id = (int) ($_POST['classified_id'] ?? 0);
            $amount = $_POST['amount'] ?? '';
            $reference = $this->text('reference', 150);
            $status = $this->text('status', 20);
            if (!$this->one('SELECT id FROM classifieds WHERE id=?', [$id]) || !is_numeric($amount) || $amount <= 0 || $amount > 9999999999.99 || !$reference || !in_array($status, ['pending', 'paid', 'refunded'], true))
                $error = 'Enter a valid advertisement, positive amount, reference and status.';
            else {
                $old = $this->one('SELECT * FROM payments WHERE reference=?', [$reference]);
                if ($old && ($old['classified_id'] != $id || (float) $old['amount'] != (float) $amount))
                    $error = 'This reference belongs to a different payment. Use its original advertisement and amount.';
                else {
                    $data = ['classified_id' => $id, 'amount' => round((float) $amount, 2), 'currency' => 'LKR', 'reference' => $reference, 'status' => $status, 'recorded_by' => $this->user['id'], 'created_at' => $old['created_at'] ?? date('Y-m-d H:i:s')];
                    $this->db->beginTransaction();
                    try {
                        $saved = $this->save('payments', $data, $old ? (int) $old['id'] : null);
                        if (!$this->one("SELECT id FROM payments WHERE classified_id=? AND status='paid'", [$id]))
                            $this->run('UPDATE classifieds SET is_premium=0 WHERE id=?', [$id]);
                        $this->audit('Payment ' . $status, 'payments', $saved);
                        $this->db->commit();
                    } catch (\Throwable $ex) {
                        $this->db->rollBack();
                        throw $ex;
                    }
                    flash('Payment record saved. This records an external payment; no money was charged.');
                    $this->redirect('/admin/payments');
                }
            }
        }
        $this->screen('payments', ['items' => $this->rows('SELECT p.*,c.title FROM payments p JOIN classifieds c ON c.id=p.classified_id ORDER BY p.id DESC'), 'classifiedChoices' => $this->rows('SELECT id,title FROM classifieds ORDER BY id DESC'), 'error' => $error]);
    }
    public function reports(): void
    {
        $this->manager();
        $stats = $this->rows('SELECT status,COUNT(*) total,COALESCE(SUM(views),0) views FROM articles GROUP BY status');
        $ads = $this->rows('SELECT status,COUNT(*) total FROM classifieds GROUP BY status');
        $payments = $this->user['role'] === 'super_admin' ? $this->rows('SELECT status,SUM(amount) total FROM payments GROUP BY status') : [];
        $this->screen('reports', ['stats' => $stats, 'adStats' => $ads, 'paymentStats' => $payments, 'top' => $this->rows('SELECT * FROM articles ORDER BY views DESC LIMIT 10')]);
    }
    public function logs(): void
    {
        $this->admin();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * 50;
        $items = $this->rows("SELECT l.*,u.name FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.id DESC LIMIT 51 OFFSET $offset");
        $this->screen('activity-logs', ['items' => array_slice($items, 0, 50), 'page' => $page, 'more' => count($items) > 50]);
    }
}
