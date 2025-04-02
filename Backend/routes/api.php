<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CSVUploadController;

Route::group(["prefix" => "v1"], function(){
    //Authorized Users
    Route::group(["middleware" => "auth:api"], function () {
        Route::post("/upload-csv", [CSVUploadController::class, "upload"]);
    });

    //Unauthenticated Users
    Route::post("/login", [AuthController::class, "login"])->name("login");
    Route::post("/signup", [AuthController::class, "signup"])->name("signup");
});
