<?php namespace Site\Tests\Configuration;

use Curl\Curl;
use Poppy\System\Tests\Base\SystemTestCase;
use Throwable;

class SocketTest extends SystemTestCase
{

    public function testUrl()
    {
        $Curl = new Curl();
        try {
            $Curl->get(url('socket'));
            if ($Curl->errorCode !== 400) {
                $this->assertTrue(false, 'Http Socket Client Not Start!');
            }
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->assertTrue(false, $e->getMessage());
        }
    }
}