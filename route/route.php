<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::header('Access-Control-Allow-Credentials', 'true')->allowCrossDomain();

Route::get('content/:id', 'index/index/content');
Route::rule('cats/:id', 'index/index/cats');
Route::rule('photos/:id','index/gallery/photos');
Route::rule('gallery','index/gallery/index');
Route::rule('index2','index/index/index2');
Route::rule('vip','index/index/vip');
Route::group('api', function () {    
    Route::post('postcomment', 'index/api/postcomment');
    Route::post('getcomments', 'index/api/getcomments');
    Route::post('getarticles', 'index/api/getarticles');
    Route::post('getimages', 'index/api/getimages');
    Route::post('gettopten', 'index/api/gettopten');
    Route::rule('gettoken','index/api/gettoken');
});

Route::group('admin', function () {    
    Route::get('test', 'admin/Spider/test');
});