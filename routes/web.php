<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;

Route::controller(ArticleController::class)->group(function(){
        Route::get("/articles/{role}", "index");
        Route::post("/articles/{role}", "store");
        Route::patch("/articles/{role}/{id}", "update");
        Route::patch("/articles/{role}", "buyArticle");
        Route::delete("/articles/{role}/{id}", "delete");

});


