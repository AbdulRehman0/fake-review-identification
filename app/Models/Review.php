<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class Review extends Model
{
    use HasFactory,Dispatchable;
    protected $fillable = [
        "user_id",
        "product_id",
        "review",
        "is_fake",
        "rating",
        "reason",
        "scores"
    ];

    /**
     * Interact with the Review's scores.
     */
    protected function scores(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? json_decode($value):$value,
        );
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
