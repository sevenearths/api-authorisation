<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::post('/', function() { return redirect('api/v1'); });

Route::get('/admin', 'LoginController@login')->name('login');
Route::post('/admin', 'LoginController@loginPost')->name('loginPost');
Route::get('/admin/logout', 'LoginController@logout')->name('logout');

Route::group(['prefix' => 'admin/panel', 'middleware' => ['userLoggedInCheck']], function () {
    Route::get('', 'AdminController@home')->name('home');
});

Route::group(['prefix' => 'admin/panel/ajax', 'middleware' => ['userLoggedInAjaxCheck']], function () {

        // Keys

    Route::get('key', 'KeysController@getAllKeys')->name('getAllKeys');
    Route::post('key', 'KeysController@postAddNewKey')->name('postAddNewKey');
    Route::get('key/{id}', 'KeysController@getKey')->name('getKey');
    Route::delete('key/{id?}', 'KeysController@deleteKey')->name('deleteKey');
    Route::patch('key/secret/{id?}', 'KeysController@patchUpdateSecretKey')->name('patchUpdateSecretKey');
    Route::patch('key/{id?}', 'KeysController@patchUpdateKey')->name('patchUpdateKey');

        // Groups

    Route::get('group', 'GroupsController@getAllGroups')->name('getAllGroups');
    Route::post('group', 'GroupsController@postAddNewGroup')->name('postAddNewGroup');
    Route::get('group/{id}', 'GroupsController@getGroup')->name('getGroup');
    Route::get('group/{id}/user', 'GroupsController@getGroupUsers')->name('getGroupUsers');
    Route::get('group/{id}/user/available', 'GroupsController@getNotGroupUsers')->name('getNotGroupUsers');
    Route::post('group/{id}/user', 'GroupsController@postAddUserToGroup')->name('postAddUserToGroup');
    Route::delete('group/{id}/user/{user_id}', 'GroupsController@deleteRemoveUserFromGroup')->name('deleteRemoveUserFromGroup');
    Route::delete('group/{id?}', 'GroupsController@deleteGroup')->name('deleteGroup');
    Route::patch('group/secret/{id?}', 'GroupsController@patchUpdateSecretGroup')->name('patchUpdateSecretGroup');
    Route::patch('group/{id?}', 'GroupsController@patchUpdateGroup')->name('patchUpdateGroup');

        // Users

    Route::get('user', 'UsersController@getAllUsers')->name('getAllUsers');
    Route::post('user', 'UsersController@postAddNewUser')->name('postAddNewUser');
    Route::get('user/{id}', 'UsersController@getUser')->name('getUser');
    Route::delete('user/{id?}', 'UsersController@deleteUser')->name('deleteUser');
    Route::patch('user/secret/{id?}', 'UsersController@patchUpdateSecretUser')->name('patchUpdateSecretUser');
    Route::patch('user/{id?}', 'UsersController@patchUpdateUser')->name('patchUpdateUser');

        // Urls

    Route::get('url', 'UrlsController@getAllUrls')->name('getAllUrls');
    Route::post('url', 'UrlsController@postAddNewUrl')->name('postAddNewUrl');
    Route::get('url/group/{group_id}', 'UrlsController@getAllUrlsForGroup')->name('getAllUrlsForGroup');
    Route::patch('url/order/{group_id}', 'UrlsController@patchReorderUrls')->name('patchReorderUrls');
    Route::get('url/{id}', 'UrlsController@getUrl')->name('getUrl');
    Route::delete('url/{id?}', 'UrlsController@deleteUrl')->name('deleteUrl');
    Route::patch('url/{id?}', 'UrlsController@patchUpdateUrl')->name('patchUpdateUrl');

});