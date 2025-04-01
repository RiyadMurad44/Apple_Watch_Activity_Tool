<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CSVUploadController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(["prefix" => "v1"], function(){
    //Authorized Users
    Route::group(["middleware" => "auth:api"], function () {
        //Authorized Users
        // Route::group(["prefix" => "Employees"], function () {});
    
    });

    //Unauthenticated Users
    Route::post('/upload-csv', [CSVUploadController::class, 'upload']);
    // Route::post("/login", [AuthController::class, "login"])->name("login");
    // Route::post("/signup", [AuthController::class, "signup"])->name("signup");
});
