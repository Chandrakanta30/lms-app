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
        'section_id',
        'subdepartment_id',
        'read_time',
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

    public function getReadTimeMinutesAttribute(): int
    {
        return self::normalizeReadTimeToMinutes($this->read_time);
    }

    public function getReadTimeSecondsAttribute(): int
    {
        return $this->read_time_minutes * 60;
    }

    public function getReadTimeLabelAttribute(): string
    {
        return $this->read_time_minutes . ' min';
    }

    public static function normalizeReadTimeToMinutes($value): int
    {
        if (is_numeric($value)) {
            return max(1, (int) $value);
        }

        if (!is_string($value) || trim($value) === '') {
            return 1;
        }

        if (preg_match('/(\d+(?:\.\d+)?)/', $value, $matches)) {
            return max(1, (int) ceil((float) $matches[1]));
        }

        return 1;
    }
}
