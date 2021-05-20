<?php

namespace Poppy\Core\Tests\Commands;

use Poppy\Core\Rbac\Helper\RbacHelper;
use Poppy\Framework\Application\TestCase;

class RbacTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testHelper()
    {
        $permissions = RbacHelper::permission('backend');
        $this->assertGreaterThan(0, $permissions->count());
    }
}