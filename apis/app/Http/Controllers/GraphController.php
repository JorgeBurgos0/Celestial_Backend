<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class GraphController extends Controller
{

    public function gender(Request $request)
    {

        // Filtrar los usuarios por género
        $h = User::where('gender', 'Hombre')->get();
        $m = User::where('gender', 'Mujer')->get();

        // Devolver la lista de usuarios filtrados
        return response()->json([$h->count(),$m->count()], 200);
    }

    public function mostSoldProducts()
    {
        try {
            // Consulta para obtener los tres productos más vendidos
            $mostSoldProducts = DB::table('order_product')
                ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit(3) // Limitar a los tres productos más vendidos
                ->get();

            $productsData = [];

            // Obtener información adicional de cada producto más vendido
            foreach ($mostSoldProducts as $mostSoldProduct) {
                $product = Product::find($mostSoldProduct->product_id);

                // Agregar información del producto a la lista
                $productsData[] = [
                    'product_id' => $mostSoldProduct->product_id,
                    'total_quantity' => $mostSoldProduct->total_quantity,
                    'product_name' => $product->name,
                    'product_description' => $product->description,
                    // Otros detalles del producto si es necesario
                ];
            }

            return response()->json($productsData, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving most sold products'], 500);
        }
    }

    public function productsByGender()
    {
        try {
            // Consulta para obtener el producto más vendido por género para hombres
            $mostSoldProductForMen = DB::table('order_product')
                ->join('orders', 'order_product.order_id', '=', 'orders.id')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->join('products', 'order_product.product_id', '=', 'products.id')
                ->select('products.id', 'products.name as product_name', 'products.description as product_description', 'users.gender', DB::raw('SUM(order_product.quantity) as total_quantity'))
                ->where('users.gender', 'Hombre')
                ->groupBy('products.id', 'products.name', 'products.description', 'users.gender')
                ->orderByDesc('total_quantity')
                ->first();

            // Consulta para obtener el producto más vendido por género para mujeres
            $mostSoldProductForWomen = DB::table('order_product')
                ->join('orders', 'order_product.order_id', '=', 'orders.id')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->join('products', 'order_product.product_id', '=', 'products.id')
                ->select('products.id', 'products.name as product_name', 'products.description as product_description', 'users.gender', DB::raw('SUM(order_product.quantity) as total_quantity'))
                ->where('users.gender', 'Mujer')
                ->groupBy('products.id', 'products.name', 'products.description', 'users.gender')
                ->orderByDesc('total_quantity')
                ->first();

            return response()->json([
                'most_sold_product_for_men' => $mostSoldProductForMen,
                'most_sold_product_for_women' => $mostSoldProductForWomen
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving most sold products by gender'], 500);
        }
    }

    public function ageGroups()
    {
        try {
            // Consulta SQL para agrupar las edades de los usuarios por rangos
            $ageGroups = DB::table('users')
                ->select(
                    DB::raw('CASE
                                WHEN age BETWEEN 0 AND 9 THEN "0-9"
                                WHEN age BETWEEN 10 AND 19 THEN "10-19"
                                WHEN age BETWEEN 20 AND 29 THEN "20-29"
                                -- Añade más rangos según sea necesario
                                ELSE "Otros"
                            END AS age_group'),
                    DB::raw('COUNT(*) AS user_count')
                )
                ->groupBy('age_group')
                ->orderBy(DB::raw('MIN(age)'))
                ->get();

            return response()->json($ageGroups, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving age groups data'], 500);
        }
    }

    public function findMostPlacedProduct()
    {
        // Ejecutar una consulta SQL para encontrar el producto más frecuente
        $mostPlacedProduct = DB::table('cart_product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->first();

        if ($mostPlacedProduct) {
            $productId = $mostPlacedProduct->product_id;
            $totalQuantity = $mostPlacedProduct->total_quantity;
            return response()->json([
                'message' => 'El producto más frecuente se encontró con éxito.',
                'data' => [
                    'product_id' => $productId,
                    'total_quantity' => $totalQuantity
                ]
            ]);
        } else {
            return response()->json([
                'message' => 'No hay productos en el carrito.'
            ], 404);
        }
    }
}
