---
description: Read AI instructions for TMS V2 Laravel Transportation Management System
---

# AI Instructions Workflow

**CRITICAL: This workflow MUST be triggered automatically at the start of EVERY coding task.**

## Purpose

The `ai_instructions.md` file contains the complete project manifest and coding guidelines for TMS V2. It defines:

-   V2 Architecture (Tailwind CSS, Alpine.js, Sheaf UI components)
-   Multi-tenancy with company slug in URLs
-   Folder structure (V2 folders for all new code)
-   Blade Component library (complete with examples)
-   Service layer patterns and Traits
-   View patterns (Index, Show, Form)
-   Dynamic color system
-   Business logic rules
-   Security checklist
-   Common pitfalls to avoid

## Steps

1. **ALWAYS READ THE AI INSTRUCTIONS FIRST:**

    ```
    Read file: d:\work\work\tms_dec_2026\ai_instructions.md
    ```

2. **Follow these CRITICAL principles:**

    - ✅ All new code goes in V2 folders
    - ✅ Use Tailwind CSS (NOT Bootstrap) for V2
    - ✅ Include company slug in all V2 URLs: `/{company}/orders`
    - ✅ Use provided Blade Components (x-app-layout, x-page-header, x-form-section, etc.)
    - ✅ All components MUST include `dark:` classes for dark mode
    - ✅ Use BelongsToCompany trait for multi-tenancy
    - ✅ Use Service classes for business logic
    - ✅ Use Form Requests for validation
    - ✅ Include breadcrumbs on every page
    - ✅ Add search functionality to index pages
    - ✅ Use dynamic color variables (primary, secondary, accent)
    - ✅ Icons must be Heroicons inline SVG
    - ✅ Forms: Single form.blade.php for both create and edit
    - ✅ Use Sheaf UI components for modals, toasts, and alerts

3. **V2 Folder Structure:**

    ```
    app/Http/Controllers/V2/     ← All V2 controllers
    app/Services/                ← Business logic services
    app/Models/Traits/           ← Reusable model traits
    resources/views/v2/          ← All V2 views
    resources/views/v2/components/ ← V2 Blade components
    routes/v2.php                ← V2 routes with company scope
    ```

4. **Before implementing ANY feature, verify:**

    - [ ] Read ai_instructions.md completely?
    - [ ] Is this V2 or V1? (V2 = new development)
    - [ ] Company slug in URL?
    - [ ] Using V2 folder structure?
    - [ ] Using Tailwind CSS (not Bootstrap)?
    - [ ] Using provided components?
    - [ ] Dark mode classes included?
    - [ ] Multi-tenancy traits applied?
    - [ ] Service layer for business logic?

## Reference Files

-   **Primary:** `d:\work\work\tms_dec_2026\ai_instructions.md` (READ THIS FIRST!)
-   V2 Components: `d:\work\work\tms_dec_2026\resources\views\v2\components\`
-   V2 Layouts: `d:\work\work\tms_dec_2026\resources\views\v2\layouts\`
-   V2 Controllers: `d:\work\work\tms_dec_2026\app\Http\Controllers\V2\`
-   Services: `d:\work\work\tms_dec_2026\app\Services\`
-   Models: `d:\work\work\tms_dec_2026\app\Models\`
-   V2 Routes: `d:\work\work\tms_dec_2026\routes\v2.php`

## Quick Reference Patterns

### URL Pattern:
```
/{company}/orders
/{company}/orders/create
/{company}/orders/{order}
/{company}/manifests
/{company}/customers
```

### Route Helper Pattern:
```blade
route('v2.orders.index', ['company' => app('current.company')])
route('v2.orders.show', ['company' => app('current.company'), 'order' => $order])
```

### Component Usage Pattern:
```blade
<x-app-layout>
    <x-page-header title="..." :breadcrumbs="[...]">
        <x-slot name="actions">...</x-slot>
    </x-page-header>
    
    <x-table-container>
        <!-- table content -->
    </x-table-container>
</x-app-layout>
```

---

**Remember: ALWAYS read ai_instructions.md FIRST before implementing any feature!**
