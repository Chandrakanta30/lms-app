<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionMaster extends Model
{
    protected $fillable = [
        'department_id',
        'name'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}