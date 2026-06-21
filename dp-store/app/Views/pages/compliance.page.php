<?php
/**
 * @var $this \CodeIgniter\View\View 
 */
$header = '<h2 class="font-bold text-2xl text-slate-800 leading-tight">Compliance & Denials Console</h2>';
?>
<?= $this->extend('layouts/app.layout.php') ?>
<?php $this->setData(['header' => $header])->setVar('header', $header) ?>

<?= $this->section('main') ?>
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg text-emerald-800 text-sm shadow-sm">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Sidebar context card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-white shadow-xl">
                <h3 class="font-bold text-base tracking-tight mb-4">Simulated Profile</h3>
                <div class="text-xs text-slate-400 space-y-1.5 font-medium">
                    <div>Username: <strong class="text-white"><?= esc($currentUser->username) ?></strong></div>
                    <div>Active Tenant ID: <strong class="text-white"><?= esc($currentUser->tenant_id) ?></strong></div>
                    <div>Active Dept ID: <strong class="text-white"><?= esc($currentUser->department_id) ?></strong>
                    </div>
                </div>
                <div class="border-t border-slate-800 my-4"></div>
                <p class="text-xxs text-slate-400 leading-relaxed uppercase tracking-wider font-bold">Concept Definition
                </p>
                <p class="text-xs text-slate-300 leading-relaxed mt-1">
                    Vima's "Denial" feature allows compliance officers to explicitly blacklist a user from executing
                    actions or activating specific roles, completely overriding any granted/inherited permissions.
                </p>
            </div>

            <!-- Create a denial rule -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2 mb-4">Enforce Denial
                    Restriction</h4>
                <form action="<?= base_url('dashboard/compliance/deny') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-xxs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Denial
                            Target Type</label>
                        <select name="type" id="deny-type-select" onchange="toggleDenyTargets()"
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:border-indigo-500">
                            <option value="permission">Specific Permission</option>
                            <option value="role">Entire Role</option>
                        </select>
                    </div>

                    <!-- Targets -->
                    <div id="target-permission-container">
                        <label class="block text-xxs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Select
                            Permission to Block</label>
                        <select name="target"
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:border-indigo-500">
                            <?php foreach ($allPermissions as $perm): ?>
                                <option value="<?= esc($perm->getFullName()) ?>"><?= esc($perm->getFullName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="target-role-container" class="hidden">
                        <label class="block text-xxs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Select
                            Role to Suspend</label>
                        <select name="target" disabled
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:border-indigo-500">
                            <?php foreach ($allRoles as $role): ?>
                                <option value="<?= esc($role->getFullName()) ?>"><?= esc($role->getFullName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xxs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Reason
                            for Suspension</label>
                        <input type="text" name="reason" placeholder="e.g. Compliance audit freeze" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg text-xs tracking-wider uppercase transition shadow-sm">
                        Enforce Denial Rule
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Panel: Lists of active denials -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Denied Permissions -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-base">Active Permission Denials</h3>
                    <p class="text-xs text-slate-500">Direct permission exclusions currently active for
                        <?= esc($currentUser->username) ?>.</p>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if (empty($deniedPermissions)): ?>
                        <div class="p-6 text-center text-slate-400 italic text-xs">No explicit permission denials enforced
                            for this user context.</div>
                    <?php else: ?>
                        <?php foreach ($deniedPermissions as $dp): ?>
                            <div class="p-5 flex items-center justify-between">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <code
                                            class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-100 rounded text-xxs font-mono font-bold"><?= esc($dp['name']) ?></code>
                                        <span class="text-xxs font-semibold text-rose-500 uppercase">Suspended</span>
                                    </div>
                                    <p class="text-xs text-slate-500">Reason: <strong
                                            class="text-slate-700"><?= esc($dp['reason']) ?></strong></p>
                                </div>
                                <form action="<?= base_url('dashboard/compliance/undeny') ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type" value="permission">
                                    <input type="hidden" name="target" value="<?= esc($dp['name']) ?>">
                                    <button type="submit"
                                        class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold transition">
                                        Lift Denial
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Denied Roles -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-base">Active Role Denials</h3>
                    <p class="text-xs text-slate-500">Direct role exclusions currently active for
                        <?= esc($currentUser->username) ?>.</p>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if (empty($deniedRoles)): ?>
                        <div class="p-6 text-center text-slate-400 italic text-xs">No explicit role denials enforced for
                            this user context.</div>
                    <?php else: ?>
                        <?php foreach ($deniedRoles as $dr): ?>
                            <div class="p-5 flex items-center justify-between">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <code
                                            class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-100 rounded text-xxs font-mono font-bold"><?= esc($dr['name']) ?></code>
                                        <span class="text-xxs font-semibold text-rose-500 uppercase">Suspended</span>
                                    </div>
                                    <p class="text-xs text-slate-500">Reason: <strong
                                            class="text-slate-700"><?= esc($dr['reason']) ?></strong></p>
                                </div>
                                <form action="<?= base_url('dashboard/compliance/undeny') ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type" value="role">
                                    <input type="hidden" name="target" value="<?= esc($dr['name']) ?>">
                                    <button type="submit"
                                        class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold transition">
                                        Lift Denial
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function toggleDenyTargets() {
        const select = document.getElementById('deny-type-select');
        const permContainer = document.getElementById('target-permission-container');
        const roleContainer = document.getElementById('target-role-container');

        if (select.value === 'permission') {
            permContainer.classList.remove('hidden');
            permContainer.querySelector('select').removeAttribute('disabled');
            roleContainer.classList.add('hidden');
            roleContainer.querySelector('select').setAttribute('disabled', 'disabled');
        } else {
            roleContainer.classList.remove('hidden');
            roleContainer.querySelector('select').removeAttribute('disabled');
            permContainer.classList.add('hidden');
            permContainer.querySelector('select').setAttribute('disabled', 'disabled');
        }
    }
</script>
<?php $this->endSection() ?>