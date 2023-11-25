<?php

use App\Http\Controllers\TailorController;
use App\Http\Controllers\ShopController;
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
    $router->post('/search',[ TailorController::class , 'search' ]);
    $router->post('/store',[ TailorController::class , 'store' ]);
    $router->post('/login',[ TailorController::class , 'login' ]); 
    $router->post('/changePassword',[ TailorController::class , 'changePassword' ]);
    $router->post('/destroy',[ TailorController::class , 'destroy' ]);
});

Route::group(['prefix' => '/shops' ], function ($router) {
    $router->post('/index',[ ShopController::class , 'index' ]);
    $router->post('/store',[ ShopController::class , 'store' ]);
    $router->post('/update',[ShopController::class , 'update'] );
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
