# School Financial and Management System (SFAMS) — Filament Rebuild Prompt

## Project Overview

Build a comprehensive School Financial and Management System (SFAMS) using **Laravel** with **[Filament](https://filamentphp.com/)** for the **staff and administrator back office**, and **Inertia.js + Vue** for **public-facing flows** and **role-specific portals** (students, parents, branch managers where those UIs should not share the admin panel).

This split keeps CRUD, reporting, configuration, and workflows in a consistent admin UI (tables, forms, actions, widgets) while still delivering polished, marketing-style and self-service experiences on the public site.

## Technology Stack

### Backend

- **Laravel 12.x** (or current stable; match [Filament’s Laravel compatibility](https://filamentphp.com/docs))
- **PHP 8.3+**
- **MySQL 8.0+** or **PostgreSQL 15+**
- **Laravel Sanctum** (optional) for token-based APIs or mobile clients
- **Laravel Horizon** for queue management
- **Laravel Telescope** for debugging (development only)
- **Spatie Laravel Permission** for roles and permissions
- **Spatie Laravel Activity Log** for audit trails
- **Spatie Laravel Media Library** for file management (integrate uploads with Filament file fields or a Filament plugin)

### Admin interface (Filament)

- **Filament** (v4+ compatible with your Laravel version) — panels, resources, relation managers, custom pages, **widgets** (dashboards, charts), **actions** (header, table row, bulk), **notifications**, imports/exports where applicable
- Built on **Livewire**, **Alpine.js**, and **Tailwind CSS** (versions pinned by Filament; do not fight the stack—extend with Filament APIs first)
- **Policies** + `canAccessPanel()` for panel access and per-resource authorization
- Optional: **multi-panel** setup (e.g. `admin` vs `branch`) or **tenancy** / global scopes for branch-scoped data

### Public site & portals (Inertia + Vue)

- **Inertia.js 2.x**
- **Vue 3** with Composition API and `<script setup>`
- **TypeScript** for type safety
- **Vite** as build tool
- **Tailwind CSS 4.x** (app theme; separate from Filament’s compiled admin assets)
- **Headless UI**, **Heroicons** as needed
- **Pinia**, **VueUse**, **Zod** for portal-specific state, utilities, and validation
- **Chart.js** or **ApexCharts** for portal dashboards if required

### Services & infrastructure

- **Cloudinary** for media storage (images, documents)
- **Laravel Queue** with Redis for background jobs
- **[Ably](https://ably.com)** for managed real-time WebSocket / pub-sub (Laravel’s built-in `ably` broadcast driver; no self-hosted Reverb process)
- **Brevo** for transactional email and campaigns

## UI architecture (who uses what)

| Surface | Stack | Typical features |
|--------|--------|------------------|
| **Admin / staff** | Filament panel(s) | Students, enrollments, payments, grades, branches, school years, settings, reports UI, activity review |
| **Public** | Inertia + Vue | Online enrollment application, marketing pages |
| **Student portal** | Inertia + Vue | Profile, balances, grades, receipts, notifications |
| **Branch portal** (optional) | Inertia + Vue *or* dedicated Filament panel | Branch-scoped dashboard and operations |

Prefer **one Filament panel** with **policies + query scoping** until a second panel is clearly needed; use **Inertia** anywhere the experience is public or heavily custom (multi-step enrollment wizard, student-facing UI).

## Core features & requirements

Implementation hint: **Filament** = admin CRUD, tables, forms, widgets, and most internal workflows. **Inertia** = public enrollment and portals unless you explicitly choose a second Filament panel.

### 1. Authentication & authorization

- **Roles**: Administrator, Staff, Branch Manager, Student (and parent/guardian if modeled as users).
- **Filament**: Panel login, optional 2FA via Filament plugins or Laravel Fortify patterns; `User::canAccessPanel()`; resource policies for create/update/delete/view.
- **Portals**: Session auth (e.g. Fortify/Breeze-style) or same `users` table with different middleware/guards; share permission names with Spatie for consistency.
- Password reset, session security, activity logging (Spatie Activitylog + Filament activity viewer or custom resource).

### 2. Student management

- **Filament**: `StudentResource` with form sections or **wizard**-style create (Filament wizard) mirroring steps: personal, address, guardians, academic; **relation managers** for enrollments, payments, grades, documents; file uploads via Media Library.
- **Student portal (Inertia)**: Read-heavy views; optional limited profile edits.

### 3. Enrollment management

- **Public (Inertia)**: Application form, uploads, confirmation.
- **Filament**: `EnrollmentApplicationResource` or equivalent; table filters by status; **actions** for approve/reject; notifications (database + email) on state changes; optional bulk actions.

### 4. Financial management

- **Filament**: Resources or **custom pages** for ledgers; **relation managers** for payments on student/branch accounts; table **summaries** / overview widgets for revenue; export actions (Excel/CSV); PDF receipts via action (e.g. DomPDF) or queued job.

### 5. Academic management

- **Filament**: Resources for grade levels, sections, subjects; `GradeResource` or grades managed via enrollment/subject relation managers; report card generation as **action** or queued job with download link.

### 6. Branch management

- **Filament**: `BranchResource`, branch settings, commission configuration; scope queries for branch managers via global scope or policy + `modifyQueryUsing` on resources.

### 7. School year management

- **Filament**: `SchoolYearResource`; custom **action** or **command** triggered from Filament page for promotion / rollover; long-running work in jobs.

### 8. Settings & configuration

- **Filament**: **Settings page** (spatie/laravel-settings or custom model + Filament form); manage fees, branding metadata, template IDs; store logos via Cloudinary/Media Library.

### 9. Notifications & communication

- Laravel **notifications** (mail, database); **Ably + Echo** for real-time updates on portals; optional **Filament** database notifications bell (built-in support in recent Filament versions).
- Brevo for email (see dedicated section below).

### 10. Reports & analytics

- **Filament**: **Dashboard widgets** (stats overview, charts); resource **exports**; custom pages with tables built from queries; print-friendly views or PDF exports.

### 11. Activity logging & audit

- Spatie Activitylog; **Filament** resource to browse/filter logs for administrators.

### 12. Requirements & documents

- **Filament**: Manage requirement types; student-side submission tracking; Media Library + Cloudinary for storage.

---

## Filament implementation guide

### Panels and discovery

- Register a panel in `app/Providers/Filament/*PanelProvider.php` (path may vary by Filament version).
- Set path (e.g. `/admin`), branding, navigation groups, and middleware.
- Auto-discover resources in `app/Filament/Resources` or register explicitly.

### Resources (primary building blocks)

- One **Resource** per core model where staff need full CRUD: `Student`, `Branch`, `Enrollment`, `Payment`, `GradeLevel`, `Section`, `Subject`, `SchoolYear`, etc.
- Use **form schemas** with sections/tabs/wizards for long forms (student registration).
- Use **infolists** for read-only detail views.
- Use **table** columns, filters, **bulk actions**, and **empty states** consistently.

### Relation managers

- Attach **RelationManager** classes for nested data: student → enrollments, payments, grades; branch → students or accounts; enrollment → grades.

### Custom pages

- School year rollover, report hub, or “financial dashboard” as **Filament Pages** with widgets and actions.

### Widgets

- Dashboard: enrollment counts, revenue series, outstanding balances, branch comparison—use Filament chart widgets or third-party widgets compatible with your Filament version.

### Actions and jobs

- Heavy work (PDF generation, bulk import, promotions): **Filament actions** that dispatch **queued jobs**; show **notifications** on completion.

### Authorization

- Laravel **Policies** for models; register in `AuthServiceProvider` or use auto-discovery.
- Restrict panel access with `canAccessPanel(User $user): bool` on `User`.
- Use `shouldRegisterNavigation`, `canViewAny`, etc., to hide resources from unauthorized roles.

### Imports / exports

- **Exports**: Filament’s export actions with `maatwebsite/excel` (or version-appropriate package).
- **Imports**: Filament import actions or custom action + job for bulk grade import.

### Multi-branch data

- Prefer **query scoping** in resources (`modifyQueryUsing`) and policies over duplicating resources.
- For strict isolation, evaluate **Filament multi-tenancy** plugins or a second panel with tenant middleware—only if requirements demand it.

### Testing

- Use Filament’s testing helpers (see Filament docs for your version) for **Livewire**-based tests: assert table state, form fill, and actions.

---

## Modern development best practices

### Backend

- **Service classes** or **actions** for business rules (especially money and enrollment state transitions).
- **Form requests** for non-Filament HTTP endpoints (public enrollment API, webhooks).
- **Database transactions** around financial operations.
- **Events and listeners** for notifications and decoupling.
- **Eager loading** in Filament resources to avoid N+1 queries (custom `EloquentQueryBuilder` or `getEloquentQuery()` overrides).
- **Feature tests** for critical flows (enrollment approval, payment posting).

### Filament-specific

- Prefer **Filament form components** and **table columns** over raw Blade in admin.
- Keep **resource classes thin**: extract repeated form/table pieces to dedicated classes or traits.
- Use **notifications** instead of ad-hoc session flash where possible.
- Align **version** of `filament/filament`, `filament/spatie-laravel-media-library-plugin`, etc., with the official upgrade guide when bumping Laravel.

### Inertia + Vue (portals and public only)

- Pages under `resources/js/Pages/` with **persistent layouts** for portal shells.
- **TypeScript** types for shared props; **Zod** for client validation mirroring server rules.
- **Composable**s for Echo, forms, and shared portal logic.
- Responsive, accessible UI; reuse design system tokens consistent with the school brand.

### DevOps & deployment

- **Sail / Docker / Valet** locally; **Forge** in production with queues, scheduler, Redis, SSL (no separate WebSocket daemon—Ably is hosted).
- CI: **Pest/PHPUnit**, **Pint**, **npm build**, optional **typescript check**.

---

## Cloudinary integration

### Setup

- Install `cloudinary-php` or Laravel Cloudinary package as appropriate
- Configure Cloudinary credentials in `.env`
- Create Cloudinary service class
- Integrate with Spatie Media Library

### Use cases

- **Student photos**: Optimize and deliver student profile pictures
- **Documents**: Store enrollment documents (birth certificates, etc.)
- **School logo**: Store and deliver school branding
- **Reports**: Store generated PDF reports
- **Backups**: Archive important files

### Implementation

- Upload files from **Filament** `FileUpload` / Spatie Media Library integration, or from Inertia forms to a controller that delegates to the same storage layer
- Generate transformation URLs (thumbnails, optimized versions)
- Implement **signed URLs** for secure access
- Use **folders** for organization (students, documents, etc.)
- Implement **tagging** for easy retrieval
- Use **eager transformations** for commonly used sizes

---

## Brevo email integration

### Overview

Brevo (formerly Sendinblue) is a comprehensive email marketing and transactional email platform with a generous free tier and excellent deliverability.

### Benefits

- **Free tier**: 300 emails per day (9,000/month) — sufficient for many schools
- **High deliverability**
- **Transactional templates** in the Brevo dashboard
- **Campaigns** for bulk school communication
- **SMTP & API**

### Brevo free tier limits

- 300 emails/day (9,000/month)
- Unlimited contacts
- Templates, campaigns, contact management, basic reporting

### Use cases in SFAMS

1. **Transactional**: Enrollment confirmations, payment receipts, reminders, password reset
2. **Bulk**: Announcements, grade release, re-enrollment reminders
3. **Automated workflows** in Brevo where appropriate

### Setup & configuration

#### 1. Create Brevo account

- Sign up at https://www.brevo.com
- Verify email; configure sender domain (recommended)
- Obtain SMTP credentials and API key

#### 2. Laravel configuration

```php
// config/mail.php
'mailers' => [
    'brevo' => [
        'transport' => 'smtp',
        'host' => 'smtp-relay.brevo.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
    ],
],

'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'noreply@yourschool.com'),
    'name' => env('MAIL_FROM_NAME', 'School Name'),
],
```

#### 3. Environment variables

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-login-email@example.com
MAIL_PASSWORD=your-brevo-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourschool.com
MAIL_FROM_NAME="Your School Name"

BREVO_API_KEY=your-api-key-here
```

#### 4. Install Brevo API package (optional)

```bash
composer require getbrevo/brevo-php
```

### Email templates in Brevo

Create transactional templates for enrollment confirmation, payment receipt, payment reminder, password reset, welcome, etc.

### Sending email from Laravel

Trigger sends from **domain code** (listeners, jobs, model observers)—whether the UI is Filament or Inertia, the mail layer stays the same.

#### Basic mailable

```php
// Create mailable
php artisan make:mail EnrollmentConfirmation

// app/Mail/EnrollmentConfirmation.php
namespace App\Mail;

use App\Models\Student;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class EnrollmentConfirmation extends Mailable
{
    public function __construct(
        public Student $student
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Enrollment Confirmation - ' . $this->student->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment-confirmation',
            with: [
                'studentName' => $this->student->name,
                'studentId' => $this->student->student_id,
                'gradeLevel' => $this->student->gradeLevel->name,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(storage_path('app/enrollment-guide.pdf')),
        ];
    }
}

// Send email
Mail::to($student->email)->send(new EnrollmentConfirmation($student));
```

#### Using Brevo templates (via API)

```php
// app/Services/BrevoService.php
use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;

class BrevoService
{
    protected $api;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', config('services.brevo.api_key'));

        $this->api = new TransactionalEmailsApi(null, $config);
    }

    public function sendEnrollmentConfirmation(Student $student)
    {
        $sendSmtpEmail = new SendSmtpEmail([
            'to' => [['email' => $student->email, 'name' => $student->name]],
            'templateId' => 1, // Your template ID from Brevo
            'params' => [
                'STUDENT_NAME' => $student->name,
                'STUDENT_ID' => $student->student_id,
                'GRADE_LEVEL' => $student->gradeLevel->name,
            ],
        ]);

        return $this->api->sendTransacEmail($sendSmtpEmail);
    }
}
```

### Queued emails

```php
Mail::to($student->email)
    ->queue(new EnrollmentConfirmation($student));

Mail::to($student->email)
    ->later(now()->addMinutes(5), new PaymentReminder($payment));
```

### Best practices

1. Queue transactional email
2. Prefer Brevo templates for branding
3. Monitor bounces and daily limits
4. Use webhooks if you need delivery analytics in-app

---

## Ably (real-time broadcasting)

### Overview

Use **[Ably](https://ably.com)** as the managed realtime layer. Laravel ships an **`ably` broadcaster** (`config/broadcasting.php`); the server uses your **root API key** (`ABLY_KEY`), while the browser only sees the **public** portion of that key (see below).

Ably’s team also maintains **[ably/laravel-broadcaster](https://github.com/ably/laravel-broadcaster)** if you want Ably-native capabilities beyond Laravel’s default integration—start with Laravel’s installer, then adopt the package if you need its extras.

### Use cases in SFAMS

- Real-time notifications for **student portal** (payments, grades, announcements)
- Optional live counters on **Filament** dashboards (usually still prefer **polling** / lazy widgets; see below)

### Ably dashboard prerequisite

For Laravel Echo’s default **Pusher protocol** client setup, enable **Pusher protocol support** on your Ably app: **Protocol Adapter Settings** in the Ably dashboard (see [Laravel broadcasting: Ably](https://laravel.com/docs/broadcasting#ably)).

### Server-side setup (Laravel 12)

**Recommended:** use the installer (adds `ably/ably-php`, `routes/channels.php`, and scaffolding as needed):

```bash
php artisan install:broadcasting --ably
```

**Manual:** install the PHP SDK and set env vars:

```bash
composer require ably/ably-php
```

```env
BROADCAST_CONNECTION=ably
ABLY_KEY=xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

`ABLY_KEY` is the full root key from Ably—**never** expose it in Vite or client bundles.

### Client-side setup (Echo + Pusher protocol)

Install Echo and Pusher’s JS client (used in **Pusher compatibility** mode against Ably):

```bash
npm install laravel-echo pusher-js
```

Initialize Echo (e.g. in `resources/js/bootstrap.ts` or a dedicated `echo.ts` imported from your Inertia app):

```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_ABLY_PUBLIC_KEY,
    wsHost: 'realtime-pusher.ably.io',
    wsPort: 443,
    disableStats: true,
    encrypted: true,
});
```

Set `VITE_ABLY_PUBLIC_KEY` to the **public** segment of your Ably key—the substring **before** the first `:` (same key string as `ABLY_KEY`, but only that prefix is safe for the browser).

For **private** / **presence** channels, ensure `routes/channels.php` and Laravel’s **broadcasting auth** route are registered and that Echo can reach `/broadcasting/auth` (session cookies for Inertia, or a Sanctum-aware `authorizer` for SPA-style auth—see [Laravel broadcasting](https://laravel.com/docs/broadcasting#authorizing-channels)).

### Broadcasting example

```php
php artisan make:event PaymentReceived

class PaymentReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('student.' . $this->payment->student_id),
        ];
    }
}

event(new PaymentReceived($payment));
```

### Client listening (Inertia + Vue portals)

```typescript
import { onMounted } from 'vue';

onMounted(() => {
    window.Echo.private(`student.${studentId}`).listen('PaymentReceived', () => {
        toast.success('Payment received!');
        refreshBalance();
    });
});
```

### Filament admin and Livewire

- Filament pages are **Livewire** components: real-time updates often use **table polling** (`->poll()`), **lazy widgets**, or dispatching browser events from jobs.
- You can wire **Echo + Ably** into admin pages if needed, but keeping heavy realtime UX on **Inertia** portals usually keeps the stack simpler.

---

## Routing and application structure

### Filament

- Admin panel routes are registered by Filament (e.g. `/admin/login`, resources under the panel path).
- Do not duplicate admin CRUD as Inertia routes unless there is a strong product reason.

### Web (Inertia)

```php
// routes/web.php (illustrative)

// Public
Route::get('/online-enrollment', ...);

// Authenticated portals
Route::middleware(['auth'])->group(function () {
    Route::get('/portal', ...)->name('portal.dashboard');
    // student routes...
});

// Role-based web groups (Spatie middleware)
Route::middleware(['auth', 'role:branch_manager'])->group(function () {
    // branch portal routes...
});
```

### Controllers

- **Filament** handles most admin HTTP for resources/pages.
- **Controllers** remain for public enrollment, downloads, webhooks, and Inertia responses: `return Inertia::render(...)`.

### APIs (optional)

- If you expose a **JSON API** (mobile app, integrations), use **Sanctum**, **API Resources**, and versioned routes—orthogonal to Filament.

---

## Database schema overview

### Core tables

- `users` — system users with roles
- `students` — student information
- `branches` — branch locations
- `grade_levels`, `sections`, `subjects`, pivot tables as needed
- `school_years` — academic years
- `enrollments` — enrollment records
- `grades` — student grades per subject / period
- `accounts`, `payments` — student financials
- `branch_accounts`, branch payment tables — branch financials
- `payment_utilities`, fee configuration tables
- `requirements`, `student_requirements` — document tracking
- `notifications` — database notifications
- `activity_log` — Spatie audit
- `media` — Spatie Media Library

---

## Implementation phases

### Phase 1: Foundation (weeks 1–2)

- Laravel + Filament install; first panel (`/admin`) with branding
- Database migrations for core tables; seed roles/permissions
- Filament `User` resource or profile; panel auth and `canAccessPanel`
- Inertia + Vue baseline for **public** layout only (or stub)
- Cloudinary + Media Library; Brevo mail; broadcasting + Ably (`install:broadcasting --ably`) configured
- CI: tests + Pint + frontend build

### Phase 2: Core admin (weeks 3–5)

- Filament resources: branches, grade levels, sections, subjects, students
- Enrollment application review resource + approve/reject actions + notifications
- Relation managers for enrollments and documents

### Phase 3: Financial (weeks 6–7)

- Payment utilities settings (Filament)
- Payments and account resources; OR/receipt generation; exports

### Phase 4: Academic (weeks 8–9)

- Grading resources/relation managers; school year resource
- Promotion/rollover job + Filament trigger page or command

### Phase 5: Portals & polish (weeks 10–11)

- Student portal (Inertia): balances, grades, receipts, notifications + Echo
- Branch portal (Inertia or second panel)
- Filament dashboard widgets; report pages; activity log resource

### Phase 6: Hardening (week 12)

- Feature tests (Filament + HTTP); performance passes; production deploy docs

---

## Environment variables template

```env
APP_NAME="SFAMS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sfams
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=ably
CACHE_DRIVER=redis
FILESYSTEM_DISK=cloudinary
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Ably: full root API key (server only — never commit to frontend)
ABLY_KEY=

# Ably: public key = segment before ':' in ABLY_KEY (safe for Vite)
VITE_ABLY_PUBLIC_KEY=

CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourschool.com
MAIL_FROM_NAME="${APP_NAME}"

BREVO_API_KEY=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Add Brevo to `config/services.php` if using the API client:

```php
'brevo' => [
    'api_key' => env('BREVO_API_KEY'),
],
```

---

## Key packages to install

### Backend (Composer)

```bash
composer require filament/filament:"^4.0"
composer require inertiajs/inertia-laravel
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
composer require spatie/laravel-medialibrary
composer require cloudinary-labs/cloudinary-laravel
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
composer require getbrevo/brevo-php

composer require --dev laravel/telescope
composer require --dev laravel/pint
composer require --dev laravel/horizon
```

Pin **Filament** to the version range that matches your Laravel install (see Filament documentation). **Broadcasting:** run `php artisan install:broadcasting --ably` (installs `ably/ably-php` and wires config) or `composer require ably/ably-php` manually. Optional:

```bash
composer require filament/spatie-laravel-media-library-plugin:"^4.0"
```

Optional Ably-native broadcaster: [ably/laravel-broadcaster](https://github.com/ably/laravel-broadcaster) (see Ably’s docs if you outgrow Pusher-compatibility mode).

### Frontend (NPM) — portals & public Inertia app

```bash
npm install @inertiajs/vue3 vue @vitejs/plugin-vue
npm install typescript
npm install laravel-echo pusher-js
npm install @headlessui/vue @heroicons/vue
npm install pinia @vueuse/core zod
npm install chart.js vue-chartjs
npm install date-fns
npm install @tanstack/vue-table

npm install -D tailwindcss postcss autoprefixer
npm install -D @types/node
npm install -D eslint @typescript-eslint/parser @typescript-eslint/eslint-plugin
```

---

## Success criteria

The rebuilt application should:

- Use **Laravel** with **Filament** for the **admin/staff** experience (resources, tables, forms, widgets, actions)
- Use **Inertia + Vue 3 + TypeScript** for **public enrollment** and **portals** as defined in scope
- Store media in **Cloudinary** (with Media Library where appropriate)
- Support **multi-branch** operations with clear authorization and query scoping
- Handle **financial** operations with transactions and auditability
- Provide **real-time** updates via **Ably** (Laravel broadcasting + Echo) where needed (primarily portals)
- Send mail through **Brevo** (queued)
- Expose **reports** via Filament exports/custom pages and downloadable artifacts
- Enforce **RBAC** (Spatie + policies + panel access)
- Include **activity logging** for sensitive changes
- Maintain **automated tests** (Filament/Livewire + feature tests for money and enrollment)
- Meet performance and accessibility targets appropriate for a school admin system

---

## Additional considerations

### Communication

Email-first via Brevo; SMS can be added later (Twilio, Vonage, etc.).

### Mobile

Filament is responsive; portals should be mobile-friendly. Native apps are out of scope unless you add a Sanctum API.

### Internationalization

Laravel localization for dates/currency; Filament supports localization for its own strings—configure as needed.

### Scalability

Indexes, Redis cache, queues, Horizon; consider read replicas at scale.

### Security checklist

- [ ] CSRF on all web forms (Filament + Inertia)
- [ ] Policies on all Filament resources and sensitive Inertia routes
- [ ] Rate limiting on login and public enrollment
- [ ] Secure file upload validation
- [ ] HTTPS in production
- [ ] Activity log for sensitive operations

---

## Documentation requirements

1. Installation guide (Laravel, Filament, Vite, queues, Ably broadcasting)
2. Admin user manual (Filament navigation and workflows)
3. Portal user manual (students / branches)
4. Developer guide (panel structure, resources, jobs, events)
5. Deployment guide (Forge, env vars, workers)

---

## Maintenance & support

- Keep Laravel, Filament, and Livewire on compatible version sets; follow Filament upgrade guides
- Database backups, log monitoring, security patches

---

## Getting started

1. **Create Laravel app**

   ```bash
   composer create-project laravel/laravel sfams
   cd sfams
   ```

2. **Install Filament**

   ```bash
   composer require filament/filament:"^4.0"
   php artisan filament:install --panels
   php artisan make:filament-user
   ```

3. **Install Inertia (portals / public)**

   ```bash
   composer require inertiajs/inertia-laravel
   php artisan inertia:middleware
   npm install @inertiajs/vue3 vue @vitejs/plugin-vue
   ```

4. **Tailwind & TypeScript** for the Inertia app (Filament ships its own assets)

   ```bash
   npm install -D tailwindcss postcss autoprefixer typescript @types/node
   ```

5. **Core packages**

   ```bash
   composer require spatie/laravel-permission spatie/laravel-activitylog spatie/laravel-medialibrary
   composer require cloudinary-labs/cloudinary-laravel getbrevo/brevo-php
   php artisan install:broadcasting --ably
   ```

6. **Configure** Cloudinary, Brevo, and Ably (`ABLY_KEY`, `VITE_ABLY_PUBLIC_KEY`, `BROADCAST_CONNECTION=ably`) in `.env`

7. **Build vertically**: migrations → Filament resources for reference data (branches, grades) → students → enrollment → finance → portals

---

**Note:** This is a large system. Ship **Filament admin + minimal public enrollment** first, then deepen portals and reporting. Adjust Filament version constraints to match your target Laravel version using the official Filament documentation.
