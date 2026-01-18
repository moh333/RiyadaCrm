# vtiger CRM to Laravel: Architecture Bridge & Usage Contract

## 1. Introduction & Purpose
This document serves as the **Architecture Bridge** between the legacy vtiger CRM system and the new Laravel-based clean re-architecture. It acts as the single source of truth for how the two systems coexist, share data, and eventually diverge.

**Primary Goal:** To rebuild the vtiger CRM functionality in a modern, testable, and scalable Laravel environment using Domain-Driven Design (DDD), while strictly maintaining compatibility with the existing vtiger database schema.

---

## 2. System Roles

### 2.1. vtiger CRM (Legacy)
**Role:** Source of Business Truth & Schema Authority.
*   **Database Schema Owner:** vtiger defines the database structure. No schema changes (migrations) are allowed in Laravel that conflict with vtiger.
*   **Operational Baseline:** The existing vtiger installation remains the verifiable "correct" behavior reference.
*   **Deprecation Candidate:** Ultimately destined for replacement, but currently essential for schema and data definition.

### 2.2. Laravel (Modern Re-architecture)
**Role:** Clean Re-implementation & Future Platform.
*   **Not a Port, But a Rebuild:** We do not refactor vtiger code; we re-implement business logic using Clean Architecture.
*   **Read-Write Peer:** Interacts directly with the existing vtiger database.
*   **Modern Standards:** Enforces strict typing, DIP (Dependency Inversion), and DDD principles.

---

## 3. Module Mapping Strategy
Mapping between legacy vtiger modules (often loosely organized) and Laravel's strict module structure.

| vtiger Module Code | Legacy Path (Approx) | Laravel Module Path | Namespace |
| :--- | :--- | :--- | :--- |
| `Leads` | `modules/Leads` | `src/Domain/Leads` | `Domain\Leads` |
| `Contacts` | `modules/Contacts` | `src/Domain/Contacts` | `Domain\Contacts` |
| `Accounts` | `modules/Accounts` | `src/Domain/Accounts` | `Domain\Accounts` |
| `Potentials` | `modules/Potentials` | `src/Domain/Opportunities` | `Domain\Opportunities` |
| `HelpDesk` | `modules/HelpDesk` | `src/Domain/Support` | `Domain\Support` |
| `Users` | `modules/Users` | `src/Domain/Users` | `Domain\Users` |
| *Custom* | `modules/Custom...` | `src/Domain/{BusinessDomain}` | `Domain\{Name}` |

**Rule:** New modules must follow the domain boundary, not strictly the legacy file folder name. (e.g., `Potentials` becomes `Opportunities` if that matches the ubiquitous language better).

---

## 4. Business Logic Extraction Rules
Since we cannot copy-paste legacy code, we follow this extraction process:

1.  **Analyze Behavior:** Run the feature in vtiger. Observe inputs and database side-effects.
2.  **Ignore Implementation:** Do not replicate vtiger's class hierarchy or "Utils" files.
3.  **Identify Invariants:** Determine what business rules *must* be true (e.g., "A Lead must have a valid status").
4.  **Re-implement per Domain:** Write the logic from scratch in the Laravel Domain Layer/Service Layer.

---

## 5. Clean Architecture + DDD Implementation
The Laravel system must strictly adhere to the following layers:

### 5.1. Domain Layer (`src/Domain`)
*   **Pure PHP:** No framework dependencies (no Eloquent models, no Facades).
*   **Entities:** Represents business objects.
*   **Value Objects:** Enforce validity of data (e.g., `EmailAddress`, `Money`).
*   **Repository Interfaces:** Define *how* we access data, implemented in Infrastructure.

### 5.2. Infrastructure Layer (`src/Infrastructure`)
*   **Persistence:** Eloquent implementations of Domain Repository Interfaces.
*   **Database Interaction:** Handles the mapping between the legacy vtiger DB schema and clean Domain Entities.
*   **External Services:** API clients, file system adapters.

### 5.3. Application Layer (`src/Application`)
*   **Use Cases:** Orchestrates domain logic (e.g., `CreateLeadUseCase`).
*   **DTOs:** Data Transfer Objects for input/output.

### 5.4. User Interface (`src/App/Http`)
*   **Controllers:** Thin HTTP handling.
*   **API Resources:** Transformers for JSON output.

---

## 6. Database Usage Rules (The "Iron Laws")
Since we share the database with vtiger:

1.  **READ-ONLY Schema:** **DO NOT** create Laravel migrations that alter `vtiger_*` tables.
    *   *Exception:* You may create *new* tables prefixed with `app_*` for Laravel-specific needs (e.g., specific job queues), but never modify legacy tables.
2.  **No Eloquent Leaks:** Eloquent Models must **never** be exposed to the Domain or UI layers. They are strictly for the Infrastructure layer.
3.  **Timestamps:** vtiger does not use standard Laravel `created_at`/`updated_at`.
    *   *Requirement:* Explicitly handle `createdtime` and `modifiedtime` in Repository implementations.
4.  **Soft Deletes:** Respect vtiger's deletion flag mechanism (usually `vtiger_crmentity.deleted = 0`).

---

## 7. EAV Handling Strategy (Entity-Attribute-Value)
vtiger relies heavily on an EAV model (tables like `vtiger_leadscf`, `vtiger_contactscf`).

**Strategy:**
1.  **Repository Abstraction:** The Domain Layer must *never* know about EAV. It simply asks for a `Lead` entity.
2.  **Infrastructure Mapping:**
    *   The Repository is responsible for joining the EAV tables (`base table` + `custom field table` + `crmentity`).
    *   Write operations must transact across these multiple tables to ensure consistency.
3.  **Performance:** Use optimized raw SQL or carefully tuned Eloquent relationships in the Infrastructure layer to avoid N+1 issues common with EAV.

---

## 8. Migration Workflow for Modules
To migrate a functional area from vtiger to Laravel:

1.  **Define Domain:** Identify the core Entity and its attributes.
2.  **Map Data:** Identify the exact vtiger tables joining to build this entity (e.g., `vtiger_leaddetails` + `vtiger_crmentity` + `vtiger_leadaddress`).
3.  **Create Interface:** Define `LeadRepositoryInterface`.
4.  **Implement Persistence:** Create `EloquentLeadRepository` that maps the ugly legacy schema to the clean Entity.

5. ID Generation Rule:
All new records must be created using vtiger’s native ID generation mechanism
(vtiger_crmentity_seq) to avoid primary key collisions between systems.
Laravel must never use auto-increment IDs for vtiger entities.

6.  **Test:** Write integration tests proving the Repository reads/writes correctly to the legacy DB.
7.  **Build Use Cases:** Implement business logic.

8.  **Expose API/UI:** Build the Laravel Controller.

---

## 9. Forbidden Practices (Anti-Patterns)
Violating these rules breaks the architecture bridge:

| Forbidden Action | Reason |
| :--- | :--- |
| **Using `DB::table('vtiger_...')` in Controllers** | Leaks infrastructure details to the UI. |
| **Modifying vtiger Core Tables** | Breaks the legacy vtiger application. |
| **Copy-pasting `include/utils` code** | Imports legacy technical debt. |
| **Global State (`global $adb`)** | vtiger relies on globals; Laravel app must be stateless and dependency-injected. |
| **Ignoring `vtiger_crmentity`** | Every standard module record MUST link to this central table for ID generation and ownership. |

---

## 10. Long-Term Vision
While we currently respect the vtiger database structure, the Clean Architecture ensures we are not trapped by it.

*   **Future Database Replacement:** Because our *Domain Layer* is agnostic of the database schema, we can eventually migrate data to a new, optimized schema by simply swapping the *Infrastructure* implementation.
*   **Strangler Fig Pattern:** We will slowly replace vtiger functionality piece-by-piece until the legacy application can be turned off, at which point the database can be refactored freely.
