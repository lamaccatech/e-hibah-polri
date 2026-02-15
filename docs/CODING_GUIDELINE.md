# Coding Guidelines

A comprehensive guide for developers and AI agents working on this Laravel + Livewire application. These guidelines ensure code that is **readable**, **maintainable**, **testable**, and **secure**.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Directory Structure](#directory-structure)
3. [Language Policy](#language-policy)
4. [PHP Conventions](#php-conventions)
5. [Enums](#enums)
6. [Laravel Conventions](#laravel-conventions)
7. [Livewire Components](#livewire-components)
8. [Repositories](#repositories)
9. [ViewModels](#viewmodels)
10. [Blade Views & Flux UI](#blade-views--flux-ui)
11. [Localization](#localization)
12. [Database & Eloquent](#database--eloquent)
13. [Testing](#testing)
14. [Error Handling](#error-handling)
15. [Events & Listeners](#events--listeners)
16. [Security](#security)
17. [Performance](#performance)
18. [Git & Version Control](#git--version-control)

---

## Architecture Overview

This application follows a **layered architecture** pattern:

```
Request → Livewire Component → ViewModel → Repository → Model → Database
                ↓
            Blade View (with Flux UI)
```

**Layer Responsibilities:**

| Layer | Responsibility |
|-------|----------------|
| **Livewire Component** | Handle user interactions, validation, orchestrate data flow |
| **ViewModel** | Transform and format data for presentation |
| **Repository** | Encapsulate database queries and business logic |
| **Model** | Represent database entities and relationships |

---

## Directory Structure

```
app/
├── Concerns/              # Shared traits (validation rules, behaviors)
├── Enums/                 # Enumerations to avoid magic strings
├── Events/                # Domain events
├── Exceptions/            # Custom exception classes
├── Http/
│   ├── Controllers/       # Traditional HTTP controllers (API, redirects)
│   └── Requests/          # Form request validation classes
├── Listeners/             # Event listeners
├── Livewire/
│   ├── Actions/           # Reusable Livewire actions (e.g., Logout)
│   ├── Auth/              # Authentication-related components
│   ├── Settings/          # Settings-related components
│   └── [Feature]/         # Feature-grouped components
├── Models/                # Eloquent models
├── Notifications/         # Notification classes
├── Policies/              # Authorization policies
├── Presenters/            # Presenter classes for complex model display logic
├── Providers/             # Service providers
├── Repositories/          # Data access layer
│   └── Concerns/          # Shared repository traits
├── Services/              # Business logic services
└── ViewModels/            # Presentation data transformers
    └── Concerns/          # Shared view model traits

resources/
├── views/
│   ├── components/        # Blade components
│   ├── flux/              # Flux UI component overrides
│   ├── layouts/           # Layout templates
│   ├── livewire/          # Livewire component views
│   └── partials/          # Reusable view partials

tests/
├── Browser/               # Pest browser tests
├── Feature/               # Feature/integration tests
└── Unit/                  # Unit tests
```

---

## Language Policy

**Default language is English.** Indonesian is used only in specific, well-defined contexts.

### What Uses English

| Context | Examples |
|---------|---------|
| Class names | `GrantProposal`, `UnitRegistration`, `FileUploader` |
| Method names | `submitProposal()`, `findActiveUsers()`, `isVerified()` |
| Variable & property names | `$isActive`, `$hasPermission`, `$activeMembers` |
| Constants | `MAX_ATTEMPTS`, `DEFAULT_TIMEOUT` |
| Comments & PHPDoc | `// Prevent race conditions`, `@return Collection<int, User>` |
| Test names | `test('user can create a project')` |
| Commit messages | `feat: add project creation with validation` |
| Enum case names | `Pending`, `Verified`, `OnHold` |
| Route names | `admin.dashboard`, `grant.proposal` |
| Config keys | `services.api.key` |
| Blade component names | `<x-project-card>`, `<x-settings-layout>` |

### What Uses Indonesian

| Context | Examples |
|---------|---------|
| Database column names (business domain tables) | `nama_lengkap`, `nomor_telepon`, `keterangan` |
| Database table names (business domain) | `hibah`, `pemberi_hibah`, `unit` |
| Foreign keys (business domain) | `id_hibah`, `id_unit`, `id_pemberi_hibah` |
| User-facing UI text (via localization files) | `__('page.login.title')` → "Masuk ke akun Anda" |
| Validation messages (via localization files) | `__('validation.registration.nik-unique')` |
| Localization keys element names | `label-email`, `submit-button`, `error-throttle` |
| Enum backing values (business domain) | `'usulan'`, `'perjanjian'` |

### Rules

1. **Never mix languages in the same scope** — A method name, variable, or comment should be entirely in one language
2. **English is always the default** — When in doubt, use English
3. **Indonesian database names stay in the database layer** — Model properties that map to Indonesian columns use snake_case to match, but derived/computed properties use English camelCase
4. **Localization handles translation** — Never hardcode Indonesian text in Blade views or PHP code; always use `__()` helper

```php
// ✅ CORRECT - English code, Indonesian stays in DB layer
$grant = Hibah::query()
    ->where('nama_hibah', $name)       // 'nama_hibah' is a database column
    ->first();

$isSubmitted = $grant->tahapan === 'perjanjian';  // English variable
$grantName = $grant->nama_hibah;                   // Accessing Indonesian DB column is fine

// ❌ WRONG - Indonesian variable/method names
$hibahAktif = Hibah::find($id);          // Should be: $activeGrant
$sudahDiajukan = true;                    // Should be: $isSubmitted
function dapatkanPemberi() {}             // Should be: function getProvider() {}
```

---

## PHP Conventions

### General Style

- **Always use curly braces** for control structures, even single-line bodies
- **Explicit return types** on all methods and functions
- **Type hints** for all method parameters
- Use **constructor property promotion** (PHP 8+)
- Run `vendor/bin/sail bin pint --dirty` before committing

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Classes | PascalCase | `UserRepository` |
| Methods | camelCase | `findActiveUsers()` |
| Properties | camelCase | `$isVerified` |
| Constants | SCREAMING_SNAKE_CASE | `MAX_ATTEMPTS` |
| Enum Cases | PascalCase | `PaymentStatus::Pending` |

### Descriptive Naming

Prefer descriptive names that reveal intent:

```php
// Good
public function isEligibleForSubmission(): bool
public function calculateTotalWithTax(float $subtotal): float
private bool $hasCompletedOnboarding;

// Avoid
public function check(): bool
public function calc(float $s): float
private bool $done;
```

### Boolean Naming

Prefix boolean variables and methods with `is`, `has`, `can`, `should`:

```php
// Good - Clear boolean intent
$isActive = $user->deleted_at === null;
$hasPermission = $user->can('edit', $project);
$shouldNotifyUser = $settings->notifications_enabled;
public function isVerified(): bool
public function hasActiveSubscription(): bool
public function canAccessDashboard(): bool

// Avoid - Unclear if boolean
$active = $user->deleted_at === null;
$permission = $this->checkAccess($userId);
```

### Variable Naming

Use descriptive, unambiguous names:

```php
// Good - Clear intent
$activeMembers = $users->filter(fn ($u) => $u->deleted_at === null);
$projectToArchive = $this->projectRepository->find($projectId);
$authorDisplayName = $author?->name ?? 'Anonymous';

// Avoid - Ambiguous single letters
$u = $this->userRepository->find($id);
$a = $users->filter(fn ($x) => $x->deleted_at === null);

// Avoid - Misleading names
$user = $this->getAdmin($id);  // It's an admin, not a user!
$data = $this->getProjects();  // 'data' is too generic
```

### PHPDoc Blocks

Use PHPDoc for complex types, array shapes, and public APIs:

```php
/**
 * Find users matching the given criteria.
 *
 * @param array{status?: string, role?: string} $filters
 * @return Collection<int, User>
 */
public function findByCriteria(array $filters): Collection
{
    // ...
}
```

Avoid redundant PHPDoc when types are self-documenting:

```php
// PHPDoc unnecessary - the signature is clear
public function findById(int $id): ?User
{
    return User::find($id);
}
```

### Constructor Property Promotion

```php
// Good
public function __construct(
    private readonly UserRepository $userRepository,
    private readonly CacheManager $cache,
) {}

// Avoid empty constructors
public function __construct() {} // Remove if empty
```

### Avoid Deep Nesting

Use early returns to flatten conditional logic:

```php
// Good - Early returns, flat structure, easy to read
public function processSubmission(User $user, int $itemId): Submission
{
    $item = $this->itemRepository->find($itemId);
    if ($item === null) {
        throw new InvalidArgumentException('Item tidak ditemukan');
    }

    if ($item->code === null) {
        throw new InvalidArgumentException('Item tidak valid');
    }

    $sequence = $this->getNextSequence($item->code);
    $reference = $this->generateReference($item, $sequence);

    return Submission::create([
        'user_id' => $user->id,
        'reference' => $reference,
        'sequence' => $sequence,
    ]);
}

// Avoid - Deep nesting, hard to follow
public function processSubmission(User $user, int $itemId): Submission
{
    $item = $this->itemRepository->find($itemId);
    if ($item !== null) {
        if ($item->code !== null) {
            $sequence = $this->getNextSequence($item->code);
            if ($sequence !== null) {
                // ... more nesting
            }
        }
    }
}
```

### Function Length

Keep functions focused and short (ideally < 30 lines). Extract helper methods for complex logic:

```php
// Good - Small, focused methods
public function registerMember(array $validated): User
{
    $user = $this->createUser($validated);
    $this->createProfile($user, $validated);
    $this->assignDefaultRole($user);
    $this->notifyAdmins($user);

    return $user;
}

private function createUser(array $data): User { /* 10-15 lines */ }
private function createProfile(User $user, array $data): void { /* 10-15 lines */ }
private function assignDefaultRole(User $user): void { /* 5-10 lines */ }
```

### Method Parameters

For methods with many parameters, use arrays with PHPDoc type hints:

```php
// Good - Array with documented shape
/**
 * Search users with filters.
 *
 * @param array{query?: string, status?: string, roleId?: int, perPage?: int} $filters
 */
public function searchUsers(array $filters = []): LengthAwarePaginator
{
    return User::query()
        ->when($filters['query'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
        ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
        ->when($filters['roleId'] ?? null, fn ($q, $roleId) => $q->where('role_id', $roleId))
        ->paginate($filters['perPage'] ?? 15);
}

// Avoid - Too many positional parameters
public function searchUsers(
    ?string $query,
    ?string $status,
    ?int $roleId,
    int $page,
    int $perPage,
): LengthAwarePaginator
```

### Comments

Use comments sparingly — code should be self-documenting:

```php
// Good - Explains WHY, not WHAT
// Use lockForUpdate() to prevent race conditions during concurrent submissions
$lastSequence = Sequence::where('code', $code)
    ->lockForUpdate()
    ->first();

// Good - Documents non-obvious business rule
// Units at MABES level don't have parent units - this is expected
if ($unit->level === UnitLevel::Mabes) {
    return;
}

// Avoid - States the obvious
// Get user from database
$user = $this->userRepository->find($userId);

// Avoid - Comment instead of better naming
// This is the admin who performed the action
$u = User::find($actorId);
// Should be: $actor = User::find($actorId);
```

---

## Enums

Use PHP enums to eliminate magic strings and provide type safety for fixed sets of values.

### Enum Structure

```
app/Enums/
├── VerificationStatus.php
├── ProjectStatus.php
├── PaymentMethod.php
└── UserRole.php
```

### Basic Enum (Backed Enum)

Use string-backed enums for values stored in the database:

```php
<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Review',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }

    /**
     * Get the badge color for UI display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Verified => 'green',
            self::Rejected => 'red',
        };
    }

    /**
     * Get the icon name for UI display.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Verified => 'check-circle',
            self::Rejected => 'x-circle',
        };
    }
}
```

### Enum with Additional Methods

```php
<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::OnHold => 'On Hold',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::Active => 'green',
            self::OnHold => 'yellow',
            self::Completed => 'blue',
            self::Archived => 'zinc',
        };
    }

    /**
     * Check if the status allows editing.
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Active, self::OnHold]);
    }

    /**
     * Get statuses available for dropdown filters.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }

    /**
     * Get only active statuses (exclude archived).
     *
     * @return array<self>
     */
    public static function active(): array
    {
        return array_filter(
            self::cases(),
            fn (self $status) => $status !== self::Archived
        );
    }
}
```

### Using Enums in Models

```php
protected function casts(): array
{
    return [
        'status' => ProjectStatus::class,
        'verification_status' => VerificationStatus::class,
    ];
}

// Query scope using enum
public function scopeActive(Builder $query): Builder
{
    return $query->where('status', ProjectStatus::Active);
}
```

### Using Enums in Livewire

```php
public string $status = '';

public function mount(): void
{
    $this->status = ProjectStatus::Draft->value;
}

protected function rules(): array
{
    return [
        'status' => ['required', Rule::enum(ProjectStatus::class)],
    ];
}

public function render()
{
    return view('livewire.projects.create', [
        'statuses' => ProjectStatus::options(),
    ]);
}
```

### Using Enums in Blade Views

```blade
{{-- Display with badge --}}
<flux:badge color="{{ $project->status->color() }}">
    {{ $project->status->label() }}
</flux:badge>

{{-- Dropdown/Select with enum options --}}
<flux:select wire:model="status" :label="__('Status')">
    @foreach ($statuses as $value => $label)
        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
    @endforeach
</flux:select>

{{-- Conditional rendering --}}
@if ($project->status->isEditable())
    <flux:button wire:click="edit">Edit</flux:button>
@endif
```

### Using Enums in Migrations

```php
public function up(): void
{
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('status')->default(ProjectStatus::Draft->value);
        $table->string('verification_status')->default(VerificationStatus::Pending->value);
        $table->timestampsTz();
    });
}
```

### Enum Guidelines

1. **Use backed enums** — Always use string-backed enums for database storage
2. **PascalCase for cases** — Enum cases should be `PascalCase` (e.g., `OnHold`, not `ON_HOLD`)
3. **Add helper methods** — Include `label()`, `color()`, and other presentation methods
4. **Provide static helpers** — Add `options()` for dropdowns, `active()` for filtered lists
5. **Cast in models** — Always cast enum columns in the model's `casts()` method
6. **Validate with Rule::enum()** — Use Laravel's built-in enum validation rule
7. **Compare with identity** — Use `===` to compare enum instances

### When to Use Enums

| Use Case | Example |
|----------|---------|
| Status fields | `ProjectStatus`, `OrderStatus`, `VerificationStatus` |
| Type classifications | `UserRole`, `PaymentMethod`, `NotificationType` |
| Fixed options | `Priority`, `Frequency`, `Visibility` |
| Configuration flags | `Feature`, `Permission` |

### When NOT to Use Enums

- Values that change frequently or are user-defined (use a database table instead)
- Large sets of values (50+ items)
- Values that need to be translated dynamically from the database

---

## Laravel Conventions

### Configuration & Environment

- Access config via `config()`, never `env()` outside config files
- Use `.env` for environment-specific values only

```php
// Good
$appName = config('app.name');

// Avoid
$appName = env('APP_NAME');
```

### Service Container

- Type-hint dependencies; let Laravel resolve them
- Avoid facades in classes where injection is possible
- Use facades in Blade views and quick scripts

### Named Routes

**NEVER use raw URL paths directly.** Always use named routes via `route()` for URL generation — in PHP code, Blade views, tests, and redirects. This ensures URLs stay consistent when paths change.

```php
// Good
return redirect()->route('dashboard');
route('users.show', $user);
$this->actingAs($user)->get(route('grant-planning.index'));

// NEVER do this
return redirect('/dashboard');
url('/users/' . $user->id);
$this->actingAs($user)->get('/grant-planning');
```

```blade
{{-- Good --}}
<a href="{{ route('grant-planning.index') }}">

{{-- NEVER do this --}}
<a href="/grant-planning">
```

### Form Requests

Create dedicated Form Request classes for validation:

```php
class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
```

### Artisan Commands

Use `vendor/bin/sail artisan make:*` commands for scaffolding:

```bash
vendor/bin/sail artisan make:model Project -mf    # Model with migration and factory
vendor/bin/sail artisan make:livewire ProjectList  # Livewire component
vendor/bin/sail artisan make:test ProjectTest      # Feature test
vendor/bin/sail artisan make:class ProjectRepository # Plain PHP class
```

---

## Livewire Components

### Component Structure

Organize components by feature in `app/Livewire/`:

```
app/Livewire/
├── Actions/           # Shared actions (e.g., Logout)
├── Auth/              # Authentication flows
├── Projects/          # Project feature
│   ├── Index.php
│   ├── Create.php
│   └── Show.php
└── Settings/          # Settings feature
    ├── Profile.php
    └── Appearance.php
```

### Component Guidelines

```php
<?php

namespace App\Livewire\Projects;

use App\Repositories\ProjectRepository;
use App\ViewModels\ProjectViewModel;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // 1. Public properties (bound to view)
    public string $search = '';
    public string $sortBy = 'created_at';

    // 2. Use mount() for initialization

    public function mount(): void
    {
        // Initialize state here
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Handle project deletion.
     */
    public function delete(int $projectId): void
    {
        $this->authorize('delete', Project::find($projectId));

        app(ProjectRepository::class)->delete($projectId);

        $this->dispatch('project-deleted');
    }

    public function render()
    {
        $projects = app(ProjectRepository::class)
            ->searchPaginated($this->search, $this->sortBy);

        return view('livewire.projects.index', [
            'projects' => ProjectViewModel::collection($projects),
        ]);
    }
}
```

### Validation in Livewire

For simple validation, use the `rules()` method:

```php
protected function rules(): array
{
    return [
        'email' => ['required', 'email', 'max:255'],
        'name' => ['required', 'string', 'max:255'],
    ];
}

public function save(): void
{
    $validated = $this->validate();
    // ...
}
```

For complex or reusable validation, extract to a trait:

```php
// app/Concerns/ProjectValidationRules.php
trait ProjectValidationRules
{
    protected function projectRules(?int $projectId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                Rule::unique(Project::class)->ignore($projectId),
            ],
        ];
    }
}
```

### Livewire Best Practices

1. **Keep components focused** — One primary responsibility per component
2. **Use events for cross-component communication** — `$this->dispatch('event-name')`
3. **Debounce search inputs** — `wire:model.live.debounce.300ms="search"`
4. **Show loading states** — Use `wire:loading` for better UX
5. **Authorize actions** — Always check permissions in action methods

---

## Repositories

Repositories encapsulate all database queries, keeping Livewire components and controllers thin.

### Repository Method Naming

| Operation | Pattern | Example |
|-----------|---------|---------|
| Get single | `find($id)` | `find($projectId)` |
| Get single by field | `findBy{Field}($value)` | `findBySlug($slug)` |
| Get single or fail | `findOrFail($id)` | `findOrFail($projectId)` |
| Get list | `all()` or `allFor{Context}()` | `allForUser($userId)` |
| Get with relations | `findWith{Related}($id)` | `findWithTasks($projectId)` |
| Search/filter | `search($params)` | `search($filters)` |
| Paginated list | `paginate()` or `searchPaginated()` | `searchPaginated($filters)` |
| Create | `create($data)` | `create($payload)` |
| Update | `update($model, $data)` | `update($project, $payload)` |
| Delete | `delete($id)` | `delete($projectId)` |
| Soft delete | `softDelete($id)` | `softDelete($projectId)` |
| Check existence | `exists($id)` | `exists($projectId)` |
| Count | `count($filters)` | `count($filters)` |

### Basic Repository

```php
<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProjectRepository
{
    public function find(int $id): ?Project
    {
        return Project::find($id);
    }

    public function findOrFail(int $id): Project
    {
        return Project::findOrFail($id);
    }

    public function allForUser(int $userId): Collection
    {
        return Project::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Search projects with pagination.
     */
    public function searchPaginated(
        string $search = '',
        string $sortBy = 'created_at',
        int $perPage = 15,
    ): LengthAwarePaginator {
        return Project::query()
            ->when($search, fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc($sortBy)
            ->paginate($perPage);
    }

    /**
     * @param array{name: string, description?: string, user_id: int} $data
     */
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    /**
     * @param array{name?: string, description?: string} $data
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Project::destroy($id);
    }
}
```

### Repository Guidelines

1. **One repository per aggregate root** — `ProjectRepository` handles `Project` and closely related queries
2. **Return Eloquent models or collections** — Let ViewModels handle transformation
3. **Accept primitives or arrays** — Avoid accepting request objects directly
4. **Use query scopes for reusable conditions** — Define scopes in the model
5. **Document array parameter shapes** — Use PHPDoc `@param array{...}` syntax
6. **Keep repositories focused on data access** — Move complex business logic to Services

### Shared Repository Traits

```php
// app/Repositories/Concerns/HandlesFiltering.php
trait HandlesFiltering
{
    protected function applyDateFilter(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));
    }
}
```

---

## ViewModels

ViewModels transform data for presentation, keeping formatting logic out of models and views.

### Basic ViewModel

```php
<?php

namespace App\ViewModels;

use App\Models\Project;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProjectViewModel implements Arrayable
{
    public function __construct(
        private readonly Project $project,
    ) {}

    /**
     * Create a collection of ViewModels from models.
     */
    public static function collection(Collection|LengthAwarePaginator $projects): Collection|LengthAwarePaginator
    {
        $transform = fn (Project $project) => new self($project);

        if ($projects instanceof LengthAwarePaginator) {
            $projects->through($transform);

            return $projects;
        }

        return $projects->map($transform);
    }

    public function __get(string $name): mixed
    {
        return $this->project->{$name};
    }

    public function createdAtForHumans(): string
    {
        return $this->project->created_at->diffForHumans();
    }

    public function createdAtFormatted(): string
    {
        return $this->project->created_at->format('M j, Y');
    }

    public function statusBadgeColor(): string
    {
        return $this->project->status->color();
    }

    public function truncatedDescription(int $limit = 100): string
    {
        return str($this->project->description)->limit($limit);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->project->id,
            'name' => $this->project->name,
            'status' => $this->project->status,
            'status_color' => $this->statusBadgeColor(),
            'created_at' => $this->createdAtFormatted(),
            'created_at_human' => $this->createdAtForHumans(),
        ];
    }
}
```

### ViewModel Guidelines

1. **Wrap a single model** — ViewModels are thin wrappers, not data aggregators
2. **Delegate to the model** — Use `__get()` to pass through model properties
3. **Format, don't fetch** — ViewModels should not make database queries
4. **Use static collection methods** — Provide `collection()` for batch transformation
5. **Implement `Arrayable`** — For easy JSON/API serialization

### Shared ViewModel Traits

```php
// app/ViewModels/Concerns/FormatsDateTime.php
trait FormatsDateTime
{
    protected function formatDate(?Carbon $date, string $format = 'M j, Y'): string
    {
        return $date?->format($format) ?? '-';
    }

    protected function humanDiff(?Carbon $date): string
    {
        return $date?->diffForHumans() ?? '-';
    }
}
```

---

## Blade Views & Flux UI

### Flux UI Components

Use `<flux:*>` components as the primary UI building blocks:

```blade
{{-- Forms --}}
<flux:input wire:model="email" :label="__('Email')" type="email" required />
<flux:textarea wire:model="description" :label="__('Description')" rows="4" />
<flux:select wire:model="status" :label="__('Status')">
    <flux:select.option value="active">Active</flux:select.option>
    <flux:select.option value="archived">Archived</flux:select.option>
</flux:select>

{{-- Buttons --}}
<flux:button variant="primary" type="submit">Save</flux:button>
<flux:button variant="danger" wire:click="delete">Delete</flux:button>
<flux:button wire:click="cancel">Cancel</flux:button>

{{-- Feedback --}}
<flux:badge color="green">Active</flux:badge>
<flux:heading size="lg">Projects</flux:heading>
```

### Blade Best Practices

```blade
{{-- Use translations for user-facing text --}}
<flux:button>{{ __('Save Changes') }}</flux:button>

{{-- Use wire: directives for Livewire integration --}}
<form wire:submit="save">
    <flux:input wire:model="name" :label="__('Name')" />
</form>

{{-- Show loading states --}}
<flux:button wire:loading.attr="disabled" wire:target="save">
    <span wire:loading.remove wire:target="save">Save</span>
    <span wire:loading wire:target="save">Saving...</span>
</flux:button>

{{-- Conditional rendering --}}
@if ($projects->isNotEmpty())
    @foreach ($projects as $project)
        <x-project-card :project="$project" />
    @endforeach
@else
    <p>{{ __('No projects found.') }}</p>
@endif
```

---

## Localization

### Translation Key Convention

This project uses **PHP translation files** with structured dot-notation keys following the pattern:

```
{context}.{section}.{element}
```

### File Structure

```
lang/
├── en/
│   ├── common.php        # Shared text (Save, Search, Log Out, etc.)
│   ├── page.php           # Page-specific text (login, registration, settings)
│   └── component.php      # Reusable component text (sidebar, user menu)
├── id/
│   ├── common.php
│   ├── page.php
│   └── component.php
```

### Context Prefixes

| Prefix | Use Case | Example Keys |
|--------|----------|--------------|
| `common.*` | Shared across many places | `common.save`, `common.logout`, `common.search` |
| `page.*` | Page-level text (headings, form labels, messages) | `page.login.title`, `page.profile.description` |
| `component.*` | Reusable UI component text | `component.sidebar.nav-dashboard` |
| `mail.*` | Email templates | `mail.reset-password.subject` |
| `notification.*` | Toast/flash notifications | `notification.profile-updated` |
| `validation.*` | Custom validation messages | `validation.name-required` |

### Element Naming Patterns

Use descriptive, **hyphenated** names for elements:

| Category | Pattern | Examples |
|----------|---------|----------|
| Headings | `title`, `sr-title`, `description` | `page.login.title` |
| Form fields | `label-{field}`, `placeholder-{field}` | `page.registration.label-email` |
| Buttons | `submit-button`, `action-{name}` | `page.login.submit-button` |
| States | `loading`, `success-title`, `error-{type}` | `page.login.success-body` |
| Options | `option-{value}` | `page.registration.option-type-a` |

### Usage in Code

```php
// In Blade templates
{{ __('page.login.title') }}
{{ __('common.save') }}

// With parameters
{{ __('page.login.success-body', ['email' => $email]) }}
{{ __('page.login.error-throttle', ['seconds' => $seconds]) }}

// In PHP code
$this->addError('email', __('page.login.error-throttle', [
    'seconds' => RateLimiter::availableIn($throttleKey),
]));
```

### Guidelines

1. **Always use `__()`** for user-facing text — never hardcode strings in views
2. **Use `common.*`** for text that appears in 3+ places (Save, Cancel, Back, etc.)
3. **Use `page.*`** for text specific to a single page or feature
4. **Use `component.*`** for text in reusable Blade/Livewire components
5. **Hyphenate element names** — use `label-email`, not `labelEmail` or `label_email`
6. **Keep both `en/` and `id/` in sync** — every key must exist in both languages

---

## Database & Eloquent

### Naming Convention Rules

**CRITICAL:** Use **consistent naming conventions within each table** — do not mix conventions.

#### Convention Categories

This project uses two naming conventions based on the model's purpose:

| Model Type | Convention | Example Tables |
|------------|-----------|----------------|
| **System/Background Models** | Laravel/English | `activity_logs`, `change_histories`, `notifications` |
| **Business/User-Facing Models** | Indonesian | `hibah`, `pemberi_hibah`, `unit` |

#### Single Convention Per Table Rule

**✅ DO:** Use one convention consistently for all fields in a table

```php
// Good - Indonesian convention (business model)
Schema::create('hibah', function (Blueprint $table) {
    $table->id();
    $table->string('nama_hibah');
    $table->string('jenis_hibah');
    $table->string('tahapan');
    $table->foreignId('id_pemberi_hibah')
        ->constrained('pemberi_hibah');
    $table->timestampsTz();
});

// Good - English convention (system model)
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->string('action');
    $table->string('message');
    $table->jsonb('metadata');
    $table->foreignId('user_id')
        ->constrained('users');
    $table->timestampsTz();
});
```

**❌ DON'T:** Mix conventions within a table

```php
// Bad - Mixed conventions ❌
Schema::create('hibah', function (Blueprint $table) {
    $table->string('nama_hibah');      // Indonesian
    $table->string('grant_name');      // English ❌ INCONSISTENT!
    $table->foreignId('provider_id')   // English ❌ INCONSISTENT!
        ->constrained('pemberi_hibah');
});
```

#### Timestamp Exception

**Always use Laravel's standard timestamp names** regardless of table convention:

```php
Schema::create('hibah', function (Blueprint $table) {
    $table->id();
    $table->string('nama_hibah');
    $table->timestampsTz();  // ✅ created_at, updated_at (NOT dibuat_pada, diperbarui_pada)
});
```

#### Foreign Key Naming

**English Convention (System Models):**
```php
$table->foreignId('user_id')->constrained('users');
// Pattern: [entity]_id
```

**Indonesian Convention (Business Models):**
```php
$table->foreignId('id_user')->constrained('users');
// Pattern: id_[entity]
```

**Must configure Eloquent relationships explicitly for Indonesian naming:**

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'id_user');
}
```

#### Dual Naming Convention Policy

**System Tables (English convention):**
- Purpose: Technical/framework features (authentication, logging, notifications)
- Examples: `users`, `activity_logs`, `change_histories`, `notifications`
- Foreign keys: `user_id`, `file_id` pattern

**Business Domain Tables (Indonesian convention):**
- Purpose: Business entities and domain concepts
- Foreign keys: `id_user`, `id_hibah` pattern

**Rationale:**
- System tables use English to align with Laravel conventions and international developer familiarity
- Business tables use Indonesian for clarity with stakeholders and domain experts
- Consistent within each domain ensures predictability

#### Bridge Convention

Some tables bridge between system and business domains. Document the foreign key naming choice:

```php
// The `unit` table (business/Indonesian) connects to `users` table (system/English)
// Uses `id_user` foreign key to match the business domain convention
$table->foreignId('id_user')->constrained('users');
```

**Rule:** Any table that bridges system and business domains should document the foreign key naming choice in migration comments.

#### Checklist for New Migrations

Before creating a migration, decide:

- [ ] Is this a system/background model? → Use **English** convention
- [ ] Is this a business/user-facing model? → Use **Indonesian** convention
- [ ] Are ALL fields following the chosen convention? (except timestamps)
- [ ] Are foreign keys named correctly for the convention?
- [ ] **Are all timestamps using `timestampsTz()` / `timestampTz()` / `softDeletesTz()`?** ← CRITICAL
- [ ] Is the `down()` method consistent with the `up()` method?

### Model Structure

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
```

### Query Guidelines

1. **Prefer `Model::query()`** over `DB::` facade
2. **Eager load relationships** to prevent N+1 queries
3. **Use query scopes** for reusable conditions
4. **Index frequently queried columns**

```php
// Good - eager loading
$projects = Project::query()
    ->with(['user', 'tasks'])
    ->active()
    ->paginate();

// Avoid - N+1 problem
$projects = Project::all();
foreach ($projects as $project) {
    echo $project->user->name; // Query per iteration!
}
```

### Migrations

**CRITICAL:** Always use timezone-aware timestamps for PostgreSQL compatibility.

```php
public function up(): void
{
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('status')->default('pending');
        $table->timestampTz('published_at')->nullable();
        $table->timestampsTz();

        $table->index(['user_id', 'status']);
    });
}
```

**Timezone-Aware Timestamps:**

```php
// ✅ CORRECT - Always use timezone-aware methods
$table->timestampsTz();                     // created_at, updated_at
$table->timestampTz('published_at');        // custom timestamp column
$table->softDeletesTz();                    // deleted_at with timezone

// ❌ WRONG - Never use non-timezone methods
$table->timestamps();
$table->timestamp('published_at');
$table->softDeletes();
```

### NULL vs Empty String

Use `nullable()` when:
- ✅ Value is truly optional/unknown
- ✅ Need to distinguish "not provided" (NULL) from "provided but empty" ('')
- ✅ Field represents optional relationship or external reference

Use NOT NULL (required) when:
- ✅ Value always exists from the source
- ✅ Field is required for business logic
- ✅ Can have a sensible default value

Use `default('')` (empty string) when:
- ✅ Field should always have a value but can be blank
- ✅ Simpler application logic (no NULL checks needed)

**Querying Differences:**

```php
// With nullable fields - need NULL checks
$users = User::whereNotNull('phone')->get();
$users = User::whereNull('email_verified_at')->get();

// With default empty string - simpler
$users = User::where('bio', '!=', '')->get();

// But consider: NULL has semantic meaning
// NULL phone = "not provided", empty phone = "provided but blank" ← different meanings
```

**Best Practice:** Prefer `nullable()` for optional data — it's semantically clearer. Use `default('')` only when empty string is a valid business value. Document the meaning of NULL in comments when it's not obvious.

---

## Testing

### Test Organization

```
tests/
├── Browser/          # Pest browser tests
├── Feature/          # Integration tests (HTTP, Livewire)
│   ├── Auth/
│   └── Settings/
└── Unit/             # Isolated unit tests
```

### Writing Tests with Pest

```php
<?php

use App\Livewire\Projects\Index;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('projects page displays user projects', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get('/projects')
        ->assertOk()
        ->assertSee($project->name);
});

test('user can create a project', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'New Project')
        ->set('description', 'A test project')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('project-created');

    expect(Project::where('name', 'New Project')->exists())->toBeTrue();
});

test('project name is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});
```

### Testing Guidelines

1. **Use factories** — Never manually insert test data
2. **One assertion per test** (when practical) — Keep tests focused
3. **Use descriptive test names** — Test names should read like specifications
4. **Test behavior, not implementation** — Focus on outcomes
5. **Run tests frequently** — `vendor/bin/sail artisan test --compact --filter=FeatureName`
6. **Test the happy path and edge cases** — Include validation errors, unauthorized access, empty states
7. **Isolate external services** — Mock APIs, use fakes for mail/queue/storage

### Writing Testable Code

Structure code to be easily testable:

```php
// Good - Dependencies are injectable, easy to mock
class ProjectCreator
{
    public function __construct(
        private readonly ProjectRepository $repository,
        private readonly NotificationService $notifications,
    ) {}

    public function create(array $data, User $user): Project
    {
        $project = $this->repository->create([...$data, 'user_id' => $user->id]);
        $this->notifications->notifyProjectCreated($project);

        return $project;
    }
}
```

**Testability principles:**
1. **Inject dependencies** — Use constructor injection, avoid `new` inside methods
2. **Avoid static calls** — Prefer injected services over facades in classes
3. **Keep methods small** — Easier to test focused behavior
4. **Separate queries from commands** — Repositories for data, Services for actions
5. **Use interfaces for external services** — Enables easy mocking

---

## Error Handling

### Exception Handling

- Let Laravel handle exceptions globally via `bootstrap/app.php`
- Create custom exceptions for domain-specific errors
- Log errors with context

```php
class ProjectLimitExceededException extends Exception
{
    public static function forUser(User $user): self
    {
        return new self("User {$user->id} has exceeded their project limit.");
    }
}
```

### Validation Errors in Livewire

```php
public function save(): void
{
    $this->validate();

    try {
        $this->projectRepository->create($this->all());
        $this->dispatch('project-created');
    } catch (ProjectLimitExceededException $e) {
        $this->addError('form', __('You have reached your project limit.'));
    }
}
```

---

## Events & Listeners

### Event Naming Convention

Use `{Entity}{PastTenseVerb}` format:

| Pattern | Examples |
|---------|----------|
| Simple action | `MemberCreated`, `ProjectUpdated`, `UserDeleted` |
| Compound action | `MemberPasswordReset`, `ProjectStatusChanged` |

### Event Structure

```php
<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Project $project,
        public readonly User $actor,
    ) {}
}
```

### Listener Guidelines

Event listeners must **never throw exceptions** — the primary business operation already succeeded:

```php
// Good - Catch and log, never throw
class LogProjectCreated
{
    public function handle(ProjectCreated $event): void
    {
        try {
            ActivityLog::create([
                'action' => 'project.created',
                'subject_id' => $event->project->id,
                'actor_id' => $event->actor->id,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to create activity log', [
                'error' => $e->getMessage(),
                'event' => 'project.created',
            ]);
        }
    }
}
```

---

## Security

### Authorization

Use policies for authorization:

```php
class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }
}
```

```php
// In Livewire component
public function delete(int $projectId): void
{
    $project = Project::findOrFail($projectId);

    $this->authorize('delete', $project);

    $this->projectRepository->delete($projectId);
}
```

### Input Validation

- **Validate all input** — Use Form Requests or Livewire `rules()`
- **Never trust user input** — Always validate, even for authenticated users
- **Use parameterized queries** — Eloquent handles this automatically
- **Validate file uploads** — Check MIME types, size limits

### Sensitive Data

- **Never commit secrets** — Keep `.env`, credentials, and API keys out of version control
- **Hide sensitive attributes** — Use `$hidden` on models for passwords, tokens, etc.
- **Encrypt at rest** — Use Laravel's `encrypted` cast for sensitive data
- **Audit access** — Log access to sensitive data when required by compliance

### Mass Assignment Protection

Always define `$fillable` explicitly on models:

```php
// Good - Explicit allowlist
protected $fillable = [
    'name',
    'email',
    'status',
];

// Avoid - $guarded = [] allows all attributes
protected $guarded = [];
```

### Foreign Key Assignment via Relationships

**Principle:** Never mass-assign foreign keys for user/ownership relationships. Always use relationship methods instead.

```php
// ❌ BAD - Direct foreign key mass assignment
ActivityLog::create([
    'user_id' => auth()->id(),
    'action' => 'login',
    'message' => 'User logged in',
]);

// ✅ GOOD - Via relationship methods
auth()->user()->activityLogs()->create([
    'action' => 'login',
    'message' => 'User logged in',
]);
```

**Implementation in Models:**

Remove foreign keys from `$fillable` for user/ownership relationships:

```php
// ✅ CORRECT - Foreign key NOT in fillable
class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'message',
        'metadata',
        // Note: user_id is NOT fillable - set via relationship
    ];
}
```

**Models that MUST follow this pattern:**

All models with user/ownership foreign keys:

| Model | Foreign Key Removed | Relationship Method |
|-------|-------------------|---------------------|
| `ActivityLog` | `user_id` | `auth()->user()->activityLogs()->create()` |
| `ChangeHistory` | `user_id` | `auth()->user()->changesMade()->create()` |
| `Grant` | `id_user` | `$user->grants()->create()` |
| `OrgUnit` | `id_user` | `$user->unit()->create()` |
| `File` | `user_id` | `auth()->user()->files()->create()` |

**When to apply this rule:**
- ✅ User relationships (`user_id`, `id_user`)
- ✅ Core entity ownership relationships
- ❌ Pivot/junction table foreign keys (can be fillable)
- ❌ Non-critical reference foreign keys (case-by-case basis)

**Exception:** Pivot/junction tables MAY keep foreign keys fillable for convenience.

### Deletion Policy & Data Integrity

**CRITICAL:** This application has strict deletion policies to maintain data integrity and audit trails. Always consult this section before implementing any delete functionality.

#### Deletion Policy Reference Table

| Table Category | Policy | On Delete Constraint | Reason |
|---------------|--------|---------------------|---------|
| **NEVER DELETE** | | | |
| `activity_logs` | ❌ No delete | `nullOnDelete` | Audit trail — must be permanent |
| `change_histories` | ❌ No delete | `nullOnDelete` | Audit trail — must be permanent |
| `riwayat_perubahan_status_hibah` | ❌ No delete | `nullOnDelete` | Status history — must be permanent |
| **RESTRICT DELETE** | | | |
| `autocomplete` | ⚠️ Restrict | `restrictOnDelete` | Reference data — prevent if in use |
| `tags` | ⚠️ Restrict | `restrictOnDelete` | Reference data — prevent if in use |
| **SOFT DELETE** | | | |
| `hibah` | ✅ Soft delete | N/A | Core business data |
| `unit` | ✅ Soft delete | N/A | Organizational structure |
| `pemberi_hibah` | ✅ Soft delete | N/A | Donor records |
| `files` | ✅ Soft delete | N/A | File audit trail |
| All other business tables | ✅ Soft delete | N/A | Business data preservation |

#### Implementation Rules

**1. NEVER DELETE Tables:**
- ❌ NEVER implement delete functionality for audit trail tables
- ❌ DO NOT add delete buttons, routes, or methods
- ❌ Foreign keys MUST use `nullOnDelete` — preserve logs even if related entity is deleted

```php
// ✅ CORRECT - Audit logs preserved
$table->foreignId('user_id')
    ->nullable()
    ->constrained('users')
    ->nullOnDelete();  // User deleted? Log remains with null user_id

// ❌ WRONG - Would lose audit trail
$table->foreignId('user_id')
    ->constrained('users')
    ->cascadeOnDelete();  // NEVER use cascade for audit tables
```

**2. RESTRICT DELETE Tables (Master Data):**
- ⚠️ Can only be deleted if not referenced by other tables
- Foreign keys MUST use `restrictOnDelete`

```php
// ✅ CORRECT - Prevents deletion if data is in use
$table->foreignId('id_autocomplete')
    ->constrained('autocomplete')
    ->restrictOnDelete();
```

**3. SOFT DELETE Tables:**
- ✅ MUST use `SoftDeletes` trait in model
- ✅ MUST add `$table->softDeletesTz();` in migration
- ✅ Cascade soft deletes explicitly in application code (NOT database level)

```php
// Model
use Illuminate\Database\Eloquent\SoftDeletes;

class Grant extends Model
{
    use SoftDeletes;
}

// Migration
$table->softDeletesTz();

// Foreign keys for soft delete parents
$table->foreignId('id_hibah')
    ->nullable()
    ->constrained('hibah')
    ->nullOnDelete();  // Safety net for force deletes
```

**4. Cascade Soft Delete Implementation:**

Application-level cascade (NOT database):

```php
// ✅ CORRECT - Application-level cascade soft delete
class Grant extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Grant $grant) {
            // Only cascade on soft delete, not force delete
            if ($grant->isForceDeleting()) {
                return;
            }

            // Cascade soft delete to related records
            $grant->withdrawalPlans()->delete();
            $grant->locationsAndAllocations()->delete();
            $grant->budgetPlans()->delete();
        });
    }
}

// ❌ WRONG - Database cascade interferes with soft deletes
$table->foreignId('id_hibah')
    ->constrained('hibah')
    ->cascadeOnDelete();  // This hard deletes even if parent soft deletes!
```

#### Dual-Layer Protection: Soft Deletes + nullOnDelete

Soft delete tables that reference other soft delete tables should use BOTH `SoftDeletes` trait AND `nullOnDelete()`:

**Scenario A: Normal Soft Delete (Application Layer)**
```php
$grant->delete(); // User clicks "Delete" button
```
- ✅ Application cascade logic runs
- ✅ Parent gets `deleted_at` timestamp
- ✅ Children get `deleted_at` timestamp via cascade
- ✅ All records preserved for audit trail

**Scenario B: Force Delete (Database Layer)**
```php
$grant->forceDelete(); // Emergency cleanup or direct DB operation
```
- ✅ Database physically deletes parent row
- ✅ Database constraint `nullOnDelete()` TRIGGERS: sets child foreign keys to NULL
- ✅ Child records survive but lose parent reference
- ✅ No orphaned foreign keys or constraint errors

**Benefits:**
- Defense in depth — protection even if application logic is bypassed
- Force delete safety — can emergency-delete without breaking referential integrity
- Audit trail — soft deleted children remain visible
- No orphans — force deleted parents don't leave dangling foreign keys

#### Deletion Code Review Checklist

Before committing any code that touches deletion:

- [ ] Check deletion policy table above
- [ ] Verify foreign key constraints match policy
- [ ] For NEVER DELETE: No delete methods, `nullOnDelete` only
- [ ] For RESTRICT: `restrictOnDelete` on foreign keys
- [ ] For SOFT DELETE: Model has `SoftDeletes` trait, migration has `softDeletesTz()`
- [ ] For cascade soft deletes: Implemented in application code, NOT database
- [ ] Dual-layer protection: Soft delete tables referencing other soft delete tables use `nullOnDelete()`
- [ ] Cascade logic includes `isForceDeleting()` check

### Rate Limiting

Protect sensitive actions from abuse:

```php
public function sendVerificationCode(): void
{
    $throttleKey = 'verify-code:' . auth()->id();

    if (RateLimiter::tooManyAttempts($throttleKey, maxAttempts: 5)) {
        $this->addError('code', __('Too many attempts. Try again in :seconds seconds.', [
            'seconds' => RateLimiter::availableIn($throttleKey),
        ]));

        return;
    }

    RateLimiter::hit($throttleKey, decaySeconds: 60);
}
```

---

## Performance

### Query Optimization

- **Eager load** relationships: `->with(['user', 'tasks'])`
- **Select only needed columns**: `->select(['id', 'name'])`
- **Use pagination** for large datasets
- **Add indexes** for frequently queried columns

### Caching

```php
$projects = Cache::remember('user.'.$userId.'.projects', 3600, function () use ($userId) {
    return $this->projectRepository->allForUser($userId);
});
```

### Livewire Performance

- Use `wire:model.blur` instead of `wire:model.live` when real-time updates aren't needed
- Debounce search inputs: `wire:model.live.debounce.300ms`
- Use `#[Computed]` for derived properties that should be cached per request

---

## Git & Version Control

### Commit Messages

```
feat: add project creation with validation
fix: resolve N+1 query in projects index
refactor: extract validation to ProjectValidationRules trait
test: add coverage for project deletion
docs: update README with setup instructions
```

### Branch Naming

```
feature/project-management
bugfix/login-redirect-issue
refactor/repository-pattern
```

### Pre-commit Checklist

1. Run `vendor/bin/sail bin pint --dirty` to format code
2. Run `vendor/bin/sail artisan test --compact` to verify tests pass
3. Review changed files for unintended changes
4. Write a meaningful commit message

---

## Quick Reference

### Creating New Features

```bash
# 1. Create the model with migration and factory
vendor/bin/sail artisan make:model Project -mf --no-interaction

# 2. Create enum for status (if needed)
vendor/bin/sail artisan make:enum ProjectStatus --no-interaction

# 3. Create the repository
vendor/bin/sail artisan make:class Repositories/ProjectRepository --no-interaction

# 4. Create the ViewModel
vendor/bin/sail artisan make:class ViewModels/ProjectViewModel --no-interaction

# 5. Create Livewire components
vendor/bin/sail artisan make:livewire Projects/Index --no-interaction
vendor/bin/sail artisan make:livewire Projects/Create --no-interaction

# 6. Create policy
vendor/bin/sail artisan make:policy ProjectPolicy --model=Project --no-interaction

# 7. Create tests
vendor/bin/sail artisan make:test Feature/Projects/IndexTest --pest --no-interaction
vendor/bin/sail artisan make:test Feature/Projects/CreateTest --pest --no-interaction

# 8. Run migrations
vendor/bin/sail artisan migrate

# 9. Run tests
vendor/bin/sail artisan test --compact
```

### Common Patterns

| Task | Pattern |
|------|---------|
| Database queries | Repository |
| Data formatting | ViewModel |
| Fixed value sets | Enum in `app/Enums/` |
| User interactions | Livewire Component |
| Input validation | Form Request or Livewire `rules()` |
| Reusable validation | Trait in `app/Concerns/` |
| Authorization | Policy |
| Side effects / logging | Event + Listener |
| Background work | Queued Job |
| Business logic | Service in `app/Services/` |
| Complex display logic | Presenter in `app/Presenters/` |

---

## Summary

### Readable Code
- Use descriptive names that reveal intent (`$isVerified`, not `$v`)
- Prefix booleans with `is`, `has`, `can`, `should`
- Keep functions short (< 30 lines), use early returns
- Comments explain WHY, not WHAT

### Maintainable Code
- Follow the layer architecture: Components → ViewModels → Repositories → Models
- One responsibility per class, extract shared logic to traits
- Use enums for fixed values, avoid magic strings
- Format with Pint before committing

### Testable Code
- Inject dependencies, avoid static calls in classes
- Use factories for test data, never manual inserts
- Test behavior not implementation, cover edge cases
- Keep tests focused with descriptive names

### Secure Code
- Validate all input with Form Requests or Livewire rules
- Authorize every action with Policies
- Define `$fillable` explicitly, never use `$guarded = []`
- Rate limit sensitive actions, hide sensitive model attributes
