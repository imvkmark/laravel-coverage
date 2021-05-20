<?php

namespace Poppy\Framework\Tests\Helper;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Helper\HtmlHelper;

/**
 * ArrayHelperTest
 */
class HtmlHelperTest extends TestCase
{

    public function testNameToId(): void
    {
        $string = "user[info][data][zh]";
        $this->assertEquals('user-info-data-zh', HtmlHelper::nameToId($string));
    }

    public function testNameToArray(): void
    {
        $name = "user[city,test][location][zh]";
        $this->assertEquals(['user', 'city,test', 'location', 'zh'], HtmlHelper::nameToArray($name));
    }

}