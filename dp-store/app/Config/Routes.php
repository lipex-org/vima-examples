<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

service('auth')->routes($routes);

// Jengo Dashboard Route
$routes->group('dashboard', ['filter' => 'session'], static function (RouteCollection $routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->post('simulate', 'Dashboard::simulate');
    // Project Routes
    $routes->post('projects/create', 'Dashboard::createProject');
    $routes->post('projects/update/(:num)', 'Dashboard::updateProject/$1');
    $routes->get('projects/delete/(:num)', 'Dashboard::deleteProject/$1');
    $routes->post('projects/delete/(:num)', 'Dashboard::deleteProject/$1');

    // Expense Routes
    $routes->post('expenses/create', 'Dashboard::createExpense');
    $routes->get('expenses/approve/(:num)', 'Dashboard::approveExpense/$1');
    $routes->post('expenses/approve/(:num)', 'Dashboard::approveExpense/$1');

    // Role Modification Route
    $routes->post('roles/update', 'Dashboard::updateRoles');

    // Compliance Pages
    $routes->get('compliance', 'Dashboard::compliance');
    $routes->post('compliance/deny', 'Dashboard::addDeny');
    $routes->post('compliance/undeny', 'Dashboard::removeDeny');

    // Cache Pages
    $routes->get('cache', 'Dashboard::cache');
    $routes->post('cache/toggle', 'Dashboard::toggleCache');
    $routes->post('cache/clear', 'Dashboard::clearCache');

    // Policy Pages
    $routes->get('policy', 'Dashboard::policy');

    // Admin Route
    $routes->get('admin-only', 'Dashboard::adminOnly', ['filter' => 'vima_rbac:admin']);

    // Permission-protected demo route
    $routes->get('approve-expense-demo', 'Dashboard::approveExpenseDemo', ['filter' => 'vima_authorize:expense.approve']);

    // Audit Trail Page
    $routes->get('audit', 'Dashboard::audit');
    $routes->post('audit/clear', 'Dashboard::clearLogs');
});