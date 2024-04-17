<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class CartController extends Controller
{
    public function index()
    {
        try {
            // Obtener el usuario autenticado
            $user = auth()->user();
    
            // Obtener el carrito del usuario con los productos y sus cantidades
            $cart = $user->cart()->with(['products' => function ($query) {
                $query->withPivot('quantity'); // Cargar la cantidad desde la relación pivot
            }])->first();
    
            // Verificar si el carrito existe
            if (!$cart) {
                return response()->json(['error' => 'Cart not found'], 404);
            }
    
            // Obtener los productos en el carrito con detalles adicionales, incluyendo la URL completa de la imagen
            $products = $cart->products->map(function ($product) {
                // Construir la URL completa de la imagen
                $imageUrl = url($product->image); // Suponiendo que el campo 'image' almacena la ruta relativa de la imagen
    
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'amount' => $product->amount,
                    'quantity' => $product->pivot->quantity, // Acceder a la cantidad desde la relación pivot
                    'image_url' => $imageUrl // Incluir la URL completa de la imagen en la respuesta
                ];
            });
    
            // Calcular el total de los montos de los productos en el carrito
            $totalAmount = $products->sum(function ($product) {
                return $product['amount'] * $product['quantity'];
            });
    
            return response()->json([
                'products' => $products,
                'total_amount' => $totalAmount
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving cart'], 500);
        }
    }

    public function removeFromCart($productId)
    {
        try {
            // Obtener el usuario autenticado
            $user = auth()->user();

            // Obtener el carrito del usuario
            $cart = $user->cart;

            // Verificar si el carrito existe
            if (!$cart) {
                return response()->json(['error' => 'Cart not found'], 404);
            }

            // Eliminar el producto del carrito
            $cart->products()->detach($productId);

            return response()->json(['message' => 'Product removed from cart successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error removing product from cart'], 500);
        }
    }
}
