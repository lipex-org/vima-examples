<?php
/**
 * @var $this \CodeIgniter\View\View
 */

$header = '<h2 class="font-bold text-2xl text-slate-800 leading-tight">Vima Security Simulation Console</h2>';

// Resolve Vima information dynamically for display
$userService = \Vima\Core\resolve(\Vima\Core\User\Services\UserService::class);
$userRes = $userService->user($currentUser);
$activeRoles = array_map(fn($r) => $r->getFullName(), $userRes->get()->roles());
$activePerms = array_keys($userRes->get()->compiled());

// Helper for mapping department IDs to names
$deptNames = [
    101 => 'Engineering',
    102 => 'Finance',
    103 => 'Sales & Marketing',
    201 => 'Tenant B Operations'
];
?>
<?= $this->extend('layouts/app.layout.php') ?>

<?= $this->section('main') ?>
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg text-emerald-800 text-sm shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium"><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-lg text-rose-800 text-sm shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-semibold mr-1">Access Denied:</span> <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Simulation Controls + Tabbed Interface Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Sidebar: Simulation Control Console -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-white shadow-xl">
                <div class="flex items-center space-x-2.5 mb-5">
                    <span class="p-2 bg-indigo-500/20 rounded-lg text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </span>
                    <h3 class="font-bold text-base tracking-tight">Simulation Switchboard</h3>
                </div>

                <form action="<?= base_url('dashboard/simulate') ?>" method="POST" class="space-y-4 text-slate-300">
                    <?= csrf_field() ?>

                    <!-- Select Profile -->
                    <div>
                        <label class="block text-xxs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Active Profile</label>
                        <select name="user_id" onchange="this.form.submit()" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm">
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u->id ?>" <?= (int) $currentUser->id === (int) $u->id ? 'selected' : '' ?>>
                                    <?= esc($u->username) ?> (<?= esc($u->username === 'ian' ? 'hybrid' : $u->username) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="border-t border-slate-800 my-4"></div>

                    <!-- Custom Simulation Overrides -->
                    <div class="space-y-3.5">
                        <h4 class="text-xxs font-bold uppercase tracking-wider text-slate-400">Temporary Attributes</h4>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xxs text-slate-400 mb-1">Tenant ID</label>
                                <input type="number" name="tenant_id" value="<?= esc($currentUser->tenant_id) ?>" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-xxs text-slate-400 mb-1">Dept ID</label>
                                <input type="number" name="dept_id" value="<?= esc($currentUser->department_id) ?>" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-xs font-semibold">
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 py-1">
                            <input type="checkbox" name="is_dept_head" value="1" id="is_dept_head" <?= (int) $currentUser->is_dept_head === 1 ? 'checked' : '' ?> class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                            <label for="is_dept_head" class="text-xs font-medium text-slate-300">Is Department Head</label>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="block text-xxs text-slate-400 mb-1">Simulated Hour</label>
                                <select name="simulated_time" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-xs">
                                    <option value="10:00" <?= $simulatedTime === '10:00' ? 'selected' : '' ?>>10:00 AM (Work Hours)</option>
                                    <option value="23:00" <?= $simulatedTime === '23:00' ? 'selected' : '' ?>>11:00 PM (Off Hours)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xxs text-slate-400 mb-1">Client IP / VPN</label>
                                <select name="simulated_ip" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-xs">
                                    <option value="192.168.1.5" <?= $simulatedIp === '192.168.1.5' ? 'selected' : '' ?>>192.168.1.5 (Corporate VPN)</option>
                                    <option value="8.8.8.8" <?= $simulatedIp === '8.8.8.8' ? 'selected' : '' ?>>8.8.8.8 (Public Network)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Super Admin Override -->
                    <div class="border-t border-slate-800 my-4"></div>
                    <div class="space-y-3">
                        <h4 class="text-xxs font-bold uppercase tracking-wider text-slate-400">Super Admin Configuration</h4>
                        
                        <div>
                            <label class="block text-xxs text-slate-400 mb-1">Super Admin Role</label>
                            <input type="text" name="super_admin_role" value="<?= esc(session()->get('simulated_super_admin_role') ?? '') ?>" placeholder="e.g. admin" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-xs placeholder-slate-600">
                        </div>

                        <div class="flex items-center space-x-2 py-1">
                            <input type="checkbox" name="super_admin_bypass" value="1" id="super_admin_bypass" <?= session()->get('simulated_super_admin_bypass') ? 'checked' : '' ?> class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                            <label for="super_admin_bypass" class="text-xs font-medium text-slate-300">Enable Admin Bypass</label>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg text-xs tracking-wider uppercase transition shadow-sm cursor-pointer">
                        Apply Overrides
                    </button>
                </form>
            </div>

            <!-- Vima Computed Permissions Diagnostics -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-2.5">
                    <span class="p-1.5 bg-indigo-50 rounded text-indigo-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                    <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Active Identity</h4>
                </div>
                
                <div class="space-y-1">
                    <span class="text-xxs text-slate-500 font-bold uppercase block">Resolved Properties</span>
                    <div class="text-xs font-semibold text-slate-700 space-y-0.5">
                        <div>User ID: <span class="text-indigo-600 font-bold"><?= esc($currentUser->id) ?></span></div>
                        <div>Tenant ID: <span class="text-indigo-600 font-bold"><?= esc($currentUser->tenant_id) ?></span></div>
                        <div>Dept ID: <span class="text-indigo-600 font-bold"><?= esc($currentUser->department_id) ?></span></div>
                        <div>Dept Head: <span class="text-indigo-600 font-bold"><?= $currentUser->is_dept_head ? 'Yes' : 'No' ?></span></div>
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-xxs text-slate-500 font-bold uppercase block">Roles Loaded</span>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <?php if (empty($activeRoles)): ?>
                            <span class="text-xs text-rose-500 italic font-medium">None</span>
                        <?php else: ?>
                            <?php foreach ($activeRoles as $role): ?>
                                <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-100 rounded text-indigo-700 text-xxs font-bold"><?= esc($role) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-xxs text-slate-500 font-bold uppercase block">Resolved Permissions</span>
                    <div class="flex flex-wrap gap-1 mt-1 max-h-36 overflow-y-auto">
                        <?php if (empty($activePerms)): ?>
                            <span class="text-xs text-rose-500 italic font-medium">None</span>
                        <?php else: ?>
                            <?php foreach ($activePerms as $perm): ?>
                                <span class="px-1.5 py-0.5 bg-slate-50 border border-slate-200 rounded text-slate-600 text-xxs font-mono font-medium"><?= esc($perm) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Vima Runtime Role Assignment -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-2.5">
                    <span class="p-1.5 bg-emerald-50 rounded text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </span>
                    <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Dynamic Roles Manager</h4>
                </div>
                
                <form action="<?= base_url('dashboard/roles/update') ?>" method="POST" class="space-y-3">
                    <?= csrf_field() ?>
                    <span class="text-xxs text-slate-500 font-bold uppercase block mb-1">Assign Database Roles</span>
                    <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                        <?php foreach ($allRoles as $role): ?>
                            <?php 
                            $roleName = $role->getFullName();
                            $hasThisRole = in_array($roleName, $activeRoles);
                            ?>
                            <div class="flex items-center space-x-2 text-xs">
                                <input type="checkbox" name="roles[]" value="<?= esc($roleName) ?>" id="role-<?= esc($roleName) ?>" <?= $hasThisRole ? 'checked' : '' ?> class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <label for="role-<?= esc($roleName) ?>" class="font-medium text-slate-700 select-none cursor-pointer" title="<?= esc($role->description) ?>">
                                    <?= esc($roleName) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="w-full mt-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg text-xs uppercase tracking-wider transition shadow-sm">
                        Save Assigned Roles
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Panel: Tabbed Playground sections -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- Tab Controls -->
            <div class="border-b border-slate-200 bg-white rounded-xl shadow-xs p-1.5 flex flex-wrap gap-1">
                <button onclick="switchTab('tab-audit')" id="btn-tab-audit" class="tab-btn px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 bg-indigo-600 text-white">
                    Audit Trail
                </button>
                <button onclick="switchTab('tab-rbac')" id="btn-tab-rbac" class="tab-btn px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 text-slate-600 hover:bg-slate-100">
                    RBAC Playground
                </button>
                <button onclick="switchTab('tab-abac')" id="btn-tab-abac" class="tab-btn px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 text-slate-600 hover:bg-slate-100">
                    ABAC Boundaries
                </button>
                <button onclick="switchTab('tab-temporal')" id="btn-tab-temporal" class="tab-btn px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 text-slate-600 hover:bg-slate-100">
                    Temporal & Environmental
                </button>
                <button onclick="switchTab('tab-threshold')" id="btn-tab-threshold" class="tab-btn px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 text-slate-600 hover:bg-slate-100">
                    Cost & Thresholds
                </button>
                <button onclick="switchTab('tab-tenancy')" id="btn-tab-tenancy" class="tab-btn px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 text-slate-600 hover:bg-slate-100">
                    Tenant Isolation
                </button>
            </div>

            <!-- TAB 1: Security Audit Log Trail -->
            <div id="tab-audit" class="tab-content space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900">Vima Live Audit Log</h3>
                            <p class="text-xs text-slate-500">Every authorization query (`can()`) triggers dispatch event hooks saved here in real time.</p>
                        </div>
                        <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xxs font-bold uppercase tracking-wider animate-pulse">Audit Active</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                                    <th class="py-3 px-4">Timestamp</th>
                                    <th class="py-3 px-4">User</th>
                                    <th class="py-3 px-4">Permission</th>
                                    <th class="py-3 px-4">Tenant Namespace</th>
                                    <th class="py-3 px-4">Evaluation Result</th>
                                    <th class="py-3 px-4">Parameters Checked</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                <?php if (empty($auditLogs)): ?>
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400 italic">No authorization checks have been logged yet. Interact with the playground tabs below to trigger audit records.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($auditLogs as $log): ?>
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="py-3.5 px-4 text-slate-400 font-mono text-xxs"><?= esc($log['created_at']) ?></td>
                                            <td class="py-3.5 px-4 font-bold text-slate-800"><?= esc($userMap[$log['user_id']] ?? 'Unknown (ID: ' . $log['user_id'] . ')') ?></td>
                                            <td class="py-3.5 px-4 font-mono text-slate-800 text-xxs"><?= esc($log['permission']) ?></td>
                                            <td class="py-3.5 px-4 font-mono text-xxs text-slate-500"><?= esc($log['namespace'] ?: 'global') ?></td>
                                            <td class="py-3.5 px-4">
                                                <?php if ($log['result'] == 1): ?>
                                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-xxs font-bold uppercase">ALLOWED</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-100 rounded text-xxs font-bold uppercase">DENIED</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-xxs font-mono text-slate-500 max-w-xs truncate" title="<?= esc($log['arguments']) ?>">
                                                <?= esc($log['arguments']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: RBAC Playground -->
            <div id="tab-rbac" class="tab-content hidden space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-4">
                    <h3 class="font-bold text-lg text-slate-900 border-b border-slate-100 pb-3">Baseline RBAC Operations</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Vima implements role hierarchical resolution. When a user has the <code class="bg-slate-100 px-1 rounded text-rose-600 font-mono text-xs">Admin</code> role, they inherit manager permissions recursively.
                    </p>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 font-mono text-xs text-slate-700 space-y-2">
                        <div class="font-bold text-slate-800 border-b border-slate-200 pb-1 mb-1">Standard RBAC Schema</div>
                        <div>Admin &rarr; inherits &rarr; Manager &rarr; inherits &rarr; Viewer</div>
                        <div>• Viewer: <code class="text-indigo-600">project.read</code>, <code class="text-indigo-600">expense.read</code></div>
                        <div>• Manager Adds: <code class="text-indigo-600">project.create</code>, <code class="text-indigo-600">project.update</code>, <code class="text-indigo-600">expense.create</code>, <code class="text-indigo-600">expense.approve</code></div>
                        <div>• Admin Adds: <code class="text-indigo-600">project.delete</code></div>
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <h4 class="font-bold text-sm text-slate-800 mb-3">Try CRUD Operations on Project (RBAC Check)</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-slate-100 rounded-xl p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-slate-700 uppercase">Create Project</span>
                                    <?php if (can('project.create', new \App\Entities\Project(['tenant_id' => $currentUser->tenant_id]))): ?>
                                        <span class="text-emerald-600 text-xs font-bold flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>Allowed</span>
                                    <?php else: ?>
                                        <span class="text-rose-600 text-xs font-bold flex items-center"><span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5"></span>Blocked</span>
                                    <?php endif; ?>
                                </div>
                                <code class="block text-xxs bg-slate-900 text-slate-300 p-2.5 rounded-lg">can('project.create', $project)</code>
                            </div>

                            <div class="border border-slate-100 rounded-xl p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-slate-700 uppercase">Delete Project</span>
                                    <?php if (can('project.delete', new \App\Entities\Project(['tenant_id' => $currentUser->tenant_id]))): ?>
                                        <span class="text-emerald-600 text-xs font-bold flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>Allowed</span>
                                    <?php else: ?>
                                        <span class="text-rose-600 text-xs font-bold flex items-center"><span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5"></span>Blocked</span>
                                    <?php endif; ?>
                                </div>
                                <code class="block text-xxs bg-slate-900 text-slate-300 p-2.5 rounded-lg">can('project.delete', $project)</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: ABAC Boundaries -->
            <div id="tab-abac" class="tab-content hidden space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900">ABAC Contextual Boundaries</h3>
                            <p class="text-xs text-slate-500">Evaluates context details like ownership, department matching, and manager/division head privileges.</p>
                        </div>
                        <?php if (can('project.create', new \App\Entities\Project(['tenant_id' => $currentUser->tenant_id]))): ?>
                            <button onclick="toggleModal('project-modal')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center transition">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Create Project
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs text-slate-700 space-y-2">
                        <div class="font-bold text-slate-800 border-b border-slate-200 pb-1 mb-1 font-mono">ProjectPolicy::canUpdate() logic:</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xxs font-mono text-slate-600">
                            <div>1. Admin can edit any project in their tenant.</div>
                            <div>2. Creator can edit their own project.</div>
                            <div>3. Department heads can edit all department projects.</div>
                            <div>4. Managers can edit projects matching their department.</div>
                        </div>
                    </div>

                    <!-- Projects List -->
                    <div class="border border-slate-100 rounded-xl divide-y divide-slate-100">
                        <?php if (empty($projects)): ?>
                            <div class="p-8 text-center text-slate-400 italic">No projects exist for this tenant. Click "Create Project" above to create one.</div>
                        <?php else: ?>
                            <?php foreach ($projects as $project): ?>
                                <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="font-bold text-slate-900 text-sm"><?= esc($project->name) ?></h4>
                                            <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 rounded text-slate-700 text-xxs font-bold">
                                                Dept: <?= esc($deptNames[$project->department_id] ?? $project->department_id) ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500"><?= esc($project->description) ?></p>
                                        <div class="flex flex-wrap gap-x-4 text-xxs text-slate-400">
                                            <span>Creator ID: <strong class="text-slate-600 font-bold"><?= esc($project->creator_id) ?></strong></span>
                                            <span>Tenant: <strong class="text-slate-600 font-bold"><?= esc($project->tenant_id) ?></strong></span>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <?php if (can('project.update', $project)): ?>
                                            <button onclick="openEditProjectModal(<?= $project->id ?>, '<?= esc($project->name, 'js') ?>', '<?= esc($project->description, 'js') ?>')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition">
                                                Edit Project
                                            </button>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-100 rounded-lg text-xs font-semibold cursor-not-allowed" title="Department / Owner match required.">Gated</span>
                                        <?php endif; ?>

                                        <?php if (can('project.delete', $project)): ?>
                                            <a href="<?= base_url('dashboard/projects/delete/' . $project->id) ?>" onclick="return confirm('Are you sure you want to delete this project?')" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-semibold transition">
                                                Delete
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Temporal & Environmental Constraints -->
            <div id="tab-temporal" class="tab-content hidden space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-4">
                    <h3 class="font-bold text-lg text-slate-900 border-b border-slate-100 pb-3">Temporal & Environmental Policies</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        ABAC lets you implement policies that check dynamic environment values like IP subnets and operating hours. Below, you can see how Vima evaluates dynamic conditions.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border border-slate-150 rounded-xl p-4 bg-slate-50">
                            <span class="font-bold text-xs text-slate-800 block mb-1">Environmental Hour Constraint</span>
                            <span class="text-xs text-slate-500">Allowed only between <strong class="text-slate-800">8:00 AM and 5:00 PM</strong> (08:00 to 17:00).</span>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-xxs font-mono bg-slate-200 px-2 py-0.5 rounded text-slate-700">Hour: <?= $simulatedTime ?></span>
                                <?php if ($simulatedTime === '10:00'): ?>
                                    <span class="text-emerald-700 text-xs font-bold uppercase tracking-wider flex items-center"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>Within window</span>
                                <?php else: ?>
                                    <span class="text-rose-700 text-xs font-bold uppercase tracking-wider flex items-center"><span class="w-1.5 h-1.5 bg-rose-500 rounded-full mr-1.5"></span>Outside window</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="border border-slate-150 rounded-xl p-4 bg-slate-50">
                            <span class="font-bold text-xs text-slate-800 block mb-1">Secure IP Subnet Constraint</span>
                            <span class="text-xs text-slate-500">Must be on the <strong class="text-slate-800">192.168.1.0/24</strong> subnet, localhost, or VPN.</span>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-xxs font-mono bg-slate-200 px-2 py-0.5 rounded text-slate-700">Client IP: <?= $simulatedIp ?></span>
                                <?php if (str_starts_with($simulatedIp, '192.168.1.')): ?>
                                    <span class="text-emerald-700 text-xs font-bold uppercase tracking-wider flex items-center"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>Secure IP</span>
                                <?php else: ?>
                                    <span class="text-rose-700 text-xs font-bold uppercase tracking-wider flex items-center"><span class="w-1.5 h-1.5 bg-rose-500 rounded-full mr-1.5"></span>Insecure IP</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: Cost & Thresholds -->
            <div id="tab-threshold" class="tab-content hidden space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900">Hybrid Cost Thresholds & Conflict Gating</h3>
                            <p class="text-xs text-slate-500">Financial policy rules evaluating expense pricing tiers and user conflict of interests.</p>
                        </div>
                        <?php if (can('expense.create', new \App\Entities\Expense(['tenant_id' => $currentUser->tenant_id]))): ?>
                            <button onclick="toggleModal('expense-modal')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center transition">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Submit Expense
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs text-slate-700 space-y-2 font-mono">
                        <div class="font-bold text-slate-800 border-b border-slate-200 pb-1 mb-1">Expense Approval Gating Matrix</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xxs text-slate-600">
                            <div>
                                <span class="text-indigo-600 font-bold block">1. Under $1,000</span>
                                Team Leads, Managers, Financiers, and Admins can approve.
                            </div>
                            <div>
                                <span class="text-indigo-600 font-bold block">2. $1,000 to $10,000</span>
                                Must be Manager or Admin AND belong to the same department.
                            </div>
                            <div>
                                <span class="text-indigo-600 font-bold block">3. Over $10,000</span>
                                Requires CFO or Admin. Filer cannot approve their own report (Conflict of Interest check).
                            </div>
                        </div>
                    </div>

                    <!-- Expenses list -->
                    <div class="border border-slate-100 rounded-xl divide-y divide-slate-100">
                        <?php if (empty($expenses)): ?>
                            <div class="p-8 text-center text-slate-400 italic">No expenses submitted for approval. Click "Submit Expense" above.</div>
                        <?php else: ?>
                            <?php foreach ($expenses as $expense): ?>
                                <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2.5">
                                            <span class="font-mono font-bold text-slate-900 text-base">$<?= number_format($expense->amount, 2) ?></span>
                                            <?php if ($expense->status === 'approved'): ?>
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-xxs font-bold rounded uppercase">Approved</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xxs font-bold rounded uppercase">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-slate-600"><?= esc($expense->description) ?></p>
                                        <div class="flex flex-wrap gap-x-4 text-xxs text-slate-400">
                                            <span>Filer: User #<?= esc($expense->creator_id) ?></span>
                                            <span>Dept: <?= esc($deptNames[$expense->department_id] ?? $expense->department_id) ?></span>
                                        </div>
                                    </div>

                                    <div class="flex items-center">
                                        <?php if ($expense->status === 'pending'): ?>
                                            <?php if (can('expense.approve', $expense)): ?>
                                                <a href="<?= base_url('dashboard/expenses/approve/' . $expense->id) ?>" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                                    Approve Expense
                                                </a>
                                            <?php else: ?>
                                                <button disabled class="px-3 py-1.5 bg-slate-50 text-slate-400 border border-slate-200 rounded-lg text-xs font-medium cursor-not-allowed" title="Condition mismatch. Check time, IP, role or conflict variables.">
                                                    Gated by Policy
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 6: Tenant Isolation -->
            <div id="tab-tenancy" class="tab-content hidden space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-4">
                    <h3 class="font-bold text-lg text-slate-900 border-b border-slate-100 pb-3">Multi-Tenancy & Tenant Isolation</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Vima offers clean namespaced resources and tenant segmentation. Below, you can see how users are restricted to access data within their assigned tenant.
                    </p>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs text-slate-700 space-y-3">
                        <span class="font-bold text-slate-800 block">The "Ian" Hybrid Case study:</span>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Ian has role <code class="bg-slate-100 px-1 rounded text-rose-600 font-mono text-xs">tenant_1:admin</code> AND <code class="bg-slate-100 px-1 rounded text-rose-600 font-mono text-xs">tenant_2:viewer</code>.
                            When Ian is active in <strong class="text-slate-800">Tenant 1</strong>, he can edit projects.
                            When Ian is active in <strong class="text-slate-800">Tenant 2</strong>, he can only read projects.
                        </p>
                    </div>

                    <div class="border border-slate-150 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-0.5">
                            <span class="font-bold text-xs text-slate-800 block">Current Tenant Context</span>
                            <span class="text-xs text-slate-500">You are simulated under tenant <strong class="text-slate-800">Tenant #<?= esc($currentUser->tenant_id) ?></strong>.</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <form action="<?= base_url('dashboard/simulate') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= esc($currentUser->id) ?>">
                                <input type="hidden" name="simulated_time" value="<?= $simulatedTime ?>">
                                <input type="hidden" name="simulated_ip" value="<?= $simulatedIp ?>">
                                <input type="hidden" name="is_dept_head" value="<?= $currentUser->is_dept_head ?>">
                                <input type="hidden" name="dept_id" value="<?= $currentUser->department_id ?>">
                                
                                <button type="submit" name="tenant_id" value="1" class="px-3 py-1.5 <?= $currentUser->tenant_id == 1 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' ?> rounded-lg text-xs font-semibold shadow-xs">
                                    Switch to Tenant 1
                                </button>
                                <button type="submit" name="tenant_id" value="2" class="px-3 py-1.5 <?= $currentUser->tenant_id == 2 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' ?> rounded-lg text-xs font-semibold shadow-xs">
                                    Switch to Tenant 2
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- PROJECT MODAL -->
<div id="project-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50 animate-fade-in">
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden transform scale-95 transition-all">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-900" id="project-modal-title">Create Project</h4>
            <button onclick="toggleModal('project-modal')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="project-form" action="<?= base_url('dashboard/projects/create') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xxs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Project Name</label>
                <input type="text" name="name" id="project-name-input" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xxs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Description</label>
                <textarea name="description" id="project-desc-input" rows="3" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-xs uppercase tracking-wider transition shadow-sm">
                Save Project
            </button>
        </form>
    </div>
</div>

<!-- EXPENSE MODAL -->
<div id="expense-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50 animate-fade-in">
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden transform scale-95 transition-all">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-900">Submit Expense</h4>
            <button onclick="toggleModal('expense-modal')" class="text-gray-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="<?= base_url('dashboard/expenses/create') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xxs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Amount ($)</label>
                <input type="number" step="0.01" name="amount" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xxs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Description</label>
                <textarea name="description" rows="3" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-xs uppercase tracking-wider transition shadow-sm">
                Submit Report
            </button>
        </form>
    </div>
</div>

<script>
    function switchTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        
        // Remove active button styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('text-slate-600', 'hover:bg-slate-100');
        });
        
        // Show current tab
        document.getElementById(tabId).classList.remove('hidden');
        
        // Style current button
        const activeBtn = document.getElementById('btn-' + tabId);
        activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-100');
        activeBtn.classList.add('bg-indigo-600', 'text-white');
        
        // Store active tab in session storage
        sessionStorage.setItem('active_demo_tab', tabId);
    }

    // Restore active tab on load
    document.addEventListener("DOMContentLoaded", function() {
        const storedTab = sessionStorage.getItem('active_demo_tab');
        if (storedTab) {
            switchTab(storedTab);
        }
    });

    function toggleModal(id) {
        const modal = document.getElementById(id);
        modal.classList.toggle('hidden');
    }

    function openEditProjectModal(id, name, description) {
        document.getElementById('project-modal-title').textContent = 'Edit Project';
        document.getElementById('project-form').action = '<?= base_url('dashboard/projects/update') ?>/' + id;
        document.getElementById('project-name-input').value = name;
        document.getElementById('project-desc-input').value = description;
        toggleModal('project-modal');
    }
</script>
<?= $this->endSection() ?>
