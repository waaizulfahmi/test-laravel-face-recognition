<?php

use App\Http\Controllers\MLApiController;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// ML API 
Route::post('/predict',  [MLApiController::class,'predict']);
Route::post('/testing',  [MLApiController::class,'testing']);
Route::post('/images',  [MLApiController::class,'images']);
Route::post('/img',  [MLApiController::class,'img']);

Route::get('/create-zip', [MLApiController::class,'createZip']);
Route::post('/upload-image',  [ImageController::class,'uploadImage']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
