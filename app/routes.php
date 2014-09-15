<?php

Route::get('/','CRUDController@index');
Route::get('/crud/create','CRUDController@create');
Route::post('/crud/create','CRUDController@store');
Route::get('/crud/delete/{id}','CRUDController@delete');
Route::get('/crud/edit/{id}','CRUDController@edit');
Route::post('/crud/update/{id}','CRUDController@update');
Route::get('/crud/all','CRUDController@index');

Route::get('/table/{table_name}/settings','TablesController@settings');
Route::post('/table/{table_name}/settings','TablesController@postSettings');
Route::get('/table/{table_name}/create','TablesController@create');
Route::post('/table/{table_name}/create','TablesController@store');
Route::get('/table/{table_name}/list','TablesController@all');
Route::get('/table/{table_name}/delete/{id}','TablesController@delete');
Route::get('/table/{table_name}/edit/{id}','TablesController@edit');
Route::post('/table/{table_name}/update/{id}','TablesController@update');

Route::get('/users/create','UsersController@showCreate');
Route::post('/users/create','UsersController@postCreate');

Route::get('/settings/users_table','SettingsController@showUserTable');
Route::post('/settings/users_table','SettingsController@postUserTable');

Route::get('/settings/find_table/{name}','SettingsController@findTable');