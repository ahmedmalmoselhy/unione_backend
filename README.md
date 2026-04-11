# UniOne Backend

Laravel backend for a full university management platform with three delivery surfaces:

- **Dashboard web app** for administration (`/dashboard`)
- **Portal web app** for students, professors, and employees (`/`)
- **REST API** for mobile/web clients (`/api`)

This README reflects the **currently implemented behavior** in this repository.

---

## Implemented Feature Set

### 1) Authentication, Sessions, and Access Control

- Session-based auth for dashboard/portal + Sanctum token auth for API.
- API auth endpoints: login, logout, me, change password, profile update.
- Forgot/reset password flow with expiring reset tokens and email delivery.
- Personal access-token management (list tokens, revoke one, revoke all).
- Middleware-driven role access:
  - `dashboard`, `portal`
  - `admin`, `university.admin`, `scoped.admin`
  - `api.role`
- Forced password-change flow (`must_change_password`) for newly assigned admins.
- Locale middleware supports English/Arabic for web and API (`X-Locale`, `?locale=`, `Accept-Language`).

### 2) Role Model and Scoped Administration

Supported roles in the system:

- `admin` (system admin)
- `university_admin`
- `faculty_admin`
- `department_admin`
- `employee`
- `professor`
- `student`

Role assignment features:

- Faculty admin assignment/revocation (system admin scope).
- Department admin assignment/revocation (system or scoped faculty admin).
- Department head assignment/revocation.
- Role records are **revocable** (not hard-deleted) via `role_user.revoked_at`.
- Admin assignment/revocation generates notifications and audit entries.

### 3) University and Academic Structure Management (Dashboard)

- University profile management (name, Arabic name, contact info, logo, president).
- University vice-president CRUD.
- Faculty CRUD (dean, logo, enrollment type, active state).
- Faculty observer auto-creates mandatory managerial departments:
- Students Care
- Students Affairs
- Legal
- Plus a `General` academic department when faculty enrollment type is `deferred`.
- Department CRUD for academic/managerial departments.
- Course CRUD with:
- prerequisite graph
- multi-department ownership (`department_course` with owner flag)
- bilingual names and academic metadata
- Section CRUD with professor assignment, teaching assistant assignment APIs, exam schedule publishing APIs, group project management APIs, capacity, room, and structured weekly schedule.
- Academic term CRUD with registration/exam/grade timeline fields and active-term switching.

### 4) People and Academic Records Management

- Student CRUD (with avatar, status, faculty/department linking, enrollment state).
- Professor CRUD (rank, specialization, office, hire date, avatar).
- Employee CRUD (job title, employment type, salary, lifecycle fields, avatar).
- Student transfer between departments with persistent transfer history.
- Dashboard transcript PDF export per student.

### 5) Enrollment, Capacity, Prerequisites, and Waitlist

Enrollment logic is implemented in both web portal and API, with API providing extended waitlist behavior.

- Registration window enforcement using active term registration dates.
- Duplicate enrollment prevention.
- Section capacity checks.
- Course prerequisite enforcement before enrollment.
- Student drop flow with period checks.

API-only waitlist capabilities:

- Auto-join waitlist when section is full (positioned queue).
- List and leave waitlist.
- Position re-numbering after removal.
- Auto-promotion of first waitlisted student when a seat is dropped.
- Promotion notification to the promoted student.

### 6) Grading, GPA, Academic Standing, and Transcript Services

- Professor/API and professor/portal grade submission/update per section enrollment.
- Dashboard grade CRUD for admins.
- Grade import/export support (CSV/XLS/XLSX).
- Student notifications when grades are posted.
- GPA service recalculates:
- cumulative GPA (credit-hour weighted)
- per-term GPA
- academic standing (`good_standing`, `probation`, `dismissal`)
- Student API endpoints for:
- grades
- transcript JSON
- transcript PDF
- full academic history with credit progress toward required department credit hours

### 7) Attendance Management

- Professor attendance session management (list/create/show/update) per section.
- Bulk attendance record capture per session.
- Attendance statuses: `present`, `absent`, `late`, `excused`.
- Student attendance endpoint with per-course summary counts.
- Portal professor attendance UI supports the same lifecycle.

### 8) Announcements and Notifications

Announcement capabilities:

- Dashboard announcement CRUD with visibility targeting:
- university
- faculty
- department
- section
- Publish/expiry behavior for active visibility.
- Scope-aware announcement management for faculty/department admins.
- Read-tracking (`announcement_reads`) for users.

Section announcements:

- Professors can post section announcements (API + portal flow).
- Enrolled students receive in-app notification on new section announcement.

Notification inboxes:

- Dashboard, portal, and API all support:
- list
- mark one read
- mark all read
- delete
- API includes unread filtering and unread counts.

### 9) Ratings and Feedback

- Students can rate completed courses only after term end.
- One rating per enrollment (upsert behavior for edits).
- Optional text feedback/comment.
- Dashboard ratings analytics by professor with:
- average rating
- total ratings
- star-level distribution breakdown

### 10) Integrations and Webhooks

- Admin/faculty_admin/department_admin API routes for webhook registration and management.
- Ownership isolation: users can only manage their own webhooks.
- Supported outbound events:
- `enrollment.confirmed`
- `grade.posted`
- Signed webhook delivery headers:
- `X-UniOne-Event`
- `X-UniOne-Signature` (HMAC SHA-256)
- Delivery history endpoint (latest attempts per webhook).
- Failure tracking with auto-disable after 10 consecutive failures.
- Dispatch implemented as queued job (`DispatchWebhooks`).

### 11) Data Import/Export

Imports:

- Students
- Professors
- Grades

Exports:

- Students
- Professors
- Employees
- Enrollments
- Grades

Template downloads are available for supported import flows.

### 12) Auditability and Governance

- Central audit log records major actions across modules (create/update/delete/assign/revoke/transfer/login/logout).
- Stores actor, action, target type/id, old/new values, IP, timestamp.
- Dashboard audit view supports filtering by action/type/search/date range.

### 13) Dashboard Insights and Operational Views

- Role-aware dashboard home views (system, university, faculty, department scopes).
- Data-health indicators (e.g., departments without head, sections without professor).
- Dashboard stats endpoint returns:
- overview counts
- enrollment status distribution
- grade distribution
- GPA distribution
- section fill rates
- Dashboard schedule board with faculty/department/term/level filtering.

### 14) Portal Experience

Portal roles: student, professor, employee.

- Student portal: profile, schedule, enrollment, grades, attendance, announcements, notifications, ratings.
- Professor portal: profile, schedule, sections, grading, attendance, section announcements, notifications.
- Employee portal: profile, schedule context, announcements, notifications.

---

## API Highlights

Public API groups:

- `POST /api/auth/login`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`

Protected API groups include:

- Auth utilities (`/api/auth/*`)
- Student APIs (`/api/student/*`)
- Professor APIs (`/api/professor/*`)
- Announcements (`/api/announcements*`)
- Notifications (`/api/notifications*`)
- Admin webhooks (`/api/admin/webhooks*`)

---

## Security and Reliability Controls

- API rate limits:
- `api.login`: 5/min per IP
- `api.password`: 3/min per IP
- `api`: 60/min per user/IP
- `api.enroll`: 10/min per user
- `api.grade`: 30/min per user
- Scoped query patterns for faculty/department admins.
- Soft delete support for users and announcements.

---

## Stack

- PHP 8.2+
- Laravel 12
- Laravel Sanctum
- Laravel Excel (`maatwebsite/excel`)
- DomPDF (`barryvdh/laravel-dompdf`)
- Pest test framework

---

## Local Development

### 1) Initial setup

```bash
composer setup
```

This runs install, environment bootstrap, key generation, migrations, and frontend build.

### 2) Run development services

```bash
composer dev
```

Starts:

- Laravel server
- Queue listener
- Vite dev process

### 3) Run tests

```bash
composer test
```
