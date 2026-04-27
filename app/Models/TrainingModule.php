<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity; // v4 uses this, but let's check the implementation
use Illuminate\Database\Eloquent\SoftDeletes;  //soft delete 

class TrainingModule extends Model
{
    use LogsActivity;
     use SoftDeletes;

    public const STATUSES = ['created', 'inreview', 'reviewed', 'approved'];

    protected $fillable = ['name', 'parent_id', 'step_number','training_type','start_date','end_date','status','created_by','updated_by','activated_at','activated_by'];

    // Get the Main Training (Parent)
    public function parent()
    {
        return $this->belongsTo(TrainingModule::class, 'parent_id');
    }

    // Get all Steps (Children)
    public function steps()
    {
        return $this->hasMany(TrainingModule::class, 'parent_id')->orderBy('step_number');
    }

    // public function documents()
    // {
    //     return $this->hasMany(TrainingDocument::class, 'training_id');
    // }

    public function documents()
    {
        return $this->belongsToMany(MasterDocument::class, 'module_document_pivot')
                    ->withPivot('question_quota') // Crucial: allows access to the quota
                    ->withTimestamps();
    }
    
    public function questions()
    {
        // A Training Module HAS MANY Questions
        return $this->hasMany(Question::class, 'training_module_id');
    }
    public function latestResult()
    {
        return $this->hasOne(ExamResult::class)->latestOfMany();
    }

    public function trainers()
    {
        return $this->belongsToMany(User::class, 'trainer_training', 'training_module_id', 'user_id')
                    ->withPivot('start_date', 'end_date')
                    ->withTimestamps();
    }

    // Relationship for Trainees (Enrollment)
    public function trainees()
    {
        return $this->belongsToMany(User::class, 'training_user', 'training_module_id', 'user_id')
                    ->withPivot('status', 'start_date', 'end_date', 'attendance_status', 'attendance_marked_at', 'attendance_marked_by')
                    ->withTimestamps();
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function editor() {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function activator() {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'training_type', 'is_active']) // Specify which fields to track
            ->logOnlyDirty() // Only log if something actually changed
            ->dontSubmitEmptyLogs() // Don't save a log if no tracked fields changed
            ->useLogName('training_management'); // Categorize these logs
    }
    public function users()
{
    return $this->belongsToMany(User::class, 'training_user', 'training_module_id', 'user_id')
                ->withPivot('status', 'start_date', 'end_date', 'attendance_status', 'attendance_marked_at', 'attendance_marked_by');
}
}
