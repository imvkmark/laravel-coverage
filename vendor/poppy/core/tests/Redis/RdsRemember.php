<?php

namespace Poppy\Core\Tests\Redis;

class RdsRemember extends RdsBaseTest
{

    public function testRemember()
    {
        $value = $this->rds->remember('remember', 20, function () {
            return [4];
        });
        $this->assertEquals(4, $value);
    }
}