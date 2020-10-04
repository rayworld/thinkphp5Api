<?php

//绑定二级域名
Route::domain('api','api');

Route::rule(':version/:controller', 'api/:version.:controller/index');
Route::rule(':version/:controller/:action', 'api/:version.:controller/:action');