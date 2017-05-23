<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {

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


    // Authentication Routes...
    $this->get('login', 'Auth\AuthController@showLoginForm');
    $this->post('login', 'Auth\AuthController@login');
    $this->get('logout', 'Auth\AuthController@logout');

    // Registration Routes...
    if (env('ALLOW_REGISTRATION', false)) {
        $this->get('register', 'Auth\AuthController@showRegistrationForm');
        $this->post('register', 'Auth\AuthController@register');
    }

    // Password Reset Routes...
    $this->get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
    $this->post('password/email', 'Auth\PasswordController@sendResetLinkEmail');
    $this->post('password/reset', 'Auth\PasswordController@reset');
});
