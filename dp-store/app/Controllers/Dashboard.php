<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ExpenseModel;
use App\Entities\Project;
use App\Entities\Expense;
use CodeIgniter\Shield\Models\UserModel;
use App\Libraries\Vima\SimulationManager;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $user = SimulationManager::getCurrentUser();
        $userModel = model(UserModel::class);
        $projectModel = model(ProjectModel::class);
        $expenseModel = model(ExpenseModel::class);

        // Fetch all users for switcher dropdown
        $users = $userModel->findAll();
        // Load custom fields for all users manually
        $db = \Config\Database::connect();
        foreach ($users as $u) {
            $row = $db->table('users')->where('id', $u->id)->get()->getRow();
            if ($row) {
                $u->department_id = $row->department_id;
                $u->tenant_id = $row->tenant_id;
                $u->is_dept_head = $row->is_dept_head;
            }
        }

        // Apply tenant isolation in database queries
        $projects = $projectModel->where('tenant_id', $user->tenant_id)->findAll();
        $expenses = $expenseModel->where('tenant_id', $user->tenant_id)->findAll();

        // Fetch recent audit logs
        $vimaConfig = service('vima_config');
        $logsTable = $vimaConfig->tables->auditLogs;
        $auditLogs = $db->table($logsTable)->orderBy('id', 'DESC')->limit(15)->get()->getResultArray();

        // Map user IDs to usernames for audit log display
        $userMap = [];
        foreach ($users as $u) {
            $userMap[$u->id] = $u->username;
        }

        // Fetch all roles from Vima
        $roleService = \Vima\Core\resolve(\Vima\Core\Role\Services\RoleService::class);
        $allRoles = $roleService->all();

        return page('dashboard', [
            'currentUser' => $user,
            'users' => $users,
            'projects' => $projects,
            'expenses' => $expenses,
            'simulatedTime' => session()->get('simulated_time') ?? '10:00',
            'simulatedIp' => SimulationManager::getSimulatedIp(),
            'auditLogs' => $auditLogs,
            'userMap' => $userMap,
            'allRoles' => $allRoles,
        ]);
    }

    public function simulate()
    {
        $session = session();
        $userId = $this->request->getPost('user_id');
        $userModel = model(UserModel::class);
        $user = $userModel->find($userId);

        $db = \Config\Database::connect();
        $row = $db->table('users')->where('id', $userId)->get()->getRow();

        $session->set('simulated_user_id', $userId);
        $session->set('simulated_tenant_id', $this->request->getPost('tenant_id') ?? $row->tenant_id ?? 1);
        $session->set('simulated_dept_id', $this->request->getPost('dept_id') ?? $row->department_id ?? 101);
        $session->set('simulated_is_dept_head', $this->request->getPost('is_dept_head') ?? $row->is_dept_head ?? 0);
        $session->set('simulated_time', $this->request->getPost('simulated_time') ?? '10:00');
        $session->set('simulated_ip', $this->request->getPost('simulated_ip') ?? '192.168.1.5');
        
        $session->set('simulated_super_admin_role', $this->request->getPost('super_admin_role') ?: null);
        $session->set('simulated_super_admin_bypass', (bool) $this->request->getPost('super_admin_bypass'));

        // Clear Vima cache
        $cache = service('vima_cache');
        $config = service('vima_config');
        $prefix = rtrim($config->cachePrefix, '_:');
        $cache->delete($prefix . ':user:' . $userId . ':roles');
        $cache->delete($prefix . ':user:' . $userId . ':permissions');

        return redirect()->to('dashboard')->with('message', 'Simulation context updated!');
    }

    public function createProject()
    {
        $user = SimulationManager::getCurrentUser();

        $project = new Project([
            'tenant_id' => $user->tenant_id
        ]);

        if (!can('project.create', $project)) {
            return redirect()->to('dashboard')->with('error', 'Access Denied: You do not have permission to create projects.');
        }

        $projectModel = model(ProjectModel::class);
        $projectModel->insert([
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'department_id' => $user->department_id,
            'creator_id' => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);

        return redirect()->to('dashboard')->with('message', 'Project created successfully!');
    }

    public function updateProject($id)
    {
        $projectModel = model(ProjectModel::class);
        $project = $projectModel->find($id);

        if (!$project) {
            return redirect()->to('dashboard')->with('error', 'Project not found.');
        }

        if (!can('project.update', $project)) {
            return redirect()->to('dashboard')->with('error', 'Access Denied: You do not have permission to edit this project.');
        }

        $projectModel->update($id, [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('dashboard')->with('message', 'Project updated successfully!');
    }

    public function deleteProject($id)
    {
        $projectModel = model(ProjectModel::class);
        $project = $projectModel->find($id);

        if (!$project) {
            return redirect()->to('dashboard')->with('error', 'Project not found.');
        }

        if (!can('project.delete', $project)) {
            return redirect()->to('dashboard')->with('error', 'Access Denied: Only Admins can delete projects.');
        }

        $projectModel->delete($id);

        return redirect()->to('dashboard')->with('message', 'Project deleted successfully!');
    }

    public function createExpense()
    {
        $user = SimulationManager::getCurrentUser();
        $expense = new Expense([
            'tenant_id' => $user->tenant_id
        ]);

        if (!can('expense.create', $expense)) {
            return redirect()->to('dashboard')->with('error', 'Access Denied: You do not have permission to submit expenses.');
        }

        $expenseModel = model(ExpenseModel::class);
        $expenseModel->insert([
            'amount' => (float) $this->request->getPost('amount'),
            'description' => $this->request->getPost('description'),
            'status' => 'pending',
            'department_id' => $user->department_id,
            'creator_id' => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);

        return redirect()->to('dashboard')->with('message', 'Expense submitted for approval.');
    }

    public function approveExpense($id)
    {
        $expenseModel = model(ExpenseModel::class);
        $expense = $expenseModel->find($id);

        if (!$expense) {
            return redirect()->to('dashboard')->with('error', 'Expense not found.');
        }

        if (!can('expense.approve', $expense)) {
            return redirect()->to('dashboard')->with('error', 'Access Denied: You are not authorized to approve this expense under current conditions (time/IP/role/threshold constraints).');
        }

        $user = SimulationManager::getCurrentUser();
        $expenseModel->update($id, [
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);

        return redirect()->to('dashboard')->with('message', 'Expense approved successfully!');
    }

    public function updateRoles()
    {
        $user = SimulationManager::getCurrentUser();
        if (!$user) {
            return redirect()->to('dashboard')->with('error', 'No active user context.');
        }

        $userService = \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class);
        $roleService = \Vima\Core\resolve(\Vima\Core\Role\Services\RoleService::class);

        $selectedRoles = $this->request->getPost('roles') ?? [];
        $currentRoles = array_map(fn($r) => $r->getFullName(), $userService->user($user)->get()->roles());

        $allRoles = $roleService->all();
        $allRoleNames = array_map(fn($r) => $r->getFullName(), $allRoles);

        $userResource = $userService->user($user);

        foreach ($allRoleNames as $roleName) {
            $wantRole = in_array($roleName, $selectedRoles);
            $hasRole = in_array($roleName, $currentRoles);

            if ($wantRole && !$hasRole) {
                $userResource->grant()->role($roleName);
            } elseif (!$wantRole && $hasRole) {
                $userResource->revoke()->role($roleName);
            }
        }

        // Clear Vima cache
        $cache = service('vima_cache');
        $config = service('vima_config');
        $prefix = rtrim($config->cachePrefix, '_:');
        $cache->delete($prefix . ':user:' . $user->id . ':roles');
        $cache->delete($prefix . ':user:' . $user->id . ':permissions');

        return redirect()->to('dashboard')->with('message', 'User roles updated in Vima database successfully!');
    }

    public function compliance(): string
    {
        $user = SimulationManager::getCurrentUser();
        $userModel = model(UserModel::class);
        $users = $userModel->findAll();

        $db = \Config\Database::connect();
        foreach ($users as $u) {
            $row = $db->table('users')->where('id', $u->id)->get()->getRow();
            if ($row) {
                $u->department_id = $row->department_id;
                $u->tenant_id = $row->tenant_id;
                $u->is_dept_head = $row->is_dept_head;
            }
        }

        $userService = \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class);
        $roleService = \Vima\Core\resolve(\Vima\Core\Role\Services\RoleService::class);
        $permissionService = \Vima\Core\resolve(\Vima\Core\Permission\Services\PermissionService::class);

        $deniedPermissionsRaw = $userService->user($user)->get()->denies()->permission();
        $deniedRolesRaw = $userService->user($user)->get()->denies()->role();

        $allRoles = $roleService->all();
        $allPermissions = $permissionService->all();

        // Resolve names for the UI
        $deniedPermissions = [];
        foreach ($deniedPermissionsRaw as $dp) {
            $perm = $permissionService->find($dp->permissionId ?? $dp['permission_id'] ?? null);
            if ($perm) {
                $deniedPermissions[] = [
                    'id' => $perm->id,
                    'name' => $perm->getFullName(),
                    'reason' => $dp->reason ?? $dp['reason'] ?? 'Compliance freeze'
                ];
            }
        }

        $deniedRoles = [];
        foreach ($deniedRolesRaw as $dr) {
            $role = $roleService->find($dr->roleId ?? $dr['role_id'] ?? null);
            if ($role) {
                $deniedRoles[] = [
                    'id' => $role->id,
                    'name' => $role->getFullName(),
                    'reason' => $dr->reason ?? $dr['reason'] ?? 'Compliance freeze'
                ];
            }
        }

        return page('compliance', [
            'currentUser' => $user,
            'users' => $users,
            'allRoles' => $allRoles,
            'allPermissions' => $allPermissions,
            'deniedPermissions' => $deniedPermissions,
            'deniedRoles' => $deniedRoles,
        ]);
    }

    public function addDeny()
    {
        $user = SimulationManager::getCurrentUser();
        $type = $this->request->getPost('type');
        $target = $this->request->getPost('target');
        $reason = $this->request->getPost('reason') ?: 'Compliance Suspension';

        $userService = \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class);
        $userResource = $userService->user($user);

        if ($type === 'permission') {
            $userResource->deny()->permission($target, $reason);
        } else {
            $userResource->deny()->role($target, $reason);
        }

        // Clear Vima cache
        $cache = service('vima_cache');
        $config = service('vima_config');
        $prefix = rtrim($config->cachePrefix, '_:');
        $cache->delete($prefix . ':user:' . $user->id . ':roles');
        $cache->delete($prefix . ':user:' . $user->id . ':permissions');

        return redirect()->to('dashboard/compliance')->with('message', 'Denial rule added successfully.');
    }

    public function removeDeny()
    {
        $user = SimulationManager::getCurrentUser();
        $type = $this->request->getPost('type');
        $target = $this->request->getPost('target');

        $userService = \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class);
        $userResource = $userService->user($user);

        if ($type === 'permission') {
            $userResource->undeny()->permission($target);
        } else {
            $userResource->undeny()->role($target);
        }

        // Clear Vima cache
        $cache = service('vima_cache');
        $config = service('vima_config');
        $prefix = rtrim($config->cachePrefix, '_:');
        $cache->delete($prefix . ':user:' . $user->id . ':roles');
        $cache->delete($prefix . ':user:' . $user->id . ':permissions');

        return redirect()->to('dashboard/compliance')->with('message', 'Denial rule removed successfully.');
    }

    public function cache(): string
    {
        $user = SimulationManager::getCurrentUser();
        $session = session();

        $cacheEnabled = config('Vima')->isCacheEnabled();

        $hits = $session->get('simulated_cache_hits') ?? 0;
        $misses = $session->get('simulated_cache_misses') ?? 0;
        $writes = $session->get('simulated_cache_writes') ?? 0;

        return page('cache', [
            'currentUser' => $user,
            'cacheEnabled' => $cacheEnabled,
            'hits' => $hits,
            'misses' => $misses,
            'writes' => $writes,
        ]);
    }

    public function toggleCache()
    {
        $session = session();
        $enabled = (bool) $this->request->getPost('enabled');
        $session->set('simulated_cache_enabled', $enabled);

        // Reset metrics
        $session->set('simulated_cache_hits', 0);
        $session->set('simulated_cache_misses', 0);
        $session->set('simulated_cache_writes', 0);

        // Clear Vima cache
        service('vima_cache')->clear();

        return redirect()->to('dashboard/cache')->with('message', 'Vima Cache status updated and flushed.');
    }

    public function clearCache()
    {
        // Reset session metrics
        $session = session();
        $session->set('simulated_cache_hits', 0);
        $session->set('simulated_cache_misses', 0);
        $session->set('simulated_cache_writes', 0);

        // Flush database cache
        service('vima_cache')->clear();

        return redirect()->to('dashboard/cache')->with('message', 'Vima Cache cleared successfully.');
    }

    public function policy(): string
    {
        $user = SimulationManager::getCurrentUser();

        // Test a sample permission string against user context
        $testPermission = $this->request->getGet('permission') ?: 'project.create';
        $testResult = can($testPermission);

        // Get compiled permissions map for inspection
        $userService = \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class);
        $compiled = $userService->user($user)->get()->compiled();

        return page('policy', [
            'currentUser' => $user,
            'testPermission' => $testPermission,
            'testResult' => $testResult,
            'compiled' => $compiled,
        ]);
    }

    public function adminOnly(): string
    {
        $user = SimulationManager::getCurrentUser();

        return page('admin', [
            'currentUser' => $user,
        ]);
    }

    public function approveExpenseDemo(): string
    {
        $user = SimulationManager::getCurrentUser();

        return page('approve_expense_demo', [
            'currentUser' => $user,
        ]);
    }

    public function audit(): string
    {
        $user = SimulationManager::getCurrentUser();
        $db = \Config\Database::connect();
        $vimaConfig = service('vima_config');
        $logsTable = $vimaConfig->tables->auditLogs;
        
        $logs = $db->table($logsTable)->orderBy('id', 'DESC')->limit(100)->get()->getResultArray();

        $userModel = model(UserModel::class);
        $users = $userModel->findAll();
        $userMap = [];
        foreach ($users as $u) {
            $userMap[$u->id] = $u->username;
        }

        return page('audit', [
            'currentUser' => $user,
            'logs' => $logs,
            'userMap' => $userMap,
        ]);
    }

    public function clearLogs()
    {
        $db = \Config\Database::connect();
        $vimaConfig = service('vima_config');
        $logsTable = $vimaConfig->tables->auditLogs;
        
        $db->table($logsTable)->truncate();

        return redirect()->to('dashboard/audit')->with('message', 'Vima Audit Logs cleared successfully.');
    }
}
