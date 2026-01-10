<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUser extends Model
{
    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = [
        'isActive' => 'boolean',
        'active' => 'integer',
        'isDocumentVerify' => 'string',
        'wallet_amount' => 'integer',
        'rotation' => 'float',
        'orderCompleted' => 'integer',
    ];
}


