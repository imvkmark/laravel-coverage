<?php
/*
|--------------------------------------------------------------------------
| Demo
|--------------------------------------------------------------------------
|
*/
Route::group([
    'namespace' => 'Site\Http\Request\Web',
], function (Illuminate\Routing\Router $route) {
    // 推广图片
    $route->get('site/show', 'SpreadImgController@show');
    $route->any('site/yun_kf', 'SpreadImgController@yunkf');
});

Route::group([
    'middleware' => ['cross'],
    'namespace'  => 'Site\Http\Request\Web',
], function (Illuminate\Routing\Router $route) {
    $route->get('/', 'HomeController@homePage')
        ->name('site:web.home.homepage');

    $route->get('site/game_server_list', 'HomeController@getGameServerListByAreaId')
        ->name('site:web.site.game_server_list');

    $route->get('site/get_dan_type', 'HomeController@getDanType')
        ->name('site:web.site.get_dan_type');

    $route->post('site/calculate', 'HomeController@calculate')
        ->name('site:web.site.calculate');

    $route->get('site/count_up', 'HomeController@countUp')
        ->name('site:web.site.count_up');

    $route->get('site/test', 'TestController@index')
        ->name('site:web.test.index');

    $route->get('test/socket/{uid?}', 'TestController@socket')
        ->name('site:web.test.socket');

    $route->get('test/zhima/{uid?}', 'TestController@zhima')
        ->name('site:web.test.zhima');

    $route->get('test/ali_push/{account_id}', 'TestController@aliPush')
        ->name('site:web.test.ali_push');

    $route->get('test/clear_hunter', 'TestController@clearHunter')
        ->name('site:web.test.clear_hunter');

    $route->get('test/close_client', 'TestController@closeClient')
        ->name('site:web.test.close_client');

    $route->any('test/qrcode', 'TestController@qrcode');
    // 订单标题解析
    $route->any('test/parse_order', 'TestController@parseOrderTitle');

    $route->get('home/cp', 'HomeController@cp')
        ->name('site:web.home.cp');
    //处理跳转页面
    $route->any('invite/{code}', 'HomeController@invite')
        ->name('site:web.home.invite');


    // 帮助
    $route->any('help/order_great_title', 'HelpController@orderGreatTitle')
        ->name('site:web.help.order_great_title');
    $route->any('help/feedback', 'HelpController@feedback')
        ->name('site:web.help.feedback');
    $route->get('help/about_us', 'HelpController@aboutUs')
        ->name('site:web.help.about_us');
    $route->get('help/contact_us', 'HelpController@contactUs')
        ->name('site:web.help.contact_us');
    $route->get('help/private', 'HelpController@privateRule')
        ->name('site:web.help.private');
    $route->get('help/rule', 'HelpController@rule')
        ->name('site:web.help.rule');
    $route->get('help/show/{id}', 'HelpController@show')
        ->name('site:web.help.show');
    $route->get('help', 'HelpController@index')
        ->name('site:web.help.index');
    $route->get('help/app_download', 'HelpController@appDownload')
        ->name('site:web.help.app_download');

    /* 活动
     * ---------------------------------------- */
    $route->any('mission', 'MissionController@index')
        ->name('site:web.activity.index');
    $route->any('mission/application', 'MissionController@application')
        ->name('site:web.activity.application');
    $route->any('mission/apply/{id?}', 'MissionController@apply')
        ->name('site:web.activity.apply');
    $route->any('mission/show/{item?}', 'MissionController@show')
        ->name('site:web.activity.show');
    $route->any('mission/game_activity', 'MissionController@gameActivity')
        ->name('site:web.activity.game');

    /* 活动页面
     * ---------------------------------------- */
    $route->any('activity/show/{html?}', 'ActivityController@show')
        ->name('site:web.activity.show');
    $route->any('activity/2019.s9.html', 'ActivityController@s9At2019')
        ->name('site:web.activity.2019_s9');
    $route->any('activity/20190416.html', 'ActivityController@s9At20190416')
        ->name('site:web.activity.20190416');
    $route->any('activity/welfare.html', 'ActivityController@welfare')
        ->name('site:web.activity.welfare');
    $route->any('activity/welfare_new.html', 'ActivityController@welfare_new')
        ->name('site:web.activity.welfare_new');
    $route->any('activity/welfare_new_10.html', 'ActivityController@welfare_new_10')
        ->name('site:web.activity.welfare_new_10');
    $route->any('activity/welfare_2020_wz.html', 'ActivityController@welfare_2020_09_24_wz')
        ->name('site:web.activity.welfare_2020_09_24_wz');
    $route->any('activity/welfare_2020_lol.html', 'ActivityController@welfare_2020_09_24_lol')
        ->name('site:web.activity.welfare_2020_09_24_lol');
    // validation
    $route->any('site_validate/answer_repeat', 'ValidateController@answerRepeat')
        ->name('site:web.site_validate.answer_repeat');
    $route->any('site_validate/payword', 'ValidateController@payword')
        ->name('site:web.site_validate.payword');
    $route->any('site_validate/money_enough/{order_id?}', 'ValidateController@moneyEnough')
        ->name('site:web.site_validate.money_enough');

    /* app
     * ---------------------------------------- */
    $route->get('app/app.html', 'AppController@appHtml')
        ->name('site:web.app.app');
    $route->get('app/invite', 'AppController@invite')
        ->name('site:web.app.invite');
    $route->any('app/help', 'AppUrlController@help')
        ->name('site:web.app.help');
    // 更多帮助信息页面
    $route->get('app/more', 'AppController@helpMore')
        ->name('site:web.app.help_more');
    $route->get('app/about_us', 'AppController@aboutUs')
        ->name('site:web.app.about_us');
    // 微信提现
    $route->get('app/wx_withdraw', 'AppController@wxWithdraw');
    $route->get('app/rule', 'AppUrlController@rule')
        ->name('site:web.app.rule');
    $route->get('app/rule_new', 'AppUrlController@rule_new')
        ->name('site:web.app.rule_new');
    $route->get('app/activity_rule', 'AppController@activityRule')
        ->name('site:web.app.activity_rule');
    $route->get('app/activity_show/{type}', 'AppController@activityImg')
        ->name('site:web.app.activity_img');
    $route->get('app/transfer', 'AppController@transfer')
        ->name('site:web.app.transfer');
    $route->get('app/s/{key}', 'AppController@share')
        ->name('site:web.app.share');
    $route->get('app/zhima/{platform?}/{hash_id?}', 'AppController@zhima')
        ->name('site:web.app.zhima');

    //活动报名
    $route->any('missionSignUp/signUp', 'MissionSignUpController@signUp')
        ->name('site:web.mission_sign_up.sign_up');

    //app服务条款
    $route->get('tips', function () {
        return view('site::web.tips');
    });
});

Route::group([
    'middleware' => ['cross'],
    'namespace'  => 'Site\Http\Request\Support',
], function (Illuminate\Routing\Router $route) {
    /* support
     * ---------------------------------------- */

    // $route->get('support_game/server_html', 'GameController@serverHtml')
    //     ->name('site:web.support_game.server_html');

    $route->get('support_game/type_html', 'GameController@typeHtml')
        ->name('site:web.support_game.type_html');

    $route->any('support_util/send_email_code', 'UtilController@sendEmailCode')
        ->name('site:web.support_util.send_email_code');
    $route->any('support_util/send_mobile_code', 'UtilController@sendMobileCode')
        ->name('site:web.support_util.send_mobile_code');
    $route->post('support_util/rebuild_send_mobile_code', 'UtilController@rebuildSendMobileCode')
        ->name('site:web.support_util.rebuild_send_mobile_code');
    $route->any('support_util/check_email_code_validate', 'UtilController@checkEmailCodeValidate')
        ->name('site:web.support_util.check_email_code_validate');
    $route->any('support_util/check_mobile_code_validate', 'UtilController@checkMobileCodeValidate')
        ->name('site:web.support_util.check_mobile_code_validate');
    $route->get('support_util/qrcode', 'UtilController@qrcode')
        ->name('site:web.support_util.qrcode');

    $route->post('support_upload/image', 'UploadController@image')
        ->name('site:web.support_upload.image');

    $route->post('support_upload/file', 'UploadController@file')
        ->name('site:web.support_upload.file');

    $route->any('support_validate/account_name_available', 'ValidateController@accountNameAvailable')
        ->name('site:web.support_validate.account_name_available');
    $route->any('support_validate/account_name_exists', 'ValidateController@accountNameExists')
        ->name('site:web.support_validate.account_name_exists');
    $route->post('support_validate/mobile_code_valid', 'ValidateController@mobileCodeValid')
        ->name('site:web.support_validate.mobile_code_valid');
    $route->post('support_validate/game_name_available', 'ValidateController@gameNameAvailable')
        ->name('site:web.support_validate.game_name_available');
    $route->post('support_validate/allow_ip_available', 'ValidateController@allowIpAvailable')
        ->name('site:web.support_validate.allow_ip_available');

});