<?php

use App\Http\Controllers\TailorController;
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
Route::group(['prefix' => '/tailors' ], function ($router) {
    $router->get('/index',[ TailorController::class , 'index' ]);
    $router->post('/store',[ TailorController::class , 'store' ]);
    $router->post('/search',[ TailorController::class , 'search' ]);
    $router->post('/login',[ TailorController::class , 'login' ]); 
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
