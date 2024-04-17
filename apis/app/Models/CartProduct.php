<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CartProduct extends Pivot
{
    protected $table = 'cart_product';

    protected $fillable = ['quantity'];

    // Puedes agregar más lógica aquí si es necesario
}
