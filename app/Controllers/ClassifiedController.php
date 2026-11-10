<?php
namespace App\Controllers;
use App\Core\Portal;
use App\Core\Upload;
class ClassifiedController extends Portal {
    public function index(): void {
        $where="c.status='approved' AND c.expires_at>? AND c.language=?"; $params=[date('Y-m-d H:i:s'),lang()];
        foreach (['q'=>['(c.title LIKE ? OR c.description LIKE ?)','search'],'location'=>['c.location LIKE ?','search'],'category'=>['cc.slug=?','exact']] as $key=>[$sql,$kind]) {
            $value=mb_substr(trim($_GET[$key] ?? ''),0,150);
            if ($value!=='') { $where.=' AND '.$sql; $params[]=$kind==='search' ? '%'.$value.'%' : $value; if ($key==='q') $params[]='%'.$value.'%'; }
        }
        foreach (['min'=>'>=','max'=>'<='] as $key=>$operator) if (isset($_GET[$key]) && is_numeric($_GET[$key]) && $_GET[$key]>=0) { $where.=" AND c.price $operator ?"; $params[]=$_GET[$key]; }
        $order=($_GET['sort'] ?? '')==='price' ? 'c.price ASC,c.id DESC' : 'c.is_premium DESC,c.created_at DESC,c.id DESC';
        $page=max(1,min(100000,(int)($_GET['page'] ?? 1))); $offset=($page-1)*12;
        $items=$this->rows("SELECT c.*,cc.name_".lang()." AS category_name FROM classifieds c JOIN classified_categories cc ON cc.id=c.category_id WHERE $where ORDER BY $order LIMIT 13 OFFSET $offset",$params);
        $categories = \App\Core\Cache::remember('portal_classified_categories', 600, fn() => $this->rows('SELECT * FROM classified_categories ORDER BY id'));
        $this->page('classifieds',['title'=>t('Classifieds'),'items'=>array_slice($items,0,12),'classifiedCategories'=>$categories,'page'=>$page,'more'=>count($items)>12]);
    }
    public function show(string $id): void {
        $item=$this->one("SELECT c.*,cc.name_".lang()." AS category_name FROM classifieds c JOIN classified_categories cc ON cc.id=c.category_id WHERE c.id=? AND c.status='approved' AND c.expires_at>?",[$id,date('Y-m-d H:i:s')]);
        if (!$item) $this->abort(404,'This advertisement is unavailable or has expired.');
        $favorite=$this->user ? $this->one('SELECT id FROM favorites WHERE user_id=? AND classified_id=?',[$this->user['id'],$id]) : false;
        $this->page('classified',['title'=>$item['title'],'item'=>$item,'favorite'=>$favorite]);
    }
    public function edit(?string $id=null): void {
        $this->requireUser(); $error=''; $item=[];
        if ($id) {
            $item=$this->one('SELECT * FROM classifieds WHERE id=? AND user_id=?',[$id,$this->user['id']]);
            if (!$item) $this->abort(404,'Advertisement not found');
        }
        $choices=$this->rows('SELECT * FROM classified_categories ORDER BY id');
        if ($this->post()) {
            $bucket = hash('sha256', 'classified_submit:' . $this->user['id']);
            $cutoff = date('Y-m-d H:i:s', time() - 3600);
            if (!$id && $this->one('SELECT COUNT(*) AS n FROM login_attempts WHERE bucket=? AND attempted_at>=?', [$bucket, $cutoff])['n'] >= 15) {
                \App\Core\Logger::security("Classified submission rate limit reached", ['user_id' => $this->user['id']]);
                http_response_code(429);
                $error = 'Submission limit reached (maximum 15 listings per hour). Please try again later.';
            } else {
                $data=['category_id'=>(int)($_POST['category_id'] ?? 0),'language'=>$this->text('language',2),'title'=>$this->text('title',200),'description'=>$this->text('description',15000),'location'=>$this->text('location',150),'price'=>($_POST['price'] ?? '')==='' ? null : $_POST['price'],'contact_name'=>$this->text('contact_name',150),'contact_email'=>$this->text('contact_email'),'contact_phone'=>$this->text('contact_phone',40),'premium_requested'=>isset($_POST['premium_requested']) ? 1 : 0];
                if (!$data['title'] || !$data['description'] || !$data['location'] || !$data['contact_name'] || !filter_var($data['contact_email'],FILTER_VALIDATE_EMAIL) || !preg_match('/^[+()\d\s-]{7,40}$/D',$data['contact_phone'])) $error='Complete the title, description, location and valid contact details.';
                elseif (!in_array($data['category_id'],array_column($choices,'id')) || !in_array($data['language'],['en','si','ta'],true)) $error='Choose a valid category and language.';
                elseif ($data['price']!==null && (!is_numeric($data['price']) || $data['price']<0 || $data['price']>9999999999.99)) $error='Enter a valid non-negative price.';
                else {
                    $images=isset($_POST['remove_images']) ? [] : (json_decode($item['images'] ?? '[]',true) ?: []);
                    $newImages=[];
                    try {
                        $files=$_FILES['images'] ?? null;
                        $count=$files ? count(array_filter($files['error'],fn($e)=>$e!==UPLOAD_ERR_NO_FILE)) : 0;
                        if (count($images)+$count>8) throw new \InvalidArgumentException('Use a maximum of eight images.');
                        for ($i=0;$files && $i<count($files['name']);$i++) {
                            if ($files['error'][$i]===UPLOAD_ERR_NO_FILE) continue;
                            $file=[]; foreach (['name','type','tmp_name','error','size'] as $key) $file[$key]=$files[$key][$i];
                            $path=Upload::store($file,true)['file_path'];
                            $images[]=$path; $newImages[]=$path;
                        }
                        $data+=['images'=>json_encode($images),'status'=>'pending','is_premium'=>0,'moderation_note'=>null,'updated_at'=>date('Y-m-d H:i:s')];
                        if (!$id) {
                            $data+=['user_id'=>$this->user['id'],'created_at'=>date('Y-m-d H:i:s'),'expires_at'=>date('Y-m-d H:i:s',time()+30*86400)];
                            $this->save('login_attempts', ['bucket' => $bucket, 'attempted_at' => date('Y-m-d H:i:s')]);
                        }
                        $saved=$this->save('classifieds',$data,$id ? (int)$id : null);
                        $this->audit($id ? 'Updated; awaiting review' : 'Submitted','classifieds',$saved);
                        flash('Advertisement submitted for review. Changes become public after approval.'); $this->redirect('/account');
                    } catch (\InvalidArgumentException $ex) { Upload::discardNew($newImages); $error=$ex->getMessage(); }
                    catch (\Throwable $ex) { Upload::discardNew($newImages); throw $ex; }
                }
                $item=array_merge($item,$data);
            }
        }
        $this->page('classified-form',['title'=>$id ? 'Edit classified listing' : 'Post a classified','item'=>$item,'error'=>$error,'classifiedCategories'=>$choices]);
    }
    public function renew(string $id): void {
        $this->requireUser();
        $item=$this->one('SELECT * FROM classifieds WHERE id=? AND user_id=?',[$id,$this->user['id']]);
        if (!$item) $this->abort(404,'Advertisement not found');
        $this->save('classifieds',['status'=>'pending','is_premium'=>0,'moderation_note'=>null,'expires_at'=>date('Y-m-d H:i:s',time()+30*86400),'updated_at'=>date('Y-m-d H:i:s')],(int)$id);
        $this->audit('Renewal requested','classifieds',(int)$id); flash('Renewal sent for approval.'); $this->redirect('/account');
    }
    public function withdraw(string $id): void {
        $this->requireUser();
        $this->run("UPDATE classifieds SET status='withdrawn',is_premium=0 WHERE id=? AND user_id=?",[$id,$this->user['id']]);
        $this->audit('Withdrawn','classifieds',(int)$id);
        flash('Advertisement withdrawn.'); $this->redirect('/account');
    }
    public function favorite(string $id): void {
        $this->requireUser();
        if (!$this->one("SELECT id FROM classifieds WHERE id=? AND status='approved' AND expires_at>?",[$id,date('Y-m-d H:i:s')])) $this->abort(404,'Advertisement not found');
        $old=$this->one('SELECT id FROM favorites WHERE user_id=? AND classified_id=?',[$this->user['id'],$id]);
        if ($old) $this->run('DELETE FROM favorites WHERE id=?',[$old['id']]);
        else $this->save('favorites',['user_id'=>$this->user['id'],'classified_id'=>$id]);
        $this->redirect('/classifieds/'.$id);
    }
    public function delete(string $id): void {
        $this->requireUser();
        $isManager = in_array($this->user['role'], ['super_admin', 'editor'], true);
        $where = $isManager ? 'id=?' : 'id=? AND user_id=?';
        $params = $isManager ? [$id] : [$id, $this->user['id']];
        $item = $this->one("SELECT * FROM classifieds WHERE $where", $params);
        if (!$item) $this->abort(404, 'Advertisement not found');
        $images = json_decode($item['images'] ?? '[]', true) ?: [];
        Upload::discardNew($images);
        $this->run('DELETE FROM favorites WHERE classified_id=?', [$id]);
        $this->run('DELETE FROM classifieds WHERE id=?', [$id]);
        $this->audit('Deleted', 'classifieds', (int)$id);
        flash('Advertisement deleted.');
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $this->redirect($isManager && str_contains($referer, '/admin') ? '/admin/classifieds' : '/account');
    }
}
