<?php
/**
 * @var $this \CodeIgniter\View\View 
 */
$header = '<h2 class="font-bold text-2xl text-slate-800 leading-tight">ABAC Policy & Rules Sandbox</h2>';
?>
<?= $this->extend('layouts/app.layout.php') ?>
<?php $this->setData(['header' => $header])->setVar('header', $header) ?>

<?= $this->section('main') ?>
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Sidebar context card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-white shadow-xl space-y-4">
                <h3 class="font-bold text-base tracking-tight border-b border-slate-800 pb-2">Active Context</h3>
                <div class="text-xs text-slate-400 space-y-1.5 font-medium">
                    <div>User Context ID: <strong class="text-white"><?= esc($currentUser->id) ?></strong></div>
                    <div>Active Tenant ID: <strong class="text-white"><?= esc($currentUser->tenant_id) ?></strong></div>
                    <div>Active Dept ID: <strong class="text-white"><?= esc($currentUser->department_id) ?></strong>
                    </div>
                    <div>Is Dept Head: <strong
                            class="text-white"><?= $currentUser->is_dept_head ? 'Yes' : 'No' ?></strong></div>
                </div>
            </div>

            <!-- Wildcard tester info -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2 mb-2">Wildcards & Namespaces
                </h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Vima supports checks like <code
                        class="bg-slate-100 px-1 rounded text-rose-600 font-mono text-xs">tenant_1:project.update</code>.
                    Namespaced roles isolate user operations to their respective divisions.
                </p>
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 font-mono text-xxs text-slate-600">
                    // Wildcard Resolution Flow:<br>
                    can('tenant_1:*') matches all actions under tenant_1 namespace.
                </div>
            </div>
        </div>

        <!-- Main Panel: Policy sandbox, testing permissions, compiled registry -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Sandbox Tester -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Interactive ABAC Policy Tester</h3>
                    <p class="text-xs text-slate-500">Test how Vima evaluates specific permission strings against the
                        current user context.</p>
                </div>

                <form action="<?= base_url('dashboard/policy') ?>" method="GET" class="space-y-4">
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label
                                class="block text-xxs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Permission
                                String to Check</label>
                            <input type="text" name="permission" value="<?= esc($testPermission) ?>" required
                                placeholder="e.g. project.create or tenant_1:project.update"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="self-end">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-5 rounded-lg text-sm tracking-wider uppercase transition shadow-sm">
                                Test
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Result card -->
                <div
                    class="border border-slate-150 rounded-xl p-4 flex items-center justify-between <?= $testResult ? 'bg-emerald-50/20 border-emerald-100/50' : 'bg-rose-50/20 border-rose-100/50' ?>">
                    <div class="space-y-0.5">
                        <span class="text-xxs font-bold text-slate-500 uppercase tracking-wider block">Checked
                            Permission String</span>
                        <code class="font-mono text-xs font-bold text-slate-800"><?= esc($testPermission) ?></code>
                    </div>

                    <div>
                        <?php if ($testResult): ?>
                            <span
                                class="px-3.5 py-1.5 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center"><span
                                    class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>ALLOWED</span>
                        <?php else: ?>
                            <span
                                class="px-3.5 py-1.5 bg-rose-100 text-rose-800 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center"><span
                                    class="w-2 h-2 rounded-full bg-rose-500 mr-2"></span>DENIED</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Compiled Permission Registry Inspection -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-base">Compiled Permissions Registry Map</h3>
                    <p class="text-xs text-slate-500">Visual inspection of compiled permissions and constraint
                        parameters loaded in memory for the active user.</p>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if (empty($compiled)): ?>
                        <div class="p-6 text-center text-slate-400 italic text-xs">No permissions active for this user
                            context. Assign roles to add permissions.</div>
                    <?php else: ?>
                        <?php foreach ($compiled as $permName => $constraintsCollection): ?>
                            <div class="p-4 flex items-start justify-between font-mono text-xs hover:bg-slate-50/30 transition">
                                <div class="space-y-1">
                                    <span class="font-bold text-slate-800"><?= esc($permName) ?></span>
                                    <div class="text-xxs text-slate-400 leading-relaxed">
                                        Constraints: <?= json_encode($constraintsCollection) ?>
                                    </div>
                                </div>
                                <span
                                    class="px-2 py-0.5 bg-indigo-50 border border-indigo-100 rounded text-indigo-700 text-xxs font-bold uppercase">RESOLVED</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
<?php $this->endSection() ?>