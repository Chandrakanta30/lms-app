<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    protected $fillable = [
        'name', 'email', 'password', 'department_id', 'designation_id', 'qualification', 'experience_years','is_trainer','user_id'
    ];
    use HasFactory, Notifiable;
    use HasRoles;
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function trainings() {
        // return $this->belongsToMany(TrainingModule::class, 'user_trainings')
        //     ->withPivot('interacted_person', 'designation', 'comments', 'is_completed', 'completed_at')
        //     ->withTimestamps();

        return $this->belongsToMany(TrainingModule::class, 'training_user', 'user_id', 'training_module_id')
                ->withPivot('status');
    }


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
    public function modules(){
        return $this->belongsToMany(
            TrainingModule::class,
            'training_user',
            'user_id',
            'training_module_id'
        );
    }
    

}
