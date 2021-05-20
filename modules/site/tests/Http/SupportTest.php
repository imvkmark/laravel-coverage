<?php namespace Site\Tests\Http;

use Poppy\System\Tests\Base\SystemTestCase;

class SupportTest extends SystemTestCase
{
    public function testQrcode()
    {
        $url      = route_url('site:web.support_util.qrcode', ['t' => $this->faker()->url]);
        $response = $this->get($url);
        $response->assertStatus(200);
    }
}