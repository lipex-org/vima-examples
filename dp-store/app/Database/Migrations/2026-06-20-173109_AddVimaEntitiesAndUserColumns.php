<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVimaEntitiesAndUserColumns extends Migration
{
    public function up()
    {
        // 1. Add columns to users table
        $this->forge->addColumn('users', [
            'department_id' => [
                'type'       => 'INTEGER',
                'default'    => 0,
            ],
            'tenant_id' => [
                'type'       => 'INTEGER',
                'default'    => 1,
            ],
            'is_dept_head' => [
                'type'       => 'INTEGER',
                'default'    => 0,
            ],
        ]);

        // 2. Create projects table
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'department_id' => [
                'type'       => 'INTEGER',
            ],
            'creator_id' => [
                'type'       => 'INTEGER',
            ],
            'tenant_id' => [
                'type'       => 'INTEGER',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('projects');

        // 3. Create expenses table
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'pending',
            ],
            'department_id' => [
                'type'       => 'INTEGER',
            ],
            'creator_id' => [
                'type'       => 'INTEGER',
            ],
            'tenant_id' => [
                'type'       => 'INTEGER',
            ],
            'approved_by' => [
                'type'       => 'INTEGER',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('expenses');
    }

    public function down()
    {
        $this->forge->dropTable('projects', true);
        $this->forge->dropTable('expenses', true);
        $this->forge->dropColumn('users', 'department_id');
        $this->forge->dropColumn('users', 'tenant_id');
        $this->forge->dropColumn('users', 'is_dept_head');
    }
}
