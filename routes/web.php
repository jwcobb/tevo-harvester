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
    $this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
    $this->post('login', 'Auth\LoginController@login');
    $this->post('logout', 'Auth\LoginController@logout')->name('logout');

    // Registration Routes...
    if (env('ALLOW_REGISTRATION', false)) {
        $this->get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
        $this->post('register', 'Auth\RegisterController@register');
    }

    // Password Reset Routes...
    $this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    $this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    $this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    $this->post('password/reset', 'Auth\ResetPasswordController@reset');
});
