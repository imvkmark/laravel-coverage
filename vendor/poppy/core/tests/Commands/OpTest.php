<?php

namespace Poppy\Core\Tests\Commands;

use Poppy\Framework\Application\TestCase;

class OpTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testMail()
    {
        $result = py_console()->call('py-core:op', [
            'do' => 'mail',
        ]);
        $this->assertEquals(0, $result);
    }
}