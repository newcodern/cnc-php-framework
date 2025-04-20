<?php
use Worker\Router;


// Example
// Router::get('/about', 'AboutController@index');
// Router::post('/contact', 'ContactController@store');

Router::get('/', 'MainController@index');

// Dispatch the current request
Router::dispatch();