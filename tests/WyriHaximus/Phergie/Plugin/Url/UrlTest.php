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
    private $url = 'http://example.com/';
    private $body = '<html><title>foo</title></html></html>';
    private $inputHeaders = array(
        'Content-Type' => 'text/html',
    );
    private $expectedHeaders = array(
        'content-type' => 'text/html',
    );
    private $code = 200;

    public function testUrl() {
        $url = new Url($this->url, $this->body, $this->inputHeaders, $this->code);
        $this->assertSame($this->url, $url->getUrl());
        $this->assertSame($this->body, $url->getBody());
        $this->assertSame($this->expectedHeaders, $url->getHeaders());
        $this->assertSame($this->code, $url->getCode());
    }
}