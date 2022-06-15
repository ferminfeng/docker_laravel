<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

Route::group([
    'middleware' => [
    ]
], function (Router $router) {

    $router->group(['prefix' => '/'], function ($router) {
        $router->get('/', 'TestController@index');

        $router->get('/testMysql', 'TestController@testMysql');

        $router->get('/testRedis', 'TestController@testRedis');

        $router->get('/testMongo', 'TestController@testMongo');

        $router->get('/sendQueue', 'TestController@sendQueue');
    });


});
