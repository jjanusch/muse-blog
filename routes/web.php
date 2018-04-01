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

Route::get('/{page?}', 'PostController@index')->name('page.posts');
Route::get('/tags/{tag}/{page?}', 'PostController@tag')->name('page.posts/tags/show');
Route::get('/{year}/{month}/{slug}', 'PostController@show')->name('page.posts/show');
Route::get('/page/{slug}', 'PageController@show')->name('page.pages/show');
