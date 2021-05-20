<?php

namespace Poppy\System\Action;

use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Helper\UtilHelper;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Classes\Contracts\PasswordContract;
use Poppy\System\Classes\Traits\PamTrait;
use Poppy\System\Classes\Traits\UserSettingTrait;
use Poppy\System\Events\LoginBannedEvent;
use Poppy\System\Events\LoginFailedEvent;
use Poppy\System\Events\LoginSuccessEvent;
use Poppy\System\Events\PamDisableEvent;
use Poppy\System\Events\PamEnableEvent;
use Poppy\System\Events\PamRegisteredEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamLog;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\SysConfig;
use Throwable;
use Tymon\JWTAuth\JWTGuard;
use Validator;

/**
 * 账号操作
 */
class Pam
{
    use UserSettingTrait, AppTrait, PamTrait;

    /**
     * @var int 父级ID
     */
    private $parentId = 0;

    /**
     * @var string Pam table
     */
    private $pamTable;

    public function __construct()
    {
        $pamClass = config('poppy.core.rbac.account');
        if (!$pamClass) {
            $pamClass = PamAccount::class;
        }
        $this->pamTable = (new $pamClass())->getTable();
    }

    /**
     * 验证验登录
     * @param string $passport 通行证
     * @param string $captcha  验证码
     * @param string $platform 平台
     * @return bool
     */
    public function captchaLogin(string $passport, string $captcha, string $platform): bool
    {
        $initDb = [
            'passport' => $passport,
            'captcha'  => $captcha,
        ];

        // 数据验证
        $validator = Validator::make($initDb, [
            'captcha'  => Rule::required(),
            'platform' => Rule::in(PamAccount::kvPlatform()),
        ]);
        if ($validator->fails()) {
            return $this->setError($validator->messages());
        }

        // 验证账号 + 验证码
        $verification = new Verification();

        if (!$verification->checkCaptcha($passport, $captcha)) {
            return $this->setError($verification->getError()->getMessage());
        }

        // 判定账号是否存在, 如果不存在则进行注册
        $this->pam = PamAccount::passport($passport);
        if (!$this->pam && !$this->register($initDb['passport'], '', PamRole::FE_USER, $platform)) {
            return false;
        }

        // 检测权限, 是否被禁用
        if (!$this->checkIsEnable($this->pam)) {
            return false;
        }

        event(new LoginSuccessEvent($this->pam, $platform));

        return true;
    }

    /**
     * 设置父级ID
     * @param int $parent_id 父级id
     */
    public function setParentId(int $parent_id): void
    {
        $this->parentId = $parent_id;
    }

    /**
     * 用户注册
     * @param string $passport  passport
     * @param string $password  密码
     * @param string $role_name 用户角色名称
     * @param string $platform  支持的平台
     * @return bool
     */
    public function register(string $passport, $password = '', $role_name = PamRole::FE_USER, $platform = ''): bool
    {
        $passport = PamAccount::fullFilledPassport($passport);
        $type     = PamAccount::passportType($passport);

        $initDb = [
            $type          => $passport,
            'password'     => (string) $password,
            'reg_platform' => (string) $platform,
            'parent_id'    => $this->parentId,
        ];

        $rule = [
            $type      => [
                Rule::required(),
                Rule::string(),
                Rule::between(6, 30),
                // 唯一性认证
                Rule::unique($this->pamTable, $type),
            ],
            'password' => [
                Rule::string(),
            ],
        ];

        // 完善主账号类型规则
        if ($type === PamAccount::REG_TYPE_USERNAME) {
            if (preg_match('/\s+/', $passport)) {
                return $this->setError(trans('py-system::action.pam.user_name_not_space'));
            }
            // 注册用户时候的正则匹配
            if ($this->parentId) {
                // 子用户中必须包含 ':' 冒号
                if (strpos($initDb[$type], ':') === false) {
                    return $this->setError(trans('py-system::action.pam.sub_user_account_need_colon'));
                }
                // 初始化子用户数据
                $initDb['parent_id'] = $this->parentId;

                // 注册子用户, 子用户比主账号多一个 :
                array_unshift($rule[$type], Rule::username(true));
            }
            else {
                array_unshift($rule[$type], Rule::username());
            }
        }

        // 密码不为空时候的检测
        if ($password !== '') {
            $rule['password'] += [
                Rule::between(6, 16),
                Rule::required(),
                Rule::simplePwd(),
            ];
        }

        // 验证数据
        $validator = Validator::make($initDb, $rule);
        if ($validator->fails()) {
            return $this->setError($validator->messages());
        }

        if (is_string($role_name)) {
            $role = PamRole::whereIn('name', (array) $role_name)->get();
        }
        else {
            $roleNames = (array) $role_name;
            $role      = PamRole::whereIn('id', $roleNames)->get();
        }
        if (!$role) {
            return $this->setError(trans('py-system::action.pam.role_not_exists'));
        }

        // 自动设置前缀
        $prefix = strtoupper(strtolower(sys_setting('py-system::pam.prefix')));
        if ($type !== PamAccount::REG_TYPE_USERNAME) {
            $hasAccountName = false;
            // 检查是否设置了前缀
            if (!$prefix) {
                return $this->setError(trans('py-system::action.pam.not_set_name_prefix'));
            }
            $username = $prefix . '_' . Carbon::now()->format('YmdHis') . Str::random(6);
        }
        else {
            $hasAccountName = true;
            $username       = $passport;
        }

        $initDb['username']  = $username;
        $initDb['type']      = $role->first()->type;
        $initDb['is_enable'] = SysConfig::ENABLE;

        try {
            // 处理数据库
            return DB::transaction(function () use ($initDb, $role, $password, $hasAccountName, $prefix) {
                /** @var PamAccount $pam pam */
                $pam = PamAccount::create($initDb);

                // 给用户默认角色
                $pam->roles()->attach($role->pluck('id'));

                // 如果没有设置账号, 则根据规范生成用户名
                if (!$hasAccountName) {
                    $formatAccountName = sprintf("%s_%'.09d", $prefix, $pam->id);
                    $pam->username     = $formatAccountName;

                }

                // 设置默认国际手机号, 后台自动生成(Backend 用户/Develop)
                if (in_array($initDb['type'], [PamAccount::TYPE_BACKEND, PamAccount::TYPE_DEVELOP]) && !isset($initDb['mobile'])) {
                    $pam->mobile = PamAccount::dftMobile($pam->id);
                }

                // 设置密码
                if ($password) {
                    $key               = Str::random(6);
                    $regDatetime       = $pam->created_at->toDateTimeString();
                    $pam->password     = app(PasswordContract::class)->genPassword($password, $regDatetime, $key);
                    $pam->password_key = $key;
                }

                $pam->save();

                // 触发注册成功的事件
                event(new PamRegisteredEvent($pam));

                $this->pam = $pam;
                return true;
            });
        } catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 密码登录
     * @param string $passport   passport
     * @param string $password   密码
     * @param string $guard_type 类型
     * @param string $platform   平台
     * @return bool
     */
    public function loginCheck(string $passport, string $password, $guard_type = PamAccount::GUARD_WEB, $platform = ''): bool
    {
        $type        = PamAccount::passportType($passport);
        $credentials = [
            $type      => $passport,
            'password' => $password,
        ];

        // check exists
        $validator = Validator::make($credentials, [
            $type      => [
                Rule::required(),
            ],
            'password' => Rule::required(),
        ], []);
        if ($validator->fails()) {
            return $this->setError($validator->errors());
        }

        $guard = Auth::guard($guard_type);

        if ($guard->attempt($credentials)) {
            // jwt 不能获取到 user， 使用 getLastAttempted 方法来获取数据
            if ($guard instanceof JWTGuard) {
                /** @var PamAccount $pam */
                $pam = $guard->getLastAttempted();
            }
            else {
                /** @var PamAccount $user */
                $pam = $guard->user();
            }
            $this->pam = $pam;

            if (!$this->checkIsEnable($this->pam)) {
                return false;
            }


            try {
                event(new LoginBannedEvent($pam, $guard));
            } catch (Throwable $e) {
                return $this->setError($e);
            }

            if (method_exists($this, 'loginAllowIpCheck') && !$this->loginAllowIpCheck()) {
                $guard->logout();
                return false;
            }

            // 兼容存在 system 模块事件
            // deprecated 为了兼容 q2
            if (class_exists('\System\Events\LoginSuccessEvent')) {
                event(new \System\Events\LoginSuccessEvent($pam, $platform, $guard));
                return true;
            }

            event(new LoginSuccessEvent($pam, $platform, $guard));

            return true;
        }

        if (!$guard->getLastAttempted()) {
            return $this->setError(trans('py-system::action.pam.account_not_exist'));
        }

        $credentials += [
            'type'     => $type,
            'passport' => $passport,
        ];

        event(new LoginFailedEvent($credentials));

        return $this->setError(trans('py-system::action.pam.login_fail_again'));

    }

    /**
     * 设置登录密码
     * @param PamAccount|mixed $pam      用户
     * @param string           $password 密码
     * @return bool
     */
    public function setPassword($pam, string $password): bool
    {
        if (is_string($pam) || is_numeric($pam)) {
            $pam = PamAccount::passport($pam);
        }

        if (!$pam && !($pam instanceof PamAccount)) {
            return $this->setError(trans('py-system::action.pam.pam_error'));
        }
        $validator = Validator::make([
            'password' => $password,
        ], [
            'password' => 'required|between:6,20',
        ]);
        if ($validator->fails()) {
            return $this->setError($validator->messages());
        }

        $key               = Str::random(6);
        $regDatetime       = $pam->created_at->toDateTimeString();
        $cryptPassword     = app(PasswordContract::class)->genPassword($password, $regDatetime, $key);
        $pam->password     = $cryptPassword;
        $pam->password_key = $key;
        $pam->save();

        return true;
    }

    /**
     * 设置角色
     * @param PamAccount|mixed $pam   账号数据
     * @param array            $roles 角色名
     * @return bool
     */
    public function setRoles($pam, array $roles): bool
    {
        /** @var PamRole[]|Collection $role */
        $role = PamRole::whereIn('id', $roles)->get();
        $pam->roles()->detach();
        $pam->roles()->attach($role->pluck('id'));

        return true;
    }

    /**
     * 生成支持 passport 格式的数组
     * @param array|Request $credentials 待转化的数据
     * @return array
     */
    public function passportData($credentials): array
    {
        if ($credentials instanceof Request) {
            $credentials = $credentials->all();
        }
        $passport     = $credentials['passport'] ?? '';
        $passport     = $passport ?: $credentials['mobile'] ?? '';
        $passport     = $passport ?: $credentials['username'] ?? '';
        $passport     = $passport ?: $credentials['email'] ?? '';
        $passportType = PamAccount::passportType($passport);

        return [
            $passportType => $passport,
            'password'    => $credentials['password'] ?? '',
        ];
    }

    /**
     * Passport Type
     * @param string $passport 通行证
     * @return string
     * @deprecated 3.1
     * @removed    4.0
     * @see        PamAccount::passportType()
     */
    public function passportType(string $passport): string
    {
        if (UtilHelper::isMobile($passport)) {
            $type = PamAccount::REG_TYPE_MOBILE;
        }
        elseif (UtilHelper::isEmail($passport)) {
            $type = PamAccount::REG_TYPE_EMAIL;
        }
        elseif (is_numeric($passport)) {
            $type = 'id';
        }
        else {
            $type = PamAccount::REG_TYPE_USERNAME;
        }

        return $type;
    }


    /**
     * 更换账号主体, 支持除非ID外的更换方式
     * @param string|numeric|PamAccount $old_passport
     * @param string                    $new_passport
     * @return bool
     */
    public function rebind($old_passport, string $new_passport): bool
    {
        $pam = null;
        if (is_numeric($old_passport) || is_string($old_passport)) {
            $old_passport = PamAccount::fullFilledPassport($old_passport);
            $pam          = PamAccount::passport($old_passport);
        }
        else if ($old_passport instanceof PamAccount) {
            $pam = $old_passport;
        }
        if (!$pam) {
            return $this->setError('原账号不存在, 无法更换');
        }
        $newPassportType = PamAccount::passportType($new_passport);
        if ($newPassportType === 'id') {
            return $this->setError('用户ID 无法更换, 请检查输入');
        }
        $pam->{$newPassportType} = PamAccount::fullFilledPassport($new_passport);
        $pam->save();
        return true;
    }

    /**
     * 后台用户禁用
     * @param int    $id     用户id
     * @param string $to     解禁时间
     * @param string $reason 禁用原因
     * @return bool
     */
    public function disable(int $id, string $to, string $reason): bool
    {
        $data      = [
            'disable_reason' => $reason,
            'disable_to'     => $to,
        ];
        $validator = Validator::make($data, [
            'disable_reason' => [
                Rule::string(),
            ],
            'disable_to'     => [
                Rule::string(),
                Rule::dateFormat('Y-m-d H:i:s'),
            ], [], [
                'disable_reason' => trans('py-system::action.pam.disable_reason'),
                'disable_to'     => trans('py-system::action.pam.disable_to'),
            ],
        ]);
        if ($validator->fails()) {
            return $this->setError($validator->messages());
        }

        /** @var PamAccount $pam */
        $pam = PamAccount::find($id);
        //当前用户已禁用
        if (!$pam->is_enable) {
            return $this->setError(trans('py-system::action.pam.account_disabled'));
        }

        $disableTo = Carbon::parse($data['disable_to']);
        if ($disableTo->lessThan(Carbon::now())) {
            return $this->setError('解禁日期需要大于当前日期');
        }
        $pam->update([
            'is_enable'        => SysConfig::DISABLE,
            'disable_reason'   => $data['disable_reason'],
            'disable_start_at' => Carbon::now(),
            'disable_end_at'   => $disableTo->toDateTimeString(),
        ]);

        event(new PamDisableEvent($pam, $this->pam, $reason));

        return true;
    }

    /**
     * 后台用户启用
     * @param int    $id     用户Id
     * @param string $reason 原因
     * @return bool
     */
    public function enable($id, $reason = ''): bool
    {
        if (PamAccount::where('id', $id)->where('is_enable', 1)->exists()) {
            return $this->setError(trans('py-system::action.pam.account_enabled'));
        }

        try {
            PamAccount::where('id', $id)->update([
                'is_enable' => SysConfig::ENABLE,
            ]);

            event(new PamEnableEvent(PamAccount::find($id), $this->pam, $reason));

            return true;
        } catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 自动解禁
     */
    public function autoEnable(): bool
    {
        try {
            PamAccount::where('disable_end_at', '<', Carbon::now())->update([
                'is_enable' => SysConfig::ENABLE,
            ]);

            return true;
        } catch (Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 清除登录日志
     * @return bool
     */
    public function clearLog(): bool
    {
        try {
            // 删除 60 天以外的登录日志
            PamLog::where('created_at', '<', Carbon::now()->subDays(60))->delete();

            return true;
        } catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 修改密码
     * @param string $old_password 老密码
     * @param string $password     新密码
     * @return bool
     */
    public function changePassword($old_password, $password): bool
    {
        if (!$this->checkPam()) {
            return false;
        }
        $old_password = trim($old_password);
        $password     = trim($password);

        if ($old_password === $password) {
            return $this->setError('新旧密码不能相同');
        }

        if (!app(PasswordContract::class)->check($this->pam, $old_password)) {
            return $this->setError('旧密码不正确');
        }

        return $this->setPassword($this->pam, $password);
    }

    /**
     * 修改账户密码
     * @param int    $id       用户id
     * @param string $password 密码
     * @return bool
     * @see        setPassword()
     * @deprecated 3.1
     */
    public function setPasswordById(int $id, string $password): bool
    {
        if (!$pam = PamAccount::find($id)) {
            return $this->setError(trans('py-system::action.pam.account_not_exist'));
        }

        if (!$this->setPassword($pam, $password)) {
            return false;
        }

        return true;
    }

    /**
     * 验证用户权限
     * @param PamAccount $pam 用户
     * @return bool
     */
    private function checkIsEnable($pam): bool
    {
        if ($pam->is_enable === SysConfig::NO) {
            return $this->setError("该账号因 $pam->disable_reason 被封禁至 $pam->disable_end_at");
        }
        return true;
    }
}
