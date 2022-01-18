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

Route::get('/testGzencode', 'oa\oaUsersController@testGzencode')->name('testGzencode');
Route::post('/exportTest', 'oa\oaUsersController@exportTest')->name('exportTest');



// 用户登录(用户名(username)，密码(password))(返回token)
Route::post('/login', 'oa\oaUsersController@store')->name('login');
// 用户登出(token)
Route::post('/logout', 'oa\oaUsersController@logout')->name('logout');
// 修改密码(token,密码(password))
Route::post('/changePwd', 'oa\oaUsersController@changePwd')->name('changePwd');
// 请求用户信息(token)
Route::get('/getUserInfo', 'oa\oaUsersController@power')->name('power');
/*********管理员(高权限者)操作*********/
// 添加用户(token,用户名(username)，昵称(nickname)，密码(password)，权限(json格式)(powerJson))
Route::post('/addUser', 'oa\oaUsersController@addUser')->name('addUser');
// 初始化密码(token,用户唯一id(uId))
Route::post('/initPwd', 'oa\oaUsersController@initPwd')->name('initPwd');
// 删除(token,用户唯一id(uId))
Route::post('/delUser', 'oa\oaUsersController@delUser')->name('delUser');
// 用户权限修改(token,用户唯一id(uId)，权限(json格式)(powerJson))
Route::get('/updatePower', 'oa\oaUsersController@updatePower')->name('updatePower');
// 添加店铺(token,店铺名称(shop_name)，公司名称(company_name)，备注(remarks))
Route::post('/addShop', 'oa\oaUsersController@addShop')->name('addShop');
// 编辑店铺(token,店铺ID(shop_id)，店铺名称(shop_name)，公司名称(company_name)，备注(remarks))
Route::post('/updateShop', 'oa\oaUsersController@updateShop')->name('updateShop');
// 删除店铺(token,店铺ID(shop_id))
Route::post('/delShop', 'oa\oaUsersController@delShop')->name('delShop');
// 获取所有用户(token)
Route::get('/getUsers', 'oa\oaUsersController@getUsers')->name('getUsers');
// 获取所有商店(token)
Route::get('/getShops', 'oa\oaUsersController@getShops')->name('getShops');
// 商店查询(店铺名(shop_name))
Route::get('/searchShop', 'oa\oaUsersController@searchShop')->name('searchShop');

// 角色添加(名称(roleName), 备注(remarks), 权限(数组)(menu))
Route::post('/addRole', 'oa\oaUsersController@addRole')->name('addRole');
// 角色删除(角色ID(roleId))
Route::post('/delRole', 'oa\oaUsersController@delRole')->name('delRole');
// 角色权限修改(角色ID(roleId), 权限(数组)(menu))
Route::post('/updateRolePower', 'oa\oaUsersController@updateRolePower')->name('updateRolePower');
// 角色列表获取
Route::get('/getRoleList', 'oa\oaUsersController@getRoleList')->name('getRoleList');


/***************用户操作***************/
// 用户店铺选择(token, 店铺ID(shop_id))
Route::post('/selectShop', 'oa\oaUsersController@selectShop')->name('selectShop');

/**************业务逻辑操作(我的订单)(以下操作均需要上传token)*************/
// 添加订单(订单信息(orderInfo(json格式)))
Route::post('/addOrder', 'oa\businessController@addOrder')->name('addOrder');
// 校验订单编号(淘宝编号(taoBaoId))
Route::post('/checkOrder', 'oa\businessController@checkOrder')->name('checkOrder');
// 上传附件(type(1:总揽附件，2:退款附件)，对应字断数据(json格式))
Route::post('/uploadOrderFile', 'oa\businessController@uploadOrderFile')->name('uploadOrderFile');
// 导出
Route::post('/exportOrder', 'oa\businessController@exportOrder')->name('exportOrder');
// 检索(json格式(shopId, pageSize))
Route::get('/searchOrder', 'oa\businessController@searchOrder')->name('searchOrder');
