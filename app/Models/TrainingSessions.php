<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TrainingSessions extends Model
{
    protected $fillable = [
        'training_date',
        'trainee_id',
        'trainer_id',
        'register_no',
        'page_no',
        'topic',
        'session_brief_type',
        'session_comments',
        'start_time',
        'end_time',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    public function trainee(): BelongsTo
    {
        // We specify 'trainee_id' because the method name 'trainee' 
        // doesn't match the default 'user_id' convention.
        return $this->belongsTo(User::class, 'trainee_id');
    }

    /**
     * Get the user conducting the training (The Trainer/Leader)
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

}
