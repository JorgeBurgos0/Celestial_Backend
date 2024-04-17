<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\GraphController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {


    ////////////
    // Logout //
    ///////////
   Route::get('logout', [AuthController::class, 'logout']); 
   
    ////////////
    // perfil //
    ///////////
   Route::get('profile/{id}', [ProfileController::class, 'show']);

    ///////////////
    // Productos //
    //////////////
   Route::get('/products', [ProductController::class, 'index'])->middleware('CheckRole:Administrador');
   Route::post('/products/create', [ProductController::class, 'store'])->middleware('CheckRole:Administrador');
   Route::post('/products/update/{id}', [ProductController::class, 'update'])->middleware('CheckRole:Administrador');
   Route::delete('/products/delete/{id}', [ProductController::class, 'destroy'])->middleware('CheckRole:Administrador');

     ///////////////
     // Usuarios //
     //////////////
    Route::get('/users', [UserController::class, 'index'])->middleware('CheckRole:Administrador');
    Route::post('/users/create', [UserController::class, 'create'])->middleware('CheckRole:Administrador');
    Route::post('/users/update/{id}', [UserController::class, 'update'])->middleware('CheckRole:Administrador');
    Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->middleware('CheckRole:Administrador'); 

     ///////////////
     //  Tienda  //
     //////////////
    Route::get('/store/products', [StoreController::class, 'index']);
    Route::post('cart/add', [StoreController::class, 'addToCart']);
    Route::post('/store/checkout', [StoreController::class, 'checkout']); 
    Route::post('/store/orders/{order}/update-stock', [StoreController::class, 'updateStock']);

     ///////////////
     // Carrito  //
     //////////////
    Route::get('/cart', [CartController::class, 'index']);
    Route::delete('/cart/{productId}', [CartController::class, 'removeFromCart']);

     ///////////////
     // Graficas //
     //////////////

    Route::get('/graph/gender', [GraphController::class, 'gender'])->middleware('CheckRole:Administrador');
    Route::get('/graph/most-sold-products', [GraphController::class, 'mostSoldProducts'])->middleware('CheckRole:Administrador');
    Route::get('/graph/products-by-gender', [GraphController::class, 'productsByGender'])->middleware('CheckRole:Administrador');
    Route::get('/graph/age-groups', [GraphController::class, 'ageGroups'])->middleware('CheckRole:Administrador');
    Route::get('/graph/most-placed-product', [GraphController::class, 'findMostPlacedProduct'])->middleware('CheckRole:Administrador');

});

