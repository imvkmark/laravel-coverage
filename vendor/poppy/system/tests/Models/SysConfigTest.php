<?php

namespace Poppy\System\Tests\Models;

use Poppy\System\Models\PamAccount;
use Poppy\System\Models\SysConfig;
use Poppy\System\Tests\Base\SystemTestCase;

class SysConfigTest extends SystemTestCase
{
    public function testTableExist()
    {
        $exist = SysConfig::tableExists((new PamAccount())->getTable());
        $this->assertTrue($exist);

        $tbExists = SysConfig::tableExists($this->faker()->lexify());
        $this->assertFalse($tbExists);
    }

    public function tearDown(): void
    {
        app('poppy.system.setting')->removeNG('py-system::db');
    }
}