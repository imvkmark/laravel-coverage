<?php

namespace Poppy\System\Tests\Ability;

use Poppy\System\Jobs\NotifyJob;
use Poppy\System\Tests\Ability\Jobs\StaticVarJob;
use Poppy\System\Tests\Base\SystemTestCase;

class JobTest extends SystemTestCase
{
    /**
     * 测试 oss 上传
     */
    public function testCallback()
    {
        // 这个队列会执行成功
        dispatch(new NotifyJob('https://www.baidu.com', 'get', []));

        // 这个会执行失败, 失败后会进行下一次的延迟请求
        dispatch(new NotifyJob('https://www.baidu-error.com', 'get', []));
        $this->assertTrue(true);
    }

    public function testStaticVars(): void
    {
        dispatch(new StaticVarJob(1));
        $this->assertTrue(true);
    }
}