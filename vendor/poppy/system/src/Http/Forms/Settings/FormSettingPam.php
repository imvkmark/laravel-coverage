<?php

namespace Poppy\System\Http\Forms\Settings;

use Poppy\Framework\Validation\Rule;
use Poppy\System\Action\Sso;

class FormSettingPam extends FormSettingBase
{

    protected $title = 'Pam设置';

    protected $group = 'py-system::pam';

    public function form()
    {
        $this->text('prefix', '账号前缀')->rules([
            Rule::required(),
        ])->placeholder('请输入账号前缀, 用于账号注册默认用户名生成');
        $this->textarea('test_account', '测试账号')->placeholder('请填写测试账号, 每行一个')->help('在此测试账号内的应用, 不需要正确的验证码即可登录');

        $this->divider('单点登录设定');
        $this->radio('sso_type', '单点登录类型')->options(Sso::kvType())->stacked()->rules([
            Rule::required(),
        ])->help('设备组设定为 app(android/ios), web(h5/webapp/mp[小程序]), pc(mac/linux/win)');
        $this->text('sso_device_num', '最大设备数量')->help('启用多端登录时候允许的最大设备数量, 没有配置则默认最大数量为10')->rules([
            Rule::max(10), Rule::required(), Rule::numeric(),
        ]);
    }
}
