<?php
/**
 * @var $this \CodeIgniter\View\View 
 */
$header = '<h2 class="font-bold text-2xl text-slate-800 leading-tight">Vima Caching & Performance Dashboard</h2>';
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

        <!-- Sidebar Controls -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Caching toggle status card -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-white shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-base tracking-tight">Vima Cache Status</h3>
                    <?php if ($cacheEnabled): ?>
                        <span
                            class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded text-xxs font-bold uppercase tracking-wider">ACTIVE</span>
                    <?php else: ?>
                        <span
                            class="px-2.5 py-0.5 bg-slate-800 text-slate-400 border border-slate-700 rounded text-xxs font-bold uppercase tracking-wider">DISABLED</span>
                    <?php endif; ?>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed font-medium">
                    When caching is enabled, Vima caches compiled permissions mapping collections for users, preventing
                    repeated, expensive DB reads during route middleware, Trait resolutions, and views checking.
                </p>

                <div class="border-t border-slate-800 pt-3">
                    <form action="<?= base_url('dashboard/cache/toggle') ?>" method="POST" class="space-y-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="enabled" value="<?= $cacheEnabled ? '0' : '1' ?>">
                        <button type="submit"
                            class="w-full font-semibold py-2 rounded-lg text-xs tracking-wider uppercase transition shadow-sm <?= $cacheEnabled ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white' ?>">
                            <?= $cacheEnabled ? 'Disable Caching' : 'Enable Caching' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Developer cache manual flash card -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2 mb-2">Administrative Cache
                    Tools</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Whenever dynamic roles or explicit compliance denials are granted/revoked, Vima dispatches events to
                    flush and invalidate cache keys for that specific user.
                </p>
                <form action="<?= base_url('dashboard/cache/clear') ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit"
                        class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold py-2 rounded-lg text-xs uppercase tracking-wider transition shadow-sm">
                        Invalidate & Clear Cache
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Panel: Cache hits/misses performance metrics tracking -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Metrics overview -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-6">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Runtime Cache Metrics Console</h3>
                    <p class="text-xs text-slate-500">Simulate web interactions inside the application to see hit/miss
                        efficiency rates.</p>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <!-- Hits -->
                    <div
                        class="border border-slate-150 rounded-xl p-4 text-center bg-emerald-50/20 border-emerald-100/50">
                        <span class="text-xxs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cache
                            Hits</span>
                        <span class="font-mono text-3xl font-extrabold text-emerald-600"><?= esc($hits) ?></span>
                        <span class="text-xxs text-slate-400 block mt-1">DB hits avoided</span>
                    </div>

                    <!-- Misses -->
                    <div class="border border-slate-150 rounded-xl p-4 text-center bg-slate-50">
                        <span class="text-xxs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cache
                            Misses</span>
                        <span class="font-mono text-3xl font-extrabold text-indigo-600"><?= esc($misses) ?></span>
                        <span class="text-xxs text-slate-400 block mt-1">DB queries fallback</span>
                    </div>

                    <!-- Writes -->
                    <div class="border border-slate-150 rounded-xl p-4 text-center bg-amber-50/20 border-amber-100/50">
                        <span class="text-xxs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cache
                            Writes</span>
                        <span class="font-mono text-3xl font-extrabold text-amber-600"><?= esc($writes) ?></span>
                        <span class="text-xxs text-slate-400 block mt-1">Keys generated</span>
                    </div>
                </div>

                <!-- Simulation section -->
                <div class="border-t border-slate-100 pt-5 space-y-4">
                    <h4 class="font-bold text-sm text-slate-800">Trigger Cache Hits by Checking Permissions</h4>
                    <p class="text-xs text-slate-600">
                        Click the permission checks below. If caching is enabled, the first check will result in a
                        **Cache Miss** (compiling permissions from the DB), while all subsequent checks will results in
                        **Cache Hits** (fetched instantly from RAM/cache).
                    </p>

                    <div class="flex flex-wrap gap-2.5">
                        <button onclick="checkPermission('project.read')"
                            class="px-3.5 py-2 border border-slate-200 hover:border-indigo-400 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Check: can('project.read')
                        </button>
                        <button onclick="checkPermission('project.update')"
                            class="px-3.5 py-2 border border-slate-200 hover:border-indigo-400 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Check: can('project.update')
                        </button>
                        <button onclick="checkPermission('expense.approve')"
                            class="px-3.5 py-2 border border-slate-200 hover:border-indigo-400 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Check: can('expense.approve')
                        </button>
                    </div>

                    <div id="check-output"
                        class="hidden border border-slate-100 rounded-xl p-4 bg-slate-900 text-white font-mono text-xs">
                        <!-- Output of the JS check -->
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function checkPermission(permission) {
        // Trigger a background request to fetch results and reload the metrics display
        fetch('<?= base_url('dashboard/policy') ?>?permission=' + permission)
            .then(res => res.text())
            .then(() => {
                const output = document.getElementById('check-output');
                output.classList.remove('hidden');
                output.textContent = 'Evaluated: can("' + permission + '") dynamically. Reloading page to update cache hit metrics...';

                setTimeout(() => {
                    window.location.reload();
                }, 800);
            });
    }
</script>
<?php $this->endSection() ?>