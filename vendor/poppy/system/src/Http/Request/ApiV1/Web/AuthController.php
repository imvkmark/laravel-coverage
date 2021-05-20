<?php

namespace Poppy\System\Http\Request\ApiV1\Web;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Classes\Traits\PoppyTrait;
use Poppy\Framework\Helper\UtilHelper;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Action\Pam;
use Poppy\System\Action\Verification;
use Poppy\System\Events\LoginTokenPassedEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\Resources\PamResource;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;

/**
 * 认证控制器
 */
class AuthController extends WebApiController
{
    use PoppyTrait, ThrottlesLogins;

    /**
     * @api              {post} api_v1/system/auth/access [Sys]检测 Token
     * @apiVersion       1.0.0
     * @apiName          SysAuthAccess
     * @apiGroup         Poppy
     *
     * @apiParam {int}   token            Token
     *
     * @apiSuccess {int}      id              ID
     * @apiSuccess {string}   username        用户名
     * @apiSuccess {string}   mobile          手机号
     * @apiSuccess {string}   email           邮箱
     * @apiSuccess {string}   type            类型
     * @apiSuccess {string}   is_enable       是否启用[Y|N]
     * @apiSuccess {string}   disable_reason  禁用原因
     * @apiSuccess {string}   created_at      创建时间
     * @apiSuccessExample {json} data:
     * {
     *     "id": 9,
     *     "username": "user001",
     *     "mobile": "",
     *     "email": "",
     *     "type": "user",
     *     "is_enable": "Y",
     *     "disable_reason": "",
     *     "created_at": "2021-03-18 15:30:15",
     *     "updated_at": "2021-03-18 16:38:06"
     * }
     */
    public function access(): JsonResponse
    {
        /** @var ResponseFactory $response */
        $response = app(ResponseFactory::class);
        try {
            if (!$user = app('tymon.jwt.auth')->parseToken()->authenticate()) {
                return $response->json([
                    'message' => '登录失效，请重新登录！',
                    'status'  => 401,
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }
        } catch (JWTException $e) {
            return $response->json([
                'message' => 'Token 错误',
                'status'  => 401,
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }

        return Resp::success(
            '有效登录',
            (new PamResource($user))->toArray(app('request'))
        );
    }

    /**
     * @api                    {post} api_v1/system/auth/login [Sys]登录/注册
     * @apiVersion             1.0.0
     * @apiName                SysAuthLogin
     * @apiGroup               Poppy
     * @apiParam {string}      guard           登录类型;web|Web;backend|后台;
     * @apiParam {string}      passport        通行证
     * @apiParam {string}      [password]      密码
     * @apiParam {string}      [captcha]       验证码
     * @apiParam {string}      [device_id]     设备ID[开启单一登录之后可用]
     * @apiParam {string}      [device_type]   设备类型[开启单一登录之后可用]
     * @apiSuccess {string}    token           认证成功的Token
     * @apiSuccess {string}    type            账号类型
     * @apiSuccessExample      data
     * {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.*******",
     *     "type": "backend"
     * }
     */
    public function login(): JsonResponse
    {
        $validator = Validator::make($this->pyRequest()->all(), [
            'passport' => Rule::required(),
        ], [
            'passport.required' => '通行证必须填写',
        ]);
        if ($validator->fails()) {
            return Resp::error($validator->messages());
        }

        $passport = PamAccount::fullFilledPassport(input('passport', ''));
        $captcha  = input('captcha', '');
        $password = input('password', '');
        $platform = input('platform', '');

        if (!$captcha && !$password) {
            return Resp::error('登录密码或者验证码必须填写');
        }

        /** @var ResponseFactory $response */
        $response = app(ResponseFactory::class);
        if ($this->hasTooManyLoginAttempts($this->pyRequest())) {
            $seconds = $this->limiter()->availableIn($this->throttleKey($this->pyRequest()));
            $message = $this->pyTranslator()->get('auth.throttle', ['seconds' => $seconds]);

            return $response->json([
                'message' => $message,
                'status'  => 401,
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $Pam = new Pam();
        if ($captcha) {
            if (!$Pam->captchaLogin($passport, $captcha, $platform)) {
                return Resp::error($Pam->getError());
            }
        }
        elseif (!$Pam->loginCheck($passport, $password, PamAccount::GUARD_JWT)) {
            return Resp::error($Pam->getError());
        }

        $this->clearLoginAttempts($this->pyRequest());
        $pam = $Pam->getPam();

        if (!$token = app('tymon.jwt.auth')->fromUser($pam)) {
            return Resp::error('获取 Token 失败, 请联系管理员');
        }

        /* 设备单一性登陆验证(基于 Redis + Db)
         * ---------------------------------------- */
        try {
            $deviceId   = $this->pyRequest()->header('X-APP-ID') ?: input('device_id', '');
            $deviceType = $this->pyRequest()->header('X-APP-OS') ?: input('device_type', '');
            event(new LoginTokenPassedEvent($pam, $token, $deviceId, $deviceType));
        } catch (Throwable $e) {
            return Resp::error($e->getMessage());
        }


        return Resp::success('登录成功', [
            'token' => $token,
            'type'  => $pam->type,
        ]);
    }


    /**
     * @api                    {post} api_v1/system/auth/reset_password [Sys]重设密码
     * @apiVersion             1.0.0
     * @apiName                SysAuthResetPassword
     * @apiGroup               Poppy
     * @apiParam {string}      [verify_code]     方式1: 通过验证码获取到-> 验证串
     * @apiParam {string}      [passport]        方式2: 手机号 + 验证码直接验证并修改
     * @apiParam {string}      [captcha]         验证码
     * @apiParam {string}      password          密码
     */
    public function resetPassword()
    {
        $verify_code = input('verify_code', '');
        $password    = input('password', '');
        $passport    = input('passport', '');
        $captcha     = input('captcha', '');

        $Verification = new Verification();
        if (!$password) {
            return Resp::error('密码必须填写');
        }

        if ((!$verify_code && !$passport) || ($verify_code && $passport)) {
            return Resp::error('请选一种方式重设密码!');
        }
        if ($passport) {
            if (!$captcha || !$Verification->checkCaptcha($passport, $captcha)) {
                return Resp::error('请输入正确验证码');
            }
        }

        if ($verify_code) {
            if (!$Verification->verifyOnceCode($verify_code)) {
                return Resp::error($Verification->getError());
            }
            $passport = $Verification->getHidden();
        }

        $Pam = new Pam();
        if ($Pam->setPassword($passport, $password)) {
            return Resp::success('密码已经重新设置');
        }

        return Resp::error($Pam->getError());
    }

    /**
     * @api                    {post} api_v1/system/auth/bind_mobile [Sys]换绑手机
     * @apiVersion             1.0.0
     * @apiName                SysAuthBindMobile
     * @apiGroup               Poppy
     * @apiParam {string}      verify_code     之前手机号生成的校验验证串
     * @apiParam {string}      passport        新手机号
     * @apiParam {string}      captcha         验证码
     */
    public function bindMobile()
    {
        $captcha     = input('captcha');
        $passport    = input('passport');
        $verify_code = input('verify_code');

        if (!UtilHelper::isMobile($passport)) {
            return Resp::error('请输入正确手机号');
        }

        $Verification = new Verification();
        if (!$Verification->checkCaptcha($passport, $captcha)) {
            return Resp::error('请输入正确验证码');
        }

        if ($verify_code && !$Verification->verifyOnceCode($verify_code)) {
            return Resp::error($Verification->getError());
        }

        $hidden = $Verification->getHidden();

        $Pam = new Pam();
        if (!$Pam->rebind($hidden, $passport)) {
            return Resp::error($Pam->getError());
        }
        return Resp::success('成功绑定手机');
    }

    protected function username(): string
    {
        return 'passport';
    }
}