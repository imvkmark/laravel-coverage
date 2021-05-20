<?php
/*
|--------------------------------------------------------------------------
| Backend Demo, 这里调用的是为后台进行服务的, 也就是管理界面
|--------------------------------------------------------------------------
|
*/
Route::group([
    'namespace'  => 'DummyNamespace\Http\Request\Backend',
], function (Illuminate\Routing\Router $route) {
    $route->get('/', 'DemoController@index');
});