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
use App\Http\Controllers\MediaController;
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
Route::post('/try',function(Request $request){
    $tailor_id = $request->tailor_id;
    $categories = app('App\Http\Controllers\TailorCategoryController')->default($tailor_id);
    $cat_parameters = app('App\Http\Controllers\TailorCategoryParameterController')->default($tailor_id);
});
Route::group(['prefix' => '/tailors'], function ($router) {
    $router->post('/login', [TailorController::class, 'login']);
    $router->post('/changePassword', [TailorController::class, 'changePassword']);
    $router->post('/store', [TailorController::class, 'store']);
});

// For Prefix Tailor we unauthenticated routes
Route::group(['prefix' => '/tailors'], function ($router) {
    $router->post('/search', [TailorController::class, 'search']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors'], function ($router) {
    $router->get('/index', [TailorController::class, 'index']);
    $router->get('/username/{username}', [TailorController::class, 'if_username']);
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

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/customers'], function ($router) {
    $router->get('/', [TailorCustomerController::class, 'index']);
    $router->get('/search', [TailorCustomerController::class, 'search']);
    $router->get('/{customer_id}', [TailorCustomerController::class, 'getCustomerById']);
    $router->post('/store', [TailorCustomerController::class, 'store']);
    $router->post('/update', [TailorCustomerController::class, 'update']);
    $router->post('/destroy', [TailorCustomerController::class, 'destroy']);
    
    $router->get('/count', [TailorCustomerController::class, 'countCustomers']);
    $router->post('/number', [TailorCustomerController::class, 'getCustomer']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/categories'], function ($router) {
    $router->get('/', [CategoryController::class, 'index']);
    $router->post('/store', [CategoryController::class, 'store']);
    $router->get('/{category_id}', [CategoryController::class, 'show']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/parameters'], function ($router) {
    $router->get('/', [ParameterController::class, 'index']);
    $router->post('/store', [ParameterController::class, 'store']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/categories/parameters'], function ($router) {
    $router->get('/{category_id}', [CategoryParameterController::class, 'index']);
    $router->post('/store', [CategoryParameterController::class, 'store']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/categories'], function ($router) {
    $router->get('/', [TailorCategoryController::class, 'index']);
    $router->post('/', [TailorCategoryController::class, 'default']);
    $router->get('/exists', [TailorCategoryController::class, 'allCategoriesWithExistStatus']);
    $router->post('/store', [TailorCategoryController::class, 'store']);
    $router->post('/{category_id}/update', [TailorCategoryController::class, 'update']);
    $router->get('/{category_id}', [TailorCategoryController::class, 'show']);
    $router->post('/{category_id}/status', [TailorCategoryController::class, 'updateStatus']);
    $router->post('/{category_id}/delete', [TailorCategoryController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/categories/{category_id}/parameters'], function ($router) {
    // Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/categories/{category_id}/parameters'], function ($router) {
    $router->get('/', [TalCatParameterController::class, 'index']);
    $router->post('/', [TalCatParameterController::class, 'default']);
    $router->post('/update', [TalCatParameterController::class, 'update']);
});
Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/categories/parameters'], function ($router) {
    // Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/categories/parameters'], function ($router) {
    $router->post('/destroy', [TalCatParameterController::class, 'destroy']);
    $router->post('/store', [TalCatParameterController::class, 'store']);
});
// Route::group(['middleware'=>['auth:sanctum'], 'prefix' => '/tailors/dresses' ], function ($router) {
Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/dresses'], function ($router) {
    $router->post('/create', [DressController::class, 'create']);
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

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/tailors/orders'], function ($router) {
    // Route::group(['prefix' => '/tailors/{tailor_id}/orders'], function ($router) {
    $router->get('/', [OrderController::class, 'getOrders']);
    $router->post('/statusupdate', [OrderController::class, 'updateStatus']);
    $router->get('/count', [OrderController::class, 'countOrders']);
    $router->get('/{order_id}/customer', [OrderController::class, 'getCustomerByOrderid']);
    $router->post('/store', [OrderController::class, 'emptyOrder']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => '/measurements'], function ($router) {
    $router->post('/dresses/store', [MeasurementController::class, 'newMeasurementWithValues']);
    $router->get('/dresses/{dress_id}', [MeasurementController::class, 'getDressMeasurementWithValues']);
});


// Route::group(['prefix' => '/media'], function ($router) {
//     $router->get('/order/{order_id}', [MediaController::class, 'getOrderMedia']);
//     $router->get('/dress/{dress_id}', [MediaController::class, 'getDressMedia']);
//     $router->post('/create', [MediaController::class, 'create']);
//     $router->post('/{media_id}/update', [MediaController::class, 'update']);
//     $router->post('/{media_id}/delete', [MediaController::class, 'delete']);
// });


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
