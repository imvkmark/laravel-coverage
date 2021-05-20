<?php namespace Site\Tests\Oss;

use Poppy\System\Classes\Contracts\UploadContract;
use Poppy\System\Classes\Uploader\DefaultUploadProvider;
use Poppy\System\Tests\Base\SystemTestCase;

class StsTest extends SystemTestCase
{
    public function testImage(): void
    {
        $image   = 'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAFVElEQVRYR62Xe2xTdRTHv+fe23Z0a8fWbW3ZZgdqhm5dBMUgUR4hWRAhKAgCGyAmEHyhUUHjg0QNfwD/mJCof6BOWIEBGv+QoIYIWTQhZoPAyjuBLUDXbunG2Ku9vfce066j29rbwuD3X3vO95zPOff3JNzH8NYGlkHTZjFRMQAXgDIAuQRcA+EaM98ACV4hLDdUHC7tupfQlMmpaWOTwdhX/AERrQTwVCb/qJ0BH5gPimT8ocJju5BOkxagZVXAzoJWR6AF95J4rA8B5wmGFekgdAFa1vieYI3qCTR9PMmHNZkgdAG8tYFDzLz8QZLf1TLvde93rksVKyXAudr29cT040NJHg8ikPBiRX3RHyk+U3KaczX+4wTMV2xGiD0RkMIPg6XB7XFEJ/KokdSBC7WdTpVVX+jxbFBIg2qRYPSHIHVFHgyCEBbCdmvFYZJHBkoCaFkdWAriX5AvoPu5Qhg6wtBMAqz/6C9rS6WG/FkqbjeL6GkWdEEZvLDK4zyWFsBbE9jK4B1iNkMdFKDkGSAFR0HH9FIuY0IpxxJHAYZH9ykRwZMiQr5EbYKJoYVjvze7PY7daQEubW7fYZutbpUsgGBkaDLAMkENA1oIsaQmB8NYkH5e9F9NdELuimoJoQD2lbxZslYXgINtTwI4/2AfO5Nae4Nsk38asU8kBBxsPQrQQoTuAFnWpEj9/QOQIxHkTcxNmeVaaxumlEWPiBSjxwfkTgKYT1NB2dM6AG3dUOSJ6LgCTKpMivLq2o2YMtmFnV9+lmS7ePkqVm94B59++C6WL1mUTODzAjYXYLL0kc1l0QM4Eztw/JcAx9SkIBve24JQWMbnH21G+WOPjrL7/AFs3bYdrtJibP/i42SAwGXAVgZIpgDZXA49gJ0AtmT6iuOyqwogStGj8mcqcL2eEiD6Z/C3G3/mv6BVxxz2NQCvvATsqQfe35ScN9gKCCKQV5ps++Z7YNUy4K+/gTWvxeyRbmo2FFI15SbuCskbUU37d1IONpWsU5BTnljf46o6Lgr7Cdd3G6D2Cm9Xeuzf6i7DqKGlxt8AYMXEZ1WU1Cr6eb/aBRTahuxhOXWH4uqb9RJu/yeCiL6urLdvSwvgrfEfZ2B+1Mm5VIFtrpoaou4A0NEFhEJA9Rxg5jMp/YKNItqPSEM2xh73fseGTB0YWgnxYV80BCEY7/8jdP0rwtcQTz4k/93tcSzO1IE2Bh4Z6ZTlZOQ/r8I6TYOUk34L1sLAnbMibjcL6Ls45mAiNLnrHTMydaAPQLZevbnTNBQv6YBg0gAhXp2mQAsLuPlrEXovCmCdk5uBW1UeR4kuwKmaq9ZsWHoyNbv8kwAM2YOAEgI0DTBlQ+7JwpVd9rRSBlS3bDfRYbo7sUYtwzMr28skka7rRVEKjLC/rMJZNZDSxXfWjM4jQuwWpTeMolRSvrfg1rB9FMDZ2s7pAqvNY8XRxP1VVmgTRBgVBe45nTDnjE4y0GdAS2MhZFGC2KfA3NILqSv5HgFVm+E+OKkpJcD5lR3TNFE7PRKATQJ6Z+ZBzUnMZmJGlhiBeUIk9goZGDQgpBrAQqIesVeB5VQ3SB69mQkGwVlRV+RPCdC02Gc2WYWjAOYOOwxUWBAuM2eaFintprZBmL137toIVFfpsa9PuwqixpaazkVgdQoRJncvKHqLRRrHLgCQBjnvWGCnSmgjjVqr9tuPjyVN+zQ7sY/niiJOjKv8uEhVMW/eGjqpFyPj47TxAB8CY7wvJM/s1VSbroCMAFFx4yEuJAXLGZgKgoMYDo3hJELsYsEMv0BoZ4IfDD8xvKIBR2atoIxP9P8B3aTvMGDYAIEAAAAASUVORK5CYII=';
        $content = base64_decode($image);
        /** @var DefaultUploadProvider $Image */
        $Image = app(UploadContract::class);
        if (!$Image->saveInput($content)) {
            $this->assertTrue(false, $Image->getError());
        }
        $url = $Image->getUrl();
        $this->assertIsString($url);
    }
}