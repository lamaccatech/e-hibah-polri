# Code Review Checklist

Quick reference for reviewing code changes to ensure compliance with project standards.

---

## Critical: Deletion Policy Violations

**Check FIRST before approving any PR:**

### Never Delete Tables (Audit Trail)
- [ ] Audit/log tables — NO delete methods/buttons/routes
- [ ] Foreign keys use `nullOnDelete` (NOT `cascadeOnDelete`)

### Master Data (Restrict If In Use)
- [ ] Master/reference tables can ONLY be deleted if not referenced
- [ ] Foreign keys use `restrictOnDelete`

### Soft Delete Tables (Preserve Audit Trail)
- [ ] Models have `SoftDeletes` trait
- [ ] Migrations have `softDeletesTz()`
- [ ] Foreign keys use `nullOnDelete` (NOT `cascadeOnDelete`)
- [ ] Cascade implemented in application code via model events

---

## General Checklist

### Database & Migrations
- [ ] Uses `timestampsTz()` NOT `timestamps()`
- [ ] Uses `timestampTz()` NOT `timestamp()` for custom timestamp columns
- [ ] Uses `softDeletesTz()` NOT `softDeletes()` for soft delete tables
- [ ] Foreign key constraints match deletion policy
- [ ] No `cascadeOnDelete` on audit tables or master data
- [ ] Single naming convention per table (no mixed Indonesian/English)

### Models
- [ ] Foreign keys (`user_id`, ownership FKs) NOT in `$fillable` array
- [ ] Uses relationship methods for foreign key assignment
- [ ] Soft delete tables have `use SoftDeletes;` trait
- [ ] Explicit return type hints on all methods
- [ ] Relationship methods have proper return types
- [ ] Enum casts in `casts()` method
- [ ] Follows naming conventions (Indonesian for business tables, English for system tables)

### Code Quality
- [ ] Ran `vendor/bin/sail bin pint --dirty` before commit
- [ ] No merge conflicts
- [ ] PHPDoc blocks for complex types
- [ ] Descriptive variable names (no `$x`, `$temp`, etc.)
- [ ] Boolean methods prefixed with `is`, `has`, `can`, `should`
- [ ] No deep nesting (use early returns)
- [ ] Methods under 30 lines

### Livewire Components
- [ ] Actions have authorization checks
- [ ] Loading states for user interactions
- [ ] Events used for cross-component communication
- [ ] Search inputs debounced

### Security
- [ ] Input validation (Form Request or Livewire rules)
- [ ] Authorization checks (`$this->authorize()`)
- [ ] No mass assignment vulnerabilities
- [ ] No SQL injection (use Eloquent, never raw queries without bindings)
- [ ] Rate limiting on sensitive actions

### Testing
- [ ] Tests updated or added for changes
- [ ] Tests pass: `vendor/bin/sail artisan test --compact`
- [ ] Uses factories for test data
- [ ] Descriptive test names

### Localization
- [ ] No hardcoded user-facing strings in views
- [ ] Uses `__()` helper for all user-facing text
- [ ] Both `en/` and `id/` translation files updated

---

## Red Flags

Stop and request changes immediately if you see:

- `->cascadeOnDelete()` on audit/log tables
- `->cascadeOnDelete()` on master data tables
- `->cascadeOnDelete()` on soft delete parent tables
- Delete methods/routes for audit/log tables
- Foreign keys (ownership FKs like `user_id`) in `$fillable` arrays
- `timestamps()` instead of `timestampsTz()`
- Missing `SoftDeletes` trait on tables in soft delete policy
- Hard coded values instead of enums
- No validation on user input
- Direct database queries without authorization checks
- Mixed naming conventions within a single table
- Hardcoded Indonesian text in Blade views (should use `__()`)

---

## Approval Criteria

Only approve PR if:
1. All critical checks pass (deletion policy, foreign keys)
2. Code formatted with Pint
3. Tests pass
4. No security vulnerabilities
5. Follows coding guidelines
6. Documentation updated if needed

---

## Reference Documents

- **Coding Standards:** `docs/CODING_GUIDELINE.md`
- **Architecture:** `docs/TECHNICAL_ARCHITECTURE.md`
- **Development Methodology:** `docs/SPEC_DRIVEN_DEVELOPMENT.md`
