<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportTemplate extends Model
{
    // ExportTemplate
    protected $fillable = ['user_id', 'name', 'is_shared', 'config'];
    protected $casts = ['config' => 'array', 'is_shared' => 'boolean'];
}
