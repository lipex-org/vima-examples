<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Expense extends Entity
{
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'            => 'integer',
        'amount'        => 'float',
        'department_id' => 'integer',
        'creator_id'    => 'integer',
        'tenant_id'     => 'integer',
        'approved_by'   => 'integer',
    ];
}
