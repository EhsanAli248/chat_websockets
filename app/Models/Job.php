<?php

namespace App\Models;

use App\Models\Bid;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'price_type',
        'min_price',
        'max_price',
        'duration',
        'skills',
        'user_id'
    ];
    protected $casts=[
        'skills'=>'json',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
}
