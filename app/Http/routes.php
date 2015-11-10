<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('dashboard', [
    'as'   => 'dashboard',
    'uses' => 'DashboardController@index',
]);


Route::get('resources/performers/popularity/harvest', 'ResourceController@performersPopularity');
Route::get('resources/{resource}', 'ResourceController@index');
Route::get('resources/{resource}/{action}', 'ResourceController@show');
Route::get('resources/{resource}/{action}/harvest', 'ResourceController@harvest');
Route::get('resources/{resource}/{action}/refresh', 'ResourceController@refresh');
Route::get('resources/{resource}/{action}/edit', 'ResourceController@edit');
Route::post('resources/{resource}/{action}/edit', 'ResourceController@store');


// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

/**
 * Registration routes...
 *
 * Un-comment these to create a user
 * then you should re-comment them to prevent others from signing up
 */
//Route::get('auth/register', 'Auth\AuthController@getRegister');
//Route::post('auth/register', 'Auth\AuthController@postRegister');
