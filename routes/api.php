<?php

use App\Http\Controllers\AuthController;
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
Route::post('/check-destination-unique', [ShortlinkController::class, 'checkDestinationUnique']);

Route::get('/shortlinks/destination/{destination}', [ShortlinkController::class, 'showShortlinkDetails'])
    ->where('destination', 'https?://.+');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/get-users', [AuthController::class, 'getUsers']);
Route::put('/edit-user/{id}', [AuthController::class, 'editUser']);
Route::delete('/delete-user/{id}', [AuthController::class, 'deleteUser']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get-profile', [AuthController::class, 'getProfile']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/{chemin_personnalise}', [ShortlinkController::class, 'redirect']);
