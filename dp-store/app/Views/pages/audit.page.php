<?php
/**
 * @var $this \CodeIgniter\View\View 
 */
$header = '<h2 class="font-bold text-2xl text-slate-800 leading-tight">Vima Security Audit Trail</h2>';
?>
<?= $this->extend('layouts/app.layout.php') ?>
<?php $this->setData(['header' => $header])->setVar('header', $header) ?>

<?= $this->section('main') ?>
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg text-emerald-800 text-sm shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium"><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        
        <!-- Table Header Actions -->
        <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
            <div>
                <h3 class="font-bold text-lg text-slate-900">Live Access Logs</h3>
                <p class="text-xs text-slate-500 mt-0.5">Showing the latest 100 authorization evaluations made by the Vima engine.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="<?= base_url('dashboard/audit/clear') ?>" method="POST" onsubmit="return confirm('Are you sure you want to clear all security logs?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-xxs cursor-pointer">
                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Clear Audit Trail
                    </button>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50 text-xxs font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Timestamp</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Permission Checked</th>
                        <th class="px-6 py-4">Namespace</th>
                        <th class="px-6 py-4">Result</th>
                        <th class="px-6 py-4">Reason / Notes</th>
                        <th class="px-6 py-4">Context Arguments</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs text-slate-600 bg-white">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                No audit events logged yet. Try performing actions in the sandbox playground.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-400 font-mono">
                                    <?= esc(date('Y-m-d H:i:s', is_numeric($log['created_at']) ? (int)$log['created_at'] : strtotime($log['created_at']))) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-800">
                                    <?= esc($userMap[$log['user_id']] ?? 'Guest (#' . $log['user_id'] . ')') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="px-1.5 py-0.5 rounded bg-slate-100 font-mono text-xs text-indigo-600 font-semibold"><?= esc($log['permission']) ?></code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-400 font-mono">
                                    <?= $log['namespace'] ? esc($log['namespace']) : '<span class="text-slate-300">Global</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ((int)$log['result'] === 1): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Allowed
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                            Denied
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 max-w-xs truncate text-slate-500" title="<?= esc($log['reason'] ?? '') ?>">
                                    <?= esc($log['reason'] ?: 'No evaluation message provided') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $args = @json_decode($log['arguments'] ?? '', true);
                                    if (!empty($args)): 
                                    ?>
                                        <details class="group">
                                            <summary class="text-xxs font-bold text-indigo-500 hover:text-indigo-700 cursor-pointer list-none flex items-center gap-1 select-none">
                                                View Payload (<?= count($args) ?>)
                                                <svg class="h-3 w-3 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                            </summary>
                                            <pre class="mt-2 p-2 bg-slate-900 text-slate-300 rounded text-xxs font-mono overflow-x-auto max-w-sm"><?= esc(json_encode($args, JSON_PRETTY_PRINT)) ?></pre>
                                        </details>
                                    <?php else: ?>
                                        <span class="text-slate-300 italic text-xxs">Empty</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php $this->endSection() ?>
