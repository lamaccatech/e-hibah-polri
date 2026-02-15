# Technical Architecture

---

## Table of Contents

1. [Overview](#overview)
2. [Core Conventions](#core-conventions)
3. [Architecture Patterns](#architecture-patterns)
4. [Audit & Tracking System](#audit--tracking-system)
5. [File Management System](#file-management-system)
6. [Usage Patterns](#usage-patterns)
7. [Best Practices](#best-practices)

---

## Overview

### Technology Stack

- **Backend:** Laravel 12, PHP 8.5+
- **Frontend:** Livewire 4, Flux UI Free, Tailwind CSS 4, Vite
- **Auth:** Fortify (password-based with 2FA)
- **Database:** PostgreSQL (Production & Dev via Sail), SQLite (Tests)
- **File Storage:** AWS S3 (Production), MinIO via Sail (Development)
- **Docker:** Laravel Sail
- **Testing:** Pest PHP 4
- **Linting:** Laravel Pint

### Layered Architecture

```
Request → Livewire Component → ViewModel → Repository → Model → Database
                ↓
            Blade View (with Flux UI)
```

| Layer | Responsibility |
|-------|----------------|
| **Livewire Component** | Handle user interactions, validation, orchestrate data flow |
| **ViewModel** | Transform and format data for presentation |
| **Presenter** | Complex display logic for models (badges, icons, state checks) |
| **Repository** | Encapsulate database queries and business logic |
| **Service** | Business logic that spans multiple models or external APIs |
| **Model** | Represent database entities and relationships |

---

## Core Conventions

### NULL vs Empty String

| Use Case | Rule | Example |
|----------|------|---------|
| **Nullable** | Optional/unknown value | `description`, `url` (NULL = use signed URL) |
| **NOT NULL** | Always exists from source | `name` (from file upload) |
| **Empty String** | Always has value, can be blank | Fields requiring simpler logic |

### Timezone Strategy

**Application Configuration:** UTC (Coordinated Universal Time)

**Why UTC?**
- Single canonical timezone for all stored data
- No DST complications
- PostgreSQL `TIMESTAMPTZ` stores internally as UTC anyway
- Server-independent
- Easy multi-timezone support

**Display-Layer Conversion:**
```php
// Blade views
{{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}

// Carbon macro (add to AppServiceProvider)
Carbon::macro('toUserTz', fn() => $this->timezone('Asia/Jakarta'));

// JavaScript-based (user's actual timezone)
<time datetime="{{ $log->created_at->toIso8601String() }}"
      x-data
      x-text="new Date('{{ $log->created_at }}').toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })">
</time>
```

**Migration Configuration:**
```php
// ✅ Correct (timezone-aware)
$table->timestampsTz();
$table->timestampTz('published_at');
$table->softDeletesTz();

// ❌ Incorrect (avoid)
$table->timestamps();
$table->timestamp('published_at');
$table->softDeletes();
```

### Naming Conventions

**Dual Convention Policy:**

| Model Type | Convention | Foreign Key Pattern |
|------------|-----------|---------------------|
| System tables (English) | `user_id`, `file_id` | `[entity]_id` |
| Business domain tables (Indonesian) | `id_user`, `id_hibah` | `id_[entity]` |

**Indonesian foreign keys require explicit Eloquent configuration:**
```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'id_user');
}
```

---

## Architecture Patterns

### Denormalization Pattern

**When:** Read-heavy queries that would otherwise require JOINs for filtering.

**How:**
1. Store redundant foreign keys for parent lookups
2. Triple-layer validation (Form Request + Model Event + DB Constraints)
3. Helper method to auto-set both parent and child IDs
4. Query scopes for direct filtering (no JOIN)

```php
// Helper method to auto-set denormalized fields
public function setChildEntity(int $childId): void
{
    $child = ChildEntity::findOrFail($childId);
    $this->id_parent = $child->id_parent;
    $this->id_child = $childId;
}

// Scopes (no JOIN needed)
public function scopeForParent(Builder $query, int $parentId): Builder
{
    return $query->where('id_parent', $parentId);
}
```

**Performance Gain:** 10-15x faster queries when filtering by parent entity.

### Soft Deletes Pattern

**Migration:**
```php
$table->softDeletesTz();  // Always use Tz variant
```

**Model:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class YourModel extends Model
{
    use SoftDeletes;
}
```

**Cascade Soft Deletes (Application Level):**
```php
protected static function booted(): void
{
    static::deleting(function (self $model) {
        if ($model->isForceDeleting()) {
            return;
        }

        // Cascade soft delete to children
        $model->children()->delete();
    });
}
```

**Dual-Layer Protection (Soft Deletes + nullOnDelete):**

These two mechanisms protect at different layers:
- **Normal soft delete:** Application cascade runs, all records get `deleted_at`
- **Force delete:** Database `nullOnDelete` triggers, child FK set to NULL, no orphans

```php
$table->foreignId('id_parent')
    ->nullable()
    ->constrained('parent_table')
    ->nullOnDelete();  // Safety net for force deletes

$table->softDeletesTz();  // Enable soft deletes
```

### Presenter Pattern

For models with complex display logic (status badges, icons, state checks), use Presenter classes:

```php
// app/Presenters/ProjectStatusPresenter.php
class ProjectStatusPresenter
{
    public function __construct(
        private readonly Project $project,
    ) {}

    public function badge(): array
    {
        return match ($this->project->status) {
            'active' => ['bg-green-100', 'text-green-800'],
            'pending' => ['bg-yellow-100', 'text-yellow-800'],
            default => ['bg-zinc-100', 'text-zinc-800'],
        };
    }

    public function icon(): string
    {
        return match ($this->project->status) {
            'active' => 'check-circle',
            'pending' => 'clock',
            default => 'minus-circle',
        };
    }
}
```

---

## Audit & Tracking System

### System Components

| Component | Purpose | Storage |
|-----------|---------|---------|
| **ActivityLog** | High-level user action tracking | Simple messages |
| **ChangeHistory** | Entity versioning with full state snapshots | JSONB fields |

### Architecture

```
User Action → User Helper Methods → ActivityLog (simple) + ChangeHistory (full snapshot)
```

**User Helper Methods:**
```php
auth()->user()->recordCreation($model, 'reason');
auth()->user()->recordChange($model, 'reason');    // BEFORE save()
auth()->user()->recordDeletion($model, 'reason');  // BEFORE delete()
```

### ActivityLog

```php
// Usage
auth()->user()->activityLogs()->create([
    'action' => 'project.created',
    'message' => 'Created project: ' . $project->name,
    'metadata' => ['project_id' => $project->id],
]);
```

### ChangeHistory

```php
// Returns diff of changes
$history->getChanges();  // ['status' => ['from' => 'draft', 'to' => 'active']]
```

**Deletion Policy:** NEVER DELETE — Audit trail tables must be permanent. Foreign keys use `nullOnDelete`.

---

## File Management System

### Polymorphic File Attachments

Any model can have file attachments using the `HasFiles` trait/contract pattern.

### Adding File Support to Models

```php
use App\Concerns\HasFiles as HasFilesTrait;
use App\Contracts\HasFiles;

class YourModel extends Model implements HasFiles
{
    use HasFilesTrait;
}
```

### Common Operations

```php
// Attach
$file = $model->attachFile(
    uploadedFile: $request->file('document'),
    fileType: FileType::Document,
    disk: 's3',
    makePublic: false,
    description: 'Optional description'
);

// Retrieve
$files = $model->getFilesByType(FileType::Document);
$file = $model->getFirstFileByType(FileType::Document);
$hasFile = $model->hasFileOfType(FileType::Document);

// Access URLs
$publicUrl = $file->url;
$signedUrl = $file->signedUrl(expirationMinutes: 120);

// Delete
$model->detachFile($file);  // Soft delete
```

**Security:** Files NEVER auto-deleted from storage (audit trail preservation).

---

## Usage Patterns

### Foreign Key Assignment via Relationships

**❌ NEVER include ownership foreign keys in `$fillable`:**
```php
// ❌ WRONG
protected $fillable = ['user_id', 'action', 'message'];

// ✅ CORRECT
protected $fillable = ['action', 'message'];
```

**✅ Create via relationships:**
```php
auth()->user()->activityLogs()->create([...]);
$parent->children()->create([...]);
```

### Deletion Policy Quick Reference

| Category | Policy | Constraint |
|----------|--------|------------|
| **Audit Tables** | NEVER DELETE | `nullOnDelete` on FKs |
| **Master Data** | RESTRICT DELETE | `restrictOnDelete` |
| **Integral Children** | CASCADE DELETE | `cascadeOnDelete` |
| **Business Data** | SOFT DELETE | `SoftDeletes` trait + `softDeletesTz()` |

---

## Best Practices

### Code Organization

1. **Check existing patterns** — Sibling files, similar models
2. **Use relationships** — Never manually assign ownership foreign keys
3. **Leverage enums** — Type-safe values with helper methods
4. **Apply scopes** — Reusable query logic
5. **Eager load** — Prevent N+1 queries with `with()`
6. **Validate integrity** — Triple-layer validation for denormalized data

### Performance Optimization

1. **Denormalize strategically** — For read-heavy queries
2. **Use indexes** — All foreign keys, frequently queried fields
3. **JSONB over TEXT** — For queryable JSON data
4. **Eager loading** — Always use `with()` for relationships
5. **Pagination** — For large datasets

### Security

1. **No mass assignment of ownership FKs** — Always use relationships
2. **Triple validation** — Form Request + Model Event + DB Constraints
3. **Audit trail** — ActivityLog + ChangeHistory for important operations
4. **Soft deletes** — Preserve data for compliance
5. **File security** — Signed URLs for private files

---

**For detailed coding standards, see `docs/CODING_GUIDELINE.md`**
**For development methodology, see `docs/SPEC_DRIVEN_DEVELOPMENT.md`**
**For review standards, see `docs/CODE_REVIEW_CHECKLIST.md`**
