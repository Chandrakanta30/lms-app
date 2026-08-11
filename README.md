# Vincatis LMS

A Laravel 12 Learning Management System built for **training compliance** in a pharmaceutical / lab environment. It tracks who needs to be trained on what SOPs/Protocols, runs timed reading + exam assessments, keeps an attendance register, and maintains a full audit trail — all requirements driven by regulatory compliance, not incidental complexity.

> This file is the **developer / maintainer** reference. For a plain-language, click-by-click guide aimed at end users and customers, see **[HELP_CARD.md](HELP_CARD.md)**.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12, PHP 8.2 |
| Auth/RBAC | Spatie `laravel-permission` v6 (roles + permissions) |
| Audit trail | Spatie `laravel-activitylog` v4 |
| Database | SQLite (default/dev) or MySQL — set via `DB_CONNECTION` |
| Frontend | Blade templates, Bootstrap/Vuexy admin theme, jQuery, SweetAlert2 |
| Build tooling | Vite, Tailwind (partial use), `laravel-vite-plugin` |
| Queue/Cache/Session | Database driver (default) |

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if using sqlite (default)
php artisan migrate
npm install && npm run build     # or `npm run dev` while developing
php artisan serve
```

Or use the bundled composer scripts: `composer run setup` (install + migrate + build) and `composer run dev` (runs server + queue listener + log tail + vite concurrently).

There's also an in-app `/system-update` route (`system.update`) that runs `migrate --force` + `optimize:clear` remotely — used as a poor-man's deploy hook. No auth guard on it as of this writing — treat as sensitive.

## Domain Concepts

| Concept | Model | Notes |
|---|---|---|
| Training Module | `TrainingModule` | Parent/child via `parent_id` (departmental steps) and `annual_parent_id` (annual plan children) |
| Annual Training | `TrainingModule` (`is_anuual=1`) | Auto-generates child instances by frequency (monthly/quarterly/half_yearly/yearly) |
| Master Document | `MasterDocument` | Global SOP/Protocol/PPT pool; linked to modules via `module_document_pivot` with a `question_quota` |
| Master Question | `MasterQuestion` | Per-document question pool (Yes/No or MCQ); exam pulls random questions per module's quota |
| Training Session | `TrainingSessions` | Training Register entry per trainee; trainer "Sign & Approve" promotes Trainee → `regular` role |
| User Training | pivot `training_user` | Enrollment + attendance status between User and TrainingModule |
| Trainer Training | pivot `trainer_training` | Trainer assignment with `acceptance_status` (pending/accepted) |
| Exam Result | `ExamResult` | Score, pass/fail, full answer breakdown (JSON) |
| Document Read Tracker | `DocumentReadTracker` | Enforces minimum reading time before exam unlocks |
| Notification | `Notification` | Written on trainer/trainee assignment & activation — **no front-end UI reads these yet** (see Known Gaps) |

## Roles

Admin / Super Admin (full access) · Reviewer (`status → reviewed`) · Approver (`status → approved`) · Trainee (limited, pre-induction) · Employee / `regular` (post-induction/post-session-approval) · Trainer (`is_trainer=1` flag on `User`, not a Spatie role).

## Key Business Rules

1. `TrainingModule.is_active`: `0` = setup phase (not visible to trainees/trainers), `1` = live. Activating a training stamps `activated_at/by` and notifies every assigned trainer/trainee.
2. **Auto-enrollment**: creating/updating a Trainee-role user, or creating a training, cross-matches Department + Subdepartment and auto-enrolls (status `pending`). Trainings under the "Development Quality Assurance" department enroll **all** DQA users regardless of subdepartment.
3. **Exam flow**: enroll → read linked documents (timed, tracked in `DocumentReadTracker`) → reading unlocks exam → exam pulls random questions from each linked document's `MasterQuestion` pool by `question_quota` → submit → grade (pass mark **60%**, denominator = configured quota total, not questions answered).
4. **Induction Training** completion (all steps for a training literally named "Induction Training", case-insensitive) → auto-promotes user to `Employee` role.
5. **Training Session approval** (`TrainingSessionController::approve`) → removes `trainee` role, assigns `regular` role.
6. Annual plans auto-spawn child trainings on creation (12/3/2/1 depending on frequency), each inheriting the parent's departmental steps.

## Status Flow (Training review/approval)

`created → inreview → reviewed → approved` — progressed by changing the Status field on the training's edit form (Reviewer sees "Reviewed" as an option, Approver sees "Approved"); there's no separate approve button.

## Module Map (controller → sidebar location)

| Controller | Sidebar location | Summary |
|---|---|---|
| `DashboardController` | Dashboard | Role-specific home summary |
| `UserController` | People → Users & Trainers | User CRUD, roles, dept/subdept, trainer flag |
| `RoleController` / `PermissionController` | Access Control | Spatie roles & permissions CRUD |
| `MasterController` | Training → Masters | Department / Sub Department / Designation / Venue / Section lookups |
| `TrainingModuleController` | Training → Training Program | Setup, Annual Plans, Calendar, trainer/trainee assignment, attendance, activation, per-training audit log |
| `TrainingSessionController` | Training → Training Register | Session log ("register book"), trainer sign-off/approval |
| `QuestionController` / `UserExamController` | Assessment → Training Schedule / Exam Workspace | Reading room, exam taking/grading, results, history, admin logs |
| `MasterDocumentController` | Documents | Document library CRUD + review/approve sign-off |
| `MasterQuestionController` | (via Documents → Manage Pool) | Per-document question bank |
| `ModuleLinkController` | (via Training → Manage documents) | Links documents to a training + sets exam question quota |
| `UserTrainingController` | Induction Progress | Step-by-step induction sign-off, drives Trainee → Employee promotion |
| `AuditLogController` | *(no sidebar link — direct URL only)* | Global activity log viewer |

Full click-by-click usage for every module lives in **[HELP_CARD.md](HELP_CARD.md)** — keep both docs updated together when controllers/routes change.

### In-app Help page

`HelpController@index` (route `help.index`, `/help`) renders `resources/views/help/index.blade.php` — a searchable, card-grid, in-product version of HELP_CARD.md. Content lives in `HelpController::catalog()` as structured data (icon, accent color, summary, "where", numbered steps, notes) per section — the view is a generic renderer, not hand-written per-role markup.

- **Access is role-gated by design:** only Admin/Super Admin gets the full tab strip (Admin/Trainer/Reviewer/Approver/Trainee/FAQ) to audit any role's guide. Every other role (Trainer/Reviewer/Approver/Trainee) only ever sees two tabs — their own role's guide and the shared FAQ — computed server-side in `HelpController::index()`, not just hidden client-side.
- **Cards expand in place** (plain vanilla JS, no Bootstrap plugin dependency) — clicking a card toggles a `.is-open` class that spans it full-width via CSS Grid and reveals the detail (where/steps/notes).
- **Search box** filters cards by title+summary text within the currently active tab only (`data-search` attribute per card, client-side substring match).
- Linked from the sidebar as **Help / User Guide**, visible to everyone (no permission gate on the route itself — the controller does the role filtering).

When you change a workflow in a controller, update `HelpController::catalog()` and HELP_CARD.md together.

## Known Gaps / Gotchas (worth fixing or at least tracking)

- **No route-level permission middleware.** `routes/web.php` only checks `auth`; access control is enforced by hiding sidebar links (`@can`) and a few in-controller checks. A user who knows/guesses a URL may reach pages their role shouldn't see.
- **Notifications have no UI.** `Notification` rows are written (trainer/trainee assignment, activation) but there is no bell icon/dropdown in `partials/navbar.blade.php` to read them — currently a backend-only feature.
- **Designation delete has no "in use" guard** (unlike Department/Venue/Section/Sub Department), so a designation assigned to users can still be deleted.
- **User delete is a hard delete with no dependency check.**
- **Self-registration hardcodes `deparment_id = 1`** (note the pre-existing column typo) — self-registered users always land in whatever department has ID 1, with no picker on the form; this feeds into auto-enrollment, so double check department seeding order.
- **Login error message binds to `email`** field key even though login is by `corporate_id` — verify the Blade view surfaces it correctly.
- Per-training **"Manage Questions"** screen (`questions.manage`/`sync`, `TrainingModule::questions()`) looks disconnected from the live exam flow, which actually pulls from `MasterQuestion` pools via `ModuleLinkController`/`MasterQuestionController`. Confirm with product owner whether it's legacy before removing or documenting it as current.
- **Master Question Pool save is a destructive full replace** (delete-all-then-recreate) — no versioning/history if someone fat-fingers a save.
- **Document delete has no "in use" guard** — deleting a document tagged to a live training can break that training's exam configuration.
- Exam countdown is a **hardcoded 15:00 client-side timer**, unrelated to each document's configured read time.
- `AuditLogController@index` and `TrainingModuleController@auditLogs` (`trainings.audit.logs`) are near-duplicate implementations over the same `Activity` model — consolidate if touching audit logs.
- Dashboard role priority is **Admin > Trainer > Reviewer > Approver > Trainee** — a multi-role user always sees the highest-priority dashboard.
- **The loaded Bootstrap JS is v5** (`public/new-layout/js/bootstrap.js`), which only responds to `data-bs-toggle` / `data-bs-target` / `data-bs-parent`. Several existing views (e.g. `trainings/index.blade.php`'s `custom-accordion` and its per-row `nav nav-pills`) use the old Bootstrap-4 `data-toggle` / `data-target` attributes instead — verified via headless-browser testing that these are silently inert (clicking does nothing beyond the first server-rendered `show`/`active` state). Confirmed and fixed in `resources/views/help/index.blade.php`; worth an app-wide sweep (`grep -rn 'data-toggle=\|data-target=\|data-parent=' resources/views`) to fix the rest.

## Session Notes / Open Items (from project scratch notes)

Historical `task list` file in the repo root shows earlier requested features (start/end date+time, self-training trainer optional, per-training document upload, section master, MCQ 4-option UI, attendance + brief description, login alert for assigned training, trainer acceptance screens) — **all of these are implemented** as of this documentation pass. Keeping this note here in case that file is deleted later.

## Where to Look When You Return to This Project

1. Skim the **Module Map** table above to find the right controller.
2. Check **Known Gaps** before assuming odd behavior is a bug you introduced — several are pre-existing.
3. `routes/web.php` is short (185 lines) and un-versioned/un-prefixed — it's the fastest way to see every URL in the app.
4. Business rules that look like "over-engineering" (timers, audit logs, sign-offs, quotas) are intentional regulatory/compliance requirements — don't simplify them away without checking with the customer.
