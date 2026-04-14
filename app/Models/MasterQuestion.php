<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterQuestion extends Model
{
    protected $fillable = ['question_text', 
    'question_type', 
    'correct_answer', 
    'options', 
    'master_document_id'];


    protected $casts = [
        'options' => 'array', 
    ];

}
