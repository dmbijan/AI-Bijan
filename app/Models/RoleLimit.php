<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Had transaksi / kelulusan setiap peranan (dimiliki tenant melalui peranan). */
class RoleLimit extends Model
{
    public $timestamps = false;

    protected $fillable = ['role_id', 'limit_key', 'value'];

    protected $casts = ['value' => 'float'];
}
