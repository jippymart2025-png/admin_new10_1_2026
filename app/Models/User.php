<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
    public function referralUsed()
    {
        // This user was referred by someone
        return $this->hasOne(Referral::class, 'referee_user_id', 'id');
    }

    public function referredBy()
    {
        return $this->hasOneThrough(
            AppUser::class,     // final model
            Referral::class,    // intermediate
            'referee_user_id',  // FK on referrals table
            'id',               // FK on users table
            'id',               // local key on users
            'referrer_user_id'  // local key on referrals
        );
    }
}
