<?php
namespace App\Models;

use App\Core\Model;

class Advertisement extends Model {
    protected $table = 'advertisements';

    public function getActiveByPosition($position) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE position = :pos AND is_active = 1 
                AND (start_date IS NULL OR start_date <= CURDATE()) 
                AND (end_date IS NULL OR end_date >= CURDATE()) 
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pos' => $position]);
        return $stmt->fetch();
    }

    public function allOrdered() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
