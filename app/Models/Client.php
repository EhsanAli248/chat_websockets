<?php

namespace App\Models;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    protected $fillable=[
        'is_verified',
        'rating',
        'description',
        'skills',
        'company_name',
        'company_website',
        'user_id'

    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function jobs(){
        return $this->hasMany(Job::class);
    }

}
