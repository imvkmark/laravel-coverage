<?php

namespace Poppy\Core\Tests\Redis;

class RdsDbTest extends RdsBaseTest
{
    public function testExists()
    {
        $key   = $this->key('exists');
        $keyNx = $this->key('exists-nx');
        $this->rds->set($key, 'exists');
        $nx = $this->rds->exists($keyNx);
        $this->assertFalse($nx);
        $ex = $this->rds->exists($key);
        $this->assertTrue($ex);
        $this->rds->del($key);
    }

    public function testType()
    {
        $keyNx   = $this->key('type-nx');
        $keyStr  = $this->key('type-string');
        $keyList = $this->key('type-list');
        $keySet  = $this->key('type-set');
        $keyZSet = $this->key('type-zset');
        $keyHash = $this->key('type-hash');
        $this->rds->del([
            $keyNx, $keyStr, $keyList, $keySet, $keyZSet, $keyHash,
        ]);
        $this->assertEquals('none', $this->rds->type($keyNx));
        $this->rds->set($keyStr, 'string');
        $this->assertEquals('string', $this->rds->type($keyStr));
        $this->rds->lPush($keyList, 'list');
        $this->assertEquals('list', $this->rds->type($keyList));
        $this->rds->sAdd($keySet, 'set');
        $this->assertEquals('set', $this->rds->type($keySet));
        $this->rds->zAdd($keyZSet, ['zset' => 10086]);
        $this->assertEquals('zset', $this->rds->type($keyZSet));
        $this->rds->hSet($keyHash, 'set', 3101);
        $this->assertEquals('hash', $this->rds->type($keyHash));
    }

    public function testRename()
    {
        $ori    = $this->key('rename-ori');
        $dist   = $this->key('rename-dist');
        $distEx = $this->key('rename-dist-ex');


        $this->rds->del([
            $ori, $dist,
        ]);

        $this->rds->set($ori, 'ori');
        $this->rds->set($distEx, 'ori');
        $res = $this->rds->rename($ori, $dist);
        $this->assertTrue($res);

        $ex = $this->rds->renameNx($dist, $distEx);
        $this->assertFalse($ex);
        $res = $this->rds->renameNx($dist, $ori);
        $this->assertTrue($res);

        $this->rds->del([
            $ori, $distEx, $dist,
        ]);
    }

    public function testDel()
    {
        $key  = $this->key('del');
        $key1 = $this->key('del-1');
        $this->rds->set($key, 'del');
        $this->rds->set($key1, 'del');

        $int = $this->rds->del([$key, $key1]);
        $this->assertEquals(2, $int);
    }
}