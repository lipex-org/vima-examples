<?php 
/**
 * @var $this \CodeIgniter\View\View 
 */ 
$header = '<h2 class="font-bold text-2xl text-slate-800 leading-tight">Vima Permission Authorization Guard</h2>';
?>
<?= $this->extend('layouts/app.layout.php') ?>
<?php $this->setData(['header' => $header])->setVar('header', $header) ?>

<?= $this->section('main') ?>
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sidebar: Active Context -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-white shadow-xl space-y-4">
                <h3 class="font-bold text-base tracking-tight border-b border-slate-800 pb-2">Active Context</h3>
                <div class="text-xs text-slate-400 space-y-2 font-medium">
                    <div>Simulated User: <strong class="text-white"><?= esc($currentUser->username ?? 'Unknown') ?></strong></div>
                    <div>User Context ID: <strong class="text-white"><?= esc($currentUser->id ?? 'N/A') ?></strong></div>
                    <div>Active Tenant ID: <strong class="text-white"><?= esc($currentUser->tenant_id ?? 'N/A') ?></strong></div>
                    <div>Active Dept ID: <strong class="text-white"><?= esc($currentUser->department_id ?? 'N/A') ?></strong></div>
                    <div>Is Dept Head: <strong class="text-white"><?= ($currentUser->is_dept_head ?? false) ? 'Yes' : 'No' ?></strong></div>
                </div>
                
                <div class="pt-2 border-t border-slate-800">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400">
                        Permission Check Passed
                    </span>
                </div>
            </div>

            <!-- Route Guard Config -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2 mb-2">Guard Configuration</h4>
                <div class="space-y-2">
                    <div>
                        <span class="block text-xxs font-bold text-slate-400 uppercase tracking-wider">Route Pattern</span>
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-600 font-mono text-xs">dashboard/approve-expense-demo</code>
                    </div>
                    <div>
                        <span class="block text-xxs font-bold text-slate-400 uppercase tracking-wider">Applied Filter</span>
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded text-rose-600 font-mono text-xs">vima_authorize:expense.approve</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Route Filter Explanation & Demo -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 space-y-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-slate-900">Access Granted</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            You are seeing this page because the active user context possesses the <strong class="font-semibold text-slate-700">expense.approve</strong> permission required by the route filter.
                        </p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6">
                    <h4 class="font-bold text-slate-800 text-sm mb-3">How this route is protected</h4>
                    
                    <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                        <p>
                            In CodeIgniter 4, we protect paths directly inside <code class="bg-slate-50 text-slate-700 px-1 py-0.5 rounded font-mono">app/Config/Routes.php</code> using the Vima authorization route filter:
                        </p>
                        
                        <div class="bg-slate-900 rounded-xl p-4 text-slate-300 font-mono text-xs overflow-x-auto shadow-inner border border-slate-800">
                            <span class="text-slate-500">// Protected Route in app/Config/Routes.php</span><br>
                            $routes->get(<span class="text-emerald-400">'approve-expense-demo'</span>, <span class="text-emerald-400">'Dashboard::approveExpenseDemo'</span>, [<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-orange-400">'filter'</span> => <span class="text-emerald-400">'vima_authorize:expense.approve'</span><br>
                            ]);
                        </div>

                        <p>
                            If you attempt to visit this route while simulating a user context that does not have the <code class="bg-slate-100 text-slate-800 px-1 py-0.5 rounded font-mono">expense.approve</code> permission, the request is intercepted by <strong class="font-semibold text-slate-800">VimaAuthorizeFilter</strong> before reaching the controller.
                        </p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6 space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm">Visualizing the Vima Route Guard Pipeline</h4>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4 font-mono text-xs text-slate-600">
                            <div class="flex-1 w-full text-center p-3 bg-white border border-slate-200 rounded-lg shadow-xxs">
                                <span class="font-semibold text-slate-700">Incoming Request</span>
                                <div class="text-xxs text-slate-400 mt-1">/dashboard/approve-expense-demo</div>
                            </div>
                            <div class="text-slate-400 hidden md:block">➔</div>
                            <div class="flex-1 w-full text-center p-3 bg-indigo-50 border border-indigo-200 rounded-lg shadow-xxs">
                                <span class="font-semibold text-indigo-700">VimaAuthorizeFilter</span>
                                <div class="text-xxs text-indigo-500 mt-1">Check permission `expense.approve`</div>
                            </div>
                            <div class="text-slate-400 hidden md:block">➔</div>
                            <div class="flex-1 w-full text-center p-3 bg-emerald-50 border border-emerald-200 rounded-lg shadow-xxs">
                                <span class="font-semibold text-emerald-700">Controller Method</span>
                                <div class="text-xxs text-emerald-500 mt-1">Dashboard::approveExpenseDemo()</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
<?php $this->endSection() ?>
