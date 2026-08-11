# Vincatis LMS — Help Card & User Guide

A complete, plain-language guide to every module in Vincatis LMS. This is written for the people who will actually use the system day to day — Administrators, Trainers, Reviewers, Approvers, and Trainees/Employees — so that anyone, even someone brand new to the system, can pick this up and know exactly what to do.

> **This guide is also built into the app itself.** Any logged-in user can click **Help / User Guide** in the sidebar (right under Dashboard) to see a role-tabbed version of this same guide, opened automatically to their own role. This markdown file is the canonical source — when you update one, update the other (`resources/views/help/index.blade.php`).

> Looking for technical/setup information instead? See [README.md](README.md).

---

## Table of Contents

1. [Who's Who — Roles in the System](#1-whos-who--roles-in-the-system)
2. [Getting Started — Login & Registration](#2-getting-started--login--registration)
3. [Dashboard](#3-dashboard)
4. [People — Users & Trainers](#4-people--users--trainers)
5. [Access Control — Roles & Permissions](#5-access-control--roles--permissions)
6. [Masters — Departments, Designations, Venues, Sections](#6-masters--departments-designations-venues-sections)
7. [Documents — The Document Library](#7-documents--the-document-library)
8. [Training Program — Setup, Annual Plans & Calendar](#8-training-program--setup-annual-plans--calendar)
9. [Assigning Trainers & Trainees to a Training](#9-assigning-trainers--trainees-to-a-training)
10. [Attendance Sheet](#10-attendance-sheet)
11. [Training Register (Session Log)](#11-training-register-session-log)
12. [Assessment — Reading Room & Taking an Exam](#12-assessment--reading-room--taking-an-exam)
13. [Exam Results, History & Admin Logs](#13-exam-results-history--admin-logs)
14. [Induction Progress](#14-induction-progress)
15. [Audit Logs](#15-audit-logs)
16. [Glossary](#16-glossary)
17. [Frequently Asked Questions / Troubleshooting](#17-frequently-asked-questions--troubleshooting)

---

## 1. Who's Who — Roles in the System

| Role | What they typically do |
|---|---|
| **Admin / Super Admin** | Full access — sets up Masters, Trainings, Users, Roles, Documents; approves everything |
| **Reviewer** | Reviews a training program and marks it "Reviewed" before it can be approved |
| **Approver** | Gives the final sign-off, marking a training program "Approved" |
| **Trainer** | A person tagged with "Authorize as a Trainer"; delivers sessions, marks attendance, signs off Training Register entries |
| **Trainee** | A new/in-progress employee going through Induction and initial trainings |
| **Employee** (called `regular` internally) | A trainee who has completed Induction or had a Training Session approved — the normal, fully onboarded user |

**Good to know:** A person can hold more than one role. If someone is both an Admin and a Trainer, the Dashboard always shows them the Admin view first.

---

## 2. Getting Started — Login & Registration

### Logging in
1. Open the Login page.
2. Enter your **Corporate ID** (not your email address) and your **Password**.
3. Click **Login**.
4. You'll land on the Dashboard, or on whatever page you originally tried to open before being asked to log in.

> If your details don't match, you'll see *"The provided credentials do not match our records."* Double-check your Corporate ID and password, or ask an Admin to confirm your Corporate ID.

### Registering a new account (self sign-up)
1. Open the Register page.
2. Enter your Name, Email, and choose a Password (enter it twice to confirm).
3. Click **Register**.
4. The system automatically:
   - Generates a **Corporate ID** for you (format `TRN00001`, `TRN00002`, …) — shown in a success message. **Write this down**, you'll use it to log in from now on.
   - Assigns you the **Trainee** role.
   - Logs you straight in and takes you to the Dashboard.

> Self-registration does not ask for your department. An Admin should update your profile (Department, Sub Department, Designation) under **Users & Trainers** shortly after you register, since your training assignments depend on it.

### Logging out
Use the **Logout** link in the profile menu (top-right, click your avatar).

---

## 3. Dashboard

The Dashboard is your home page — what you see depends on your role:

- **Admin/Super Admin:** total Users, total Trainers, count of Active vs. Setup (not-yet-activated) trainings, trainings waiting on a Reviewer, trainings waiting on an Approver, trainers who haven't accepted their assignment yet, unapproved Training Register entries, overall exam pass/fail counts, and recently activated trainings.
- **Trainer:** trainings you've accepted (with trainee counts), invitations still awaiting your acceptance, and upcoming trainings.
- **Reviewer:** trainings currently sitting in "In Review" status, waiting for you.
- **Approver:** trainings sitting in "Reviewed" status, waiting for your final approval.
- **Trainee/Employee:** your active enrolled trainings, and for each one, exactly what to do next — *nothing to do*, *read the documents*, *take the exam*, or *retake the exam* — plus your recent results.

No setup needed here — just log in and check what's on your plate.

---

## 4. People — Users & Trainers

**Who can access:** Admin (needs the "User List/Create" or "Trainer List" permission).
**Where:** *People → Users & Trainers*

### Viewing & searching users
1. Go to **Users List**.
2. Use the search bar to filter by Name, Employee ID, or Email, and/or the Department/Role dropdowns.
3. Click **Filter** to apply, or **Reset** to clear.
4. Click a user's row to open their **Profile** — shows department, designation, roles, trainings they're enrolled in, and their last 6 exam results.

### Adding a new user
1. Click **Add User**.
2. Fill in: Full Name, Employee ID (must be unique — this becomes their login Corporate ID), Internal ID (optional), one or more Job Roles, Password + Confirm Password, Department, Sub Department, Designation, Years of Experience, Qualification.
3. Tick **"Authorize as a Trainer"** if this person should be selectable as a Trainer elsewhere in the system.
4. Click **Create User**.

> As soon as you save, the system checks whether this user (if they're a Trainee) matches any live **Annual Training Plan** for their Department/Sub Department (or any training under Development Quality Assurance, which applies company-wide) and automatically enrolls them.

### Editing / deleting a user
- Open the user, change any field, and save. Leave the password fields blank to keep their current password.
- **Delete** removes the user permanently and immediately — there's no confirmation trail or "in use" check, so double check before deleting.

### Trainers List
A simple, read-only list of everyone flagged as a Trainer, grouped by department — useful when assigning trainers to a training.

---

## 5. Access Control — Roles & Permissions

**Who can access:** Admin.
**Where:** *Access Control → Roles* / *Permissions*

### Roles
A Role is a named bundle of permissions (e.g., "Reviewer" might bundle the permissions needed to review trainings).

1. Go to **Roles**, click **Add Role**.
2. Enter a Role Name (required, must be unique), an optional Description, and an optional "Training Required" note.
3. Tick every Permission this role should grant.
4. Click **Save**.
5. To change a role later, click **Edit**, adjust the checkboxes, and save — this replaces the role's permission set with whatever is checked (unchecking a box removes that permission from everyone with that role).

### Permissions
A Permission is a single named capability (e.g., `user-create`, `training-list`) that controllers and menus check before showing a feature.

1. Go to **Permissions**, click **Add Permission**.
2. Enter a unique Name and a Guard Name (normally `web`).
3. Click **Save**.

> Permissions mostly control what appears in the left-hand menu. Creating a permission by itself does nothing until it's assigned to a Role and that Role is assigned to a User.

---

## 6. Masters — Departments, Designations, Venues, Sections

**Who can access:** Admin.
**Where:** *Training → Masters*

Masters are the shared drop-down lists used everywhere else in the system (Department, Sub Department, Designation, Venue, Section). Set these up **before** creating users, documents, or trainings, since those all reference Masters data.

### Adding an entry
Each of the five cards (Departments, Designations, Venues, Section, Sub Departments) works the same way:
1. Type the name into the box next to the card title.
2. Click **+**.
3. It appears immediately in the list below.

### Deleting an entry
Click the trash-can icon next to the item.

> **Delete protection:** Departments, Venues, Sections, and Sub Departments **cannot be deleted while still in use** — e.g., a Department linked to any Document can't be removed, a Venue linked to any training can't be removed. You'll see *"[X] is already in use"* if you try. **Designations are the one exception** — they can be deleted even while assigned to users, so change any affected users' Designation afterward if needed.

---

## 7. Documents — The Document Library

**Who can access:** Admin/Reviewer (needs the "documents" permission).
**Where:** *Documents*

This is the master pool of SOPs, Protocols, PPTs and other controlled documents. Trainees read these documents before taking an exam, so documents need to be uploaded and reviewed here before they can be attached to a training.

### Uploading a document
1. Click **Upload New Document**.
2. Fill in: Document Name, Doc Number (required, must be unique), Type (SOP / Protocol / PPT / Others), Department, Sub Department, Section (all required), Read Time (e.g. "5 min" — this drives the reading timer trainees see later), and choose the file (PDF, DOC/DOCX, or PPT/PPTX, up to ~10 MB).
3. Click **Save to Master Pool**.

### Reviewing & approving a document
Newly uploaded documents show a **Review** button.
1. Click **Review**.
2. A "Sign & Approve" confirmation appears showing your name as a signature.
3. Click **Sign & Approve** to confirm — this stamps the document as reviewed by you, with a timestamp.

Once reviewed, the Review button disappears from that row.

### Managing the question pool for a document
Each document row has a **Manage Pool (N)** button — this is where you build the bank of exam questions tied to that specific document. See [Section 7a](#7a-managing-a-documents-question-pool) below.

### Viewing / Deleting
- **Show** opens a detail view with an inline preview (where supported).
- **Delete** removes the document and its uploaded file permanently.

> **Caution:** Deleting a document has **no "in use" check** — if it's already linked to a live training's exam configuration, deleting it can break that training's assessment. Before deleting, check whether the document is linked to any training via *Training → Manage documents*.

### 7a. Managing a Document's Question Pool

**Where:** *Documents → Manage Pool* on a specific document.

This is the actual bank of questions the exam engine draws from for that document.

1. For each question, choose the **Type**: *Yes/No* (pick the correct answer from a dropdown) or *MCQ* (fill in 4 answer options and type the correct answer's text so it matches one of the 4 options exactly).
2. Click **+ Add Question to Pool** to add more rows; click the small **×** to remove a row.
3. Click **Save Question Pool**.

> **Important:** Saving **completely replaces** the pool — every existing question for this document is deleted and re-created from what's on screen at that moment. If you open this page and accidentally remove a row, save, and close — that question is gone for good. Double-check before saving.

---

## 8. Training Program — Setup, Annual Plans & Calendar

**Who can access:** Admin (needs the "training-list" permission) for setup; everyone can view the Calendar.
**Where:** *Training → Training Program*

This is the heart of the system — where you define what a "training" is, who it's for, and how it's delivered.

### Creating a new training
1. Go to **Training Setup**, click **Add**.
2. Fill in the Main Training Title, initial Workflow Status, and optionally add **Departmental Steps** — a checklist of named steps the trainee must go through (e.g., "Administration & Maintenance"). This is used heavily for Induction-style trainings — see [Section 14](#14-induction-progress).
3. Choose the **Training Type**:
   - **Classroom / Instructor Led** — set a Start/End Date and Start/End Time; you'll assign Trainers later.
   - **Self Training (E-Learning)** — no trainer required; instead, attach one or more Documents (Type, Name, Number, Version, file upload) right here, or link existing library documents afterward.
4. If this should repeat automatically, tick **Annual Training Program** and choose a Frequency (Monthly / Quarterly / Half-Yearly / Yearly), plus the Department and Sub Department it applies to.
5. Click **Save Program**.

New trainings are saved **inactive** — they land in "Training Setup," not visible to trainees yet.

### Activating a training
From the Training List or Training Setup screen, click the **Activate** toggle.
- This flips the training to **live**, records who activated it and when, and **sends a notification to every assigned trainer and every enrolled trainee**.
- You can deactivate the same way if the training needs to go back into setup.

### Moving a training through review/approval
There's no separate "Approve" button — progression happens by editing the training and changing its **Status** field:
`Created → In Review → Reviewed → Approved`
- Only a Reviewer sees "Reviewed" as a status option to select.
- Only an Approver sees "Approved" as a status option to select.

### Annual Training Plans
- **Annual Plan Setup** = annual plans not yet active. **Created Annual Plan** = active ones.
- When you save an Annual training with a Frequency, the system **automatically creates the child trainings for you** (12 for Monthly, 3 for Quarterly, 2 for Half-Yearly, 1 for Yearly), each named after the parent (e.g., "*Fire Safety - January Training*"), each carrying over the same Departmental Steps.
- Click into a plan to see its generated child trainings.

> **Automatic enrollment:** if a training (or a generated annual child) has both a Department and Sub Department set — or belongs to the Development Quality Assurance department — every matching user is **automatically enrolled** as a trainee (status "pending") the moment it's created.

### Training List & Training Calendar
- **Training List** — a flat list of every training, useful for quick lookup.
- **Training Calendar** — a visual month/year calendar of every training's start/end dates; click an event to jump to that training's detail page.

### Linking Documents & Setting the Exam Quota
From a training's detail page, use **Manage documents** to decide which library Documents apply and how many questions to pull from each for the exam:
1. Tick **Select** for each document that should be part of this training.
2. Enter a **Quota** (how many random questions to pull from that document's question pool).
3. Click **Save**.

> For **Annual** trainings with a Department + Sub Department set, the system will **pre-select every matching document automatically** (with a default quota of 1 question each) as soon as you open this page — review and adjust before saving, don't assume the defaults are what you want.

---

## 9. Assigning Trainers & Trainees to a Training

**Where:** open a training, then **Manage Trainers** or **Manage Trainees**.

### Manage Trainers
1. For each row, pick a Trainer from the dropdown and set their Assignment Start/End Date.
2. Click **+ Add Trainer** for more rows; the trash icon removes one.
3. Below, in **Assign Venues**, add one or more Venues the same way.
4. Click **Update Assignments**.

> For Self Training programs, a trainer isn't required — the fields won't be marked mandatory.
> Newly added trainers only get notified if the training is already Active. If a trainer already accepted before, re-saving this page won't reset their acceptance.

### Manage Trainees
1. Tick each user who should be enrolled, and set their individual Start/End dates.
2. Click **Save** — newly added trainees get a notification (if the training is Active), and the change is recorded in the training's audit log.

### Trainer Acceptance
When a Trainer is assigned to a live training, they receive a "Training Session Assigned" notification with an **Accept** action. Accepting flips their status to "accepted" — visible on their Dashboard under accepted trainings.

---

## 10. Attendance Sheet

**Where:** open a training → **Attendance Sheet**.
**Who can access:** an Admin, the training's accepted Trainer, or an enrolled Trainee of that specific training.

1. Choose the **Briefed Session Type** (SOP / STP / Protocol / Others).
2. Set **Start Time** and **End Time** — Duration calculates automatically.
3. Add any **Comments**.
4. For every listed trainee, use the toggle switch to mark **Present** or **Absent**.
5. Click **Submit Attendance**.

> The trainee list is paginated (20 per page). Your in-progress marks are kept in your browser as you move between pages, so you won't lose earlier entries before you finally submit.
> **Submitting attendance also automatically creates a Training Register entry** for every trainee marked — you don't need to log the session separately in the Training Register afterward.

---

## 11. Training Register (Session Log)

**Who can access:** Admin/Trainer (needs the "session-list" permission).
**Where:** *Training → Training Register*

This is the digital equivalent of a physical training register book — one row per training session delivered to one trainee.

### Searching entries
Filter by Trainee, Topic, or a date range, then click **Search**.

### Logging a new entry
1. Click **+ Add New Entry**.
2. Fill in: Date of Training, Trainee (required), Trainer/Leader (optional for self-training — defaults to you if left blank), Register No., Page No. (both required — matches your physical register numbering), and Topic.
3. Click **Update Register**.

> Logging a session **automatically assigns the trainee the "Trainee" role** if they don't already have it.

### Signing off / approving an entry
Each entry shows **Sign & Approve** (visible to the assigned Trainer or an Admin, until it's approved). Clicking it stamps the approval with your name and a timestamp, displayed as a stylized digital signature.

> **This is an important promotion trigger:** approving a session removes the person's "Trainee" role and makes them a full **Employee**. Only sign off a session once you're confident the trainee actually completed it.

### Training Card (report)
`report/user/{user}/training/{id}` shows one trainee's complete session history in order — useful as a printable training record for audits.

---

## 12. Assessment — Reading Room & Taking an Exam

**Who can access:** any Trainee/Employee enrolled in an active Self-Training module.
**Where:** *Assessment → Training Schedule → Schedule List*, or *Exam Workspace*

### Step by step, as a trainee:
1. Open **Schedule List** — you'll see every active training you're enrolled in, and whether you've completed the reading requirement yet.
2. Click a training to open its **Reading Room**. A countdown timer starts, based on the total reading time configured across all the training's linked documents.
3. Click **Open Document** to preview each one in a secure viewer (PDF, DOCX, images, PPT all supported). While previewing: copy, right-click, print-screen, and browser dev tools are disabled/discouraged as a document-security measure.
4. The **"Complete Reading and Unlock Assessment"** button stays greyed out until the timer reaches zero — you can't skip ahead.
5. Once enabled, click it. The system double-checks your reading time server-side, then unlocks the exam.
6. Go back to Schedule List and click **Take Exam**.
7. The exam is assembled automatically: a random set of questions is pulled from each linked document's question pool (based on the quota the Admin configured), then shuffled together.
8. You'll see a progress bar that fills as you answer, and a **15-minute countdown timer**. If time runs out, your answers are **automatically submitted** as-is.
9. **Do not navigate away or close the tab mid-exam** — the browser will warn you, and doing so may lose unsaved answers.
10. Click **Submit Assessment** when done (or let the timer auto-submit).
11. You'll land on the **Result** page immediately, showing your score and whether you passed.

> **Pass mark is 60%.** Your score is based on the training's full configured question quota — **leaving a question blank counts against you the same as answering it wrong**, so answer everything before submitting.
> The 15-minute exam timer is fixed and the same for every exam, regardless of how long the reading material took.
> If you try to jump straight to the exam without finishing the required reading, you'll be redirected back with a message asking you to complete the reading first.

---

## 13. Exam Results, History & Admin Logs

**Where:** *Assessment → Training Schedule*

- **Results History** (your own results) — every exam attempt you've made, paginated, with pass/fail shown.
- **Admin Logs** (Admin/authorized users only, needs "admin-logs" permission) — a company-wide view of every exam attempt by every user, with overall pass/fail statistics.
- **Exam Details** — click into any result (yours, or any result if you're an authorized Admin) to see a question-by-question breakdown: the question, the answer given, the correct answer, and whether it was right or wrong.

> Only the person who took the exam, or an Admin/authorized user, can open an exam's detailed breakdown — everyone else gets an "Unauthorized action" message.

---

## 14. Induction Progress

**Who can access:** Admin/HR/Trainer (needs the "induction-training" permission) to log progress; a Trainee can view their own progress only.
**Where:** *Induction Progress*

This module tracks a new hire's one-on-one walkthrough of each Departmental Step in the "Induction Training" program — it's a sign-off record, not an exam.

1. Open **Induction Progress** to see every trainee currently going through induction, their completion percentage, and status: **Enrolled**, **In Progress**, or **Completed** — plus a department-by-department breakdown card.
2. Click into a trainee to see their step-by-step checklist (each step auto-labeled with a short code, e.g. "AMD" for Administration & Maintenance).
3. To mark a step complete: fill in **Interacted Person** (defaults to you), **Designation** (defaults to your designation), and **Comments** (pre-filled with a standard sign-off sentence you can edit), then submit.
4. Use **Report** to view/print a trainee's full sign-off history — who confirmed each step and when.

> **This is the Trainee → Employee promotion path:** once **every** step for a training literally named "Induction Training" is marked complete, the trainee is **automatically promoted to Employee**. Make sure a company's induction program is actually named "Induction Training" (any capitalization is fine) — programs named anything else won't trigger this promotion, even if they have identical steps.

---

## 15. Audit Logs

**Who can access:** Admin. (Note: there's currently no menu link to this page — reach it via the direct Audit Logs URL, or via a training's own "Audit Log" quick action.)

A complete, filterable history of who created, updated, or deleted what, across the whole system.

1. Open **Audit Logs**.
2. Use the **Action** filter (All / Created / Updated / Deleted) and click **Filter**.
3. Each row shows: Who made the change (or "System" if automated), the Action, the affected record type, what changed, and when.

**Per-training audit log:** open any training and use its own **Audit Log** quick action to see only the changes made to that specific training (creation, status changes, trainer/trainee assignment, step/document edits).

---

## 16. Glossary

| Term | Meaning |
|---|---|
| **Corporate ID** | Your login username — either assigned by an Admin or auto-generated (`TRNxxxxx`) at self-registration |
| **Master Document** | A controlled file (SOP, Protocol, PPT, etc.) in the central Document Library |
| **Question Pool** | The bank of exam questions tied to one specific Master Document |
| **Question Quota** | How many questions a training pulls from a given document's pool when assembling an exam |
| **Departmental Steps** | The checklist items inside a training program (used most for Induction) |
| **Annual Training Plan** | A training that auto-repeats and auto-generates child trainings on a set frequency |
| **Active / Setup** | Whether a training is live and visible to trainees (Active) or still being configured (Setup) |
| **Training Register** | The digital log of individual training sessions delivered, one row per trainee per session |
| **Reading Room** | The timed document-preview screen a trainee must complete before an exam unlocks |
| **`is_trainer`** | A flag on a user's profile that makes them selectable as a Trainer — separate from their Role |

---

## 17. Frequently Asked Questions / Troubleshooting

**Q: I activated a training but the assigned trainees say they didn't get notified.**
A: Notifications are recorded internally, but there is currently no notification bell/inbox in the app for users to view them. This is a known gap — flag it to your system administrator/developer if in-app alerts are expected; in the meantime, communicate new assignments to trainees directly.

**Q: I can't delete a Department, Venue, Section, or Sub Department.**
A: These are protected while still referenced by a Document or Training. Reassign or remove the dependent records first. (Designations are the exception — they can always be deleted.)

**Q: I edited a document's Question Pool and now old questions are missing.**
A: Saving the Question Pool always replaces the full list with what's currently on screen — it doesn't merge. Always review all rows carefully before saving.

**Q: A trainee's exam score seems lower than expected even though they answered every question shown.**
A: Scoring is based on the training's full configured question quota, not just the questions that were actually displayed. If a document's quota is set higher than the number of questions available in its pool, the score calculation still divides by the full quota — check the Question Pool size against the configured Quota for that document.

**Q: I deleted a Document and now a training's exam is broken.**
A: Document deletion doesn't check whether it's linked to a live training. Before deleting a document, check *Training → Manage documents* on any related training to confirm it isn't still linked.

**Q: A user completed all their induction steps but wasn't promoted to Employee.**
A: Promotion only triggers for a training program named exactly "Induction Training" (capitalization doesn't matter, but the wording does). Confirm the training's title matches.

**Q: Self-registered users seem to be enrolled in the wrong department's trainings.**
A: Self-registration doesn't ask for a department and defaults new users to Department ID 1. An Admin should correct the new user's Department/Sub Department under *Users & Trainers* right after they register, before relying on auto-enrollment.

**Q: Can a Trainee reach a page they shouldn't see just by typing the URL?**
A: Menu items are hidden based on role/permission, but not every page is blocked at the server if the exact URL is guessed. Don't rely on the menu alone for anything sensitive — this is a known area for future hardening.

---

*This help card reflects the system as implemented at the time of writing. If a workflow described here doesn't match what you see on screen, the application may have been updated since — please let your system administrator know so this document can be refreshed.*
