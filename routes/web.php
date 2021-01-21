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

$router->group(['prefix' => 'users/{userID}/boxes'], function () use ($router) {
    $router->get('/', 'UserBoxController@index');
    $router->post('/', 'UserBoxController@store');
    $router->get('/{boxID}', 'UserBoxController@show');
    $router->put('/{boxID}', 'UserBoxController@update');
    $router->delete('/{boxID}', 'UserBoxController@destroy');
});

$router->group(['prefix' => 'boxes/{boxID}/cards'], function () use ($router) {
    $router->get('/', 'BoxCardController@index');
    $router->post('/', 'BoxCardController@store');
    $router->get('/{cardID}', 'BoxCardController@show');
    $router->put('/{cardID}', 'BoxCardController@update');
    $router->delete('/{cardID}', 'BoxCardController@destroy');
});

$router->group(['prefix' => 'boxes/{boxID}/session'], function () use ($router) {
    $router->post('/create', 'SessionController@create');
    $router->post('/start', 'SessionController@start');
    
    $router->get('/cards/next', 'SessionCardController@next');
    $router->post('/cards/{cardID}/review', 'SessionCardController@review');
});
