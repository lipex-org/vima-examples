# 🛒 DP-Store: Vima PHP Security Playground

Welcome to the **DP-Store Vima Playground**, a contract-first access control showcase. This application demonstrates the implementation of both **Role-Based Access Control (RBAC)** and **Attribute-Based Access Control (ABAC)** using **Vima PHP** and its **CodeIgniter 4 (CI4)** integration.

It is designed to serve as a gold standard example for developers integrating Vima into their enterprise-grade PHP applications.

---

## 🚀 Getting Started

Follow these steps to spin up the playground locally:

1. **Install Dependencies**:
   ```bash
   composer install
   pnpm install
   ```

2. **Setup Database & Schema**:
   Make sure you copy the environment file and set up your SQLite/MySQL database, then run migrations:
   ```bash
   # Install jengo db
   php spark jengo:install db
   php spark migrate --all
   ```

3. **Synchronize Security Configurations**:
   Push the roles and permissions configured in `app/Config/Vima.php` (via `SetupLibrary`) directly into your database:
   ```bash
   php spark vima:sync
   ```

4. **Generate Vima Constants Maps**:
   Generate type-safe class maps of your permissions and roles to eliminate magic strings in your code:
   ```bash
   php spark vima:maps generate
   ```

5. **Start Dev Servers**:
   Run the CodeIgniter development server alongside Vite for front-end assets:
   ```bash
   # Start dev server - start ci4 dev server with vite dev server for frontend assets
   composer dev
   ```

---

## 🛡️ Key Features Showcased

The sidebar contains dedicated diagnostic sections that illustrate Vima's capabilities:

### 1. 🎛️ Simulation Switchboard (Playground Console)
Simulate different user contexts instantly:
- **Active Profiles**: Switch between users (e.g. `admin`, `manager`, `viewer`, or custom roles).
- **Temporary Attributes**: Override Tenant ID, Department ID, and Department Head status in the active session.
- **Environmental Context**: Simulate different hours of the day (Work Hours vs Off-Hours) and client IP locations to evaluate temporal and network ABAC boundaries.
- **Super Admin Toggle**: Test how the global bypass mechanism overrides checks dynamically when `superAdminBypass` is enabled.

### 2. 🚦 Vima Route Filters
Inspect how Vima shields endpoints using middleware route guards:
- **Admin Guard (`vima_rbac`)**: Restricts access entirely at the routing layer based on role matches (e.g., `vima_rbac:admin`).
- **Expense Guard (`vima_authorize`)**: Filters incoming requests based on specific permission checks (e.g., `vima_authorize:expense.approve`).

### 3. 🛑 Compliance & Denials
Evaluate Vima's compliance features:
- **Direct Freezes / Exclusions**: Impose immediate role or permission blocks (direct denies) on active users.
- See how Vima evaluates explicit denials first, ensuring a **fail-closed** architecture regardless of active role hierarchies.

### 4. ⚡ Caching & Metrics
Observe Vima's high-performance optimization:
- Check simulation metrics including **Cache Hits**, **Cache Misses**, and **Cache Writes** in real-time.
- Clear or toggle cache status dynamically to see the performance gains from pre-warmed cache configurations.

### 5. 📜 ABAC Rules & Policies
- Interactively test custom policies implementing `PolicyInterface` against active identity properties.
- Inspect how context parameters (e.g., department boundaries, transaction limits, and ownership) determine access outputs.

### 6. 📝 Live Security Audit Trail
- View the **Security Audit Trail** displaying a real-time stream of authorization queries.
- Detailed records include: evaluated user, permission checked, namespace, evaluation outcome (Allowed/Denied), failure reason, and JSON-serialized context arguments.

---

## 📂 Implementation Code Reference

When integrating Vima into your own codebase, check the following key files in this example for reference:
- **Configuration & Setup**: [Vima.php](file:///app/Config/Vima.php) & [Setup.php](file:///app/Libraries/Vima/Setup.php)
- **Controller Enforcement**: [Dashboard.php](file:///app/Controllers/Dashboard.php)
- **Route Interception**: [Routes.php](file:///app/Config/Routes.php)
- **Dynamic ABAC Policies**: [Policies/](file:///app/Policies/)

---

> [!NOTE]  
> This entire sandbox site and its security dashboard integration were built by an AI agent (Antigravity) with minimal human input.
