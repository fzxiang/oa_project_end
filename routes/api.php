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

Route::get('/foo', function (){
    return [1,4,2,6,5,8,9];
});
Route::get('/test', 'testController@test')->name('test');



// 用户登录(用户名，密码)
Route::post('/login', 'oaUsersController@store')->name('login');
// 权限获取
Route::get('/power', 'oaUsersController@power')->name('power');
/*********管理员(高权限者)操作*********/
// 添加用户(用户名，昵称，密码，权限(json格式))
Route::post('/addUser', 'oaUsersController@addUser')->name('addUser');
// 初始化密码(用户唯一id)
Route::post('/initPwd', 'oaUsersController@initPwd')->name('initPwd');
// 删除(用户唯一id)
Route::post('/delUser', 'oaUsersController@delUser')->name('delUser');
// 用户权限修改(用户唯一id，权限(json格式))
Route::post('/updateShop', 'oaUsersController@updateShop')->name('updateShop');
