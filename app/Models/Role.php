<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    protected string $table = 'roles';

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
