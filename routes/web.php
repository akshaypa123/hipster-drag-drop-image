<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|----------------------------------                                                                                                                     ----------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});
Route::post('/import', [ImportController::class, 'import']);
Route::post('/upload/chunk', [UploadController::class, 'uploadChunk']);
Route::post('/upload/finalize', [UploadController::class, 'finalize']);

// Optional GET route for friendly message (prevents exception)
Route::get('/upload/chunk', function() {
    return 'This route only supports POST for uploads.';
});