<?php

namespace Poppy\Framework\Tests\Helper;

use Carbon\Carbon;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Helper\TimeHelper;

class TimeHelperTest extends TestCase
{
    public function testToString(): void
    {
        $date   = Carbon::now()->subMinutes(4000);
        $result = $date->diffForHumans(null, false, false, 3);
        $this->assertEquals('2天18小时40分钟前', $result);
    }

    public function testGmTime(): void
    {
        $result = TimeHelper::gmTime();
        $this->assertEquals($result, TimeHelper::gmTime());
    }

    public function testIsDate(): void
    {
        $this->assertEquals(true, TimeHelper::isDate('2020-11-21', '-'));
    }

    public function testDatetime(): void
    {
        $carbon = Carbon::now();
        $this->assertEquals($carbon->toDateString(), TimeHelper::datetime($carbon->timestamp, '3-0'));
    }

    public function testTime2string(): void
    {
        $this->assertEquals('0天23小时', TimeHelper::time2string('82800'));
    }

    public function testGmStr2Time(): void
    {
        $this->assertEquals('1605859200', TimeHelper::gmStr2Time('2020-11-21'));
    }

    public function testServerTimezone(): void
    {
        $this->assertEquals('PRC', TimeHelper::serverTimezone());
    }

    public function testDayStart(): void
    {
        $this->assertEquals('2020-11-20 00:00:00', TimeHelper::dayStart('1605859200'));
    }

    public function testDayEnd(): void
    {
        $this->assertEquals('2020-11-20 23:59:59', TimeHelper::dayEnd('1605859200'));
    }

    public function testFormat(): void
    {
        $this->assertEquals('2020-11-20 16:00', TimeHelper::format('1605859200'));
    }

    public function testIsEmpty(): void
    {
        $this->assertEquals(false, TimeHelper::isEmpty('1606034151'));

        $this->assertEquals(true, TimeHelper::isEmpty('0000-00-00 00:00:00'));
    }

    public function testDatetimeToTimestamp(): void
    {
        $this->assertEquals('1605917445', TimeHelper::datetimeToTimestamp('2020-11-21 08:10:45'));
    }

    public function testTimestampToDatetime(): void
    {
        $this->assertEquals('2020-11-22 16:35:51', TimeHelper::timestampToDatetime('1606034151'));
    }

    public function testTodayStart(): void
    {
        $today = Carbon::now();
        $this->assertEquals($today->startOfDay(), TimeHelper::todayStart());
    }

    public function testTodayEnd(): void
    {
        $today = Carbon::now();
        $this->assertEquals($today->endOfDay(), TimeHelper::todayEnd());
    }

    public function testTranTime(): void
    {
        $minute = 1;
        $date   = Carbon::now()->subMinutes($minute);
        $this->assertEquals($minute . '分钟前', TimeHelper::tranTime($date->timestamp));

        $minute = 60;
        $date   = Carbon::now()->subMinutes($minute);
        $this->assertEquals((int) ($minute / 60) . '小时前 ' . $date->format('H:i'), TimeHelper::tranTime($date->timestamp));

        $date = Carbon::now()->subDay();
        $this->assertEquals('昨天 ' . $date->format('m-d H:i'), TimeHelper::tranTime($date->timestamp));

        $date = Carbon::now()->subDays(2);
        $this->assertEquals('前天 ' . $date->format('m-d H:i'), TimeHelper::tranTime($date->timestamp));
    }

    public function testTimeSince(): void
    {
        $date   = Carbon::now()->subHours(999);
        $result = $date->diffForHumans();
        $this->assertEquals($result, TimeHelper::timeSince($date->timestamp));
    }

    public function testTimeTense(): void
    {
        $this->assertEquals('21 Nov 2020 at 23:59', TimeHelper::timeTense('2020-11-21 23:59:58'));
    }

    public function testMakeCarbon(): void
    {
        $carbon = TimeHelper::makeCarbon('2001-04-28');
        $this->assertNotNull($carbon);

        $carbon = TimeHelper::makeCarbon('123');
        $this->assertEquals(123, $carbon->timestamp);

        $this->assertNull(TimeHelper::makeCarbon('x45'));
    }

    public function testMomentFormat(): void
    {
        $this->assertEquals('YYYY-MM-DD', TimeHelper::momentFormat('Y-m-d'));
    }

    public function testMicro(): void
    {
        // 微秒数值 可变
        $this->assertLessThan(1000, TimeHelper::micro());
    }

    public function testFetchFormat(): void
    {
        $today = Carbon::now();
        $this->assertEquals($today->toDateTimeString(), TimeHelper::fetchFormat($today));
        $this->assertEquals('2020-11-21', TimeHelper::fetchFormat('2020-11-21'));
    }

    public function testWeek(): void
    {

        $start = Carbon::createFromFormat('Y-m-d', '2019-12-28');
        for ($i = 0; $i < 10; $i++) {
            $start->addDay();
            echo $start->format('Y-m-d,Y,W') . '-' . implode('-', TimeHelper::week($start->toDateString())) . PHP_EOL;
        }

        // 11-02 - 11-08 (2020-45)
        $this->assertEquals(['2020', '01'], TimeHelper::week('2019-12-30'));
        $this->assertEquals(['2020', '45'], TimeHelper::week('2020-11-03'));

        // 12-28 - 01 - 03(2021-01)
        $this->assertEquals(['2020', '53'], TimeHelper::week('2020-12-28'));
        $this->assertEquals(['2020', '53'], TimeHelper::week('2020-12-29'));
        $this->assertEquals(['2020', '53'], TimeHelper::week('2020-12-30'));
        $this->assertEquals(['2020', '53'], TimeHelper::week('2020-12-31'));
        $this->assertEquals(['2020', '53'], TimeHelper::week('2021-01-01'));
        $this->assertEquals(['2020', '53'], TimeHelper::week('2021-01-02'));
        $this->assertEquals(['2020', '53'], TimeHelper::week('2021-01-03'));

        // 2021-01-04
        $this->assertEquals(['2021', '01'], TimeHelper::week('2021-01-04'));
    }
}