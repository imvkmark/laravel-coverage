<?php

namespace Poppy\Core\Tests\Commands;

use Poppy\Framework\Application\TestCase;

class InspectTest extends TestCase
{

    public function testDbSeo()
    {
        $result = py_console()->call('py-core:inspect', [
            'type' => 'db_seo',
        ]);
        $this->assertEquals(0, $result);
    }
}