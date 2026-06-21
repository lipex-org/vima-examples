<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Entities\Project;
use App\Entities\Expense;
use CodeIgniter\Shield\Models\UserModel;
use App\Libraries\Vima\SimulationManager;
use App\Database\Seeds\VimaSeeder;
use Vima\Core\Policy\Services\PolicyRegistry;
use function Vima\Core\resolve;

/**
 * @internal
 */
final class VimaCheckpointsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $namespace = null;
    protected $seed = VimaSeeder::class;

    protected function setUp(): void
    {
        // Reset shared services to ensure fresh DB connections in models/repositories
        \Config\Services::reset();

        // Disable foreign keys check before parent setup/migrating/seeding
        $db = \Config\Database::connect();
        $db->query('PRAGMA foreign_keys = OFF');

        parent::setUp();

        // Clear simulated context
        SimulationManager::clearOverrides();

        // Force re-initialize VimaRegistrar
        \Vima\CodeIgniter\Support\VimaRegistrar::init(true);

        $db->query('PRAGMA foreign_keys = ON');

        // Clear Vima cache
        service('vima_cache')->clear();
    }



    private function setSimulatedUser(string $username, ?array $overrides = null): void
    {
        $userModel = model(UserModel::class);
        $user = $userModel->where('username', $username)->first();
        $this->assertNotNull($user, "User {$username} should exist in seeded database.");

        auth()->logout();
        auth()->loginById($user->id);

        $db = \Config\Database::connect();
        $row = $db->table('users')->where('id', $user->id)->get()->getRow();

        $mergedOverrides = array_merge([
            'tenant_id' => $row->tenant_id ?? 1,
            'department_id' => $row->department_id ?? 101,
            'is_dept_head' => $row->is_dept_head ?? 0,
            'simulated_time' => '10:00',
            'simulated_ip' => '192.168.1.5',
        ], $overrides ?? []);

        SimulationManager::setOverrides($mergedOverrides);

        // Clear Vima cache and reset the singleton policy registry to avoid any stale cache/evaluators
        PolicyRegistry::reset();
        service('vima_cache')->clear();
    }

    /**
     * 1. The RBAC Foundation (The Baseline Checks)
     */
    public function testRbacFoundationCheckpoints(): void
    {
        // Project belonging to Tenant 1, Engineering (101)
        $project = new Project([
            'name' => 'Test Project',
            'description' => 'Test Desc',
            'department_id' => 101,
            'creator_id' => 99,
            'tenant_id' => 1,
        ]);

        // A. Admin can create, read, update, and delete any Project.
        $this->setSimulatedUser('admin');

        $this->assertTrue(can('project.create', $project));
        $this->assertTrue(can('project.read', $project));
        $this->assertTrue(can('project.update', $project));
        $this->assertTrue(can('project.delete', $project));

        // B. Manager can create and edit projects, but cannot delete them.
        $this->setSimulatedUser('manager');
        $this->assertTrue(can('project.create', $project));
        $this->assertTrue(can('project.read', $project));
        $this->assertTrue(can('project.update', $project));
        $hasDelete = can('project.delete', $project);
        $this->assertFalse($hasDelete);

        // C. Viewer can only read projects.
        $this->setSimulatedUser('viewer');
        $this->assertFalse(can('project.create', $project));
        $this->assertTrue(can('project.read', $project));
        $this->assertFalse(can('project.update', $project));
        $this->assertFalse(can('project.delete', $project));
    }

    /**
     * 2. Dynamic ABAC Boundaries
     * Scenario A: Contextual Ownership & Hierarchy
     */
    public function testContextualOwnershipAndHierarchy(): void
    {
        // Project belonging to Tenant 1, Sales & Marketing (103), created by Admin (User 1)
        $marketingProject = new Project([
            'name' => 'Sales Blitz',
            'description' => 'Marketing',
            'department_id' => 103,
            'creator_id' => 1, // Admin is creator
            'tenant_id' => 1,
        ]);

        // A Manager (from Engineering department 101) attempts to edit Sales & Marketing (103) Project.
        $this->setSimulatedUser('manager', ['department_id' => 101]);
        // Deny because manager.department_id !== project.department_id
        $this->assertFalse(can('project.update', $marketingProject));

        // Allow if user is Manager AND user.department_id === project.department_id
        $this->setSimulatedUser('manager', ['department_id' => 103]);
        $this->assertTrue(can('project.update', $marketingProject));

        // Advanced Twist: Allow access if user.id === project.creator_id
        $this->setSimulatedUser('viewer', ['department_id' => 101]); // Viewer role generally can't edit
        $viewer = SimulationManager::getCurrentUser();
        $myProject = new Project([
            'name' => 'My Viewer Project',
            'description' => 'Owned by viewer',
            'department_id' => 101,
            'creator_id' => $viewer->id, // Created by this viewer
            'tenant_id' => 1,
        ]);
        $this->assertTrue(can('project.update', $myProject));

        // Advanced Twist: Or if the user is a Department_Head of that project's division.
        $this->setSimulatedUser('viewer', [
            'department_id' => 103,
            'is_dept_head' => 1
        ]);
        $this->assertTrue(can('project.update', $marketingProject));
    }

    /**
     * 2. Dynamic ABAC Boundaries
     * Scenario B: Environmental & Temporal Constraints
     */
    public function testEnvironmentalAndTemporalConstraints(): void
    {
        $expense = new Expense([
            'amount' => 500.00, // Small amount
            'description' => 'Office supplies',
            'department_id' => 102,
            'creator_id' => 4,
            'tenant_id' => 1,
        ]);

        // A. Time-window restriction: allowed only if current server time is between 8:00 AM and 5:00 PM
        $this->setSimulatedUser('financier', ['simulated_time' => '10:00', 'simulated_ip' => '192.168.1.5']);
        $this->assertTrue(can('expense.approve', $expense));

        $this->setSimulatedUser('financier', ['simulated_time' => '23:00', 'simulated_ip' => '192.168.1.5']);
        $this->assertFalse(can('expense.approve', $expense), "Should deny off-hours approval at 11:00 PM.");

        // B. IP / Location restriction: allowed only if secure subnet/VPN (192.168.1.0/24 or local)
        $this->setSimulatedUser('financier', ['simulated_time' => '10:00', 'simulated_ip' => '8.8.8.8']);
        $this->assertFalse(can('expense.approve', $expense), "Should deny approval from public network IP.");
    }

    /**
     * 3. The Hybrid Sweet Spot (RBAC + ABAC Combined)
     */
    public function testHybridSweetSpotCheckpoints(): void
    {
        // A. If the expense is under $1,000, any user with the role Team_Lead can approve it.
        $smallExpense = new Expense([
            'amount' => 500.00,
            'description' => 'Hosting',
            'department_id' => 101, // Engineering
            'creator_id' => 2,
            'tenant_id' => 1,
        ]);
        $this->setSimulatedUser('team_lead', ['department_id' => 102]); // lead of finance
        $this->assertTrue(can('expense.approve', $smallExpense), "Team Lead can approve any small expense under 1000.");

        // B. If between $1,000 and $10,000, must have Manager role AND belong to the same department as expense.
        $midExpense = new Expense([
            'amount' => 5000.00,
            'description' => 'Servers',
            'department_id' => 101, // Engineering
            'creator_id' => 4,
            'tenant_id' => 1,
        ]);

        // Manager but in finance (102) -> Deny
        $this->setSimulatedUser('manager', ['department_id' => 102]);
        $this->assertFalse(can('expense.approve', $midExpense));

        // Manager in engineering (101) -> Allow
        $this->setSimulatedUser('manager', ['department_id' => 101]);
        $this->assertTrue(can('expense.approve', $midExpense));

        // C. If over $10,000, requires CFO role, and cannot be approved if CFO is the one who created it.
        $largeExpense = new Expense([
            'amount' => 15000.00,
            'description' => 'Security Audit',
            'department_id' => 102, // Finance
            'creator_id' => 6, // CFO (User 6)
            'tenant_id' => 1,
        ]);

        // CFO trying to approve their own large expense -> Deny (Conflict of Interest)
        $this->setSimulatedUser('cfo');
        $this->assertFalse(can('expense.approve', $largeExpense));

        // CFO trying to approve someone else's large expense -> Allow
        $largeExpense->creator_id = 4; // Created by Team Lead
        $this->assertTrue(can('expense.approve', $largeExpense));
    }

    /**
     * 4. Multi-Tenancy & Tenant Isolation
     */
    public function testMultiTenancyAndTenantIsolation(): void
    {
        // Project belonging to Tenant 2
        $tenant2Project = new Project([
            'name' => 'Isolated Work',
            'description' => 'Tenant 2',
            'department_id' => 201,
            'creator_id' => 7,
            'tenant_id' => 2,
        ]);

        // Admin of Tenant 1 tries to read or update Tenant 2's project -> Deny
        $this->setSimulatedUser('admin', ['tenant_id' => 1]);
        $this->assertFalse(can('project.read', $tenant2Project));
        $this->assertFalse(can('project.update', $tenant2Project));

        // Ian is a special hybrid user: Admin in Tenant 1 (via tenant_1:admin) but Viewer in Tenant 2 (via tenant_2:viewer)
        // Let's check Namespaced roles!

        // A. Ian in Tenant 1 context (tenant_1:admin) -> can update a Tenant 1 project
        $tenant1Project = new Project([
            'name' => 'T1 Project',
            'description' => 'T1 Description',
            'department_id' => 101,
            'creator_id' => 2,
            'tenant_id' => 1,
        ]);

        // We set Ian's active simulation tenant to 1
        $this->setSimulatedUser('ian', ['tenant_id' => 1]);
        $this->assertTrue(can('tenant_1:project.update', $tenant1Project));
        $this->assertTrue(can('project.update', $tenant1Project));

        // B. Ian in Tenant 2 context (tenant_2:viewer) -> cannot update Tenant 2 project, only read
        $this->setSimulatedUser('ian', ['tenant_id' => 2]);
        $this->assertTrue(can('project.read', $tenant2Project));
        $this->assertFalse(can('project.update', $tenant2Project));
    }
}
