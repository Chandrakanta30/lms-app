<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingUser extends Model
{
    protected $table = 'training_user';

    protected $guarded = [];

    protected $casts = [
        'reassigned_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class, 'training_module_id');
    }

    public function reassignedFromTraining(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class, 'reassigned_from_training_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
