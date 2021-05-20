<?php

namespace Poppy\Framework\Tests\Helper;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Helper\ArrayHelper;

/**
 * ArrayHelperTest
 */
class ArrayHelperTest extends TestCase
{
    /**
     * testCombine
     */
    public function testCombine(): void
    {
        $arr     = [
            1, 2, 3, [4, 5], 6, 7,
        ];
        $combine = ArrayHelper::combine($arr);
        $this->assertEquals('1,2,3,4,5,6,7', $combine);
    }

    public function testGenKey(): void
    {
        $arr    = [
            'location' => 'http://www.baidu.com',
            'status'   => 'error',
        ];
        $genKey = ArrayHelper::genKey($arr);

        // 组合数组
        $this->assertEquals('location|http://www.baidu.com;status|error', $genKey);

        // 组合空
        $this->assertEquals('', ArrayHelper::genKey([]));
    }

    public function testToKvStr(): void
    {
        $array1 = ['a' => 'b'];

        $this->assertEquals('a=b', ArrayHelper::toKvStr($array1));

        $array2 = [
            'a' => '1',
            'b' => '2',
        ];
        $this->assertEquals('a=1,b=2', ArrayHelper::toKvStr($array2));

        $array3 = [
            'a' => [
                'd', 'e',
            ],
            'b' => '2',
        ];
        $this->assertEquals('a=["d","e"],b=2', ArrayHelper::toKvStr($array3));
    }

    public function testNext(): void
    {
        $array = [
            'a', 'b', 'd', 'f',
        ];
        $this->assertEquals('d', ArrayHelper::next($array, 'b'));
    }

    public function testDelete(): void
    {
        $array     = [
            1, 2, 3,
        ];
        $arrDelete = ArrayHelper::delete($array, [3]);
        $this->assertEquals([1, 2], $arrDelete);
    }

    public function testMapNull()
    {
        $array  = [
            null, [], '5',
        ];
        $return = ArrayHelper::mapNull($array);
        $this->assertEquals(['', [], '5'], $return);
    }


    public function testFindKey()
    {
        $arr = [
            '姓名', '电话', '手机',
        ];
        $this->assertEquals(0, ArrayHelper::findKey($arr, ['姓名']));
        $this->assertEquals(0, ArrayHelper::findKey($arr, '姓名'));
        $this->assertEquals(0, ArrayHelper::findKey($arr, ['真实姓名', '姓名']));
        $this->assertEquals(2, ArrayHelper::findKey($arr, ['手机', '电话']));
        $this->assertEquals(1, ArrayHelper::findKey($arr, ['电话', '手机']));
        $this->assertEquals(1, ArrayHelper::findKey($arr, '电话'));
    }
}