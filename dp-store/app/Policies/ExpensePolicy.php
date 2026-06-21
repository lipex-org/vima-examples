<?php

namespace App\Policies;

use Vima\Core\Policy\Contracts\PolicyInterface;
use Vima\Core\Policy\DTOs\AccessContext;
use App\Entities\Expense;

class ExpensePolicy implements PolicyInterface
{
    public static function getResource(): string
    {
        return Expense::class;
    }

    private function hasRole(AccessContext $context, string $role): bool
    {
        return $context->hasRole($role);
    }

    private function checkTenant(AccessContext $context, Expense $expense): bool
    {
        if ((int) $context->user->tenant_id !== (int) $expense->tenant_id) {
            return false;
        }

        if ($context->namespace) {
            $nsTenantId = (int) str_replace('tenant_', '', $context->namespace);
            if ($nsTenantId !== (int) $expense->tenant_id) {
                return false;
            }
        }

        return true;
    }

    public function canCreate(AccessContext $context, ?Expense $expense = null): bool
    {
        if ($expense && !$this->checkTenant($context, $expense)) {
            return false;
        }

        return !$this->hasRole($context, 'viewer');
    }

    public function canRead(AccessContext $context, Expense $expense): bool
    {
        return $this->checkTenant($context, $expense);
    }

    public function canApprove(AccessContext $context, Expense $expense): bool
    {
        if (!$this->checkTenant($context, $expense)) {
            return false;
        }

        // 1. Environmental & Temporal constraints
        // Time window restriction: 8:00 AM to 5:00 PM (Hour 8 to 17)
        $hour = \App\Libraries\Vima\SimulationManager::getSimulatedHour();
        if ($hour < 8 || $hour >= 17) {
            return false;
        }

        // IP / Location restriction: secure subnet 192.168.1.0/24 or localhost for dev
        $ip = \App\Libraries\Vima\SimulationManager::getSimulatedIp();
        if (!str_starts_with($ip, '192.168.1.') && $ip !== '127.0.0.1' && $ip !== '::1') {
            return false;
        }

        // 2. Hybrid threshold rules
        $amount = (float) $expense->amount;

        // Rule A: Under $1,000
        if ($amount < 1000.00) {
            return $this->hasRole($context, 'team_lead') || 
                   $this->hasRole($context, 'manager') || 
                   $this->hasRole($context, 'cfo') || 
                   $this->hasRole($context, 'admin') ||
                   $this->hasRole($context, 'financier');
        }

        // Rule B: Between $1,000 and $10,000
        if ($amount >= 1000.00 && $amount <= 10000.00) {
            $isManagerOrAbove = $this->hasRole($context, 'manager') || 
                                $this->hasRole($context, 'cfo') || 
                                $this->hasRole($context, 'admin');

            return $isManagerOrAbove && (int) $context->user->department_id === (int) $expense->department_id;
        }

        // Rule C: Over $10,000
        if ($amount > 10000.00) {
            // Cannot be approved if user is the creator (Conflict of Interest)
            if ((int) $context->user->id === (int) $expense->creator_id) {
                return false;
            }

            return $this->hasRole($context, 'cfo') || $this->hasRole($context, 'admin');
        }

        return false;
    }
}
