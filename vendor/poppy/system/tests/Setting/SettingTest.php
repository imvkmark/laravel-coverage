<?php

namespace Poppy\System\Tests\Setting;

use Exception;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Setting\Repository\SettingRepository;

class SettingTest extends TestCase
{

    public function testItem()
    {
        $key     = $this->randKey();
        $setting = new SettingRepository();
        $this->assertTrue($setting->set($key, 'value'));
        $item = $setting->get($key);
        $this->assertEquals('value', $item, 'Value Fetch Error');
        $this->assertTrue($setting->delete($key));
    }

    public function testGet()
    {
        $item = sys_setting($this->randKey('set'));
        $this->assertNull($item);
        $item = sys_setting($this->randKey('set'), '');
        $this->assertEmpty($item);
        $item = sys_setting($this->randKey('set'), 'testing');
        $this->assertEquals('testing', $item);
    }

    public function testGetGn()
    {
        app('poppy.system.setting')->removeNG('testing::set');

        // A : Str
        $keyA = $this->randKey('set');
        $valA = $this->faker()->lexify();
        app('poppy.system.setting')->set($keyA, $valA);
        $valGetA = sys_setting($keyA);
        $this->assertEquals($valA, $valGetA);

        // B : Array
        $keyB = $this->randKey('set');
        $valB = $this->faker()->words();
        app('poppy.system.setting')->set($keyB, $valB);
        $valGetB = sys_setting($keyB);
        $this->assertEquals($valB, $valGetB);

        $gn = app('poppy.system.setting')->getNG('testing::set');
        $this->assertCount(2, $gn);
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void
    {
        app('poppy.system.setting')->removeNG('testing::set');
    }

    private function randKey($group = ''): string
    {
        $faker = $this->faker();
        return 'testing::' . ($group ?: $faker->regexify('[a-z]{3,5}')) . '.' . $faker->regexify('/[a-z]{5,8}/');
    }
}