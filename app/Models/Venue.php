<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $primaryKey = 'venue_id';
    protected $guarded = [];


    public function modules()
    {
        return $this->belongsToMany(
            TrainingModule::class,
            'module_venue',
            'venue_id',
            'training_module_id'
        );
    }
}
