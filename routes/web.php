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

/**
 * Load the Authentication routes.
 * Use an .env var to decide if registration is allowed
 */
Auth::routes(['register' => config('app.allow_registration', false)]);

Route::get('home', [
    'as'   => 'dashboard',
    'uses' => 'DashboardController@index',
]);
Route::redirect('/', route('dashboard'), 301);
Route::redirect('dashboard', route('dashboard'), 301);


Route::get('resources/performers/popularity/harvest', 'ResourceController@performersPopularity');
Route::get('resources/{resource}', 'ResourceController@index');
Route::get('resources/{resource}/{action}', 'ResourceController@show');
Route::get('resources/{resource}/{action}/harvest', 'ResourceController@harvest');
Route::get('resources/{resource}/{action}/refresh', 'ResourceController@refresh');
Route::get('resources/{resource}/{action}/edit', 'ResourceController@edit');
Route::post('resources/{resource}/{action}/edit', 'ResourceController@store');


