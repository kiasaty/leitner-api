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

$router->group(['prefix' => 'users/{user_id}/boxes'], function () use ($router) {
    $router->get('/', 'UserBoxController@index');
    $router->post('/', 'UserBoxController@store');
    $router->get('/{id}', 'UserBoxController@show');
    $router->put('/{id}', 'UserBoxController@update');
    $router->delete('/{id}', 'UserBoxController@destroy');
});

$router->group(['prefix' => 'boxes/{box_id}/cards'], function () use ($router) {
    $router->get('/', 'CardController@index');
    $router->post('/', 'CardController@store');
    $router->get('/{id}', 'CardController@show');
    $router->put('/{id}', 'CardController@update');
    $router->delete('/{id}', 'CardController@destroy');
});