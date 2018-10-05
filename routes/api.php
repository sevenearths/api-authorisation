<?php

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// This class needs to be created
use ApiResponse\ApiResponseClass;

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

/*Route::post('/v1', function (Request $request) {

    $response = new ApiResponseClass('',200);
    return $response->badRequest('Key and Secret Required');

}); //->middleware('auth:api');*/

Route::post('/v1', 'AuthorisationController@postAuthorisation')->name('postAuthorisation');