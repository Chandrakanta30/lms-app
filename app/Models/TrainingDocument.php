<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingDocument extends Model
{
    protected $fillable = ['training_id', 'doc_type', 'doc_name','doc_number','doc_version','file_path'];

}
