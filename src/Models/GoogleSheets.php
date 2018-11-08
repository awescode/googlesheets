<?php

namespace Awescode\GoogleSheets\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSheets extends Model
{
    //protected $fillable = [];

    public function getTable()
    {
        return config('googlesheets.table_name');
    }
}
