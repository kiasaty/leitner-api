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
    $router->get('/{id}', 'BoxController@show');
});

$router->group(['prefix' => 'users/{user_id}/boxes'], function () use ($router) {
    $router->get('/', 'UserBoxController@index');
    $router->post('/', 'UserBoxController@store');
    $router->get('/{id}', 'UserBoxController@show');
    $router->put('/{id}', 'UserBoxController@update');
    $router->delete('/{id}', 'UserBoxController@destroy');
});

$router->group(['prefix' => 'boxes/{box_id}/cards'], function () use ($router) {
    $router->get('/', 'BoxCardController@index');
    $router->post('/', 'BoxCardController@store');
    $router->get('/{id}', 'BoxCardController@show');
    $router->put('/{id}', 'BoxCardController@update');
    $router->delete('/{id}', 'BoxCardController@destroy');
});

$router->group(['prefix' => 'session'], function () use ($router) {
    $router->get('/start', 'SessionController@start');
    $router->get('/next', 'SessionController@next');
    $router->get('/review', 'SessionController@review');
});