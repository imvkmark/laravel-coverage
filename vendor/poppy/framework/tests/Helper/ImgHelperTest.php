<?php

namespace Poppy\Framework\Tests\Helper;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Helper\ImgHelper;

/**
 * ArrayHelperTest
 */
class ImgHelperTest extends TestCase
{

    public function testType(): void
    {
        $file = poppy_path('poppy.framework', 'tests/files/demo.jpeg');
        $this->assertEquals('jpeg', ImgHelper::typeFromMime($file));
    }

    public function testGetImageInfo(): void
    {
        $file = poppy_path('poppy.framework', 'tests/files/demo.jpeg');
        $this->assertEquals("image/jpeg", ImgHelper::getImageInfo($file)['mime']);
    }
}