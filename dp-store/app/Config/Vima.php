<?php

/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Config;

use Vima\CodeIgniter\Config\Vima as BaseVima;
use App\Libraries\Vima\Setup as SetupLibrary;
use Vima\Core\Config\DTOs\PolicyConfig;
use Vima\Core\Config\DTOs\Setup;
use Vima\Core\Config\DTOs\UserMethods;
use Vima\Core\Config\Schema\Columns;
use Vima\Core\Config\Schema\Tables;
use Vima\Core\Config\VimaConfig;

class Vima extends BaseVima
{
    public Tables $tables;
    public Columns $columns;
    public Setup $setup;

    /**
     * List of setup providers.
     * @var class-string[]
     */
    public array $setupProviders = [
        SetupLibrary::class,
    ];

    /**
     * Policy auto-discovery and registration settings.
     */
    public array $policies = [
        'autoDiscover' => true,
        'directory' => 'Policies',
        'registered' => [],
    ];

    /**
     * User context resolution and methods settings.
     */
    public array $user = [
        'current' => null,
        'resolver' => null,
        'methods' => [],
        'segment' => null,
    ];

    /**
     * Super Admin bypass and role specifications.
     */
    public array $superAdmin = [
        'role' => null,
        'bypass' => false,
    ];

    /**
     * Cache and optimization settings for authorization results.
     */
    public array $cache = [
        'enabled' => false,
        'ttl' => 3600,
        'prefix' => 'vima_',
    ];

    /**
     * Audit log configurations.
     */
    public array $audit = [
        'enabled' => true,
        'level' => 'all',
    ];

    /**
     * Quickly checks if audit logging features are active.
     */
    public function isAuditEnabled(): bool
    {
        return (bool) ($this->audit['enabled'] ?? false);
    }

    /**
     * Retrieve the audit log level.
     */
    public function getAuditLevel(): string
    {
        return $this->audit['level'] ?? 'all';
    }

    /**
     * The view file to render on 403 Forbidden/Access Denied errors.
     * @var string
     */
    public string $view403 = 'Vima\CodeIgniter\Views\error_403';

    public function __construct()
    {
        parent::__construct();

        $this->user = [
            'current' => fn() => \App\Libraries\Vima\SimulationManager::getCurrentUser(),
            'resolver' => fn($user) => \App\Libraries\Vima\SimulationManager::getUserKey($user),
            'methods' => [
                'id' => 'getId'
            ],
            'segment' => null,
        ];

        $this->tables = new Tables();

        if (ENVIRONMENT !== 'testing' && session()->has('simulated_super_admin_role')) {
            $this->superAdmin = [
                'role' => session()->get('simulated_super_admin_role'),
                'bypass' => (bool) session()->get('simulated_super_admin_bypass'),
            ];
        }
    }

    // --------------------------------------------------------------------
    // Convenience Helpers
    // --------------------------------------------------------------------

    /**
     * Resolves the current user using the configured closure.
     */
    public function getCurrentUser(): mixed
    {
        if (property_exists($this, 'currentUser') && isset($this->currentUser) && is_callable($this->currentUser)) {
            return ($this->currentUser)();
        }

        if (isset($this->user['current']) && is_callable($this->user['current'])) {
            return ($this->user['current'])();
        }

        return null;
    }

    /**
     * Resolves a user's primary ID/Key using the configured resolver closure.
     */
    public function getUserKey(mixed $user): mixed
    {
        if (property_exists($this, 'userResolver') && isset($this->userResolver) && is_callable($this->userResolver)) {
            return ($this->userResolver)($user);
        }

        if (isset($this->user['resolver']) && is_callable($this->user['resolver'])) {
            return ($this->user['resolver'])($user);
        }

        if (is_object($user) && isset($this->user['methods']['id'])) {
            $method = $this->user['methods']['id'];
            return method_exists($user, $method) ? $user->$method() : ($user->id ?? null);
        }

        if (is_object($user) && property_exists($this, 'userMethods') && isset($this->userMethods['id'])) {
            $method = $this->userMethods['id'];
            return method_exists($user, $method) ? $user->$method() : ($user->id ?? null);
        }

        return is_array($user) ? ($user['id'] ?? null) : ($user->id ?? null);
    }

    public function isCacheEnabled(): bool
    {
        // Allow dynamic session overrides for the simulation console
        if (ENVIRONMENT !== 'testing' && session()->has('simulated_cache_enabled')) {
            return (bool) session()->get('simulated_cache_enabled');
        }
        return (bool) ($this->cache['enabled'] ?? false);
    }

    /**
     * Determine if a role is a configured Super Admin, and if bypass is active.
     */
    public function shouldBypassForSuperAdmin(string $roleName): bool
    {
        if (!($this->superAdmin['bypass'] ?? false)) {
            return false;
        }

        $target = $this->superAdmin['role'];
        return is_string($target) ? ($target === $roleName) : false;
    }

    /**
     * Maps the CI4 array-structured configuration back to the strict Core DTO.
     */
    public function toVimaConfig(): VimaConfig
    {
        return new VimaConfig(
            tables: $this->tables,
            columns: $this->columns,
            setup: $this->setup,
            userMethods: new UserMethods(
                id: $this->user['methods']['id'] ?? null
            ),
            policy: new PolicyConfig(
                registered: $this->policies['registered']
            ),
            superAdminRole: $this->superAdmin['role'] ?? null,
            superAdminBypass: $this->superAdmin['bypass'] ?? false,
            userResolver: $this->user['resolver'] ?? null,
            cacheEnabled: $this->isCacheEnabled(),
            cacheTTL: $this->cache['ttl'] ?? 3600,
            cachePrefix: $this->cache['prefix'] ?? 'vima_',
            auditLevel: $this->getAuditLevel()
        );
    }
}