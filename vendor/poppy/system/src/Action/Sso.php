<?php

namespace Poppy\System\Action;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Helper\EnvHelper;
use Poppy\System\Events\PamSsoEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamToken;

/**
 * 单点登录
 */
class Sso
{
    use AppTrait;

    public const SSO_NONE   = 'none';
    public const SSO_ALL    = 'all';
    public const SSO_SINGLE = 'single';
    public const SSO_GROUP  = 'group';
    public const SSO_DEVICE = 'device';


    private static $groups = [
        'app' => ['android', 'ios'],
        'web' => ['h5', 'webapp', 'mp'],
        'pc'  => ['mac', 'linux', 'win'],
    ];

    /**
     * @param PamAccount $pam
     * @param            $device_id
     * @param            $device_type
     * @param            $token
     * @return bool
     * @throws \Exception
     */
    public function handle(PamAccount $pam, $device_id, $device_type, $token): bool
    {
        $ssoType      = (string) sys_setting('py-system::pam.sso_type');
        $maxDeviceNum = (int) sys_setting('py-system::pam.sso_device_num') ?: 10;
        // 不启用
        if (!self::isEnable()) {
            return true;
        }

        // 启用
        if (!$device_id || !$device_type) {
            return $this->setError('开启单一登录必须传递设备ID/设备类型');
        }

        $devices = Arr::flatten(self::$groups);
        if (!in_array($device_type, $devices)) {
            return $this->setError('设备类型必须是' . implode(',', $devices) . '中的一种');
        }

        $tokenMd5  = md5($token);
        $pamId     = $pam->id;
        $expiredAt = Carbon::now()->addMinutes(config('jwt.ttl'));

        // 允许同时登录, 记录设备信息, 同时登录数量受{最大设备数量}限制
        if ($ssoType === self::SSO_ALL) {
            // 检查同时登录设备量
            // 这里需要配上用户的设备管理, 否则会出问题的
            $num = PamToken::where('account_id', $pamId)->where('device_id', '!=', $device_id)->count();
            if ($maxDeviceNum >= $num) {
                return $this->setError('已经超过当前登录设备最大限制, 无法继续登录');
            }
        }

        // 放行当前设备
        $Ban = new Ban();
        $Ban->allow($pam->id, $tokenMd5, $expiredAt);

        switch ($ssoType) {
            case self::SSO_ALL:
                return true;
            case self::SSO_DEVICE:
                // 单端登录, 只移除当前类型设备
                $logoutUsers = PamToken::where('account_id', $pam->id)->where('device_type', $device_type)
                    ->get();
                break;
            case self::SSO_SINGLE:
                // 单点登录(Sso), 移除其他端所有设备
                $logoutUsers = PamToken::where('account_id', $pam->id)->get();
                break;
            case self::SSO_GROUP:
                // 同组内登录
                $total = [];
                foreach (self::$groups as $group) {
                    if (in_array($device_type, $group)) {
                        $total = $group;
                    }
                }
                $logoutUsers = PamToken::where('account_id', $pam->id)
                    ->whereIn('device_type', $total)
                    ->get();
                break;
            default:
                $logoutUsers = collect();
                break;
        }
        if ($logoutUsers->count()) {
            PamToken::whereIn('id', $logoutUsers->pluck('id')->toArray())->delete();
            event(new PamSsoEvent($pam, $logoutUsers));
        }

        // 创建/更新用户的设备类型
        /** @var PamToken $current */
        PamToken::updateOrInsert([
            'account_id'  => $pamId,
            'device_type' => $device_type,
        ], [
            'token_hash' => $tokenMd5,
            'device_id'  => $device_id,
            'expired_at' => $expiredAt->toDateTimeString(),
            'login_ip'   => EnvHelper::ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        return true;
    }

    /**
     * 是否启用 sso 登录
     * @return bool
     */
    public static function isEnable(): bool
    {
        $ssoType = (string) sys_setting('py-system::pam.sso_type');
        if ($ssoType === '' || $ssoType === self::SSO_NONE) {
            return false;
        }
        return true;
    }

    /**
     * @param string|null $key          Key
     * @param bool        $check_exists 检测键值是否存在
     * @return array|string
     */
    public static function kvType(string $key = null, bool $check_exists = false)
    {
        $desc = [
            Sso::SSO_NONE   => '不启用',
            Sso::SSO_SINGLE => '单点登录(Sso), 仅允许一端登录',
            Sso::SSO_GROUP  => '同组内单点登录. 各组之间允许同时登录',
            Sso::SSO_DEVICE => '单端登录, 同类型互踢, 其他类型可同时在线',
            Sso::SSO_ALL    => '允许同时登录, 记录设备信息, 同时登录数量受{最大设备数量}限制',
        ];
        return kv($desc, $key, $check_exists);
    }
}