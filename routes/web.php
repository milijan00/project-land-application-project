<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});
Route::controller(ArticleController::class)->group(function(){
        Route::get("/articles/{role}", "index");
        Route::post("/articles/{role}", "store");
        Route::patch("/articles/{role}/{id}", "update");
        Route::patch("/articles/{role}", "buyArticle");
        Route::delete("/articles/{role}/{id}", "delete");
//        Route::get("/articles/{role}", "index");
//        Route::post("/articles/{role}", "store");
//        Route::patch("/articles/{role}/{id}", "update");
//        Route::patch("/articles/{role}", "buyArticle");
//        Route::delete("/articles/{role}/{id}", "delete");
});

//Route::get('/profile', function () {
//    //
//})->middleware(EnsureTokenIsValid::class);
