<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'Memorize anything leitner way!';
});

$router->post('/login', 'AuthController@authenticate');

$router->group(['prefix' => 'users'], function () use ($router) {
    $router->get('/', 'UserController@index');
    $router->post('/', 'UserController@store');
    $router->get('/{id}', 'UserController@show');
    $router->put('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@destroy');
});

$router->group(['prefix' => 'boxes'], function () use ($router) {
    $router->get('/', 'BoxController@index');
    $router->post('/', 'BoxController@store');
    $router->get('/{id}', 'BoxController@show');
    $router->put('/{id}', 'BoxController@update');
    $router->delete('/{id}', 'BoxController@destroy');
});