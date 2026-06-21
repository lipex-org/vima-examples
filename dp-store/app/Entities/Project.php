<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Project extends Entity
{
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'            => 'integer',
        'department_id' => 'integer',
        'creator_id'    => 'integer',
        'tenant_id'     => 'integer',
    ];
}
