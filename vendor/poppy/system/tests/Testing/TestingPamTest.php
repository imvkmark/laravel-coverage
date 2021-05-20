<?php

namespace Poppy\System\Tests\Testing;

/**
 * Copyright (C) Update For IDE
 */

use Poppy\Framework\Application\TestCase;

class TestingPamTest extends TestCase
{

    public function testExclude()
    {
        $exclude = TestingPam::exclude();
        $this->assertNotNull($exclude);
    }
}
