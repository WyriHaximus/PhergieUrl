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

use Phake;

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

    public function testHandle() {
        $url = new Url('', '', array(), 200, 3.14159265359);

        $handler = Phake::partialMock('WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler');
        Phake::when($handler)->handle($url)->thenCallParent();
        Phake::when($handler)->getDefaultReplacements($url)->thenReturn(array());
        Phake::when($handler)->extract($this->isType('array'), $url)->thenReturn(array());

        $message = $handler->handle($url);
        $this->assertSame(DefaultUrlHandler::DEFAULT_PATTERN, $message);

        Phake::inOrder(
            Phake::verify($handler)->getDefaultReplacements($url),
            Phake::verify($handler)->extract($this->isType('array'), $url)
        );
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

    public function testExtract() {
        $url = new Url('', '', array(
            'Content-Type' => 'text/html',
        ), 200, 3.14159265359);
        $replacements = array();

        $html = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Mime\Html');
        Phake::when($html)->getMatchingList()->thenCallParent();
        Phake::when($html)->extract($replacements, $url)->thenReturn($replacements);

        $handler = Phake::partialMock('WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler', null, array(
            $html,
        ));

        $handler->extract(array(), $url);

        Phake::inOrder(
            Phake::verify($html)->getMatchingList(),
            Phake::verify($html)->extract($replacements, $url)
        );
    }

    public function testExtractNoMatch() {
        $url = new Url('', '', array(
            'Content-Type' => 'text/xml',
        ), 200, 3.14159265359);
        $replacements = array();

        $html = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Mime\Html');
        Phake::when($html)->getMatchingList()->thenCallParent();
        Phake::when($html)->extract($replacements, $url)->thenReturn($replacements);

        $handler = Phake::partialMock('WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler', null, array(
            $html,
        ));

        $handler->extract(array(), $url);

        Phake::verify($html)->getMatchingList();
        Phake::verifyNoFurtherInteraction($html);
    }
}