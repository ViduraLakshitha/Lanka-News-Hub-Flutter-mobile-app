<?php
namespace App\Models;

use App\Core\Model;

class Article extends Model {
    protected $table = 'articles';

    public function allWithDetails() {
        $sql = "SELECT a.*, c.name_en as category_name, u.name as author_name 
                FROM {$this->table} a
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
