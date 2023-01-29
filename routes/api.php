<?php

use App\Http\Controllers\BoxCardController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\SessionCardController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserBoxController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$router->get('/', function () use ($router) {
    return 'Memorize anything leitner way!';
});

$router->post('/login', [AuthController::class, 'authenticate'])->name('login');

$router->group(['prefix' => 'users'], function () use ($router) {
    $router->get('/', [UserController::class, 'index']);
    $router->post('/', [UserController::class, 'store']);
    $router->get('/{id}', [UserController::class, 'show']);
    $router->put('/{id}', [UserController::class, 'update']);
    $router->delete('/{id}', [UserController::class, 'destroy']);
});

$router->group(['prefix' => 'boxes'], function () use ($router) {
    $router->get('/', [BoxController::class, 'index']);
    $router->get('/{id}', [BoxController::class, 'show']);
});

$router->group(['prefix' => 'users/{userID}/boxes'], function () use ($router) {
    $router->get('/', [UserBoxController::class, 'index']);
    $router->post('/', [UserBoxController::class, 'store']);
    $router->get('/{boxID}', [UserBoxController::class, 'show']);
    $router->put('/{boxID}', [UserBoxController::class, 'update']);
    $router->delete('/{boxID}', [UserBoxController::class, 'destroy']);
});

$router->group(['prefix' => 'boxes/{boxID}/cards'], function () use ($router) {
    $router->get('/', [BoxCardController::class, 'index']);
    $router->post('/', [BoxCardController::class, 'store']);
    $router->get('/{cardID}', [BoxCardController::class, 'show']);
    $router->put('/{cardID}', [BoxCardController::class, 'update']);
    $router->delete('/{cardID}', [BoxCardController::class, 'destroy']);
});

$router->group(['prefix' => 'boxes/{boxID}/session'], function () use ($router) {
    $router->post('/create', [SessionController::class, 'create']);
    $router->post('/start', [SessionController::class, 'start']);
    
    $router->get('/cards/next', [SessionCardController::class, 'next']);
    $router->post('/cards/{cardID}/review', [SessionCardController::class, 'review']);
});
