<?php

namespace App\Policies;

use Vima\Core\Policy\Contracts\PolicyInterface;
use Vima\Core\Policy\DTOs\AccessContext;
use App\Entities\Project;

class ProjectPolicy implements PolicyInterface
{
    public static function getResource(): string
    {
        return Project::class;
    }

    private function hasRole(AccessContext $context, string $role): bool
    {
        return $context->hasRole($role);
    }

    private function checkTenant(AccessContext $context, Project $project): bool
    {
        // Direct tenant ID check
        if ((int) $context->user->tenant_id !== (int) $project->tenant_id) {
            return false;
        }

        // If a namespace is present (e.g., 'tenant_1'), make sure it matches the project's tenant_id
        if ($context->namespace) {
            $nsTenantId = (int) str_replace('tenant_', '', $context->namespace);
            if ($nsTenantId !== (int) $project->tenant_id) {
                return false;
            }
        }

        return true;
    }

    public function canCreate(AccessContext $context, ?Project $project = null): bool
    {
        if ($project && !$this->checkTenant($context, $project)) {
            return false;
        }

        return $this->hasRole($context, 'admin') || $this->hasRole($context, 'manager');
    }

    public function canRead(AccessContext $context, Project $project): bool
    {
        return $this->checkTenant($context, $project);
    }

    public function canUpdate(AccessContext $context, Project $project): bool
    {
        if (!$this->checkTenant($context, $project)) {
            return false;
        }

        // 1. Admin can update any project
        if ($this->hasRole($context, 'admin')) {
            return true;
        }

        // 2. Creator can update their own project
        if ((int) $context->user->id === (int) $project->creator_id) {
            return true;
        }

        // 3. Department Head of the project's department can update
        if ((int) $context->user->is_dept_head === 1 && (int) $context->user->department_id === (int) $project->department_id) {
            return true;
        }

        // 4. Managers can update projects in their own department
        if ($this->hasRole($context, 'manager') && (int) $context->user->department_id === (int) $project->department_id) {
            return true;
        }

        return false;
    }

    public function canDelete(AccessContext $context, Project $project): bool
    {
        if (!$this->checkTenant($context, $project)) {
            return false;
        }

        // Only Admin can delete
        return $this->hasRole($context, 'admin');
    }
}
