<?php

namespace Poppy\System\Tests\Testing;

use Poppy\Framework\Helper\StrHelper;
use Poppy\System\Models\PamAccount;

/**
 * 随机获取数据
 */
class TestingPam
{
    /**
     * 获取随机用户名
     * @param bool $is_register 是否已经注册
     * @return mixed
     */
    public static function username($is_register = true)
    {
        $Db = PamAccount::orderByRaw('rand()');
        if ($is_register) {
            $Db->where('password', '!=', '');
        }
        else {
            $Db->where('password', '=', '');
        }

        return $Db->value('username');
    }

    /**
     * 获取随机AccountId
     * @param bool $is_register 是否已经注册
     * @return int
     */
    public static function id($is_register = true)
    {
        $Db = PamAccount::orderByRaw('rand()');
        if ($is_register) {
            $Db->where('password', '!=', '');
        }
        else {
            $Db->where('password', '=', '');
        }

        return $Db->value('id');
    }

    /**
     * 除去测试用户
     * @return array
     */
    public static function exclude(): array
    {
        $users = StrHelper::separate(PHP_EOL, (string) sys_setting('py-system::testing.users'));
        if (!$users) {
            return [];
        }

        return PamAccount::whereIn('mobile', $users)->pluck('id')->toArray();
    }
}
