<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function index()
    {
        try {
            $productos = Product::all();
            return response()->json($productos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving products'], 500);
        }
    }

    public function addToCart(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Verificar si el usuario tiene un carrito existente, sino, crear uno nuevo
            $cart = $user->cart()->firstOrCreate([]);

            // Obtener los IDs de los productos y sus cantidades del cuerpo de la solicitud
            $productIds = $request->input('product_ids', []);
            $quantities = $request->input('quantities', []);

            // Verificar si se proporcionaron IDs de productos
            if (empty($productIds)) {
                return response()->json(['error' => 'No product IDs provided'], 400);
            }

            // Verificar si la cantidad de productos coincide con la cantidad de cantidades proporcionadas
            if (count($productIds) !== count($quantities)) {
                return response()->json(['error' => 'Product IDs and quantities mismatch'], 400);
            }

            // Recorrer los productos y agregarlos al carrito
            foreach ($productIds as $key => $productId) {
                $product = Product::findOrFail($productId);
                $quantity = $quantities[$key];
                
                // Lógica para agregar el producto al carrito
                if ($product->stock < $quantity) {
                    return response()->json(['error' => 'Insufficient stock for product: ' . $product->name], 400);
                }

                // Si el producto ya está en el carrito, sumar la cantidad, de lo contrario, agregarlo
                if ($cart->products()->where('product_id', $productId)->exists()) {
                    $cart->products()->updateExistingPivot($productId, ['quantity' => \DB::raw('quantity + ' . $quantity)]);
                } else {
                    $cart->products()->attach($productId, ['quantity' => $quantity]);
                }

                // Actualizar el stock del producto
                $product->stock -= $quantity;
                $product->save();
            }

            // Retornar respuesta exitosa
            return response()->json(['message' => 'Products added to cart successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error adding products to cart'], 500);
        }
    }

    public function checkout(Request $request)
    {
        try {
            // 1. Obtener el usuario autenticado
            $user = auth()->user();

            // 2. Obtener el token de tarjeta de crédito enviado desde el frontend
            $token = $request->input('token');

            // 3. Obtener el carrito del usuario desde el controlador de carrito
            $cartController = new CartController();
            $cartResponse = $cartController->index();

            // Verificar si se pudo obtener el carrito correctamente
            if ($cartResponse->getStatusCode() !== 200) {
                return response()->json(['error' => 'Error retrieving cart'], $cartResponse->getStatusCode());
            }

            $cartData = json_decode($cartResponse->getContent(), true);

            // Obtener los productos y el monto total del carrito
            $products = $cartData['products'];
            $totalAmount = $cartData['total_amount'];

            // 4. Lógica para generar un pedido (crear un nuevo registro en la base de datos para representar el pedido)
            $order = $user->orders()->create([
                'total_amount' => $totalAmount,
                // Puedes agregar más campos según sea necesario para tu aplicación
            ]);

            // 5. Asociar los productos del carrito con el pedido
            foreach ($products as $product) {
                $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);
            }

            // 6. Realizar el pago con Stripe utilizando el token de tarjeta
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            $charge = \Stripe\Charge::create([
                'amount' => $totalAmount * 100,
                'currency' => 'usd',
                'source' => $token, // Utilizar el token de tarjeta como fuente de pago
            ]);

            // 7. Confirmar la compra
            $order->update(['status' => 'completed']);

            // 8. Limpiar el carrito de compras después de realizar la compra
            $user->cart->products()->detach();

            // 9. Retornar una respuesta exitosa con el total del pedido
            return response()->json(['message' => 'Checkout completed successfully', 'total_amount' => $totalAmount], 200);
        } catch (\Stripe\Exception\CardException $e) {
            // Manejar errores de tarjeta rechazada
            return response()->json(['error' => 'Card was declined'], 400);
        } catch (\Exception $e) {
            // Manejar cualquier otro error
            return response()->json(['error' => 'Error processing checkout: ' . $e->getMessage()], 500);
        }
    }
}
