<?php
namespace App\Core;
use PDO;
class Portal extends Controller {
    protected PDO $db;
    protected ?array $user = null;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (!empty($_SESSION['user_id'])) {
            $this->user = $this->one('SELECT u.*, r.slug AS role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND u.status=?', [$_SESSION['user_id'],'active']) ?: null;
            if (!$this->user) unset($_SESSION['user_id']);
        }
        if (setting('language_' . lang(), '1') !== '1') {
            foreach (['en','si','ta'] as $language) if (setting('language_'.$language,'1') === '1') { $_SESSION['language']=$language; break; }
        }
    }
    protected function rows(string $sql, array $params=[]): array { $s=$this->db->prepare($sql); $s->execute($params); return $s->fetchAll(); }
    protected function one(string $sql, array $params=[]) { return $this->rows($sql,$params)[0] ?? false; }
    protected function run(string $sql, array $params=[]): void { $s=$this->db->prepare($sql); $s->execute($params); }
    protected function save(string $table, array $data, ?int $id=null): int {
        if (in_array($table,['users','articles'],true)) $data['updated_at']=date('Y-m-d H:i:s');
        if (!$id && in_array($table,['users','articles','media'],true)) $data['created_at']=date('Y-m-d H:i:s');
        if ($id) { $set=implode(',', array_map(fn($k)=>"$k=?", array_keys($data))); $this->run("UPDATE $table SET $set WHERE id=?", [...array_values($data),$id]); return $id; }
        $keys=implode(',',array_keys($data)); $marks=implode(',',array_fill(0,count($data),'?'));
        $this->run("INSERT INTO $table ($keys) VALUES ($marks)",array_values($data)); return (int)$this->db->lastInsertId();
    }
    protected function requireUser(): array {
        if (!$this->user) $this->redirect('/account/login');
        return $this->user;
    }
    protected function requireRole(array $roles): void {
        $this->requireUser();
        if (!in_array($this->user['role'], $roles, true)) $this->abort(403,'You do not have access to this area.');
    }
    protected function abort(int $status, string $message): never { http_response_code($status); $this->page('message',['title'=>$message]); exit; }
    protected function publicCategories(): array {
        return Cache::remember('portal_categories', 600, function() {
            $groups=[];
            foreach ($this->rows("SELECT * FROM categories WHERE status='active' ORDER BY order_index,id") as $category) {
                // Legacy installations can have multiple slugs for the same desk.
                $key=mb_strtolower(trim(preg_replace('/\s+/u',' ', $category['name_en'])));
                if ($key==='') $key='slug:'.$category['slug'];
                if (!isset($groups[$key])) $groups[$key]=$category + ['category_ids'=>[], 'category_slugs'=>[]];
                $groups[$key]['category_ids'][]=(int)$category['id'];
                $groups[$key]['category_slugs'][]=$category['slug'];
            }
            return array_values($groups);
        });
    }
    protected function publicMenus(): array {
        return Cache::remember('portal_menus', 600, function() {
            return $this->rows('SELECT * FROM menus WHERE is_active=1 ORDER BY order_index,id');
        });
    }
    protected function page(string $page, array $data=[]): void {
        $data += ['user'=>$this->user,'categories'=>$this->publicCategories(),'menus'=>$this->publicMenus()];
        $this->view('portal/'.$page, $data);
    }
    protected function audit(string $action, string $entity, ?int $id=null): void {
        $this->save('activity_logs',['user_id'=>$this->user['id'] ?? null,'action'=>$action,'entity'=>$entity,'entity_id'=>$id,'ip_address'=>$_SERVER['REMOTE_ADDR'] ?? 'cli','created_at'=>date('Y-m-d H:i:s')]);
        Logger::info("Audit: {$action} on {$entity} #" . ($id ?? 0), ['user_id' => $this->user['id'] ?? null]);
    }
    protected function post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
    protected function text(string $key, ?int $max = null): string {
        $val = trim((string)($_POST[$key] ?? ($_GET[$key] ?? '')));
        return $max !== null ? mb_substr($val, 0, $max) : $val;
    }
    protected function uniqueSlug(string $table, string $slug, ?int $id=null): string {
        $clean = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/','-',$slug),'-'));
        if (!$clean) {
            $clean = 'story-' . date('Ymd') . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
        }
        $base = substr($clean, 0, 180); 
        $slug = $base; 
        $n = 2;
        while ($this->one("SELECT id FROM $table WHERE slug=? AND id<>?", [$slug, $id ?? 0])) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }
}
