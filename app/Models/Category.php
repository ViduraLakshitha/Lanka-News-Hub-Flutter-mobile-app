<?php
namespace App\Models;

use App\Core\Model;

class Category extends Model {
    protected $table = 'categories';

    public function allOrdered() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY order_index ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
