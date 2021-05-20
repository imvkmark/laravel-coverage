<?php

namespace Poppy\Core\Tests\Redis;

use Artisan;
use Carbon\Carbon;
use DB;
use Poppy\Core\Redis\RdsPersist;
use Poppy\Framework\Application\TestCase;
use Predis\Client;

/**
 * 内存持久化测试
 */
class RdsPersistTest extends TestCase
{
    /**
     * @var Client $redis
     */
    public $redis;

    /**
     * 写入单条测试
     */
    public function testInsert()
    {
        $maxId = DB::table('pam_log')->max('id');
        $item  = function () {
            return [
                'account_id'   => 10,
                'parent_id'    => 0,
                'account_type' => 'user',
                'type'         => 'success',
                'ip'           => $this->faker()->ipv4,
                'area_text'    => $this->faker()->word(),
                'created_at'   => Carbon::now()->toDateTimeString(),
                'updated_at'   => Carbon::now()->toDateTimeString(),
            ];
        };

        $items = [];
        $rand  = rand(4, 30);
        for ($i = 0; $i < $rand; $i++) {
            $items[] = $item();
        }

        /* attention: 如果这里不进行数据库提交, 则自增ID 会变大, 导致无法一致
         * ---------------------------------------- */
        RdsPersist::insert('pam_log', $items);
        Artisan::call('system:persist', ['table' => 'pam_log']);
        DB::commit();
        $this->assertEquals($maxId + $rand, DB::table('pam_log')->max('id'));
    }


    /**
     * 修改测试
     */
    public function testUpdate()
    {
        $where = [
            'account_id' => 10,
            'gift_id'    => 5,
        ];

        $getGiftNum = function ($where) {
            return DB::table('gift_collection')->where($where)->value('gift_num');
        };

        /* 初始化
         * ---------------------------------------- */
        RdsPersist::update('gift_collection', $where, [
            'gift_num' => 8,
        ]);
        RdsPersist::update('gift_collection', $where, [
            'gift_num[+]' => 8,
        ]);
        Artisan::call('system:persist', ['table' => 'gift_collection']);
        $this->assertEquals(8 + 8, $getGiftNum($where));

        /* -8
         * ---------------------------------------- */
        $ori = $getGiftNum($where);
        RdsPersist::update('gift_collection', $where, [
            'gift_num[+]' => 8,
        ]);
        Artisan::call('system:persist', ['table' => 'gift_collection']);
        $this->assertEquals($ori + 8, $getGiftNum($where));

        /* +8
         * ---------------------------------------- */
        $ori = $getGiftNum($where);
        RdsPersist::update('gift_collection', $where, [
            'gift_num[-]' => 8,
        ]);
        Artisan::call('system:persist', ['table' => 'all']);
        $this->assertEquals($ori - 8, $getGiftNum($where));

        /* .8
        * ---------------------------------------- */
        $ori = $getGiftNum($where);
        RdsPersist::update('gift_collection', $where, [
            'gift_num[.]' => 8,
        ]);
        Artisan::call('system:persist', ['table' => 'gift_collection']);
        $this->assertEquals($ori . '8', $getGiftNum($where));
        DB::commit();
    }

    public function testUpdateMoreFields()
    {
        $where = [
            'id' => 79,
        ];

        RdsPersist::update('chat_room', $where, [
            'hot_num' => 8,
        ]);
        RdsPersist::update('chat_room', $where, [
            'password' => '2333',
        ]);
        $result = RdsPersist::where('chat_room', $where);
        $this->assertEquals(8, $result['hot_num']);
        $this->assertEquals('2333', $result['password']);
    }

    /**
     * 测试解析 Update
     */
    public function testParseUpdate()
    {

        $init = [
            'add'      => 0,
            'subtract' => 0,
            'preserve' => 0,
            'force'    => 0,
        ];

        $update = [
            'add[+]'      => 5,
            'subtract[-]' => 5,
            'force'       => 8,
        ];

        $result = RdsPersist::calcUpdate($init, $update);
        $this->assertEquals($result['add'], '5.00');
        $this->assertEquals($result['subtract'], '-5.00');
        $this->assertEquals($result['preserve'], 0);
        $this->assertEquals($result['force'], 8);


        $purColumn = function ($keys) {
            $columns = [];
            foreach ($keys as $key) {
                preg_match('/(?<column>[a-zA-Z0-9_]+)(\[(?<operator>\+|-|\.)])?/i', $key, $match);
                $columns[] = $match['column'];
            }
            return $columns;
        };
        $columns   = $purColumn(array_keys($update));
        $this->assertEquals('force', $columns[2]);

    }

    public function testParseUpdateWithDiff()
    {
        $init = [
            'add' => 0,
        ];

        $update = [
            'append' => 5,
        ];
        $result = RdsPersist::calcUpdate($init, $update);
        $this->assertCount(2, array_keys($result));
    }
}