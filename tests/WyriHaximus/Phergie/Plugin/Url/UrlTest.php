<?php
/**
 * This file is part of PhergieUrl.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WyriHaximus\Phergie\Plugin\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    const URL = 'http://example.com/';
    const BODY = '<html><title>foo</title></html></html>';
    const HEADERS = 'Content-Type-text/html';
    const CODE = 200;

    public function testUrl() {
        $url = new Url(self::URL, self::BODY, array(self::HEADERS), self::CODE);
        $this->assertSame(self::URL, $url->getUrl());
        $this->assertSame(self::BODY, $url->getBody());
        $this->assertSame(array(self::HEADERS), $url->getHeaders());
        $this->assertSame(self::CODE, $url->getCode());
    }
}