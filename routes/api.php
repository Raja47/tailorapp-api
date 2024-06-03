<?php

use App\Http\Controllers\DressController;
use App\Http\Controllers\TailorController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TailorCustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\CategoryParameterController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\MeasurementValueController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TailorCategoryController;
use App\Http\Controllers\TailorParameterController;
use App\Http\Controllers\TailorCategoryParameterController as TalCatParameterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Tailor;

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
// Route::get('/try',function(){
//     $result = Tailor::select('name','password')->where('password','123456')->get()->groupBy('status');
//     return $result;
// });
Route::group(['prefix' => '/tailors'], function ($router) {
    $router->post('/login', [TailorController::class, 'login']);
    $router->post('/changePassword', [TailorController::class, 'changePassword']);
    $router->post('/store', [TailorController::class, 'store']);
});
Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors'], function ($router) {
    $router->get('/index', [TailorController::class, 'index']);
    $router->post('/search', [TailorController::class, 'search']);
    $router->post('/exists', [TailorController::class, 'exists']);
    $router->post('/destroy', [TailorController::class, 'destroy']);
    $router->post('/logout', [TailorController::class, 'logout']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/shops'], function ($router) {
    $router->post('/index', [ShopController::class, 'index']);
    $router->post('/store', [ShopController::class, 'store']);
    $router->post('/update', [ShopController::class, 'update']);
});

Route::group(['prefix' => '/customers'], function ($router) {
    $router->get('/index', [CustomerController::class, 'index']);
    $router->post('/store', [CustomerController::class, 'store']);
    $router->post('/update', [CustomerController::class, 'update']);
    $router->post('/destroy', [CustomerController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/customers'], function ($router) {
    $router->get('/', [TailorCustomerController::class, 'index']);
    $router->get('/count', [TailorCustomerController::class, 'countCustomers']);
    $router->post('/number', [TailorCustomerController::class, 'getCustomer']);
    $router->get('/{customer_id}', [TailorCustomerController::class, 'getCustomerById']);
    $router->post('/search', [TailorCustomerController::class, 'search']);
    $router->post('/store', [TailorCustomerController::class, 'store']);
    $router->post('/update', [TailorCustomerController::class, 'update']);
    $router->post('/destroy', [TailorCustomerController::class, 'destroy']);
});

Route::group(['prefix' => '/categories'], function ($router) {
    $router->get('/', [CategoryController::class, 'index']);
    $router->post('/store', [CategoryController::class, 'store']);
    $router->get('/{category_id}', [CategoryController::class, 'show']);
});

Route::group(['prefix' => '/parameters'], function ($router) {
    $router->get('/', [ParameterController::class, 'index']);
    $router->post('/store', [ParameterController::class, 'store']);
});

Route::group(['prefix' => '/categories/parameters'], function ($router) {
    $router->get('/{category_id}', [CategoryParameterController::class, 'index']);
    $router->post('/store', [CategoryParameterController::class, 'store']);
});

Route::group(['prefix' => '/tailors/{tailor_id}/categories'], function ($router) {
// Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/categories'], function ($router) {
    $router->get('/', [TailorCategoryController::class, 'index']);
    $router->post('/', [TailorCategoryController::class, 'default']);
    $router->post('/store', [TailorCategoryController::class, 'store']);
    $router->get('/{category_id}', [TailorCategoryController::class, 'show']);
    $router->post('/{category_id}/status', [TailorCategoryController::class, 'updateStatus']);
});
Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/parameters'], function ($router) {
    $router->get('/', [TailorParameterController::class, 'index']);
    $router->post('/', [TailorParameterController::class, 'default']);
    $router->post('/store', [TailorParameterController::class, 'store']);
});
Route::group(['prefix' => '/tailors/{tailor_id}/categories/{category_id}/parameters'], function ($router) {
// Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/categories/{category_id}/parameters'], function ($router) {
    $router->get('/', [TalCatParameterController::class, 'index']);
    $router->post('/', [TalCatParameterController::class, 'default']);
    $router->post('/update', [TalCatParameterController::class, 'update']);
});
Route::group(['prefix' => '/tailors/{tailor_id}/categories/parameters'], function ($router) {
    // Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/categories/parameters'], function ($router) {
    $router->post('/destroy', [TalCatParameterController::class, 'destroy']);
    $router->post('/store', [TalCatParameterController::class, 'store']);
});

Route::group(['prefix' => '/measurements'], function ($router) {
    $router->get('/dresses/{dress_id}', [MeasurementController::class, 'getDressMeasurementWithValues']);
    $router->post('/store/{dress_id}', [MeasurementController::class, 'newMeasurementWithValues']);
    $router->get('/customer/{customer_id}', [MeasurementController::class, 'getCustomerMeasurements']);
    $router->get('/delete/{measurement_id}', [MeasurementController::class, 'deleteMeasurement']);
});

Route::group(['prefix' => '/measurements/values'], function ($router) {
    $router->post('/store', [MeasurementValueController::class, 'newMeasurementValue']);
    $router->post('/delete', [MeasurementValueController::class, 'deleteInvalidMvs']);
});

// Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/dresses' ], function ($router) {
Route::group(['prefix' => '/tailors/{tailor_id}/dresses'], function ($router) {
    $router->get('/{dress_id}/measurement', [DressController::class, 'getOrderDressMeasurement']);
    $router->post('/tabdress', [DressController::class, 'getTabDresses']);
    $router->post('/store', [DressController::class, 'addDress']);
    $router->post('/update', [DressController::class, 'updateDress']);
    $router->get('/countbystatus/{shop_id}/{index}', [DressController::class, 'countDressesByStatus']);
    $router->get('/count', [DressController::class, 'countDresses']);
    $router->get('/{dress_id}/delete', [DressController::class, 'delete']);
    $router->get('/order/{order_id}', [DressController::class, 'getOrderDresses']);
    $router->post('/statusupdate', [DressController::class, 'updateStatus']);
});

// Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/tailors/{tailor_id}/orders' ], function ($router) {
Route::group(['prefix' => '/tailors/{tailor_id}/orders'], function ($router) {
    $router->get('/', [OrderController::class, 'getOrders']);
    $router->post('/statusupdate', [OrderController::class, 'updateStatus']);
    $router->get('/count', [OrderController::class, 'countOrders']);
    $router->get('/{order_id}/customer', [OrderController::class, 'getCustomerByOrderid']);
    $router->post('/empty', [OrderController::class, 'emptyOrder']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
