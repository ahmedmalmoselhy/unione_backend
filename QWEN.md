# UniOne Backend - Project Context

## Project Overview

**UniOne Backend** is a comprehensive Laravel 12-based university management platform. It provides three delivery surfaces:

- **Dashboard Web App** (`/dashboard`) — Administrative interface for system admins, university admins, faculty admins, and department admins
- **Portal Web App** (`/`) — Role-based portal for students, professors, and employees
- **REST API** (`/api`) — Token-based API for mobile/web clients using Laravel Sanctum

The system manages the full university lifecycle including students, professors, employees, faculties, departments, courses, sections, enrollments, grades, attendance, announcements, notifications, ratings, and webhooks.

## Tech Stack

| Category | Technology |
| ---------- | ------------ |
| Backend Framework | Laravel 12 (PHP 8.2+) |
| Database | PostgreSQL 16 |
| Cache/Session | Redis 7 |
| Web Server | Nginx |
| Authentication | Laravel Sanctum (API) + Session (web) |
| Frontend Build | Vite + Tailwind CSS 4 |
| Excel Import/Export | maatwebsite/excel |
| PDF Generation | barryvdh/laravel-dompdf |
| Testing | Pest (PHPUnit-based) |
| Queue | Database driver (async via workers) |
| Containerization | Docker + Docker Compose |

## Project Architecture

### Directory Structure

```bash
unione_backend/
├── app/
│   ├── Exports/          # Laravel Excel export classes
│   ├── Imports/          # Laravel Excel import classes
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/      # REST API controllers
│   │   │   ├── Auth/     # Auth controllers (login, password reset)
│   │   │   ├── Dashboard/# Admin dashboard controllers
│   │   │   └── Portal/   # Role-based portal controllers
│   │   ├── Middleware/   # Custom middleware (role checks, locale, etc.)
│   │   └── Requests/     # Form request validation
│   ├── Jobs/             # Queue jobs (e.g., DispatchWebhooks)
│   ├── Mail/             # Mailable classes
│   ├── Models/           # Eloquent models (31 models)
│   ├── Notifications/    # In-app + email notifications
│   ├── Observers/        # Model observers for audit/logging
│   ├── Providers/        # Service providers
│   └── Services/         # Business logic services (e.g., GPA service)
├── config/               # Laravel configuration
├── database/
│   ├── factories/        # Model factories for testing
│   ├── migrations/       # Database migrations (48 migrations)
│   └── seeders/          # Database seeders
├── docker/               # Docker configuration
│   ├── entrypoint.sh
│   ├── nginx/default.conf
│   └── supervisor/supervisord.conf
├── lang/                 # Localization (i18n)
├── resources/            # Views, JS, CSS assets
├── routes/
│   ├── api.php           # REST API routes (Sanctum token auth)
│   ├── web.php           # Dashboard + Portal web routes (session auth)
│   └── console.php       # Artisan console commands
├── storage/              # Logs, uploads, compiled views
└── tests/                # Pest test suites (Unit + Feature)
```

### Core Models (31 total)

**Organizational:** University, UniversityVicePresident, Faculty, Department, AcademicTerm

**People:** User, Student, Professor, Employee, Role, RoleUser

**Academic:** Course, Section, Department, Enrollment, Grade, CoursePrerequisite

**Attendance/Engagement:** AttendanceRecord, AttendanceSession, CourseRating, SectionAnnouncement, SectionAnnouncement, SectionTeachingAssistant, GroupProject, GroupProjectMember

**Communication:** Announcement, AnnouncementRead, Notification

**Operations:** AuditLog, EnrollmentWaitlist, ExamSchedule, StudentDepartmentHistory, StudentTermGpa, Webhook, WebhookDelivery

### Role System

The platform supports scoped role-based access through the `role_user` pivot table (with `revoked_at` soft-delete support):

- `admin` — System administrator (full access)
- `university_admin` — University-level admin
- `faculty_admin` — Faculty-level admin
- `department_admin` — Department-level admin
- `employee` — Staff/employee role
- `professor` — Professor/instructor role
- `student` — Student role

Role scoping uses `scope` and `scope_id` fields on the `role_user` table for hierarchical access control.

### Middleware Stack

- **Web:** `dashboard`, `portal` — session-based authentication for respective surfaces
- **Role-based:** `admin`, `university.admin`, `scoped.admin`, `api.role:{roles}` — authorization checks
- **Security:** `force.password` — forces password change for newly assigned admins
- **Localization:** Locale middleware supports English/Arabic via `X-Locale` header, `?locale=` query param, or `Accept-Language` header

## Building and Running

### Prerequisites

- PHP 8.2+ with extensions: pdo_pgsql, pgsql, mbstring, exif, pcntl, bcmath, gd, zip
- Composer
- Node.js (for Vite build)
- PostgreSQL 16
- Redis 7 (optional, for cache/session)

### Local Development (Non-Docker)

```bash
# Initial setup (installs deps, generates key, runs migrations, builds frontend)
composer setup

# Start development server (Laravel + Queue + Vite)
composer dev

# Run tests
composer test
```

### Docker Development

```bash
# Start all services (App + PostgreSQL + Redis + Queue Worker)
docker-compose up -d

# Access the application at http://localhost:8080

# View logs
docker-compose logs -f app
docker-compose logs -f worker

# Run Artisan commands inside container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan route:list

# Stop services
docker-compose down

# Production build
docker-compose -f docker-compose.prod.yml up -d --build
```

### Port Mapping (Docker)

| Service | Host Port | Container Port |
| --------- | ----------- | ---------------- |
| Nginx (Web App) | 8080 | 80 |
| PostgreSQL | 5433 | 5432 |
| Redis | 6380 | 6379 |

### Database

- Uses PostgreSQL (configured via `DB_CONNECTION=pgsql`)
- SQLite used only for testing (`DB_CONNECTION=sqlite` in phpunit.xml)
- 48 migrations covering all domain models

## Key Features

### Authentication & Security

- Session auth for dashboard/portal, Sanctum tokens for API
- Forgot/reset password with expiring tokens + email delivery
- Personal access-token management (list, revoke one, revoke all)
- API rate limiting: login (5/min), password (3/min), general API (60/min), enroll (10/min), grade (30/min)
- Forced password-change flow for newly assigned admins

### Academic Management

- University, faculty, department CRUD operations
- Course management with prerequisite graphs and multi-department ownership
- Section management with professor/TA assignment, capacity, scheduling
- Academic term management with registration/exam/grade timelines
- Active-term switching

### People Management

- Student/Professor/Employee CRUD with avatars and lifecycle fields
- Student transfer between departments with persistent history
- Dashboard transcript PDF export
- Data import/export (Excel/CSV) for students, professors, employees, grades, enrollments

### Enrollment & Waitlist

- Registration window enforcement
- Duplicate enrollment prevention
- Section capacity checks
- Course prerequisite enforcement
- API-only waitlist with auto-join, position management, auto-promotion, and notifications

### Grading & GPA

- Grade submission via portal (professors) and API
- Grade import/export (CSV/XLS/XLSX)
- GPA service: cumulative (credit-weighted), per-term, academic standing (good/probation/dismissal)
- Student API: grades, transcript JSON, transcript PDF, academic history with credit progress

### Attendance

- Professor attendance session management
- Bulk attendance capture per session
- Statuses: present, absent, late, excused
- Student attendance endpoint with per-course summary

### Announcements & Notifications

- Announcement CRUD with visibility targeting (university/faculty/department/section)
- Publish/expiry behavior, read-tracking
- Section announcements for professors
- In-app + email notifications on grades posted, exam schedules published, section announcements
- Notification inbox: list, mark read, mark all read, delete, unread filtering

### Ratings & Feedback

- Students rate completed courses after term end
- One rating per enrollment (upsert for edits), optional text feedback
- Dashboard ratings analytics with average, total, star distribution

### Webhooks & Integrations

- Outbound webhooks for `enrollment.confirmed` and `grade.posted` events
- HMAC SHA-256 signed delivery headers
- Delivery history tracking
- Auto-disable after 10 consecutive failures
- Queued dispatch via `DispatchWebhooks` job

### Audit & Governance

- Central audit log for major actions (create/update/delete/assign/revoke/transfer/login/logout)
- Stores actor, action, target type/id, old/new values, IP, timestamp
- Filterable audit view by action/type/search/date range

## Testing

Tests use **Pest** (a PHP testing framework built on PHPUnit).

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test suite
php artisan test tests/Feature
php artisan test tests/Unit
```

Test configuration in `phpunit.xml`:

- Uses SQLite in-memory database for testing
- Bcrypt rounds set to 4 for faster tests
- Queue set to sync, cache to array, mail to array
- Coverage source includes the `app/` directory

## Development Conventions

- **Bilingual support:** English/Arabic locale support throughout
- **Soft deletes:** Users, announcements, and role assignments support soft deletion
- **Scoped queries:** Faculty/department admins have scoped query patterns for data isolation
- **Audit logging:** Major actions are logged to central audit table
- **Notifications:** Both in-app and email notifications for important events
- **Queue-based processing:** Webhook dispatch, imports, and other long-running tasks use queues

## Environment Variables

Key environment variables (see `.env.example` for full list):

| Variable | Description |
| ---------- | ------------- |
| APP_NAME | Application name |
| APP_LOCALE | Default locale (en/ar) |
| DB_CONNECTION | Database driver (pgsql) |
| DB_HOST/PORT/DATABASE/USERNAME/PASSWORD | PostgreSQL connection |
| SESSION_DRIVER | Session storage (database/file) |
| QUEUE_CONNECTION | Queue driver (database) |
| CACHE_STORE | Cache driver (database/file) |
| REDIS_HOST/PORT | Redis connection |
| MAIL_MAILER | Mail driver (log/smtp) |

## API Route Groups

| Group | Prefix | Auth | Description |
| ------- | -------- | ------ | ------------- |
| Auth | /api/auth/* | Mixed | Login, logout, password reset, profile, token management |
| Student | /api/student/* | Sanctum + role:student | Profile, enrollment, grades, transcript, attendance, schedule, ratings, waitlist |
| Professor | /api/professor/* | Sanctum + role:professor | Profile, sections, grading, attendance, section announcements |
| Admin | /api/admin/* | Sanctum + role:admin/faculty_admin/department_admin | Teaching assistants, exam schedules, group projects, webhooks |
| Shared | /api/announcements, /api/notifications | Sanctum | Announcements and notifications for all authenticated users |
