<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'amount', 'stock', 'image'];

    public function carts()
    {
        return $this->belongsToMany(Cart::class)->withTimestamps();
    }
}
