<?php

namespace Poppy\Core\Tests\Commands;

use Poppy\Framework\Application\TestCase;

class PermissionTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testList()
    {
        $result = py_console()->call('py-core:permission', [
            'do' => 'list',
        ]);
        $this->assertEquals(0, $result);
    }

    public function testInit()
    {
        $result = py_console()->call('py-core:permission', [
            'do' => 'init',
        ]);
        $this->assertEquals(0, $result);
    }
}