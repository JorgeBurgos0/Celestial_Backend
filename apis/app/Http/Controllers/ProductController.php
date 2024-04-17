<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|integer',
            'stock' => 'required|integer',
        ]);
    
        // Procesar y guardar la imagen si se proporciona
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Generar un nombre único para la imagen
            $imageName = time().'.'.$image->extension();
            
            // Guardar la imagen en el almacenamiento público
            $image->storeAs('public/products', $imageName);
            
            // Obtener la ruta de la imagen almacenada
            $imagePath = 'storage/products/'.$imageName;
        } else {
            // Si no se proporciona una imagen, establecer el valor de la ruta como nulo
            $imagePath = null;
        }
    
        // Crear el producto con los datos proporcionados
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'stock' => $request->stock,
            'image' => $imagePath, // Asignar la ruta de la imagen al campo 'image'
        ]);
    
        // Devolver una respuesta JSON con el producto creado
        return response()->json($product, 201);
    }
    

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|integer',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validación opcional para la imagen
        ]);

        // Procesar y actualizar la imagen si se proporciona
        if ($request->hasFile('image')) {
            // Eliminar la imagen anterior si existe
            Storage::disk('public')->delete($product->image);
            // Procesar y guardar la nueva imagen
            $image = $request->file('image');
            $imagePath = $image->store('products', 'public');
            $request->merge(['image' => $imagePath]);
        }

        $product->update($request->all());
        return response()->json($product, 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        // Eliminar la imagen asociada al producto si existe
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return response()->json(null, 204);
    }
}
