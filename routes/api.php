<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BidController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('pasword/email',[AuthController::class,'sendResetLinkEmail']);
Route::get('pasword/reset/{id}',[AuthController::class,'getemail_fromtoken']);
Route::post('pasword/reset',[AuthController::class,'submitResetPasswordForm']);

Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware(['auth:sanctum', 'signed'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (Request $request) {
        $request->user()->markEmailAsVerified();
        return response()->json(['message' => 'Email verified']);
    })->name('verification.verify');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('enable-two-factor', [AuthController::class, 'enableTwoFactor']);
    Route::post('disable-two-factor', [AuthController::class, 'disableTwoFactor']);
    Route::post('verify-two-factor', [AuthController::class, 'verifyTwoFactor']);
    Route::post('send-two-factor-code', [AuthController::class, 'sendTwoFactorCode']);
});

//jobs routes
Route::group(['middleware' => 'auth:sanctum'],function(){
    Route::get('all_jobs',[JobController::class,'getUserJobs']);
    // Route::post('show/jobs/client',[JobController::class,'showJobsToClient']);
    Route::post('create_job',[JobController::class,'createJob']);
    Route::put('update_job/{id}',[JobController::class,'updateJob']);
    Route::delete('delete_job/{id}',[JobController::class,'deleteJob']);

});

//Bids Routes
Route::group(['middleware' => 'auth:sanctum'],function(){
    Route::post('submit/bid/{id}',[BidController::class,'submitBid']);
    Route::put('update/bid/{id}',[BidController::class,'updateBid']);
    Route::delete('delete/bid/{id}',[BidController::class,'deleteBid']);
    Route::get('show/bid',[BidController::class,'showVendorBids']);
    Route::get('show/bids/client/{id}',[BidController::class,'showJobBids']);
});

//to sechdule the jobs
Route::get('/schedule-jobs', function () {
    dispatch(new SendJobsToVendors())->onQueue('default');
});

//Routes for Chat module
Route::get('messages', [HomeController::class, 'messages'])->name('messages');

//Routes to update user profile i-e clinet , vendor ,"will have to work on these"
Route::get('show_profile/{id}',[ProfileController::class,'showProfile']);
Route::put('update_profile/{id}',[ProfileController::class,'updateProfile']);









