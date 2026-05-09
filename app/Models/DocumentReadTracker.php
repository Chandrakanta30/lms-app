<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentReadTracker extends Model
{
    protected $fillable = [
        'user_id',
        'training_module_id',
        'required_seconds',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(TrainingModule::class, 'training_module_id');
    }
}
