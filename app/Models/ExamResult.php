<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = ['user_id',
    'training_module_id',
    'total_questions_attempted', // Make sure this is exactly as named in migration
    'correct_answers',
    'percentage',
    'is_passed',
    'details'];

    protected $casts = [
        'is_passed' => 'boolean',
        'details' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function module() { return $this->belongsTo(TrainingModule::class, 'training_module_id'); }
}
