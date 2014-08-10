<?php
/**
 * This file is part of PhergieUrl.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WyriHaximus\Phergie\Tests\Plugin\Url;

use WyriHaximus\Phergie\Plugin\Url\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    private $url = 'http://example.com/';
    private $body = '<html><title>foo</title></html></html>';
    private $inputHeaders = array(
        'Content-Type' => 'text/html;charset=utf-8',
        'Set-Cookie' => array(
            'text/html;charset=utf-8',
            'text/html;charset=utf-7',
            'text/html;charset=utf-16',
        ),
    );
    private $expectedHeaders = array(
        'content-type' => array(
            'text/html',
            'charset=utf-8',
        ),
        'set-cookie' => array(
            array(
                'text/html',
                'charset=utf-8',
            ),
            array(
                'text/html',
                'charset=utf-7',
            ),
            array(
                'text/html',
                'charset=utf-16',
            ),
        ),
    );
    private $code = 200;
    private $timing = 3.14159265359;

    public function testUrl() {
        $url = new Url($this->url, $this->body, $this->inputHeaders, $this->code, $this->timing);
        $this->assertSame($this->url, $url->getUrl());
        $this->assertSame($this->body, $url->getBody());
        $this->assertSame($this->expectedHeaders, $url->getHeaders());
        $this->assertSame($this->code, $url->getCode());
        $this->assertSame($this->timing, $url->getTiming());
        $this->assertSame($this->url, $url->getShortUrl());
    }
    public function testShortUrl() {
        $url = new Url($this->url, $this->body, $this->inputHeaders, $this->code, $this->timing, 'http://t.co/');
        $this->assertSame($this->url, $url->getUrl());
        $this->assertSame($this->body, $url->getBody());
        $this->assertSame($this->expectedHeaders, $url->getHeaders());
        $this->assertSame($this->code, $url->getCode());
        $this->assertSame($this->timing, $url->getTiming());
        $this->assertSame('http://t.co/', $url->getShortUrl());
    }


    public function testExtractHostProvider() {
        return array(
            array(
                'www.google.com',
                'google.com',
            ),
            array(
                'google.com',
                'google.com',
            ),
            array(
                'http://www.google.com/',
                'google.com',
            ),
            array(
                'http://google.com/',
                'google.com',
            ),
            array(
                'https://www.google.com/',
                'google.com',
            ),
            array(
                'https://google.com/',
                'google.com',
            ),
        );
    }

    /**
     * @dataProvider testExtractHostProvider
     */
    public function testExtractHost($input, $expected) {
        $this->assertSame($expected, Url::extractHost($input));
    }
}