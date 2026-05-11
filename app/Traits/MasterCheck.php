<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait MasterCheck
{
    public function isMasterUsed($value, $mapping)
    {
        foreach ($mapping as $table => $column) {
            if (DB::table($table)->where($column, $value)->exists()) {
                return true;
            }
        }

        return false;
    }
}