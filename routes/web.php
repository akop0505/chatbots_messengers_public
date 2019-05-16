<?php


Route::get('/', function () {
    return view('welcome');
});

Route::get('/widget-admin', function () {
    return view('widget_admin');
});

Route::get('/widget-executor', function () {
    return view('widget_executor');
});
