<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryMovementModel extends Model
{
    protected $table = 'inventory_movements';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'variant_id', 'quantity_delta', 'movement_type',
        'reference_type', 'reference_id', 'note', 'created_at',
    ];
    protected $useTimestamps = false;
}