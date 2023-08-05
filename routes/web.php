<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\OrdersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index']);
Route::post('/startsession', [HomeController::class, 'start_session'])->name("startsession");

// Create new items page
Route::get('/items', [ItemsController::class, 'index']);
Route::get('/items/{id}', [ItemsController::class, 'edit_page']);
Route::post('/getitems', [ItemsController::class, 'get'])->name("getitems");
Route::post('/displayitems', [ItemsController::class, 'display'])->name("displayitems");
Route::post('/saveitems', [ItemsController::class, 'save'])->name("saveitems");
Route::post('/deleteitems', [ItemsController::class, 'delete'])->name("deleteitems");
Route::post('/updateitems', [ItemsController::class, 'update'])->name("updateitems");
Route::post('/getmods', [ItemsController::class, 'get_mods'])->name("getmods");
Route::post('/savemod', [ItemsController::class, 'save_mod'])->name("savemod");
Route::post('/editmod', [ItemsController::class, 'updateMods'])->name("editmod");
Route::post('/removemod', [ItemsController::class, 'deleteModifier'])->name("removemod");

// Categories page
Route::get('/categorias', [CategoriesController::class, 'index']);
Route::post('/getcategories', [CategoriesController::class, 'getCategories'])->name("getcategories");
Route::post('/savecategories', [CategoriesController::class, 'save'])->name("savecategories");
Route::post('/deletecategory', [CategoriesController::class, 'remove'])->name("deletecategory");

// Orders
Route::get('/pedidos', [OrdersController::class, 'index']);
Route::post('/closesess', [OrdersController::class, 'closeSession'])->name("closesess");
Route::post('/addcart', [OrdersController::class, 'addItem'])->name("addcart");
Route::post('/reset', [OrdersController::class, 'resetItems'])->name("reset");