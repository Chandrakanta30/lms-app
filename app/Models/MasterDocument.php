<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDocument extends Model
{

    protected $fillable = ['doc_name', 'doc_number', 'version', 'doc_type', 'file_path'];

    public function questions() {
        return $this->hasMany(MasterQuestion::class);
    }
    
    public function modules() {
        return $this->belongsToMany(TrainingModule::class, 'module_document_pivot')
                    ->withPivot('question_quota');
    }
}
