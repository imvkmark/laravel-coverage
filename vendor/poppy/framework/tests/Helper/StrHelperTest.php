<?php

namespace Poppy\Framework\Tests\Helper;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Helper\FileHelper;
use Poppy\Framework\Helper\StrHelper;

/**
 * ArrayHelperTest
 */
class StrHelperTest extends TestCase
{
    public function testSuffix(): void
    {
        $this->assertEquals('jpeg', StrHelper::suffix('demo.jpeg', '.'));
    }

    public function testPrefix(): void
    {
        $this->assertEquals('demo', StrHelper::prefix('demo.jpeg', '.'));
    }

    public function testHasSpace(): void
    {
        $this->assertEquals(true, StrHelper::hasSpace('this '));
    }

    public function testStripSlashes(): void
    {
        $this->assertEquals('this', StrHelper::stripSlashes('\\this\\'));
    }

    public function testAddSlashes(): void
    {
        $this->assertEquals("{\'test\':\'xxx\'}", StrHelper::addSlashes("{'test':'xxx'}"));
    }

    public function testHtmlSpecialChars(): void
    {
        $this->assertEquals('&lt;a&gt;&lt;/a&gt;', StrHelper::htmlSpecialChars("<a></a>"));
    }

    public function testSafe(): void
    {
        $this->assertEquals('</ br>', StrHelper::safe('</ br>'));
    }

    public function testTrimEOL(): void
    {
        $this->assertEquals('a  b', StrHelper::trimEOL('a' . '  ' . PHP_EOL . 'b'));
    }

    public function testTrimSpace(): void
    {
        $this->assertEquals('ab', StrHelper::trimSpace('a' . PHP_EOL . '  ' . 'b'));
    }

    public function testCut(): void
    {
        $this->assertEquals('forma', StrHelper::cut('information', 5, 'a', 2));
    }

    public function testToHex(): void
    {
        $this->assertEquals('616374', StrHelper::toHex('act'));
    }

    public function testFromHex(): void
    {
        $this->assertEquals('acv', StrHelper::fromHex('616376'));
    }

    public function testRandomCustom(): void
    {
        $result = preg_match('/[a-zA-Z0-9]+$/', StrHelper::randomCustom('4'));
        $this->assertEquals(1, $result);
    }

    public function testRandom(): void
    {
        $this->assertEquals(1, strlen(StrHelper::randomAscii(1)));
    }

    public function testRandomNumber(): void
    {
        $result = preg_match('/\d+$/', StrHelper::randomNumber(1, 9999));
        $this->assertEquals(1, $result);
    }

    public function testConvert(): void
    {
        $this->assertEquals('act&lt;', StrHelper::convert('act&lt;'));
    }

    public function testCh2Utf8(): void
    {
        $this->assertEquals('头条H', StrHelper::ch2Utf8('头条H'));
    }

    public function testCount(): void
    {
        $this->assertEquals('4', StrHelper::count('YL娱乐'));
    }

    public function testIsUtf8(): void
    {
        $this->assertEquals(true, StrHelper::isUtf8('ZH??'));
    }

    public function testChrysanthemum(): void
    {
        $this->assertEquals('a&#1161;&&#1161; &#1161;', StrHelper::chrysanthemum('a& '));
    }

    public function testJsEscape(): void
    {
        $this->assertEquals('var t=document;\&', StrHelper::jsEscape('var t=document;&'));
    }

    public function testSeparate(): void
    {
        $this->assertEquals(['author', 'EN'], StrHelper::separate('-', 'author-EN'));
    }

    public function testParseKey(): void
    {
        $this->assertEquals(['author' => 'zh'], StrHelper::parseKey('author|zh'));
    }

    public function testMatchEncode(): void
    {
        $this->assertEquals(',_124_,', StrHelper::matchEncode('124'));
    }

    public function testMatchDecode(): void
    {
        $this->assertEquals(['124', '1'], StrHelper::matchDecode('124_,_1', true));
    }

    public function testHideContact(): void
    {
        $this->assertEquals('155****2279', StrHelper::hideContact('15533012279'));
    }

    public function testHideEmail(): void
    {
        $this->assertEquals('925****@qq.com', StrHelper::hideEmail('9252582219@qq.com'));
    }

    public function testOrdinal(): void
    {
        $this->assertEquals('101st', StrHelper::ordinal('101'));
    }

    public function testNormalizeEol(): void
    {
        $this->assertEquals('manual?', StrHelper::normalizeEol('manual?'));
    }

    public function testNormalizeClassName(): void
    {
        $this->assertEquals('\Poppy\Framework\Helper\FileHelper', StrHelper::normalizeClassName(FileHelper::class));
    }

    public function testGetClassId(): void
    {
        $this->assertEquals('poppy_framework_helper_filehelper', StrHelper::getClassId(FileHelper::class));
    }

    public function testGetClassNamespace(): void
    {
        $this->assertEquals('\Poppy\Framework\Helper', StrHelper::getClassNamespace(FileHelper::class));
    }

    public function testClearLink(): void
    {
        // todo li
        $this->assertEquals('www.php.net', StrHelper::clearLink('<a href="https://www.php.net">www.php.net</a>'));
    }

    public function testFixLink(): void
    {
        $this->assertEquals('http://www.php.net/*', StrHelper::fixLink('www.php.net/*'));
    }

    public function testIntro(): void
    {
        $this->assertEquals('int...', StrHelper::intro('intro spec', 6));
    }

    public function testUnique(): void
    {
        $this->assertEquals('work,1', StrHelper::unique('work', '1'));

        $this->assertEquals('work', StrHelper::unique('work', '0'));

        $this->assertEquals('work', StrHelper::unique('work', ''));
    }
}