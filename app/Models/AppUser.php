<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerWallet;
use App\Models\DailyCheckin;
use App\Models\Referral;

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

    public function customerWallet()
    {
        return $this->hasOne(CustomerWallet::class, 'user_id', 'id');
    }

    public function latestDailyCheckin()
    {
        return $this->hasOne(DailyCheckin::class, 'user_id', 'id')
            ->latest('checkin_date');
    }

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


