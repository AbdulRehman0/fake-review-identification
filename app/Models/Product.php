<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "desc",
        "status",
        "price"
    ];

    public function reviews(){
        return $this->hasMany(Review::class);
    }
}
