<?php

namespace Poppy\Core\Tests\Support;

/**
 * Copyright (C) Update For IDE
 */

use Carbon\Carbon;
use Poppy\System\Tests\Base\SystemTestCase;

class FunctionTest extends SystemTestCase
{

    public function testSysCacher(): void
    {
        for ($i = 0; $i <= 2; $i++) {
            $timestamp = Carbon::now()->timestamp;
            $core      = sys_cacher('poppy.core.action.verification-clear', function () {
                return Carbon::now()->timestamp;
            }, 2);
            if ($i === 0) {
                $this->assertEquals($timestamp, $core, $i);
            }
            // 第一秒 未过期
            if ($i === 1) {
                $this->assertEquals($timestamp - 1, $core, $i);
            }

            // 第二秒已经过期
            if ($i === 2) {
                $this->assertEquals($timestamp, $core, $i);
            }
            sleep(1);
        }
    }


    /**
     * 缓存测试, 带标签的使用 Flush 来清除标签缓存
     */
    public function testSysCache(): void
    {
        sys_cache('py-core')->forever('test.sys.cache', 'sys_cache');
        $value = sys_cache('py-core')->get('test.sys.cache');
        $this->assertEquals('sys_cache', $value);

        sys_cache('py-core')->forever('test.sys_cache', 5);
        sys_cache()->forever('test.sys_cache', 8);
        $this->assertEquals(5, sys_cache('py-core')->get('test.sys_cache'));
        sys_cache('py-core')->flush();
        $this->assertEquals(8, sys_cache()->get('test.sys_cache'));
        $this->assertEquals(null, sys_cache('py-core')->get('test.sys_cache'));
    }


    public function testSysInfos()
    {
        sys_debug('testing', self::class, 'debug@' . $this->faker()->words('20', true));
        sys_info('testing', self::class, 'info@' . $this->faker()->words('20', true));
        sys_error('testing', self::class, 'error@' . $this->faker()->words('20', true));
        $this->assertTrue(true);
    }

}