<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Katalog kebenaran global (bukan data tenant). */
class Permission extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'module', 'description'];
}
