<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingUser extends Model
{
    protected $table = 'training_user';

    protected $guarded = [];

    protected $casts = [
        'reassigned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(TrainingModule::class, 'training_module_id');
    }

    public function reassignedFromTraining()
    {
        return $this->belongsTo(TrainingModule::class, 'reassigned_from_training_id');
    }
}
