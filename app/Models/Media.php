<?php
namespace App\Models;

use App\Core\Model;

class Media extends Model {
    protected $table = 'media';

    public function allOrdered() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
