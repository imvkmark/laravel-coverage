<?php

namespace Poppy\Framework\Tests\Helper;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Helper\TreeHelper;

/**
 * todo li 这个可以使用 demo/demo 来进行验证
 */
class TreeHelperTest extends TestCase
{

    protected $trees;

    public function setUp(): void
    {
        parent::setUp();
        $this->trees = [
            1 => ['id' => 1, 'pid' => 0, 'name' => '一级栏目一'],
            2 => ['id' => 2, 'pid' => 0, 'name' => '一级栏目二'],
            3 => ['id' => 3, 'pid' => 1, 'name' => '二级栏目一'],
            4 => ['id' => 4, 'pid' => 1, 'name' => '二级栏目二'],
            5 => ['id' => 5, 'pid' => 2, 'name' => '三级栏目一'],
            6 => ['id' => 6, 'pid' => 2, 'name' => '三级栏目二'],
        ];
    }

    public function testInit(): void
    {
        $Tree = new TreeHelper();
        $this->assertEquals(true, $Tree->init($this->trees));
    }

    public function testGet(): void
    {
        $Tree = new TreeHelper();
        $Tree->init($this->trees);
        $this->assertEquals(2, count($Tree->getParent(3)));

        // 验证子级别
        $this->assertEquals(2, count($Tree->getChild(2)));
    }
}