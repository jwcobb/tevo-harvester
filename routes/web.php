<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('dashboard', function () {
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

    /**
     * Load the Authentication routes.
     * Use an .env var to decide if registration is allowed
     */
    Auth::routes(['register' => env('ALLOW_REGISTRATION', false)]);

    Route::get('home', [
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


});
