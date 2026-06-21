<?php

namespace App\Libraries\Vima;

use CodeIgniter\Shield\Models\UserModel;

class SimulationManager
{
    private static array $overrides = [];

    public static function setOverrides(array $overrides): void
    {
        self::clearOverrides();
        self::$overrides = $overrides;
    }

    public static function clearOverrides(): void
    {
        self::$overrides = [];
    }

    public static function getCurrentUser(): ?object
    {
        $user = auth()->user();
        if ($user) {
            $session = session();
            $user->tenant_id = self::$overrides['tenant_id'] ?? $session->get('simulated_tenant_id') ?? $user->tenant_id ?? 1;
            $user->department_id = self::$overrides['department_id'] ?? $session->get('simulated_dept_id') ?? $user->department_id ?? 101;
            $user->is_dept_head = self::$overrides['is_dept_head'] ?? $session->get('simulated_is_dept_head') ?? $user->is_dept_head ?? 0;
            $user->simulated_role = self::$overrides['simulated_role'] ?? $session->get('simulated_role') ?? null;
        }
        return $user;
    }

    public static function getUserKey(mixed $user): mixed
    {
        return $user->id ?? null;
    }

    public static function getSimulatedIp(): string
    {
        return self::$overrides['simulated_ip'] ?? session()->get('simulated_ip') ?? '192.168.1.5';
    }

    public static function getSimulatedHour(): int
    {
        $timeStr = self::$overrides['simulated_time'] ?? session()->get('simulated_time') ?? '10:00';
        return (int) explode(':', $timeStr)[0];
    }
}
