<?php

use App\Http\Controllers\admin\AdminCategoryController;
use App\Http\Controllers\admin\ManagePointRetailer;
use App\Http\Controllers\seller\CategoryController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\customer\CartController;
use App\Http\Controllers\customer\CommentController;
use App\Http\Controllers\seller\ProductController;
use App\Http\Controllers\customer\CustomerProductController;
use App\Http\Controllers\customer\UserEvaluationController;
use App\Http\Controllers\customer\ProductEvaluationCotroller;
use App\Http\Controllers\customer\FavoriteController;
use App\Http\Controllers\customer\CustomerOrderController;
use App\Http\Controllers\DeliveryDriver\DeliveryDriverController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\pointRetailer\OperationsController;
use App\Http\Controllers\seller\SellerDeliveryDriverController;
use App\Http\Controllers\seller\SellerOrderController;
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
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

*/

Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);
Route::post("/checkEmail", [ForgetPasswordController::class, "checkEmail"]);
Route::post("/changePassword", [ForgetPasswordController::class, "changePassword"]);
Route::post("/checkCode", [ForgetPasswordController::class, "checkCode"]);


Route::middleware('auth:sanctum')->group(function () {
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'destroy']);
    Route::post('/notifications/reading/{notification}', [NotificationController::class, 'reading']);
    Route::post('/notifications/addNotification', [NotificationController::class, 'addNotification']);
    Route::post('/notifications/readingAll', [NotificationController::class, 'readingAll']);
    Route::delete('/notifications/destroyAll', [NotificationController::class, 'destroyAll']);
    Route::post('/notifications/test', [NotificationController::class, 'test']);


    Route::post('udateProfile', [ProfileController::class, 'udateProfile']);
    Route::get('/updateData', [ProfileController::class, 'updateData']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/getAllCategory', [AdminCategoryController::class, 'getAllCategory']);
    Route::get('/getRequestCategory', [AdminCategoryController::class, 'getRequestCategory']);
    Route::post('/category/accept/{category}', [AdminCategoryController::class, 'accept']);
    Route::post('/category/reject/{category}', [AdminCategoryController::class, 'reject']);
    Route::get('/getAllRequests', [ManagePointRetailer::class, 'getAllRequests']);
    Route::post('/pointRetailer/accept/{pointRetailer}', [ManagePointRetailer::class, 'accept']);
    Route::post('/pointRetailer/reject/{pointRetailer}', [ManagePointRetailer::class, 'reject']);
});

Route::middleware('auth:sanctum')->prefix('seller')->group(function () {
    Route::get('/getAllCategory', [CategoryController::class, 'getAllCategory']);
    Route::get('/getMyCategory', [CategoryController::class, 'getMyCategory']);
    Route::get('/getMyRequestCategory', [CategoryController::class, 'getMyRequestCategory']);
    Route::get('/getAllProductCategory/{category}', [CategoryController::class, 'getAllProductCategory']);
    Route::post('/addCategory', [CategoryController::class, 'addCategory']);
    Route::delete('/category/destroyCategory/{category}', [CategoryController::class, 'destroyCategory']);
    Route::delete('/category/destroyRequestCategory/{requestCategory}', [CategoryController::class, 'destroyRequestCategory']);
    //------------------
    Route::apiResource('products', ProductController::class)->except('update'); //->scoped(['cooments' => 'products']);
    Route::post('/products/update/{products}', [ProductController::class, 'update']);
    Route::apiResource('deliveryDrivers', SellerDeliveryDriverController::class)->only(['index', 'store', 'destroy']);
    Route::get('/orders/wiatingOrders', [SellerOrderController::class, 'wiatingOrders']);
    Route::get('/orders/acceptedOrders', [SellerOrderController::class, 'acceptedOrders']);
    Route::get('/orders/{order}', [SellerOrderController::class, 'show']);
    Route::post('/orders/reject/{order}', [SellerOrderController::class, 'reject']);
    Route::post('/orders/accept/{order}', [SellerOrderController::class, 'accept']);
    Route::post('/orders/update/{order}', [SellerOrderController::class, 'update']);
});

Route::middleware('auth:sanctum')->prefix('customer')->group(function () {
    Route::apiResource('products', CustomerProductController::class)->only('index');
    Route::get('/getAllSellerproducts/{seller_id}', [CustomerProductController::class, 'getAllproducts']);
    Route::get('/getAllSeller', [CustomerProductController::class, 'getAllSeller']);
    Route::apiResource('carts', CartController::class)->except(['show', 'update']);
    Route::post('/carts/addToCart/{product_id}', [CartController::class, 'addToCart']);
    Route::post('/carts/saveCart', [CartController::class, 'saveCart']);
    Route::post('/carts/confirmOrder', [CartController::class, 'confirmOrder']);
    Route::apiResource('favorites', FavoriteController::class)->only(['index', 'destroy']);
    Route::post('/addFavorite/{product_id}', [FavoriteController::class, 'addFavorite']);
    Route::apiResource('orders', CustomerOrderController::class)->only(['index', 'show', 'destroy']);
    Route::get('products/{product}/comments', [CommentController::class, 'index']);
    Route::post('products/{product}/comments/{comment}', [CommentController::class, 'update']);
    Route::post('products/{product}/comments', [CommentController::class, 'store']);
    Route::delete('products/{product}/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/checkUserEvaluation', [UserEvaluationController::class, 'checkUserEvaluation']);
    Route::post('/checkProductEvaluation', [ProductEvaluationCotroller::class, 'checkProductEvaluation']);
    Route::post('/userEvaluation', [UserEvaluationController::class, 'evaluation']);
    Route::post('/productEvaluation', [ProductEvaluationCotroller::class, 'evaluation']);
});

Route::middleware('auth:sanctum')->prefix('pointRetailer')->group(function () {
    Route::get('/getAllOperations', [OperationsController::class, 'getAllOperations']);
    Route::post('/deposit', [OperationsController::class, 'deposit']);
    Route::post('/withdrawal', [OperationsController::class, 'withdrawal']);
    Route::post('/confirmationCodeWithdrawal', [OperationsController::class, 'confirmationCodeWithdrawal']);
});

Route::middleware('auth:sanctum')->prefix('deliveryDrive')->group(function () {
    Route::get('/wiatingOders', [DeliveryDriverController::class, 'wiatingOders']);
    Route::get('/startedDelivery', [DeliveryDriverController::class, 'startedDelivery']);
    Route::post('/delivered/{order}', [DeliveryDriverController::class, 'delivered']);
    Route::post('/createConfirmOrderCode/{order}', [DeliveryDriverController::class, 'createConfirmOrderCode']);
    Route::post('/startDelivery/{order}', [DeliveryDriverController::class, 'startDelivery']);
});