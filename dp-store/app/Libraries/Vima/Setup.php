<?php

declare(strict_types=1);

namespace App\Libraries\Vima;

use Vima\Core\Config\Contracts\SetupProviderInterface;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Entities\Role;

final class Setup implements SetupProviderInterface
{
    public function get(): array
    {
        return [
            'roles' => [
                Role::define('admin', description: 'Admin')->withPermissions([
                    'project.delete',
                ])->withParents(['manager']),
                Role::define('manager', description: 'Manager')->withPermissions([
                    'project.create',
                    'project.update',
                    'expense.create',
                    'expense.approve',
                ])->withParents(['viewer']),
                Role::define('viewer', description: 'Viewer')->withPermissions([
                    'project.read',
                    'expense.read',
                ]),
                Role::define('team_lead', description: 'Team Lead')->withPermissions([
                    'expense.approve',
                ])->withParents(['viewer']),
                Role::define('financier', description: 'Financier')->withPermissions([
                    'expense.approve',
                ])->withParents(['viewer']),
                Role::define('cfo', description: 'CFO')->withPermissions([
                    'expense.approve',
                ])->withParents(['viewer']),
            ],
            'permissions' => [
                Permission::define('project.create', description: 'Create Project'),
                Permission::define('project.read', description: 'Read Project'),
                Permission::define('project.update', description: 'Update Project'),
                Permission::define('project.delete', description: 'Delete Project'),
                Permission::define('expense.create', description: 'Create Expense'),
                Permission::define('expense.read', description: 'Read Expense'),
                Permission::define('expense.approve', description: 'Approve Expense'),
            ]
        ];
    }
}
