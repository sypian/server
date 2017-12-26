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
    return $router->app->version();
});

$router->post('/category', [
    'as' => 'category', 'uses' => 'CategoryController@createCategory'
]);
$router->get('/category', [
    'as' => 'category', 'uses' => 'CategoryController@getCategory'
]);
$router->put('/category', [
    'as' => 'category', 'uses' => 'CategoryController@updateCategory'
]);
$router->delete('/category', [
    'as' => 'category', 'uses' => 'CategoryController@deleteCategory'
]);

$router->post('project', [
    'as' => 'project', 'uses' => 'ProjectController@createProject'
]);
$router->get('project/{id:[0-9]+}', [
    'as' => 'project', 'uses' => 'ProjectController@getProject'
]);
$router->put('project/{id:[0-9]+}', [
    'as' => 'project', 'uses' => 'ProjectController@updateProject'
]);
$router->delete('project/{id:[0-9]+}', [
    'as' => 'project', 'uses' => 'ProjectController@deleteProject'
]);
