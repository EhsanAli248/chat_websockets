<?php

namespace App\Models;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Connect extends Model
{
    use HasFactory;
    protected $fillable=[
        'available_count',
        'used_count',
        'vendor_id'

    ];
    public function vendor(){
        return $this->belongsTo(Vendor::class);
    }
}
