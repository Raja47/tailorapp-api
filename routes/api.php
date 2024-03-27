<?php

use App\Http\Controllers\TailorController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TailorCustomerController;
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
    $router->post('/exists',[ TailorController::class , 'exists' ]);
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

Route::group(['prefix' => '/customers' ], function ($router) {
    $router->get('/index', [CustomerController::class,'index']);
    $router->post('/store', [CustomerController::class,'store']);
    $router->post('/update', [CustomerController::class,'update']);
    $router->post('/destroy', [CustomerController::class,'destroy']);
});

Route::group(['prefix' => '/tailorcustomers' ], function ($router) {
    $router->post('/index', [TailorCustomerController::class,'index']);
    $router->post('/countcustomers', [TailorCustomerController::class,'countCustomers']);
    $router->post('/getcustomer', [TailorCustomerController::class,'getCustomer']);
    $router->post('/getcustomerbyid', [TailorCustomerController::class,'getCustomerById']);
    $router->post('/getcustomers', [TailorCustomerController::class,'getCustomers']);
    $router->post('/getcustomerbynumber', [TailorCustomerController::class,'getCustomersByNumber']);
    $router->post('/store', [TailorCustomerController::class,'store']);
    $router->post('/update', [TailorCustomerController::class,'update']);
    $router->post('/destroy', [TailorCustomerController::class,'destroy']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
