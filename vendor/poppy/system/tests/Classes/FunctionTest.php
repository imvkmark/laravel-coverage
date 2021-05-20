<?php

namespace Poppy\System\Tests\Classes;

/**
 * Copyright (C) Update For IDE
 */

use Poppy\System\Tests\Base\SystemTestCase;

class FunctionTest extends SystemTestCase
{
    public function testOrderMatch(): void
    {
        $prefix = sys_order_prefix('Game2018');
        $this->assertEquals('Game', $prefix);

        $prefix = sys_order_prefix('Game');
        $this->assertEquals('Game', $prefix);
    }

    public function testSysStrUnique(): void
    {
        $this->assertSame('h:lol,h:pubg,h:wz', sys_str_unique('h:lol,h:wz', 'h:pubg'));
        $this->assertSame('h:dance,h:lol,h:pubg,h:wz', sys_str_unique('h:lol,h:wz,h:pubg,h:dance', 'h:wz'));
        $this->assertNotSame('h:lol,h:dance,h:pubg,h:wz', sys_str_unique('h:lol,h:wz,h:pubg,h:dance', 'h:wz'));
    }
}
