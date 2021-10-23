<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/indexx','IndexController@index');

// 测试接口
Route::prefix('v1')->group(function (){
    // 测试接口
    Route::get('/index','IndexController@index');
    // 测试入列redis
    Route::get('/toRedisList','IndexController@toRedisList');
});
