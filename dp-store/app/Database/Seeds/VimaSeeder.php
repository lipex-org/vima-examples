<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Models\UserModel;
use App\Models\ProjectModel;
use App\Models\ExpenseModel;

class VimaSeeder extends Seeder
{
    public function run()
    {
        $userModel = model(UserModel::class);
        $projectModel = model(ProjectModel::class);
        $expenseModel = model(ExpenseModel::class);

        // Clean tables - disable foreign key checks for truncation/clean seeder run
        $this->db->query('PRAGMA foreign_keys = OFF');
        $this->db->table('vima_role_permissions')->truncate();
        $this->db->table('vima_role_parents')->truncate();
        $this->db->table('vima_user_roles')->truncate();
        $this->db->table('vima_user_permissions')->truncate();
        $this->db->table('vima_user_denies')->truncate();
        $this->db->table('vima_user_role_denies')->truncate();
        $this->db->table('vima_roles')->truncate();
        $this->db->table('vima_permissions')->truncate();
        $this->db->table('users')->truncate();
        $this->db->table('auth_identities')->truncate();
        $this->db->table('projects')->truncate();
        $this->db->table('expenses')->truncate();
        $this->db->query('PRAGMA foreign_keys = ON');

        // 1. Sync Vima Config to DB
        service('vima_sync')->refresh(true)->sync();

        // 2. Create Users
        $usersData = [
            [
                'username' => 'admin',
                'email'    => 'admin@example.com',
                'password' => 'secret123',
                'role'     => 'admin',
                'dept'     => 101,
                'tenant'   => 1,
                'head'     => 1
            ],
            [
                'username' => 'manager',
                'email'    => 'manager@example.com',
                'password' => 'secret123',
                'role'     => 'manager',
                'dept'     => 101,
                'tenant'   => 1,
                'head'     => 1
            ],
            [
                'username' => 'viewer',
                'email'    => 'viewer@example.com',
                'password' => 'secret123',
                'role'     => 'viewer',
                'dept'     => 101,
                'tenant'   => 1,
                'head'     => 0
            ],
            [
                'username' => 'team_lead',
                'email'    => 'team_lead@example.com',
                'password' => 'secret123',
                'role'     => 'team_lead',
                'dept'     => 101,
                'tenant'   => 1,
                'head'     => 0
            ],
            [
                'username' => 'financier',
                'email'    => 'financier@example.com',
                'password' => 'secret123',
                'role'     => 'financier',
                'dept'     => 102,
                'tenant'   => 1,
                'head'     => 0
            ],
            [
                'username' => 'cfo',
                'email'    => 'cfo@example.com',
                'password' => 'secret123',
                'role'     => 'cfo',
                'dept'     => 102,
                'tenant'   => 1,
                'head'     => 1
            ],
            [
                'username' => 'tenant_b_admin',
                'email'    => 'tenant_b@example.com',
                'password' => 'secret123',
                'role'     => 'admin',
                'dept'     => 201,
                'tenant'   => 2,
                'head'     => 1
            ],
            [
                'username' => 'ian',
                'email'    => 'ian@example.com',
                'password' => 'secret123',
                'role'     => 'hybrid', // Special case: admin in T1, viewer in T2
                'dept'     => 101,
                'tenant'   => 1,
                'head'     => 0
            ]
        ];

        foreach ($usersData as $data) {
            $user = new \CodeIgniter\Shield\Entities\User([
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => $data['password'],
                'active'   => 1,
            ]);
            $userModel->save($user);
            $userId = $userModel->getInsertID();

            // Direct update for custom columns since Shield save might filter them
            $this->db->table('users')
                ->where('id', $userId)
                ->update([
                    'department_id' => $data['dept'],
                    'tenant_id'     => $data['tenant'],
                    'is_dept_head'  => $data['head'],
                ]);

            // Load user with updated columns for Vima
            $dbUser = $userModel->find($userId);
            
            if ($data['role'] === 'hybrid') {
                // Ian is admin in Tenant 1, viewer in Tenant 2
                // We use Vima namespacing:
                \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class)->user($dbUser)->grant()->role('tenant_1:admin');
                \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class)->user($dbUser)->grant()->role('tenant_2:viewer');
            } else {
                \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class)->user($dbUser)->grant()->role($data['role']);
            }
        }

        // 3. Create Sample Projects
        $projectModel->insert([
            'name'          => 'AI Engine Alpha',
            'description'   => 'Core engine for next-gen store recommendation algorithms.',
            'department_id' => 101,
            'creator_id'    => 2, // manager
            'tenant_id'     => 1,
        ]);

        $projectModel->insert([
            'name'          => 'Security Audit 2026',
            'description'   => 'Penetration testing and authorization verification project.',
            'department_id' => 101,
            'creator_id'    => 4, // team_lead
            'tenant_id'     => 1,
        ]);

        $projectModel->insert([
            'name'          => 'Marketing & Sales Blitz',
            'description'   => 'Promotions for the summer sale events.',
            'department_id' => 103, // Sales & Marketing
            'creator_id'    => 1, // admin
            'tenant_id'     => 1,
        ]);

        $projectModel->insert([
            'name'          => 'Tenant B Database Isolation',
            'description'   => 'Database configuration and setup for isolated Tenant B accounts.',
            'department_id' => 201,
            'creator_id'    => 7, // tenant_b_admin
            'tenant_id'     => 2,
        ]);

        // 4. Create Sample Expenses
        $expenseModel->insert([
            'amount'        => 850.00,
            'description'   => 'AWS dev servers hosting',
            'status'        => 'pending',
            'department_id' => 101,
            'creator_id'    => 4, // team_lead
            'tenant_id'     => 1,
        ]);

        $expenseModel->insert([
            'amount'        => 4500.00,
            'description'   => 'Engineering workstation updates',
            'status'        => 'pending',
            'department_id' => 101,
            'creator_id'    => 2, // manager
            'tenant_id'     => 1,
        ]);

        $expenseModel->insert([
            'amount'        => 15000.00,
            'description'   => 'External security auditing consultants',
            'status'        => 'pending',
            'department_id' => 102, // Finance
            'creator_id'    => 6, // cfo
            'tenant_id'     => 1,
        ]);
    }
}
