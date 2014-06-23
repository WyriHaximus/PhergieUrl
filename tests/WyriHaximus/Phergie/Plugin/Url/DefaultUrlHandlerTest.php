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

class DefaultUrlHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct() {
        $handler = new DefaultUrlHandler();
        $this->assertSame(DefaultUrlHandler::DEFAULT_PATTERN, $handler->getPattern());
        $handler = new DefaultUrlHandler('foo:bar');
        $this->assertSame('foo:bar', $handler->getPattern());
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testHandleEmpty() {
        $handler = new DefaultUrlHandler();
        $handler->handle();
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testHandleNull() {
        $handler = new DefaultUrlHandler();
        $handler->handle(null);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testHandleStdClass() {
        $handler = new DefaultUrlHandler();
        $handler->handle(new \stdClass());
    }

    public function testHandleProvider() {
        return array(
            array(
                null,
                new Url('http://example.com/', '<html><title>foo</title></html></html>', array(
                    'Content-Type' => 'text/html',
                ), 200, 3.14159265359),
                '[ http://example.com/ ] foo',
            ),
            array(
                null,
                new Url('http://example.com/', '<html><title>Phergie &gt; A PHP IRC Bot</title></html></html>', array(
                    'Content-Type' => 'text/html',
                ), 200, 3.14159265359),
                '[ http://example.com/ ] Phergie > A PHP IRC Bot',
            ),
            array(
                null,
                new Url('http://example.com/', '', array(
                    'Content-Type' => 'odd/non-existing',
                ), 200, 3.14159265359),
                '[ http://example.com/ ] ',
            ),
        );
    }

    /**
     * @dataProvider testHandleProvider
     */
    public function testHandle($pattern, $url, $expectedMessage) {
        $handler = new DefaultUrlHandler($pattern);
        $message = $handler->handle($url);
        $this->assertSame($expectedMessage, $message);
    }

    public function testGetDefaultReplacementsProvider() {
        return array(
            array(
                new Url('http://example.com/', '<html><title>foo</title></html></html>', array(
                    'Content-Type' => 'text/html',
                    'Age' => 13,
                    'Content-Length' => 128,
                    'Content-Language' => 'nl',
                    'Date' => 'The Darkages',
                ), 200, 3.14159265359),
                array(
                    '%url%' => 'http://example.com/',
                    '%http-status-code%' => 200,
                    '%timing%' => 3.14159265359,
                    '%timing2%' => 3.14,
                    '%title%' => '',
                    '%composed-title%' => '',
                    '%header-age%' => '13',
                    '%header-content-type%' => 'text/html',
                    '%header-content-length%' => '128',
                    '%header-content-language%' => 'nl',
                    '%header-date%' => 'The Darkages',
                    '%header-etag%' => '',
                    '%header-expires%' => '',
                    '%header-last-modified%' => '',
                    '%header-server%' => '',
                    '%header-x-powered-by%' => '',
                ),
            ),
            array(
                new Url('https://example.com/', '<html><title>bar</title></html></html>', array(
                    'Etag' => 'foo:bar',
                    'ExpirEs' => 'tomorrow',
                    'Last-Modified' => 'now',
                    'ServeR' => 'nginx',
                    'X-PoWered-By' => 'ColdFusion',
                ), 201, 3.14159265359),
                array(
                    '%url%' => 'https://example.com/',
                    '%http-status-code%' => 201,
                    '%timing%' => 3.14159265359,
                    '%timing2%' => 3.14,
                    '%title%' => '',
                    '%composed-title%' => '',
                    '%header-age%' => '',
                    '%header-content-type%' => '',
                    '%header-content-length%' => '',
                    '%header-content-language%' => '',
                    '%header-date%' => '',
                    '%header-etag%' => 'foo:bar',
                    '%header-expires%' => 'tomorrow',
                    '%header-last-modified%' => 'now',
                    '%header-server%' => 'nginx',
                    '%header-x-powered-by%' => 'ColdFusion',
                ),
            ),
        );
    }

    /**
     * @dataProvider testGetDefaultReplacementsProvider
     */
    public function testGetDefaultReplacements($url, $expectedReplacements) {
        $handler = new DefaultUrlHandler();
        $replacements = $handler->getDefaultReplacements($url);
        $this->assertSame($expectedReplacements, $replacements);
    }
}