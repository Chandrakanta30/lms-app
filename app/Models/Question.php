<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['training_module_id', 'question_text', 'correct_answer'];

    public function trainingModule() {
        return $this->belongsTo(TrainingModule::class);
    }
}
