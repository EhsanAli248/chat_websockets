<?php

namespace App\Models;

use App\Models\Bid;
use App\Models\Job;
use App\Models\Client;
use App\Models\Vendor;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PasswordResetToken;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;


    protected $fillable = [
        // 'user_role',
        // 'user_type',
        'first_name',
        'last_name',
        'mobile_number',
        'country',
        'profile_image',
        'email',
        'password',
        'gauth_id',
        'gauth_type',
        'email_verified_at',
    ];

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function passwordResetToken()
    {
        return $this->hasOne(PasswordResetToken::class, 'email', 'email');
    }



    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
