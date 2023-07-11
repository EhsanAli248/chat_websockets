<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Twilio\TwiML\Voice\Connect;

class Vendor extends Model
{
    use HasFactory;
    protected $fillable=[
        'is_verified',
        'rating',
        'description',
        'skills',
        'user_id',

    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function connect(){
        return $this->hasOne(Connect::class);
    }

}
