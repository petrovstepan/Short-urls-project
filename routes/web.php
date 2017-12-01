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

Route::get('/', function () {
    return Auth::check() ? redirect()->route('users.me') : redirect()->route('users.create');
});

Route::get('/register', function () {
    return Auth::check() ? redirect()->route('users.me') : redirect()->route('users.create');
});

Route::get('/start', function () {
    return Auth::check() ? redirect()->route('users.me') : null;
})->middleware('auth.basic')->name('start');



Route::get('/api/v1/shorten_urls/{hash}', 'ShortenUrlController@redirectHash')->name('url.hash');
Route::resource('api/v1/users', 'UserController', ['only' => ['create', 'store']]);


Route::group(['prefix' => '/api/v1/users/me', 'middleware' => 'auth.basic'], function () {
    Route::get('/', 'UserController@showMe')->name('users.me');
    Route::get('/shorten_urls/{id}/referers', 'ShortenUrlController@urlReferers')->name('url.referers');
    Route::get('/shorten_urls/{id}/{sort}', 'ShortenUrlController@urlReport')->name('url.stats');
    Route::resource('/shorten_urls', 'ShortenUrlController', ['except' => ['edit', 'update']]);

});
