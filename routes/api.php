<?php

use App\Http\Controllers\ShortlinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('shortlinks', [ShortlinkController::class, 'index']);
Route::post('shortlinks', [ShortlinkController::class, 'store']);
Route::get('shortlinks/{id}', [ShortlinkController::class, 'show']);
Route::put('shortlinks/{id}', [ShortlinkController::class, 'update']);
Route::delete('shortlinks/{id}', [ShortlinkController::class, 'destroy']);
Route::post('/check-chemin-unique', [ShortlinkController::class, 'checkCheminUnique']);
