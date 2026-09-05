<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity; // v4 uses this, but let's check the implementation
use Illuminate\Database\Eloquent\SoftDeletes;  //soft delete 

class TrainingModule extends Model
{
    use LogsActivity;
    use SoftDeletes;

    public const STATUSES = ['created', 'inreview', 'reviewed', 'approved'];

    protected $guarded = [];
    protected $casts = [
        'subdepartment_id' => 'array',
    ];
    // Get the Parent Training
    public function parent()
    {
        return $this->belongsTo(TrainingModule::class, 'parent_id');
    }

    // Get all Childrens steps
    public function steps()
    {
        return $this->hasMany(TrainingModule::class, 'parent_id')->orderBy('step_number');
    }

    // public function documents()
    // {
    //     return $this->hasMany(TrainingDocument::class, 'training_id');
    // }

    public function documents()
    {
        return $this->belongsToMany(MasterDocument::class, 'module_document_pivot')
            ->withPivot('question_quota') // Crucial: allows access to the quota
            ->withTimestamps();
    }

    public function examDocuments()
    {
        return $this->documents()
            ->whereNotNull('master_documents.reviewed_at')
            ->where(function ($query) {
                $query->where('module_document_pivot.question_quota', '>', 0)
                    ->orWhereDoesntHave('questions');
            });
    }

    public function requiredReadingSeconds(): int
    {
        $this->loadMissing('documents');

        return max(60, $this->documents->sum(function ($document) {
            return (int) ($document->read_time_seconds ?? 60);
        }));
    }

    public function examRequiredReadingSeconds(): int
    {
        $this->loadMissing('examDocuments');

        return max(60, $this->examDocuments->sum(function ($document) {
            return (int) ($document->read_time_seconds ?? 60);
        }));
    }

    public function questions()
    {
        // A Training Module HAS MANY Questions
        return $this->hasMany(Question::class, 'training_module_id');
    }
    public function latestResult()
    {
        return $this->hasOne(ExamResult::class)->latestOfMany();
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class, 'training_module_id');
    }

    public function trainers()
    {
        return $this->belongsToMany(User::class, 'trainer_training', 'training_module_id', 'user_id')
            ->withPivot('start_date', 'end_date', 'acceptance_status')
            ->withTimestamps();
    }
    public function acceptedTrainers()
    {
        return $this->belongsToMany(User::class, 'trainer_training', 'training_module_id', 'user_id')
            ->withPivot('start_date', 'end_date', 'acceptance_status')
            ->wherePivot('acceptance_status', 'accepted')
            ->withTimestamps();
    }

    public function trainerAcceptanceSummary(): array
    {
        $this->loadMissing('trainers');

        $total = $this->trainers->count();
        $accepted = $this->trainers->filter(function ($trainer) {
            return ($trainer->pivot->acceptance_status ?? 'pending') === 'accepted';
        })->count();
        $rejected = $this->trainers->filter(function ($trainer) {
            return ($trainer->pivot->acceptance_status ?? 'pending') === 'rejected';
        })->count();
        $pending = max(0, $total - $accepted - $rejected);

        if ($total === 0) {
            return [
                'label' => 'No Trainers',
                'class' => 'badge-secondary',
                'total' => 0,
                'accepted' => 0,
                'pending' => 0,
                'rejected' => 0,
                'display' => 'No Trainers Assigned',
            ];
        }

        if ($accepted === $total) {
            $label = 'Accepted';
            $class = 'badge-success';
        } elseif ($accepted === 0 && $rejected === $total) {
            $label = 'Rejected';
            $class = 'badge-danger';
        } elseif ($accepted === 0) {
            $label = 'Pending';
            $class = 'badge-warning';
        } else {
            $label = 'Partially Pending';
            $class = 'badge-info';
        }

        return [
            'label' => $label,
            'class' => $class,
            'total' => $total,
            'accepted' => $accepted,
            'pending' => $pending,
            'rejected' => $rejected,
            'display' => $total > 0 ? "{$label} ({$accepted}/{$total})" : $label,
        ];
    }

    public function expiryDateTime(): ?Carbon
    {
        if (!$this->end_date) {
            return null;
        }

        $endDate = Carbon::parse($this->end_date);

        if ($this->end_time) {
            return Carbon::parse($this->end_date . ' ' . $this->end_time);
        }

        return $endDate->endOfDay();
    }

    public function isExpired(): bool
    {
        $expiryDateTime = $this->expiryDateTime();

        return $expiryDateTime ? now()->greaterThan($expiryDateTime) : false;
    }

    public function venues()
    {
        return $this->belongsToMany(
            Venue::class,
            'module_venue',
            'training_module_id',
            'venue_id'
        );
    }
    // Relationship for Trainees (Enrollment)
    public function trainees()
    {
        return $this->belongsToMany(User::class, 'training_user', 'training_module_id', 'user_id')
            ->withPivot('status', 'start_date', 'end_date', 'attendance_status', 'attendance_marked_at', 'attendance_marked_by', 'reassigned_at', 'reassignment_mode', 'reassignment_note', 'reassigned_from_training_id')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activator()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'training_type', 'is_active']) // Specify which fields to track
            ->logOnlyDirty() // Only log if something actually changed
            ->dontSubmitEmptyLogs() // Don't save a log if no tracked fields changed
            ->useLogName('training_management'); // Categorize these logs
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'training_user', 'training_module_id', 'user_id')
            ->withPivot('status', 'start_date', 'end_date', 'attendance_status', 'attendance_marked_at', 'attendance_marked_by', 'reassigned_at', 'reassignment_mode', 'reassignment_note', 'reassigned_from_training_id');
    }

    public function resolveTrainingStatusForUser(User|int $user): string
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        $assignment = $this->currentAssignmentForUser($userId);

        if (!$assignment) {
            return 'pending';
        }

        $latestResult = $this->latestResultForUser($userId);

        if ($assignment->reassigned_at) {
            $reassignedAt = Carbon::parse($assignment->reassigned_at);

            if ($latestResult && $latestResult->created_at && Carbon::parse($latestResult->created_at)->gt($reassignedAt)) {
                return $latestResult->is_passed ? 'passed' : 'failed';
            }

            return 'pending';
        }

        if (in_array($assignment->status ?? null, ['passed', 'failed'], true)) {
            return $assignment->status;
        }

        if ($latestResult && $latestResult->is_passed) {
            return 'passed';
        }

        if ($latestResult && !$latestResult->is_passed) {
            return 'failed';
        }

        $deadline = $this->trainingDeadlineForAssignment($assignment);

        return $deadline && now()->greaterThan($deadline)
            ? 'failed'
            : 'pending';
    }

    public function syncTrainingStatusForUser(User|int $user): string
    {
        $userId = $user instanceof User ? $user->id : (int) $user;
        $status = $this->resolveTrainingStatusForUser($userId);

        DB::table('training_user')
            ->where('training_module_id', $this->id)
            ->where('user_id', $userId)
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);

        return $status;
    }

    public function currentAssignmentForUser(User|int $user): ?object
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        return DB::table('training_user')
            ->where('training_module_id', $this->id)
            ->where('user_id', $userId)
            ->first();
    }

    public function latestResultForUser(User|int $user): ?ExamResult
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        return $this->examResults()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->first();
    }

    public function hasUnlockedReassignmentForUser(User|int $user): bool
    {
        $assignment = $this->currentAssignmentForUser($user);
        $latestResult = $this->latestResultForUser($user);

        if (!$assignment || ($assignment->reassignment_mode ?? null) !== 'same' || !$assignment->reassigned_at) {
            return false;
        }

        if (!$latestResult || !$latestResult->created_at) {
            return true;
        }

        return Carbon::parse($latestResult->created_at)->lte(Carbon::parse($assignment->reassigned_at));
    }

    private function trainingDeadlineForAssignment(object $assignment): ?Carbon
    {
        $deadline = $assignment->end_date ?? $this->end_date ?? null;

        if (!$deadline) {
            return null;
        }

        return Carbon::parse($deadline)->endOfDay();
    }

    public function hasAssignmentDeadlinePassed(object $assignment): bool
    {
        $deadline = $this->trainingDeadlineForAssignment($assignment);

        return $deadline ? now()->greaterThan($deadline) : false;
    }
}
