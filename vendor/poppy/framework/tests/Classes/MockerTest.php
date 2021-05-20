<?php

namespace Poppy\Framework\Tests\Classes;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Classes\Mocker;
use Poppy\Framework\Helper\UtilHelper;

class MockerTest extends TestCase
{
    public function testRandom(): void
    {
        $json = <<<JSON
{
    "name|2" : "name",
    "master" : "*",
    "unixTime" : "unixTime",
    "url-image" : "imageUrl(400,20)",
    "url-image-slash" : "imageUrl|400,20",
    "url" : "imageUrl()",
    "urls-array|5" : [
        "imageUrl"
    ],
    "urls|5" : [
        {
            "url": "url"
        }
    ]
}
JSON;
        $gen  = Mocker::generate($json);
        $this->assertIsString($gen['name']);
        $this->assertTrue(UtilHelper::isUrl($gen['url']));


        $array = <<<JSON
[
    {
       "id": 1,
       "title": "北京市",
       "code": "110000",
       "children|6": [ {
            "id": 32,
            "title": "市辖区",
            "code": "110100",
            "children": [{
                "id": 1,
                "title": "北京市",
                "code": "110000",
                "children": [{
                     "id": 32,
                     "title": "市辖区",
                     "code": "110100"
                }]
            }]
        }]
    }
]
JSON;

    }
}