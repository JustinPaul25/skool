# SFAMS — Implementation Checklist

> **Stack:** Laravel 13 · PHP 8.4 · SQLite (dev) → MySQL/PostgreSQL (prod) · Filament v4 · Inertia.js 2 + Vue 3 + TypeScript · Tailwind CSS 4 · Spatie suite · Cloudinary · Brevo · Ably

---

## Phase 1 — Foundation

### 1.1 Laravel Project Bootstrap

- [x] **Create the Laravel application**
  - Run `composer create-project laravel/laravel sfams` and `cd sfams`.
  - Confirm PHP 8.4 is active (`php -v`).
  - Set `APP_NAME`, `APP_URL`, and database credentials in `.env` (use SQLite for local dev; switch to MySQL/PostgreSQL before staging).

- [x] **Install and configure Filament v4**
  - Run `composer require filament/filament:"^4.0"`.
  - Run `php artisan filament:install --panels` to scaffold the admin panel at `/admin` and generate `app/Providers/Filament/AdminPanelProvider.php`.
  - Run `php artisan make:filament-user` to create the first superadmin user.
  - Verify the panel loads at `/admin` and the branding/favicon are set in `AdminPanelProvider`.

- [x] **Install Inertia.js with Vue 3**
  - Run `composer require inertiajs/inertia-laravel` and `php artisan inertia:middleware`.
  - Register `HandleInertiaRequests` middleware in the `web` middleware group in `bootstrap/app.php`.
  - Run `npm install @inertiajs/vue3 vue @vitejs/plugin-vue`.
  - Update `vite.config.ts` to use `@vitejs/plugin-vue`.
  - Create the Inertia root template at `resources/views/app.blade.php` with the `@inertiaHead` and `@inertia` directives.
  - Initialize the Vue app in `resources/js/app.ts` using `createInertiaApp`.

- [x] **Install and configure TypeScript**
  - Run `npm install -D typescript @types/node`.
  - Create or update `tsconfig.json` with `strict: true`, `paths` aliases for `@/` → `resources/js/`.
  - Rename `.js` entry files to `.ts`; update `vite.config.ts` input references.

- [x] **Configure Tailwind CSS 4 for the Inertia app**
  - Run `npm install -D tailwindcss postcss autoprefixer`.
  - Create `tailwind.config.ts` scoped to `resources/js/**` and `resources/views/**` (Filament manages its own compiled assets — do NOT include Filament's vendor paths in the public-app config).
  - Import Tailwind in `resources/css/app.css` and reference the CSS in `app.blade.php`.

---

### 1.2 Core Composer Packages

- [x] **Spatie Laravel Permission**
  - Run `composer require spatie/laravel-permission`.
  - Publish and run migrations: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"` → `php artisan migrate`.
  - Add `HasRoles` trait to the `User` model.
  - Seed the four base roles in `database/seeders/RoleSeeder.php`: `administrator`, `staff`, `branch_manager`, `student`.

- [x] **Spatie Laravel Activity Log**
  - Run `composer require spatie/laravel-activitylog`.
  - Publish config and migration, run `php artisan migrate`.
  - Add `LogsActivity` trait and `getActivitylogOptions()` to sensitive models (Student, Payment, Enrollment).

- [x] **Spatie Laravel Media Library**
  - Run `composer require spatie/laravel-medialibrary`.
  - Publish and run migration (`media` table).
  - Add `InteractsWithMedia` and `HasMedia` to models that store files (Student, EnrollmentApplication).
  - Install the Filament plugin: `composer require filament/spatie-laravel-media-library-plugin:"^4.0"` and register it in the panel provider.

- [x] **Cloudinary integration**
  - Run `composer require cloudinary-labs/cloudinary-laravel`.
  - Publish config: `php artisan vendor:publish --provider="CloudinaryLabs\CloudinaryLaravel\CloudinaryServiceProvider"`.
  - Set `CLOUDINARY_URL`, `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET` in `.env`.
  - Set `FILESYSTEM_DISK=cloudinary` in `.env`.
  - Create `app/Services/CloudinaryService.php` with methods for upload, signed URL generation, and folder-organized storage (folders: `students/`, `documents/`, `reports/`).
  - Wire Spatie Media Library to use the Cloudinary disk in `config/media-library.php`.

- [x] **Brevo transactional email**
  - Run `composer require getbrevo/brevo-php`.
  - Configure `config/mail.php` with a `brevo` SMTP mailer (host `smtp-relay.brevo.com`, port 587, TLS).
  - Set `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` in `.env`.
  - Add `BREVO_API_KEY` to `.env` and register `'brevo' => ['api_key' => env('BREVO_API_KEY')]` in `config/services.php`.
  - Create `app/Services/BrevoService.php` with `sendTransactional(string $to, int $templateId, array $params)` method using the Brevo API client.

- [x] **Ably real-time broadcasting**
  - Run `php artisan install:broadcasting --ably` (installs `ably/ably-php`, wires `config/broadcasting.php`, scaffolds `routes/channels.php`).
  - Set `BROADCAST_CONNECTION=ably`, `ABLY_KEY=<full-root-key>`, `VITE_ABLY_PUBLIC_KEY=<public-prefix>` in `.env`.
  - Run `npm install laravel-echo pusher-js`.
  - Create `resources/js/echo.ts` initializing Echo in Pusher-compatibility mode pointing to `realtime-pusher.ably.io`.
  - Import `echo.ts` from the Inertia `app.ts` bootstrap.
  - Enable Pusher Protocol support on the Ably dashboard (Protocol Adapter Settings).

- [x] **PDF generation**
  - Run `composer require barryvdh/laravel-dompdf`.
  - Create `app/Services/PdfService.php` with a `generate(string $view, array $data, string $filename): Response` helper used by Filament actions.

- [x] **Excel exports/imports**
  - Run `composer require maatwebsite/excel`.
  - Publish config if needed.
  - Plan one `Export` class per major resource (StudentsExport, PaymentsExport) under `app/Exports/`.

- [x] **Queue infrastructure (Redis)**
  - Set `QUEUE_CONNECTION=redis`, `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis` in `.env`.
  - Install Laravel Horizon: `composer require laravel/horizon`, publish config, run `php artisan horizon:install`.
  - Configure Horizon queues (`default`, `notifications`, `exports`) in `config/horizon.php`.

- [x] **Developer tooling**
  - Install Telescope: `composer require --dev laravel/telescope` → `php artisan telescope:install` → `php artisan migrate`. Gate Telescope to admin emails in `app/Providers/TelescopeServiceProvider.php`.
  - Install Pint: `composer require --dev laravel/pint`. Create `pint.json` with `laravel` preset.

---

### 1.3 Database Migrations — Core Tables

> Create all migrations via `php artisan make:migration`. Run them in dependency order. Use `foreignId()->constrained()->cascadeOnDelete()` where appropriate.

- [x] **`branches` table**
  - Columns: `id`, `name`, `code` (unique), `address`, `phone`, `email`, `is_active` (bool), `commission_rate` (decimal 5,2), `timestamps`.

- [x] **`school_years` table**
  - Columns: `id`, `name` (e.g. "2025–2026"), `start_date`, `end_date`, `is_active` (bool, one active at a time), `timestamps`.

- [x] **`grade_levels` table**
  - Columns: `id`, `name`, `order` (int for sorting), `branch_id` (nullable FK), `timestamps`.

- [x] **`sections` table**
  - Columns: `id`, `name`, `grade_level_id` (FK), `branch_id` (FK), `capacity` (int, nullable), `timestamps`.

- [x] **`subjects` table**
  - Columns: `id`, `name`, `code` (unique), `grade_level_id` (FK), `units` (decimal, nullable), `timestamps`.

- [x] **`students` table**
  - Columns: `id`, `student_id` (unique, formatted), `user_id` (nullable FK → users), `branch_id` (FK), `first_name`, `last_name`, `middle_name`, `birth_date`, `gender`, `address`, `phone`, `email` (unique), `guardian_name`, `guardian_phone`, `guardian_relationship`, `timestamps`, `deleted_at` (soft deletes).

- [x] **`enrollments` table**
  - Columns: `id`, `student_id` (FK), `school_year_id` (FK), `grade_level_id` (FK), `section_id` (nullable FK), `branch_id` (FK), `status` (enum: `pending`, `enrolled`, `dropped`, `graduated`), `enrolled_at`, `timestamps`.

- [x] **`enrollment_applications` table**
  - Columns: `id`, `student_id` (nullable FK — may be a new applicant), `school_year_id` (FK), `grade_level_id` (FK), `branch_id` (FK), `status` (enum: `submitted`, `under_review`, `approved`, `rejected`), `notes`, `submitted_at`, `reviewed_by` (FK → users, nullable), `timestamps`.

- [x] **`grades` table**
  - Columns: `id`, `enrollment_id` (FK), `subject_id` (FK), `period` (enum: `q1`, `q2`, `q3`, `q4`, `final`), `score` (decimal 5,2, nullable), `remarks`, `graded_by` (FK → users, nullable), `timestamps`.

- [x] **`accounts` (student financial accounts) table**
  - Columns: `id`, `student_id` (FK, unique), `balance` (decimal 10,2, default 0), `timestamps`.

- [x] **`payments` table**
  - Columns: `id`, `account_id` (FK), `enrollment_id` (nullable FK), `amount` (decimal 10,2), `type` (enum: `tuition`, `miscellaneous`, `discount`, `penalty`), `reference_no` (unique), `received_by` (FK → users), `paid_at`, `notes`, `timestamps`.

- [x] **`payment_utilities` (fee configuration) table**
  - Columns: `id`, `name`, `amount` (decimal 10,2), `type`, `grade_level_id` (nullable FK), `branch_id` (nullable FK), `school_year_id` (FK), `is_active` (bool), `timestamps`.

- [x] **`requirements` (document types) table**
  - Columns: `id`, `name`, `description`, `is_required` (bool), `grade_level_id` (nullable FK), `timestamps`.

- [x] **`student_requirements` (submission tracking) table**
  - Columns: `id`, `student_id` (FK), `requirement_id` (FK), `enrollment_id` (FK), `status` (enum: `pending`, `submitted`, `verified`, `rejected`), `notes`, `submitted_at`, `verified_by` (nullable FK → users), `timestamps`.

- [x] **`branch_accounts` table**
  - Columns: `id`, `branch_id` (FK), `school_year_id` (FK), `balance` (decimal 10,2), `timestamps`.

- [x] **Run all migrations and seed roles/permissions**
  - Run `php artisan migrate`.
  - Create `RolePermissionSeeder`: define all permissions (e.g. `view students`, `manage payments`) and assign them to the four roles using `spatie/laravel-permission`.
  - Run `php artisan db:seed --class=RolePermissionSeeder`.

- [x] **Create Eloquent models with relationships**
  - All 15 models created with proper `$fillable`, `casts()`, relationships, and traits:
    - `Branch` — HasMany: students, enrollments, sections, gradeLevels, enrollmentApplications, paymentUtilities, branchAccounts
    - `SchoolYear` — HasMany: enrollments, enrollmentApplications, paymentUtilities, branchAccounts
    - `GradeLevel` — BelongsTo: branch; HasMany: sections, subjects, enrollments, enrollmentApplications, paymentUtilities, requirements
    - `Section` — BelongsTo: gradeLevel, branch; HasMany: enrollments
    - `Subject` — BelongsTo: gradeLevel; HasMany: grades
    - `Student` — Uses: SoftDeletes, LogsActivity, InteractsWithMedia; BelongsTo: user, branch; HasMany: enrollments, enrollmentApplications, studentRequirements; HasOne: account
    - `Enrollment` — Uses: LogsActivity; BelongsTo: student, schoolYear, gradeLevel, section, branch; HasMany: grades, payments, studentRequirements
    - `EnrollmentApplication` — Uses: InteractsWithMedia; BelongsTo: student, schoolYear, gradeLevel, branch, reviewer (User)
    - `Grade` — BelongsTo: enrollment, subject, grader (User)
    - `Account` — BelongsTo: student; HasMany: payments
    - `Payment` — Uses: LogsActivity; BelongsTo: account, enrollment, receiver (User)
    - `PaymentUtility` — BelongsTo: gradeLevel, branch, schoolYear
    - `Requirement` — BelongsTo: gradeLevel; HasMany: studentRequirements
    - `StudentRequirement` — BelongsTo: student, requirement, enrollment, verifier (User)
    - `BranchAccount` — BelongsTo: branch, schoolYear

---

### 1.4 CI / Developer Workflow

- [x] **Configure Pest**
  - Pest is already installed. Create `tests/Feature/.gitkeep` and `tests/Unit/.gitkeep`.
  - In `tests/Pest.php`, set `uses(RefreshDatabase::class)->in('Feature')`.
  - Write a smoke test: `it('loads the home page', fn() => $this->get('/')->assertOk())`.

- [x] **Add a Composer script for CI**
  - In `composer.json` `scripts`, add `"ci": ["@pint --test", "@pest", "npm run build"]`.

---

## Phase 2 — Core Admin Resources (Filament)

> Each resource lives in `app/Filament/Resources/`. Every resource should have a `Policy` in `app/Policies/` registered via auto-discovery or `AuthServiceProvider`.

### 2.1 Authentication & Panel Access

- [x] **`canAccessPanel()` on the `User` model**
  - Implement `canAccessPanel(Panel $panel): bool` — return `true` only if the user has role `administrator` or `staff` or `branch_manager`.
  - Restrict `student` role users to portal routes, not the Filament panel.

- [x] **User resource in Filament (`UserResource`)**
  - Columns: name, email, roles (via Spatie tag column), `created_at`.
  - Form: name, email, password (hashed on save), roles (multi-select from Spatie).
  - Actions: reset password (sends mail via Brevo), impersonate (optional plugin).
  - Policy: only `administrator` can create/update/delete users.

- [x] **Panel branding**
  - Set school logo, colors, and favicon in `AdminPanelProvider` using `->brandLogo()`, `->colors()`, `->favicon()`.
  - Configure navigation groups: "Students", "Academics", "Finance", "Branches", "Settings", "System".

### 2.2 Branch Resource

- [x] **`BranchResource`**
  - Table columns: name, code, phone, `is_active` badge, students count.
  - Form: name (required), code (unique), address, phone, email, is_active toggle.
  - Actions: activate/deactivate toggle action; view branch statistics.
  - Policy: only `administrator` can create/update/delete; `branch_manager` of that branch can view.

### 2.3 School Year Resource

- [x] **`SchoolYearResource`**
  - Table: name, start_date, end_date, `is_active` badge.
  - Form: name, start_date, end_date, is_active (only one can be active — validate in `SchoolYearService::activate()`).
  - Header action: **"Set Active"** action that calls `SchoolYearService::setActive($schoolYear)` — deactivates all others.
  - Header action: **"Year Rollover"** — dispatches `SchoolYearRolloverJob` (promotes enrolled students, resets sections) with a confirmation modal and progress notification.
  - Policy: `administrator` only.

### 2.4 Academic Resources

- [x] **`GradeLevelResource`**
  - Table: name, order, branch (nullable).
  - Form: name, display order, branch selector (nullable).
  - Policy: `administrator` and `staff`.

- [x] **`SectionResource`**
  - Table: name, grade level, branch, capacity.
  - Form: name, grade_level_id, branch_id, capacity.
  - Policy: `administrator`, `staff`.

- [x] **`SubjectResource`**
  - Table: code, name, grade level, units.
  - Form: code (unique), name, grade_level_id, units.
  - Policy: `administrator`, `staff`.

### 2.5 Student Resource

- [x] **`StudentResource`** (most complex resource)
  - **Wizard-style create form** with steps:
    1. Personal info (first/last/middle name, birth date, gender, photo via SpatieMediaLibraryFileUpload).
    2. Contact & address (email, phone, address).
    3. Guardian info (name, phone, relationship).
    4. Academic placement (branch, grade level, section).
  - **Table**: student_id, full name, branch, grade level, status, `created_at`; filters by branch, grade level, enrollment status; searchable by name and student_id.
  - **Infolist** (view page): grouped sections mirroring form; photo displayed.
  - **Relation managers**:
    - `EnrollmentsRelationManager` — list enrollments per student.
    - `PaymentsRelationManager` — list payments; subtotal summary.
    - `GradesRelationManager` — list grades per subject/period.
    - `DocumentsRelationManager` — list submitted requirements with status badges.
  - **Header actions**: generate report card (dispatches job), download OR receipt (DomPDF action), export student data.
  - **Global scope / query scoping**: `branch_manager` role sees only their branch's students via `modifyQueryUsing()`.
  - Policy: `administrator` and `staff` full CRUD; `branch_manager` view + limited edit for their branch.

### 2.6 Requirement Types Resource

- [x] **`RequirementResource`**
  - Table: name, is_required badge, grade_level applicability.
  - Form: name, description, is_required toggle, grade_level_id (nullable).
  - Policy: `administrator`, `staff`.

---

## Phase 3 — Enrollment Management

### 3.1 Enrollment Application Resource (Filament)

- [x] **`EnrollmentApplicationResource`**
  - Table columns: applicant name, branch, grade level, status badge, submitted_at, reviewed_by.
  - Filters: status, branch, school year, grade level.
  - **Row actions**:
    - **Approve** (modal with optional notes) → sets status to `approved`, creates `Enrollment` record, creates `Account` if not existing, fires `EnrollmentApproved` event (triggers notification email via Brevo + database notification).
    - **Reject** (modal requiring rejection notes) → sets status to `rejected`, fires `EnrollmentRejected` event.
  - **Bulk action**: Bulk approve selected applications.
  - Policy: `administrator`, `staff`; `branch_manager` scoped to their branch.

### 3.2 Public Enrollment Form (Inertia + Vue)

- [x] **Multi-step enrollment wizard page**
  - Route: `GET /online-enrollment` → `EnrollmentController@index` → `Inertia::render('Enrollment/Index')`.
  - Vue component at `resources/js/Pages/Enrollment/Index.vue` using a step-based layout (Headless UI `Tab` or custom stepper).
  - Steps:
    1. Personal information form (Zod schema validation client-side).
    2. Guardian/contact information.
    3. Academic preference (branch, grade level).
    4. Document uploads (photo, birth certificate, etc.) sent to `POST /enrollment/documents`.
    5. Confirmation / review screen.
  - Form submission: `POST /online-enrollment` → `EnrollmentController@store` → creates `EnrollmentApplication` record → dispatches `EnrollmentSubmitted` notification (email via Brevo, template: enrollment confirmation).
  - Show success page with application reference number.

- [x] **Enrollment controller and form request**
  - `app/Http/Controllers/EnrollmentController.php` with `index()`, `store()`, `uploadDocuments()`.
  - `app/Http/Requests/StoreEnrollmentApplicationRequest.php` with all validation rules mirroring Zod schema.
  - Rate-limit the `store` endpoint (`throttle:5,1`) to prevent abuse.

---

## Phase 4 — Financial Management

### 4.1 Payment Utilities (Fee Configuration)

- [x] **`PaymentUtilityResource`**
  - Table: name, amount, type, grade level, branch, school year, is_active badge.
  - Form: name, amount (decimal), type (select), grade_level_id (nullable), branch_id (nullable), school_year_id (required), is_active toggle.
  - Policy: `administrator` only.

### 4.2 Payments Resource

- [x] **`PaymentResource`**
  - Table: reference_no, student name, amount, type badge, paid_at, received_by.
  - Filters: type, branch, school year, date range.
  - **Table summary row**: sum of `amount` column.
  - **Create action** (also accessible from Student relation manager): select account, enrollment, amount, type, reference_no (auto-generated), notes; wrapped in `DB::transaction()` inside `PaymentService::record()`.
  - **Row action**: "Print OR" → generates PDF receipt via DomPDF action, logs activity.
  - **Header action**: "Export" → `PaymentsExport` using Maatwebsite Excel, columns: reference_no, student, amount, type, paid_at, received_by.
  - Policy: `administrator`, `staff`; `branch_manager` scoped to their branch.

### 4.3 Financial Services

- [x] **`PaymentService` class** (`app/Services/PaymentService.php`)
  - `record(Account $account, array $data): Payment` — runs inside `DB::transaction()`, updates `Account.balance`, creates `Payment`, fires `PaymentReceived` event (triggers Ably broadcast + Brevo receipt email).
  - `generateReceiptNumber(): string` — auto-increment with prefix (e.g. `OR-2025-00001`).

- [x] **`PaymentReceived` event and broadcasting**
  - `php artisan make:event PaymentReceived`.
  - Implements `ShouldBroadcast` → broadcasts on `PrivateChannel('student.{student_id}')`.
  - Student portal listens on this channel to refresh balance display in real time.

---

## Phase 5 — Academic Management

### 5.1 Grading

- [x] **`GradeResource`** (or manage entirely via `GradesRelationManager` on enrollments)
  - If standalone: table with enrollment (student + year), subject, period, score, remarks.
  - Form: enrollment_id, subject_id, period (select), score (0–100), remarks.
  - **Bulk import action**: upload CSV → `GradeImportJob` dispatched, result notification sent.
  - Policy: `administrator`, `staff`.

### 5.2 Report Card Generation

- [x] **Report card action on `StudentResource`**
  - Header action "Generate Report Card" → dispatches `GenerateReportCardJob($student, $schoolYear)`.
  - Job: queries grades, renders `resources/views/pdf/report-card.blade.php`, generates PDF via DomPDF, uploads to Cloudinary, stores URL in `media` table, sends download link notification to student user.

---

## Phase 6 — Student Portal (Inertia + Vue)

> All portal routes require `auth` middleware. Students authenticate via the same `users` table with the `student` role.

### 6.1 Portal Layout and Authentication

- [x] **Persistent portal layout**
  - `resources/js/Layouts/PortalLayout.vue` — sidebar with navigation links (Dashboard, Profile, Grades, Payments, Documents, Notifications); responsive (mobile hamburger).
  - Wrap all portal pages with this layout using Inertia's persistent layouts.

- [x] **Portal authentication routes**
  - Login page: `GET /portal/login` → `Inertia::render('Portal/Auth/Login')`.
  - Post login: authenticate, redirect to `/portal/dashboard`.
  - Route group under `middleware(['auth', 'role:student'])`.

### 6.2 Portal Pages

- [x] **Dashboard page** (`Portal/Dashboard.vue`)
  - Shared props from `HandleInertiaRequests`: current user, active enrollment summary, outstanding balance.
  - Widgets: balance card, latest payment, grade summary card, notification bell count.

- [x] **Profile page** (`Portal/Profile.vue`)
  - Display personal info, allow limited edits (phone, address) via `PATCH /portal/profile`.
  - Show photo (from Cloudinary via Spatie Media Library).

- [x] **Grades page** (`Portal/Grades/Index.vue`)
  - List grades grouped by subject for the active school year.
  - Period tabs (Q1, Q2, Q3, Q4, Final).
  - "Download Report Card" button → `GET /portal/report-card/download` → streams PDF from Cloudinary.

- [x] **Payments page** (`Portal/Payments/Index.vue`)
  - Balance summary card at top.
  - Table of payment history: reference_no, amount, type, paid_at.
  - "Print Receipt" button per row → PDF download.
  - Real-time balance update: `window.Echo.private('student.{id}').listen('PaymentReceived', ...)` refreshes balance via `router.reload({ only: ['balance'] })`.

- [x] **Documents page** (`Portal/Documents/Index.vue`)
  - List requirements with status badges (pending / submitted / verified / rejected).
  - Upload file per requirement → `POST /portal/documents/{requirement}` → stores via Spatie Media Library → Cloudinary.

- [x] **Notifications page** (`Portal/Notifications/Index.vue`)
  - List `DatabaseNotification` records for the user.
  - Mark as read action.
  - Real-time unread count badge in sidebar via Ably.

---

## Phase 7 — Branch Manager Portal (Filament — scoped)

> Prefer scoping the existing admin panel via policies and `modifyQueryUsing` before creating a second panel.

- [ ] **Branch manager scoping**
  - In each relevant resource (`StudentResource`, `EnrollmentApplicationResource`, `PaymentResource`), override `getEloquentQuery()` or use `modifyQueryUsing()`:
    ```php
    ->modifyQueryUsing(fn (Builder $query) => $query->where('branch_id', auth()->user()->branch_id))
    ```
  - Hide resources irrelevant to branch managers via `shouldRegisterNavigation()` returning `false` when the user lacks the required role.

- [ ] **Branch dashboard widget**
  - `BranchOverviewWidget` — shows enrollment count, revenue, outstanding balances scoped to the authenticated branch manager's branch.

---

## Phase 8 — Filament Dashboard & Reporting

### 8.1 Dashboard Widgets

- [ ] **`StatsOverviewWidget`**
  - Stat cards: Total Students, Enrolled This Year, Total Revenue (current school year), Outstanding Balances.
  - Scope by branch for `branch_manager`.

- [ ] **`EnrollmentsByGradeLevelChart` (bar chart)**
  - Filament chart widget; data from `enrollments` grouped by `grade_level_id` for the active school year.

- [ ] **`RevenueTimeSeriesChart` (line chart)**
  - Monthly revenue for the active school year from `payments` table.

- [ ] **`RecentActivityWidget`**
  - Last 10 records from Spatie `activity_log` table.

### 8.2 Activity Log Resource

- [ ] **`ActivityLogResource`**
  - Read-only resource (no create/edit/delete).
  - Table: event, subject type, subject id, causer (user), `created_at`; filter by event type and causer.
  - Policy: `administrator` only.

### 8.3 Report Hub Page

- [ ] **`ReportHubPage` (custom Filament page)**
  - Navigation item under "Reports" group.
  - Sections: "Export Students", "Export Payments", "Export Grades" — each a button triggering the appropriate export action (Excel download via Maatwebsite).
  - "Generate Report Cards (Bulk)" action — dispatches `BulkReportCardJob` for all enrolled students in the active year.

---

## Phase 9 — Settings & Configuration

- [ ] **`SettingsPage` (custom Filament page)**
  - Use `spatie/laravel-settings` or a simple `settings` model/JSON column.
  - Fields: School Name, School Logo (SpatieMediaLibraryFileUpload → Cloudinary), address, phone, active school year override, email footer text.
  - Save via `SettingsService::update(array $data)`.
  - Policy: `administrator` only.

- [ ] **Global settings access**
  - Create `app/Settings/SchoolSettings.php` (if using spatie/laravel-settings: `composer require spatie/laravel-settings`).
  - Alternatively: create a `Setting` model with key/value store accessed via `Setting::get('school_name')`.
  - Share critical settings (school name, logo URL) as Inertia shared props in `HandleInertiaRequests::share()`.

---

## Phase 10 — Notifications & Email

- [ ] **Define all Laravel notification classes** under `app/Notifications/`:
  - `EnrollmentSubmittedNotification` — email (Brevo template) + database.
  - `EnrollmentApprovedNotification` — email + database.
  - `EnrollmentRejectedNotification` — email + database.
  - `PaymentReceivedNotification` — email (receipt PDF attached) + database; triggers Ably broadcast.
  - `PaymentReminderNotification` — email; queued with `->later()`.
  - `ReportCardReadyNotification` — email (download link) + database.
  - `PasswordResetNotification` — override Laravel default to use Brevo SMTP.

- [ ] **Filament database notification bell**
  - Enable `->databaseNotifications()` in `AdminPanelProvider` for staff notifications.

- [ ] **Email templates in Brevo dashboard**
  - Create templates for each notification type.
  - Document the template IDs in `config/services.php` or a constants file so they can be updated without code changes.

- [ ] **Queue all notifications**
  - All notification classes implement `ShouldQueue`.
  - Assign to the `notifications` Horizon queue.

---

## Phase 11 — Authorization & Security

- [ ] **Create policies for all models**
  - `UserPolicy`, `StudentPolicy`, `BranchPolicy`, `EnrollmentPolicy`, `PaymentPolicy`, `GradePolicy`, `SchoolYearPolicy`, `GradeLevelPolicy`, `SectionPolicy`, `SubjectPolicy`, `RequirementPolicy`, `ActivityLogPolicy`.
  - Each policy checks the appropriate Spatie permission or role.
  - Register via Laravel's auto-discovery (models and policies in standard locations) or explicitly in `AuthServiceProvider`.

- [ ] **Security hardening**
  - Add `throttle:10,1` middleware to `POST /online-enrollment` and login routes.
  - Validate all file uploads: allowed MIME types (`image/jpeg`, `image/png`, `application/pdf`), max size (10 MB).
  - Ensure all Filament resources that modify data have corresponding policy methods (`create`, `update`, `delete`, `viewAny`, `view`).
  - Confirm CSRF tokens are present on all web routes (automatic for Filament; Inertia handles it automatically via the `X-XSRF-TOKEN` header).
  - Enable HTTPS enforcement in production via `AppServiceProvider::boot()` → `URL::forceScheme('https')` when not local.

---

## Phase 12 — Testing

- [ ] **Feature tests for critical flows** (using Pest)
  - `tests/Feature/Enrollment/EnrollmentApplicationTest.php` — submit application, approve, verify enrollment record created and notification fired.
  - `tests/Feature/Finance/PaymentTest.php` — post payment inside transaction, verify balance updated, `PaymentReceived` event fired.
  - `tests/Feature/Auth/PanelAccessTest.php` — verify student role cannot access `/admin`; admin can.
  - `tests/Feature/Portal/StudentPortalTest.php` — authenticated student can view grades, payments; unauthenticated redirects to login.

- [ ] **Filament resource tests**
  - Use Filament's testing helpers (`livewire(StudentResource\Pages\ListStudents::class)->assertCanSeeTableRecords([...])`).
  - Test approve/reject enrollment actions trigger the expected events.

- [ ] **Architecture tests** (Pest `arch()`)
  - `arch()->expect('App\Services')->toBeClasses()->not->toHavePublicProperties()`.
  - `arch()->expect('App\Models')->toUseStrictTypes()`.

---

## Phase 13 — Jobs & Background Processing

- [ ] **Define all queued jobs** under `app/Jobs/`:
  - `SchoolYearRolloverJob` — promotes enrolled students to next grade level, closes current year, opens next; runs in `DB::transaction()`.
  - `GenerateReportCardJob` — renders PDF for one student, uploads to Cloudinary, fires `ReportCardReadyNotification`.
  - `BulkReportCardJob` — loops over enrolled students and dispatches `GenerateReportCardJob` for each (use `Bus::batch()`).
  - `GradeImportJob` — parses uploaded CSV, validates rows, inserts grades, sends summary notification.
  - `SendPaymentReminderJob` — queried overdue accounts, dispatches `PaymentReminderNotification` for each.

- [ ] **Schedule recurring jobs**
  - In `routes/console.php` (Laravel 13 style): schedule `SendPaymentReminderJob` weekly; schedule Horizon snapshot daily.

---

## Phase 14 — Routing Summary

- [ ] **`routes/web.php` — public and portal routes**
  ```php
  // Public
  Route::get('/', [HomeController::class, 'index'])->name('home');
  Route::get('/online-enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
  Route::post('/online-enrollment', [EnrollmentController::class, 'store'])->name('enrollment.store')->middleware('throttle:5,1');
  Route::post('/enrollment/documents', [EnrollmentController::class, 'uploadDocuments'])->name('enrollment.documents');

  // Portal auth
  Route::get('/portal/login', [PortalAuthController::class, 'showLogin'])->name('portal.login');
  Route::post('/portal/login', [PortalAuthController::class, 'login']);
  Route::post('/portal/logout', [PortalAuthController::class, 'logout'])->name('portal.logout');

  // Student portal (authenticated)
  Route::middleware(['auth', 'role:student'])->prefix('portal')->name('portal.')->group(function () {
      Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
      Route::get('/profile', [PortalProfileController::class, 'index'])->name('profile');
      Route::patch('/profile', [PortalProfileController::class, 'update']);
      Route::get('/grades', [PortalGradeController::class, 'index'])->name('grades');
      Route::get('/payments', [PortalPaymentController::class, 'index'])->name('payments');
      Route::get('/documents', [PortalDocumentController::class, 'index'])->name('documents');
      Route::post('/documents/{requirement}', [PortalDocumentController::class, 'store'])->name('documents.store');
      Route::get('/notifications', [PortalNotificationController::class, 'index'])->name('notifications');
      Route::patch('/notifications/{notification}/read', [PortalNotificationController::class, 'markRead']);
      Route::get('/report-card/download', [PortalReportCardController::class, 'download'])->name('report-card.download');
  });
  ```

- [ ] **`routes/channels.php` — broadcast channel authorization**
  ```php
  Broadcast::channel('student.{studentId}', function (User $user, int $studentId) {
      return $user->student?->id === $studentId || $user->hasRole(['administrator', 'staff']);
  });
  ```

---

## Phase 15 — Environment & Deployment

- [ ] **Complete `.env` configuration**
  - All values from the environment variables template in `recreate.md` are set.
  - `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` generated.
  - Cloudinary, Brevo, Ably, Redis credentials all set.
  - `VITE_ABLY_PUBLIC_KEY` set to the segment before `:` in `ABLY_KEY`.

- [ ] **Production deployment (Laravel Forge)**
  - Server: PHP 8.4, Nginx, Redis, MySQL/PostgreSQL.
  - Deploy script: `composer install --no-dev`, `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, `npm ci && npm run build`.
  - Configure Horizon as a daemon process in Forge.
  - Configure Laravel scheduler (cron: `* * * * * cd /path && php artisan schedule:run`).
  - SSL via Forge/Let's Encrypt.
  - No separate WebSocket daemon needed — Ably is fully hosted.

- [ ] **Security post-deploy checks**
  - `APP_DEBUG=false` confirmed.
  - Telescope disabled or gated to production admin emails.
  - Storage link created: `php artisan storage:link`.
  - File permission review on `storage/` and `bootstrap/cache/`.

---

## Quick Reference — Key Service Classes

| Class | Location | Responsibility |
|---|---|---|
| `PaymentService` | `app/Services/PaymentService.php` | Record payments in DB transaction, update balances |
| `EnrollmentService` | `app/Services/EnrollmentService.php` | Approve/reject applications, create enrollment records |
| `SchoolYearService` | `app/Services/SchoolYearService.php` | Activate school year, validate single active constraint |
| `BrevoService` | `app/Services/BrevoService.php` | Send transactional emails via Brevo API |
| `CloudinaryService` | `app/Services/CloudinaryService.php` | Upload, signed URLs, folder organization |
| `PdfService` | `app/Services/PdfService.php` | Generate PDFs via DomPDF from Blade views |
| `SettingsService` | `app/Services/SettingsService.php` | Read/write school-wide settings |

---

## Quick Reference — Filament Resources

| Resource | Model | Navigation Group |
|---|---|---|
| `UserResource` | `User` | System |
| `BranchResource` | `Branch` | Branches |
| `SchoolYearResource` | `SchoolYear` | Settings |
| `GradeLevelResource` | `GradeLevel` | Academics |
| `SectionResource` | `Section` | Academics |
| `SubjectResource` | `Subject` | Academics |
| `StudentResource` | `Student` | Students |
| `EnrollmentApplicationResource` | `EnrollmentApplication` | Students |
| `RequirementResource` | `Requirement` | Students |
| `PaymentResource` | `Payment` | Finance |
| `PaymentUtilityResource` | `PaymentUtility` | Finance |
| `ActivityLogResource` | `Activity` | System |
| `GradeResource` | `Grade` | Academics |

---

## Quick Reference — Inertia Pages

| Page | Route | Vue file |
|---|---|---|
| Online Enrollment wizard | `/online-enrollment` | `Pages/Enrollment/Index.vue` |
| Portal Login | `/portal/login` | `Pages/Portal/Auth/Login.vue` |
| Portal Dashboard | `/portal/dashboard` | `Pages/Portal/Dashboard.vue` |
| Portal Profile | `/portal/profile` | `Pages/Portal/Profile.vue` |
| Portal Grades | `/portal/grades` | `Pages/Portal/Grades/Index.vue` |
| Portal Payments | `/portal/payments` | `Pages/Portal/Payments/Index.vue` |
| Portal Documents | `/portal/documents` | `Pages/Portal/Documents/Index.vue` |
| Portal Notifications | `/portal/notifications` | `Pages/Portal/Notifications/Index.vue` |

---

*Build vertically: get the admin panel working end-to-end for one feature (e.g. students + enrollment) before adding portals. Deepen breadth only after core flows are solid.*
