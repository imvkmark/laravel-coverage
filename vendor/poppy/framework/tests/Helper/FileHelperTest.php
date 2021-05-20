<?php

namespace Poppy\Framework\Tests\Helper;

use Carbon\Carbon;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Helper\FileHelper;
use Poppy\Framework\Helper\TimeHelper;

/**
 * ArrayHelperTest
 */
class FileHelperTest extends TestCase
{

    public function testExt(): void
    {
        $file = 'demo.jpg';
        $this->assertEquals('jpg', FileHelper::ext($file));
    }

    public function testCorrectName(): void
    {
        $file = '?*demo.jpg@';
        $this->assertEquals('demo.jpg', FileHelper::correctName($file));
    }

    public function testGetJson(): void
    {
        // todo li 可以生成一个 json 文件, 来验证是否正确
        $file = poppy_path('poppy.framework', 'tests/files/demo.jpg');
        $this->assertEquals([], FileHelper::getJson($file));
    }

    public function testDirPath(): void
    {
        $file = poppy_path('poppy.framework', 'tests/files');
        $this->assertEquals($file . '/', FileHelper::dirPath($file));
    }

    public function testTouch(): void
    {
        $carbon = Carbon::now();
        $dir    = poppy_path('poppy.framework', 'tests/files');
        $this->assertEquals(true, FileHelper::touch($dir));
        $file = poppy_path('poppy.framework', 'tests/files/demo.jpg');
        clearstatcache(false, $file);
        $fmtime = filemtime($file);
        $fatime = fileatime($file);
        $this->outputVariables($carbon->toDateTimeString());

        $this->assertEquals(TimeHelper::datetime($fatime, '3-3'), $carbon->toDateTimeString(), 'file access time');
        $this->assertEquals(TimeHelper::datetime($fmtime, '3-3'), $carbon->toDateTimeString(), 'file modify time');
    }

    public function testRemoveExtension(): void
    {
        $file = 'demo.jpg';
        $this->assertEquals('demo', FileHelper::removeExtension($file));
    }
}