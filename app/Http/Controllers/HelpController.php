<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $isAdmin    = $user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'super-admin']);
        $isReviewer = !$isAdmin && $user->hasRole('Reviewer');
        $isApprover = !$isAdmin && $user->hasRole('Approver');
        $isTrainer  = (int) $user->is_trainer === 1;

        $ownRole = match (true) {
            $isAdmin    => 'admin',
            $isTrainer  => 'trainer',
            $isReviewer => 'reviewer',
            $isApprover => 'approver',
            default     => 'trainee',
        };

        $catalog = $this->catalog();

        // Admin can browse every role's guide; everyone else only ever sees
        // their own guide plus the shared FAQ (by design — see project help card).
        $visibleSections = $isAdmin
            ? ['admin', 'trainer', 'reviewer', 'approver', 'trainee', 'faq']
            : [$ownRole, 'faq'];

        $sections = collect($catalog)->only($visibleSections)->all();

        return view('help.index', [
            'sections'  => $sections,
            'activeTab' => $ownRole,
            'isAdmin'   => $isAdmin,
        ]);
    }

    private function catalog(): array
    {
        $accents = ['blue', 'green', 'orange', 'purple', 'red', 'teal', 'indigo', 'pink'];

        $withAccents = function (array $cards) use ($accents) {
            foreach ($cards as $i => &$card) {
                $card['accent'] = $accents[$i % count($accents)];
            }
            return $cards;
        };

        return [
            'admin' => [
                'label' => 'Admin',
                'icon'  => 'shield-crown-outline',
                'intro' => 'As an Admin you have full access. Recommended setup order: <strong>1. Masters &rarr; 2. Roles &amp; Permissions &rarr; 3. Users &amp; Trainers &rarr; 4. Documents &rarr; 5. Training Program &rarr; 6. Assign Trainers/Trainees.</strong>',
                'cards' => $withAccents([
                    [
                        'icon' => 'office-building-cog-outline',
                        'title' => 'Masters',
                        'badge' => 'Do this first',
                        'summary' => 'Set up the shared drop-down lists — Department, Sub Department, Designation, Venue, Section — before anything else.',
                        'where' => 'Training &rarr; Masters',
                        'steps' => [
                            'Type a name into the box on the card you want (Departments / Designations / Venues / Section / Sub Departments).',
                            'Click <strong>+</strong> to add it.',
                            'Click the trash-can icon on any row to delete it.',
                        ],
                        'notes' => [
                            'Departments, Venues, Sections and Sub Departments cannot be deleted while a Document or Training is still using them &mdash; you\'ll see &ldquo;already in use&rdquo;. Designations are the one exception and can always be deleted.',
                        ],
                    ],
                    [
                        'icon' => 'shield-key-outline',
                        'title' => 'Roles &amp; Permissions',
                        'summary' => 'Bundle capabilities into named Roles, then assign Roles to Users to control what each person can see and do.',
                        'where' => 'Access Control &rarr; Roles / Permissions',
                        'steps' => [
                            'Go to <strong>Roles &rarr; Add Role</strong>, name it, tick every permission it should grant, and Save.',
                            'Editing a role and saving <em>replaces</em> its permission set with whatever is checked at that moment.',
                            'Go to <strong>Permissions &rarr; Add Permission</strong> to create a brand-new capability, then assign it to a Role.',
                        ],
                        'notes' => [
                            'A permission does nothing on its own &mdash; it only takes effect once it\'s on a Role, and that Role is on a User.',
                        ],
                    ],
                    [
                        'icon' => 'account-group-outline',
                        'title' => 'Users &amp; Trainers',
                        'summary' => 'Create accounts, assign roles/department, and flag trainers so they can be assigned to trainings.',
                        'where' => 'People &rarr; Users &amp; Trainers',
                        'steps' => [
                            'Click <strong>Add User</strong>.',
                            'Fill in Full Name, Employee ID (becomes their login Corporate ID, must be unique), Job Role(s), Password, Department, Sub Department, Designation, Experience, Qualification.',
                            'Tick <strong>&ldquo;Authorize as a Trainer&rdquo;</strong> if this person should be selectable as a Trainer.',
                            'Click <strong>Create User</strong>.',
                        ],
                        'notes' => [
                            'Saving a Trainee-role user instantly checks for any live Annual Training Plan matching their Department/Sub Department (or any Development Quality Assurance training) and enrolls them automatically.',
                            '<span class="text-danger"><strong>Caution:</strong> Delete removes a user immediately and permanently &mdash; there is no undo and no &ldquo;in use&rdquo; check.</span>',
                        ],
                    ],
                    [
                        'icon' => 'file-document-multiple-outline',
                        'title' => 'Documents &amp; Question Pools',
                        'summary' => 'Upload and review controlled documents (SOPs/Protocols/PPTs), then build the exam question bank behind each one.',
                        'where' => 'Documents',
                        'steps' => [
                            'Click <strong>Upload New Document</strong>, fill in Name, Doc Number (unique), Type, Department, Sub Department, Section, Read Time, and choose a file (PDF/DOC/DOCX/PPT/PPTX, up to ~10&nbsp;MB), then <strong>Save to Master Pool</strong>.',
                            'Click <strong>Review</strong> on a new document, confirm with <strong>Sign &amp; Approve</strong> to stamp who reviewed it and when.',
                            'Click <strong>Manage Pool</strong> on a document to add exam questions (Yes/No or 4-option MCQ), then <strong>Save Question Pool</strong>.',
                        ],
                        'notes' => [
                            '<span class="text-danger"><strong>Caution:</strong> Saving a Question Pool replaces the whole pool. Deleting a Document has no &ldquo;in use&rdquo; check either &mdash; confirm it isn\'t linked to a live training first (Training &rarr; Manage documents).</span>',
                        ],
                    ],
                    [
                        'icon' => 'calendar-multiple-check',
                        'title' => 'Training Program',
                        'summary' => 'Create trainings, set up Annual repeating plans, and move a training through Review &rarr; Approval &rarr; Activation.',
                        'where' => 'Training &rarr; Training Program',
                        'steps' => [
                            'Go to <strong>Training Setup &rarr; Add</strong>. Enter the Title, optional Departmental Steps, and choose the Type: Classroom/Instructor Led or Self Training (E-Learning).',
                            'To repeat automatically, tick <strong>Annual Training Program</strong> and pick a Frequency + Department/Sub Department.',
                            'Click <strong>Save Program</strong> &mdash; it\'s saved <em>inactive</em> until you click <strong>Activate</strong>, which makes it live and notifies every assigned trainer/trainee.',
                            'To progress review/approval, edit the training and change its <strong>Status</strong> field: <code>Created &rarr; In Review &rarr; Reviewed &rarr; Approved</code>.',
                            'Open a training &rarr; <strong>Manage documents</strong> to tick which documents apply and set a <strong>Quota</strong> (questions pulled per document for the exam).',
                        ],
                        'notes' => [
                            'Annual plans with a Frequency auto-create the child trainings for you. For Annual trainings with Department + Sub Department set, matching documents are pre-selected the moment you open the link page &mdash; review before saving.',
                        ],
                    ],
                    [
                        'icon' => 'account-multiple-plus-outline',
                        'title' => 'Assign Trainers &amp; Trainees',
                        'summary' => 'Decide who delivers a training and who is enrolled in it, plus which venues are booked.',
                        'where' => 'Open a Training &rarr; Manage Trainers / Manage Trainees',
                        'steps' => [
                            'Trainers: pick a Trainer and Start/End dates per row, add Venues below, then <strong>Update Assignments</strong>.',
                            'Trainees: tick who\'s enrolled with individual Start/End dates, then <strong>Save</strong>.',
                        ],
                        'notes' => [
                            'Trainer isn\'t required for Self Training. New assignees are only notified if the training is already Active; an already-accepted trainer\'s status is preserved on re-save.',
                        ],
                    ],
                    [
                        'icon' => 'clipboard-check-outline',
                        'title' => 'Attendance Sheet',
                        'summary' => 'Mark who showed up to a session — this also automatically logs the Training Register for you.',
                        'where' => 'Open a Training &rarr; Attendance Sheet',
                        'steps' => [
                            'Choose the Briefed Session Type, Start/End Time, and any Comments.',
                            'Toggle each trainee <strong>Present</strong>/<strong>Absent</strong>.',
                            'Click <strong>Submit Attendance</strong>.',
                        ],
                        'notes' => [
                            'The trainee list is paginated 20 at a time &mdash; your marks are kept as you page through. Submitting attendance automatically creates a Training Register entry for every trainee marked.',
                        ],
                    ],
                    [
                        'icon' => 'notebook-outline',
                        'title' => 'Training Register',
                        'summary' => 'The digital training-session log book, with trainer sign-off that promotes a trainee to Employee.',
                        'where' => 'Training &rarr; Training Register',
                        'steps' => [
                            'Click <strong>+ Add New Entry</strong>, fill in Date, Trainee, Trainer/Leader, Register No., Page No., Topic, then <strong>Update Register</strong>.',
                            'Click <strong>Sign &amp; Approve</strong> once you\'ve confirmed the session took place.',
                        ],
                        'notes' => [
                            '<strong>Important:</strong> approving a session removes the person\'s Trainee role and promotes them to <strong>Employee</strong>. Only sign off once you\'re sure.',
                        ],
                    ],
                    [
                        'icon' => 'chart-box-outline',
                        'title' => 'Assessment &amp; Admin Logs',
                        'summary' => 'Company-wide view of every exam attempt, with pass/fail stats and a question-by-question breakdown.',
                        'where' => 'Assessment &rarr; Training Schedule &rarr; Admin Logs',
                        'steps' => [
                            'Open Admin Logs to see every user\'s exam attempts with pass/fail stats.',
                            'Click any result to see the question-by-question breakdown (answer given vs. correct answer).',
                        ],
                        'notes' => [
                            'Pass mark is 60%. Score is based on the training\'s full configured question quota &mdash; a blank/unanswered question counts the same as a wrong one.',
                        ],
                    ],
                    [
                        'icon' => 'school-outline',
                        'title' => 'Induction Progress',
                        'summary' => 'Track and sign off a new hire\'s walkthrough of each induction step — completing all of them promotes them to Employee.',
                        'where' => 'Induction Progress',
                        'steps' => [
                            'Click into a trainee to see their step checklist.',
                            'To mark a step complete, fill in Interacted Person, Designation, and Comments, then submit.',
                        ],
                        'notes' => [
                            'This only auto-promotes for a program literally named &ldquo;Induction Training&rdquo; (case doesn\'t matter, wording does).',
                        ],
                    ],
                    [
                        'icon' => 'history',
                        'title' => 'Audit Logs',
                        'summary' => 'A complete, filterable history of who changed what and when, across the whole system.',
                        'where' => 'No menu link &mdash; open via a training\'s own Audit Log quick action, or the direct Audit Logs page',
                        'steps' => [
                            'Filter by Action (Created/Updated/Deleted) and click Filter.',
                            'Use the per-training version to see only that training\'s history.',
                        ],
                        'notes' => [],
                    ],
                ]),
            ],

            'trainer' => [
                'label' => 'Trainer',
                'icon'  => 'account-tie-outline',
                'intro' => 'As a Trainer, your job is to accept assigned trainings, deliver the session, mark attendance, and sign off Training Register entries.',
                'cards' => $withAccents([
                    [
                        'icon' => 'view-dashboard-outline',
                        'title' => 'Your Dashboard',
                        'summary' => 'See trainings you\'ve accepted, invitations awaiting your acceptance, and upcoming sessions.',
                        'where' => null,
                        'steps' => [],
                        'notes' => [
                            'Your Dashboard shows trainings you\'ve <strong>accepted</strong> (with trainee counts), invitations still <strong>awaiting your acceptance</strong>, and <strong>upcoming</strong> trainings by start date. Start here every day.',
                        ],
                    ],
                    [
                        'icon' => 'check-decagram-outline',
                        'title' => 'Accepting an Assignment',
                        'summary' => 'Confirm a pop-up invite to officially take on an assigned training.',
                        'where' => null,
                        'steps' => [
                            'When assigned to a live training, a pop-up appears asking you to <strong>Accept Training</strong>. Confirm it.',
                        ],
                        'notes' => [
                            'If you already accepted before, an Admin re-saving the trainer list won\'t reset your acceptance.',
                        ],
                    ],
                    [
                        'icon' => 'clipboard-check-outline',
                        'title' => 'Attendance Sheet',
                        'summary' => 'Mark who attended — this also automatically logs the Training Register for you.',
                        'where' => 'Open the training &rarr; Attendance Sheet',
                        'steps' => [
                            'Choose the Briefed Session Type and Start/End Time.',
                            'Toggle each trainee <strong>Present</strong>/<strong>Absent</strong>.',
                            'Click <strong>Submit Attendance</strong>.',
                        ],
                        'notes' => [
                            'Submitting attendance automatically creates a Training Register entry for each trainee &mdash; you don\'t need to log it again separately.',
                        ],
                    ],
                    [
                        'icon' => 'notebook-outline',
                        'title' => 'Training Register',
                        'summary' => 'Log a session yourself, and sign off entries once you confirm training actually happened.',
                        'where' => 'Training &rarr; Training Register',
                        'steps' => [
                            'Click <strong>+ Add New Entry</strong>, fill in the Date, Trainee, Register No., Page No. and Topic, then <strong>Update Register</strong>.',
                            'Click <strong>Sign &amp; Approve</strong> on an entry once you\'ve confirmed it took place.',
                        ],
                        'notes' => [
                            '<strong>Important:</strong> signing off promotes the trainee to <strong>Employee</strong>, so only do it once you\'re certain.',
                        ],
                    ],
                ]),
            ],

            'reviewer' => [
                'label' => 'Reviewer',
                'icon'  => 'file-search-outline',
                'intro' => 'As a Reviewer, your job is to check trainings that are &ldquo;In Review&rdquo; and move them forward.',
                'cards' => $withAccents([
                    [
                        'icon' => 'view-dashboard-outline',
                        'title' => 'Your Dashboard',
                        'summary' => 'Lists every training waiting on your review, plus what you\'ve already handled.',
                        'where' => null,
                        'steps' => [],
                        'notes' => [
                            'Your Dashboard lists every training currently sitting in <strong>&ldquo;In Review&rdquo;</strong> status, waiting for you, plus counts of what you\'ve already reviewed/approved.',
                        ],
                    ],
                    [
                        'icon' => 'file-check-outline',
                        'title' => 'Reviewing a Training Program',
                        'summary' => 'There\'s no separate Review button — you progress a training by changing its Status field.',
                        'where' => null,
                        'steps' => [
                            'Open the training and click <strong>Edit</strong>.',
                            'Change the <strong>Status</strong> field to <strong>Reviewed</strong> (this option only appears for your role).',
                            'Save.',
                        ],
                        'notes' => [
                            'This moves the training to &ldquo;Reviewed&rdquo; status, ready for an Approver to give the final sign-off.',
                        ],
                    ],
                ]),
            ],

            'approver' => [
                'label' => 'Approver',
                'icon'  => 'check-decagram-outline',
                'intro' => 'As an Approver, your job is to give the final sign-off on trainings that have already been reviewed.',
                'cards' => $withAccents([
                    [
                        'icon' => 'view-dashboard-outline',
                        'title' => 'Your Dashboard',
                        'summary' => 'Lists every training waiting on your final approval, plus what you\'ve already approved.',
                        'where' => null,
                        'steps' => [],
                        'notes' => [
                            'Your Dashboard lists every training currently in <strong>&ldquo;Reviewed&rdquo;</strong> status, waiting on your final approval.',
                        ],
                    ],
                    [
                        'icon' => 'stamper',
                        'title' => 'Approving a Training Program',
                        'summary' => 'The last step in the workflow before an Admin activates the training.',
                        'where' => null,
                        'steps' => [
                            'Open the training and click <strong>Edit</strong>.',
                            'Change the <strong>Status</strong> field to <strong>Approved</strong> (this option only appears for your role).',
                            'Save.',
                        ],
                        'notes' => [
                            'Workflow: <code>Created &rarr; In Review &rarr; Reviewed &rarr; Approved</code>. An Admin still needs to <strong>Activate</strong> the training separately to make it live for trainees.',
                        ],
                    ],
                ]),
            ],

            'trainee' => [
                'label' => 'Trainee / Employee',
                'icon'  => 'school-outline',
                'intro' => 'This is everything you need: logging in, checking your Dashboard, completing your reading and exam, and tracking your induction.',
                'cards' => $withAccents([
                    [
                        'icon' => 'login-variant',
                        'title' => 'Logging In / Registering',
                        'summary' => 'Use your Corporate ID (not email) to log in, or self-register to get one automatically.',
                        'where' => null,
                        'steps' => [
                            '<u>Logging in:</u> enter your <strong>Corporate ID</strong> and Password, then click Login.',
                            '<u>Registering yourself:</u> fill in Name, Email and Password on the Register page. The system generates a Corporate ID like <code>TRN00001</code> &mdash; write it down.',
                        ],
                        'notes' => [
                            'Self-registration doesn\'t ask for your department, so ask an Admin to set your correct Department/Sub Department right after you sign up &mdash; your training assignments depend on it.',
                        ],
                    ],
                    [
                        'icon' => 'view-dashboard-outline',
                        'title' => 'Your Dashboard',
                        'summary' => 'Shows every active training you\'re enrolled in and exactly what to do next for each one.',
                        'where' => null,
                        'steps' => [],
                        'notes' => [
                            'Tells you: <em>nothing to do</em>, <em>read the documents</em>, <em>take the exam</em>, or <em>retake the exam</em> &mdash; plus your recent results.',
                        ],
                    ],
                    [
                        'icon' => 'book-clock-outline',
                        'title' => 'Reading Room &amp; Taking an Exam',
                        'badge' => 'Most important',
                        'summary' => 'Complete the timed reading first, then answer every question within 15 minutes to pass.',
                        'where' => null,
                        'steps' => [
                            'Open <strong>Exam Workspace</strong> and click a training. In the Reading Room, a countdown timer starts &mdash; read each attached document.',
                            'The <strong>&ldquo;Complete Reading and Unlock Assessment&rdquo;</strong> button stays disabled until the timer finishes.',
                            'Once enabled, click it, then click <strong>Take Exam</strong>. You have <strong>15 minutes</strong> &mdash; it auto-submits when time runs out.',
                            'Click <strong>Submit Assessment</strong> when done.',
                        ],
                        'notes' => [
                            '<span class="text-danger"><strong>Important:</strong> pass mark is 60%, and leaving a question blank counts the same as answering it wrong &mdash; always answer everything before submitting.</span>',
                        ],
                    ],
                    [
                        'icon' => 'chart-line',
                        'title' => 'Results &amp; History',
                        'summary' => 'Review every exam you\'ve taken, with a question-by-question breakdown of each attempt.',
                        'where' => null,
                        'steps' => [],
                        'notes' => [
                            'Go to <strong>Results History</strong> to see pass/fail for every exam you\'ve taken. Click any result for a full breakdown.',
                        ],
                    ],
                    [
                        'icon' => 'school-outline',
                        'title' => 'Induction Progress (Your Own)',
                        'summary' => 'Track your own step-by-step induction checklist and completion status.',
                        'where' => null,
                        'steps' => [],
                        'notes' => [
                            'Once every step is signed off by your Trainer/Admin, you\'re automatically promoted from Trainee to <strong>Employee</strong>.',
                        ],
                    ],
                ]),
            ],

            'faq' => [
                'label' => 'FAQ',
                'icon'  => 'help-circle-outline',
                'intro' => 'Answers to the questions people actually run into while using the system.',
                'cards' => $withAccents([
                    [
                        'icon' => 'bell-outline',
                        'title' => 'I activated a training but trainees say they weren\'t notified.',
                        'summary' => 'Notifications are recorded internally, but there\'s no in-app inbox to view them yet.',
                        'where' => null, 'steps' => [],
                        'notes' => ['This is a known gap &mdash; communicate new assignments to trainees directly in the meantime.'],
                    ],
                    [
                        'icon' => 'delete-alert-outline',
                        'title' => 'I can\'t delete a Department, Venue, Section, or Sub Department.',
                        'summary' => 'These are protected while still referenced by a Document or Training.',
                        'where' => null, 'steps' => [],
                        'notes' => ['Reassign or remove the dependent records first. Designations are the one exception and can always be deleted.'],
                    ],
                    [
                        'icon' => 'file-question-outline',
                        'title' => 'I edited a Question Pool and old questions disappeared.',
                        'summary' => 'Saving a Question Pool always replaces the full list &mdash; it doesn\'t merge.',
                        'where' => null, 'steps' => [],
                        'notes' => ['Always review every row carefully before saving.'],
                    ],
                    [
                        'icon' => 'percent-outline',
                        'title' => 'A trainee\'s exam score seems lower than expected.',
                        'summary' => 'Scoring divides by the training\'s full configured question quota, not just questions shown.',
                        'where' => null, 'steps' => [],
                        'notes' => ['Check the Question Pool size against the configured Quota for that document.'],
                    ],
                    [
                        'icon' => 'file-remove-outline',
                        'title' => 'I deleted a Document and a training\'s exam broke.',
                        'summary' => 'Document deletion doesn\'t check whether it\'s linked to a live training.',
                        'where' => null, 'steps' => [],
                        'notes' => ['Before deleting, check Training &rarr; Manage documents on any related training to confirm it isn\'t still linked.'],
                    ],
                    [
                        'icon' => 'account-arrow-up-outline',
                        'title' => 'A user finished induction but wasn\'t promoted to Employee.',
                        'summary' => 'Promotion only fires for a program named exactly &ldquo;Induction Training&rdquo;.',
                        'where' => null, 'steps' => [],
                        'notes' => ['Capitalization doesn\'t matter, but the wording must match exactly.'],
                    ],
                    [
                        'icon' => 'office-building-marker-outline',
                        'title' => 'Self-registered users end up in the wrong department.',
                        'summary' => 'Self-registration defaults new users to Department ID 1 with no picker.',
                        'where' => null, 'steps' => [],
                        'notes' => ['An Admin should correct the new user\'s Department/Sub Department right after they register.'],
                    ],
                    [
                        'icon' => 'link-lock',
                        'title' => 'Can a user reach a page just by typing the URL?',
                        'summary' => 'Menu items are hidden by role, but not every page is blocked at the server.',
                        'where' => null, 'steps' => [],
                        'notes' => ['Don\'t rely on the menu alone for anything sensitive &mdash; this is a known area for future hardening.'],
                    ],
                ]),
            ],
        ];
    }
}
