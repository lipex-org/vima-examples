<?php
/**
 * @var $this \CodeIgniter\View\View
 */
?>
<?= $this->extend('layouts/base.layout.php') ?>

<?= $this->section('content') ?>
<?php 
$uriString = (string) current_url(true)->getPath();
$isDashboard = str_ends_with($uriString, 'dashboard');
$isCompliance = str_contains($uriString, 'compliance');
$isCache = str_contains($uriString, 'cache');
$isPolicy = str_contains($uriString, 'policy');
$isAudit = str_contains($uriString, 'audit');
$isAdminOnly = str_contains($uriString, 'admin-only');
$isApproveExpenseDemo = str_contains($uriString, 'approve-expense-demo');
?>
<div class="min-h-screen bg-slate-50 flex">
    
    <!-- Left Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col justify-between border-r border-slate-800">
        <div>
            <!-- Sidebar Header / Logo -->
            <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800">
                <a href="<?= url_to('/') ?>" class="text-xl font-black tracking-wider text-indigo-400 font-sans">
                    JENGO<span class="text-slate-200">.</span>
                </a>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="mt-6 px-4 space-y-1.5">
                <span class="block px-3 text-xxs font-bold text-slate-500 uppercase tracking-widest mb-2">Sandbox Console</span>
                
                <a href="<?= url_to('dashboard') ?>"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition-all <?= $isDashboard ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' ?>">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    Playground
                </a>
                
                <a href="<?= base_url('dashboard/compliance') ?>"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition-all <?= $isCompliance ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' ?>">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Compliance & Denials
                </a>
                
                <a href="<?= base_url('dashboard/cache') ?>"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition-all <?= $isCache ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' ?>">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Caching & Metrics
                </a>
                
                <a href="<?= base_url('dashboard/policy') ?>"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition-all <?= $isPolicy ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' ?>">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    ABAC Rules & Policy
                </a>

                <a href="<?= base_url('dashboard/audit') ?>"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition-all <?= $isAudit ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' ?>">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Security Audit Trail
                </a>

                <span class="block px-3 pt-4 text-xxs font-bold text-slate-500 uppercase tracking-widest mb-2">Vima Route Filters</span>
                
                <a href="<?= base_url('dashboard/admin-only') ?>"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition-all <?= $isAdminOnly ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' ?>">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Admin Guard (RBAC)
                </a>

                <a href="<?= base_url('dashboard/approve-expense-demo') ?>"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition-all <?= $isApproveExpenseDemo ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' ?>">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Expense Guard (Perm)
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer / Auth Context info -->
        <div class="p-4 bg-slate-955 border-t border-slate-800">
            <?php if (auth()->loggedIn()): ?>
                <div class="flex items-center justify-between gap-2 text-xs">
                    <div class="truncate text-slate-300">
                        <span class="block text-xxs text-slate-500 font-bold uppercase tracking-wider">Logged In As</span>
                        <?= esc(auth()->user()->username ?? 'User') ?>
                    </div>
                    <a href="<?= url_to('logout') ?>" class="text-xxs text-slate-400 hover:text-rose-400 font-bold uppercase transition">Logout</a>
                </div>
            <?php else: ?>
                <div class="flex flex-col gap-2">
                    <a href="<?= url_to('login') ?>" class="block text-center text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 py-2 rounded-lg transition">Log in</a>
                    <a href="<?= url_to('register') ?>" class="block text-center text-xs font-semibold text-indigo-400 hover:text-indigo-300 py-1 transition font-sans">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Right Side Dashboard Area -->
    <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
        <!-- Top bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 flex-shrink-0">
            <div class="flex items-center gap-4">
                <?php if (isset($header)): ?>
                    <?= $header ?>
                <?php else: ?>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight">Vima Sandbox Console</h2>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-xxs">
                    Vima Core v1.0
                </span>
            </div>
        </header>

        <!-- Main Content Pane -->
        <main class="flex-grow overflow-y-auto">
            <?= $this->renderSection('main') ?>
        </main>
    </div>
</div>
<?= $this->endSection() ?>