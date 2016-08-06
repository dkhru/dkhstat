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

   Route::get(
      '/', function () {
      return view('welcome');
   });
   Route::get(
      '/page/{pg}', function () {
      return view('page');
   });

   Route::auth();

   Route::group(
      [ 'middleware'=>'auth', 'namespace'=>'Admin' ], function () {
      Route::get('/admin/stat/', 'StatController@index');
      Route::get('/admin/stat/{pg_id}', 'StatController@index');
   });

