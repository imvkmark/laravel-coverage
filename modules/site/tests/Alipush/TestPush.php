<?php namespace Site\Tests\Alipush;

use Poppy\System\Tests\Base\SystemTestCase;

class TestPush extends SystemTestCase
{

    public function setUp():void
    {
        parent::setUp();
        $this->initPam();
    }
}