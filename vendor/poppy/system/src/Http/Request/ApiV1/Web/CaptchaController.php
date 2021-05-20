<?php

namespace Poppy\System\Http\Request\ApiV1\Web;

use Poppy\Framework\Classes\Resp;
use Poppy\System\Action\Verification;
use Poppy\System\Events\CaptchaSendEvent;
use Poppy\System\Events\PassportVerifyEvent;
use Throwable;

/**
 * 验证码控制器
 */
class CaptchaController extends WebApiController
{

    /**
     * @api              {post} api_v1/system/captcha/send [Sys]发送验证码
     * @apiVersion       1.0.0
     * @apiName          SysCaptchaSend
     * @apiGroup         Poppy
     *
     * @apiParam {string}  passport       通行证
     * @apiParam {string}  [type]         exist : 如果给已经存在的发送验证码, 需要传值
     */
    public function send()
    {
        $input    = input();
        $passport = sys_get($input, 'passport');
        $type     = sys_get($input, 'type');

        try {
            event(new PassportVerifyEvent($passport, $type));
        } catch (Throwable $e) {
            return Resp::error($e);
        }

        $Verification = new Verification();
        if ($Verification->genCaptcha($passport)) {

            $captcha = $Verification->getCaptcha();

            try {
                event(new CaptchaSendEvent($passport, $captcha));
                return Resp::success('验证码发送成功' . (!is_production() ? ', 验证码:' . $captcha : ''));
            } catch (Throwable $e) {
                return Resp::error($e);
            }
        }
        else {
            return Resp::error($Verification->getError());
        }
    }

    /**
     * @api              {post} api_v1/system/captcha/fetch [Sys][L]获取验证码
     * @apiVersion       1.0.0
     * @apiName          SysCaptchaFetch
     * @apiGroup         Poppy
     *
     * @apiParam {int}   passport            通行证
     */
    public function fetch()
    {
        if (is_production()) {
            return Resp::error('Prod 环境不返回数据');
        }
        $passport = input('passport');

        $Verification = new Verification();
        if ($Verification->fetchCaptcha($passport)) {
            $captcha = $Verification->getCaptcha();
            return Resp::success('获取验证码成功', [
                'captcha' => $captcha,
            ]);
        }
        else {
            return Resp::error($Verification->getError());
        }
    }

    /**
     * @api              {post} api_v1/system/captcha/verify_code [Sys]获取验证串
     * @apiDescription   用以保存 passport 验证的验证串, 隐藏字串为 passport
     * @apiVersion       1.0.0
     * @apiName          SysCaptchaVerifyCode
     * @apiGroup         Poppy
     *
     * @apiParam {string}   passport           通行证
     * @apiParam {string}   captcha            验证码
     * @apiParam {string}   [expire_min]       验证串有效期[默认:10 分钟, 最长不超过 60 分钟]
     */
    public function verifyCode()
    {
        $passport   = (string) input('passport');
        $captcha    = (string) input('captcha');
        $expire_min = (int) input('expire_min', 10);
        if ($expire_min > 60) {
            $expire_min = 60;
        }
        if ($expire_min < 1) {
            $expire_min = 1;
        }

        $Verification = new Verification();
        if (!$Verification->checkCaptcha($passport, $captcha)) {
            return Resp::error($Verification->getError());
        }
        $onceCode = $Verification->genOnceVerifyCode($expire_min, $passport);
        return Resp::success('生成验证串', [
            'verify_code' => $onceCode,
        ]);
    }
}