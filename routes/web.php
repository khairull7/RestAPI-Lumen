<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
    return $router->app->version();
});

$router->group(['middleware' => 'cors'], function ($router) {
    $router->post('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout');
    $router->get('/profile', 'AuthController@me');
// $router->post('/lendings/store', 'LendingController@store');
// $router->get('/lendings', 'LendingController@index');



// $router->get('/', function () use ($router) {

    // $router->post('/login', 'UserController@login');
    // $router->get('/logout', 'UserController@logout');

// }); 

// stuff
// struktur: $router->method('/path', 'NamaController@namaFunction');
$router->group(['prefix' => 'stuffs'], function() use ($router){
    // static routes
    $router->get('/data', 'StuffController@index');
    $router->post('/store', 'StuffController@store');
    $router->get('/trash', 'StuffController@trash');

    // dynamic routes
    $router->get('{id}', 'StuffController@show');
    $router->patch('/update/{id}', 'StuffController@update');
    $router->delete('/delete/{id}', 'StuffController@destroy');
    $router->get('/restore/{id}', 'StuffController@restore');
    $router->delete('/permanent/{id}', 'StuffController@deletePermanent');
    
});

$router->group(['prefix' => 'user'], function() use ($router){

    $router->get('/data', 'UserController@index');
    $router->post('/store', 'UserController@store');
    $router->get('/trash', 'UserController@trash');

    $router->get('{id}', 'UserController@show');
    $router->patch('/update/{id}', 'UserController@update');
    $router->delete('/delete/{id}', 'UserController@destroy');
    $router->get('/restore/{id}', 'UserController@restore');
    $router->delete('/permanent/{id}', 'UserController@deletePermanent');


});

$router->group(['prefix' => 'inbound-stuffs', 'middleware' => 'auth'], function() use ($router){

    $router->get('/data', 'InboundStuffController@index');
    $router->post('/store', 'InboundStuffController@store');
    $router->get('detail/{id}', 'InboundStuffController@show');
    $router->patch('update/{id}', 'InboundStuffController@update');
    $router->delete('delete/{id}', 'InboundStuffController@destroy');
    $router->get('/trash', 'InboundStuffController@trash');
    $router->get('/restore/{id}', 'InboundStuffController@restore');
    $router->delete('permanent/{id}', 'InboundStuffController@deletePermanent');

});

$router->group(['prefix' => 'stuff-stock', 'middleware' => 'auth'], function() use ($router){
    $router->get('/data', 'StuffStockController@index');
    $router->post('/store', 'StuffStockController@store');
    $router->get('detail/{id}', 'StuffStockController@show');
    $router->patch('update/{id}', 'StuffStockController@update');
    $router->delete('delete/{id}', 'StuffStockController@destroy');
    $router->get('trash', 'StuffStockController@trash');
    $router->get('/restore/{id}', 'StuffStockController@restore');
    $router->delete('/permanent/{id}', 'StuffStockController@deletePermanent');
    $router->post('add-stock/{id}', 'StuffStockController@addStock');
    $router->post('sub-stock/{id}', 'StuffStockController@subStock');

});

$router->group(['prefix' => 'lending'], function() use ($router){
    $router->get('/data', 'LendingController@index');
    $router->post('/store', 'LendingController@store');
    $router->get('/detail/{id}', 'LendingController@show');
    $router->patch('/update/{id}', 'LendingController@update');
    $router->delete('/delete/{id}', 'LendingController@destroy');
    $router->get('/trash', 'LendingController@trash');
    $router->get('/restore/{id}', 'LendingController@restore');
    $router->delete('/permanent/{id}', 'LendingController@deletePermanent');

});

$router->group(['prefix' => 'restoration'], function() use ($router){
    $router->get('/data', 'RestorationController@index');
    $router->post('/store', 'RestorationController@store');
});


});


