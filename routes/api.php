<?php

use App\Http\Controllers\TailorController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TailorCustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\CategoryParameterController;
use App\Http\Controllers\TailorCategoryController;
use App\Http\Controllers\TailorParameterController;
use App\Http\Controllers\TailorCategoryParameterController as TalCatParameterController;
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
    $router->post('/login',[ TailorController::class , 'login' ]); 
    $router->post('/changePassword',[ TailorController::class , 'changePassword' ]);
    $router->post('/store',[ TailorController::class , 'store' ]);
});
Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/tailors' ], function ($router) {
    $router->get('/index',[ TailorController::class , 'index' ]);
    $router->post('/search',[ TailorController::class , 'search' ]);
    $router->post('/exists',[ TailorController::class , 'exists' ]);
    $router->post('/destroy',[ TailorController::class , 'destroy' ]);
    $router->post('/logout',[ TailorController::class , 'logout' ]);
});

Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/shops' ], function ($router) {
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

Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/customers' ], function ($router) {
    $router->get('/', [TailorCustomerController::class,'index']);
    $router->get('/count', [TailorCustomerController::class,'countCustomers']);
    $router->post('/number', [TailorCustomerController::class,'getCustomer']);
    $router->get('/{customer_id}', [TailorCustomerController::class,'getCustomerById']);
    $router->post('/search', [TailorCustomerController::class,'search']);
    $router->post('/store', [TailorCustomerController::class,'store']);
    $router->post('/update', [TailorCustomerController::class,'update']);
    $router->post('/destroy', [TailorCustomerController::class,'destroy']);
});

Route::group(['prefix' => '/categories' ], function ($router) {
    $router->get('/', [CategoryController::class,'index']);
    $router->post('/store', [CategoryController::class,'store']);
    $router->get('/{category_id}', [CategoryController::class,'show']);
});

Route::group(['prefix' => '/parameters' ], function ($router) {
    $router->get('/', [ParameterController::class,'index']);
    $router->post('/store', [ParameterController::class,'store']);
});

Route::group(['prefix' => '/categories/parameters' ], function ($router) {
    $router->get('/{category_id}', [CategoryParameterController::class,'index']);
    $router->post('/store', [CategoryParameterController::class,'store']);
});

Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/categories' ], function ($router) {
    $router->get('/', [TailorCategoryController::class,'index']);
    $router->post('/', [TailorCategoryController::class,'default']);
    $router->post('/store', [TailorCategoryController::class,'store']);
    $router->get('/{category_id}', [TailorCategoryController::class,'show']);
});
Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/parameters' ], function ($router) {
    $router->get('/', [TailorParameterController::class,'index']);
    $router->post('/', [TailorParameterController::class,'default']);
    $router->post('/store', [TailorParameterController::class,'store']);
});
Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/categories/{category_id}/parameters' ], function ($router) {
    $router->get('/', [TalCatParameterController::class,'index']);
    $router->post('/', [TalCatParameterController::class,'default']);
    $router->post('/store', [TalCatParameterController::class,'store']);
    $router->post('/update', [TalCatParameterController::class,'update']);
    $router->post('/destroy', [TalCatParameterController::class,'destroy']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
