<?php

namespace Poppy\System\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * User\Models\PamToken
 *
 * @property int         $id
 * @property int         $account_id   用户id
 * @property string      $device_id    设备id
 * @property string      $device_type  设备类型
 * @property string      $login_ip     token 登录IP
 * @property string      $token_hash   token 的md5值
 * @property string      $push_id      push ID
 * @property string      $expired_at   过期时间
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PamToken newModelQuery()
 * @method static Builder|PamToken newQuery()
 * @method static Builder|PamToken query()
 * @mixin Eloquent
 */
class PamToken extends Eloquent
{

    public static $instance;

    protected $table = 'pam_token';

    protected $fillable = [
        'account_id',
        'device_id',
        'device_type',
        'login_ip',
        'token_hash',
        'push_id',
        'expired_at',
    ];

    /**
     * 获取示例, 用于中间件的数据传输
     * @return static
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    /**
     * 设置示例
     * @param $instance
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * 获取单条数据
     * @param string $account_id account_id
     * @return PamToken
     */
    public static function getItemById($account_id)
    {
        return self::where('account_id', $account_id)->first();
    }

}
