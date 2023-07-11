<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bid extends Model
{
    use HasFactory;
    protected $fillable=[
        'purposal_detail',
        'price',
        'timeline',
        'boost_level',
        'user_id',
        'job_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
     public function job(){
        return $this->belongsTo(Job::class);
     }

}
