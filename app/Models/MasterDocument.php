<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDocument extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'doc_name',
        'doc_number',
        'version',
        'file_path',
        'doc_type',
        'uploaded_by', //added this line to track who uploaded the document
        'reviewed_by',
        'reviewed_at',
        'department_id',
        'section_id'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime', // ✅ important
    ];



    // Who uploaded
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Who reviewed
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function questions()
    {
        return $this->hasMany(MasterQuestion::class);
    }

    public function modules()
    {
        return $this->belongsToMany(TrainingModule::class, 'module_document_pivot')
            ->withPivot('question_quota');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'sec_id');
    }
}
