# Spec-Driven Development Guide

---

## Philosophy

> "Specs are not documentation. Specs are executable contracts that prove your code works."

We practice Spec-Driven Development (SDD) to ensure:
1. **Clear requirements** before coding
2. **Living documentation** that never goes stale
3. **Team alignment** on expected behavior
4. **Regression prevention** through executable specs

---

## Three-Layer Spec System

### Layer 1: User Journeys (High-Level)
**Location:** `specs/user-journeys/`
**Purpose:** Describe end-to-end user flows
**Format:** Markdown

**When to create:**
- New major feature
- Cross-cutting functionality
- Complex multi-step processes

**Template:**
```markdown
# User Journey: [Feature Name]

## Actors
- **[Actor 1]**: [Description]
- **[Actor 2]**: [Description]

## Happy Path Flow
1. [Step 1]
2. [Step 2]
3. [Step 3]

## Success Criteria
- ✅ [Criterion 1]
- ✅ [Criterion 2]

## Edge Cases
- ⚠️ [Edge case 1]
- ⚠️ [Edge case 2]
```

### Layer 2: Feature Specifications (Implementation-Ready)
**Location:** `specs/features/`
**Purpose:** Detailed specs for developers
**Format:** Markdown with code examples

**When to create:**
- Before starting any new feature
- Before significant refactoring
- When API contracts change

**Template:**
```markdown
# Feature: [Feature Name]

## Overview
[1-2 sentence description]

## Business Rules
- [Rule 1]
- [Rule 2]

## API Behavior
### Endpoint: [Method] [Path]
**Request:**
json {...}

**Response 200:**
json {...}

**Response 422:**
- Error: [Validation error description]

## Database Constraints
- Foreign key: [column] [constraint]
- Unique: [column]
- Soft deletes: YES/NO

## Security Rules
- [Rule 1]
- [Rule 2]

## Test Scenarios
### Happy Path
1. ✅ [Scenario 1]

### Edge Cases
1. ⚠️ [Scenario 1]
```

### Layer 3: Executable Specs (Pest Tests)
**Location:** `tests/Feature/`, `tests/Unit/`
**Purpose:** Executable specifications that prove correctness
**Format:** Pest PHP tests

**When to create:**
- **ALWAYS** — Before writing implementation code
- One test file per feature spec

**Template:**
```php
<?php

// SPEC: [Feature Name]
// This test file serves as executable specification for [feature]

use function Pest\Laravel\{actingAs, postJson};

describe('[Feature Name] - Happy Path', function () {
    it('does X when Y happens', function () {
        // SPEC: Business rule explanation
        // Arrange
        // Act
        // Assert
    });
});

describe('[Feature Name] - Validation', function () {
    it('rejects invalid data with clear error', function () {
        // SPEC: Validation rule explanation
    });
});

describe('[Feature Name] - Edge Cases', function () {
    it('handles edge case gracefully', function () {
        // SPEC: Edge case explanation
    });
});
```

---

## Workflow: From Spec to Code

### Step 1: Plan (Before Coding)

```bash
# 1. Create feature spec
touch specs/features/feature-name.md

# 2. Write high-level requirements
# - What problem does this solve?
# - What are the business rules?
# - What are the validation rules?
# - What are the edge cases?

# 3. Review with team (PR or meeting)
```

### Step 2: Specify (Write Tests First)

```bash
# 1. Create Pest test file
vendor/bin/sail artisan make:test Feature/FeatureNameTest --pest --no-interaction

# 2. Write test cases (RED phase)
# - Happy path scenarios
# - Validation scenarios
# - Edge case scenarios
# - Security scenarios

# 3. Run tests (should fail)
vendor/bin/sail bin pest tests/Feature/FeatureNameTest.php
```

### Step 3: Implement (Make Tests Pass)

```bash
# 1. Write minimal code to pass tests (GREEN phase)
# - Follow YAGNI: Only implement what's needed
# - Follow KISS: Use simplest solution
# - Follow DRY: Extract common patterns

# 2. Run tests (should pass)
vendor/bin/sail bin pest tests/Feature/FeatureNameTest.php

# 3. Refactor (REFACTOR phase)
# - Clean up code
# - Extract methods
# - Improve naming
# - Ensure tests still pass
```

### Step 4: Review (Team Validation)

```bash
# Create PR with:
# 1. Feature spec (specs/features/*.md)
# 2. Executable tests (tests/Feature/*Test.php)
# 3. Implementation code (app/*)

# Reviewers check:
# - ✅ Spec matches implementation
# - ✅ All test scenarios covered
# - ✅ Tests are descriptive (serve as docs)
# - ✅ Code follows YAGNI/KISS/DRY
```

---

## Enforcement Rules

### Rule 1: No Code Without Spec
❌ **Blocked:** PR with new feature code but no spec in `specs/features/`
✅ **Allowed:** PR with both spec and tests

### Rule 2: No Spec Without Tests
❌ **Blocked:** Spec file without corresponding Pest tests
✅ **Allowed:** Spec with matching test coverage

### Rule 3: Tests Must Be Descriptive
❌ **Bad:**
```php
it('works', function () { ... });
```

✅ **Good:**
```php
it('blocks expired license from creating submission', function () {
    // SPEC: Expired license must prevent submission
    // This ensures compliance and data integrity
    ...
});
```

### Rule 4: Breaking Changes Require Spec Updates
❌ **Blocked:** Changed behavior without updating spec
✅ **Allowed:** Spec updated before changing behavior

---

## Tips for Writing Good Specs

### DO ✅

1. **Start with "why"** — Explain the business reason
2. **Use concrete examples** — Show actual JSON, SQL, etc.
3. **Document edge cases** — Think about failure scenarios
4. **Write tests first** — Let tests drive implementation
5. **Keep specs focused** — One feature per spec file
6. **Use SPEC comments** — Explain business rules in tests
7. **Update specs when behavior changes** — Keep in sync

### DON'T ❌

1. **Don't write implementation details** — Focus on behavior
2. **Don't duplicate code** — Reference, don't copy-paste
3. **Don't skip edge cases** — They cause bugs
4. **Don't write vague specs** — Be specific and testable
5. **Don't let specs go stale** — Update with code changes
6. **Don't over-specify** — Focus on important behavior
7. **Don't skip CI validation** — Automate enforcement

---

## FAQ

### Q: Do I need all three layers for every feature?

**A:** No. Use judgment:
- **Small feature** (simple CRUD): Layer 3 only (Pest tests)
- **Medium feature** (with business logic): Layer 2 + 3 (Spec + Tests)
- **Large feature** (complex flows): All 3 layers (Journey + Spec + Tests)

### Q: What if I'm fixing a bug, not adding a feature?

**A:** Write a Pest test that reproduces the bug first, then fix it. Update the feature spec if behavior changed.

### Q: How do I handle refactoring?

**A:** Refactoring shouldn't change behavior, so:
1. Tests should still pass after refactoring
2. No spec changes needed (unless API contracts change)
3. If tests break, either fix tests or reconsider the refactor

### Q: What about exploratory changes?

**A:** Use feature branches:
1. Create `spike/feature-name` branch
2. Experiment without specs
3. Once approach is clear, delete spike
4. Start fresh with proper specs on new branch

---

**Remember:** Specs are not bureaucracy. Specs are how we communicate intent, validate correctness, and build confidence in our code.
