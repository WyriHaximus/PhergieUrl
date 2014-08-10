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

use Phake;
use WyriHaximus\Phergie\Plugin\Url\Plugin;

/**
 * Tests for the Plugin class.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Url
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    protected static function getMethod($name) {
        $class = new \ReflectionClass('WyriHaximus\Phergie\Plugin\Url\Plugin');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function testGetSubscribedEvents()
    {
        $plugin = new Plugin;
        $subscribedEvents = $plugin->getSubscribedEvents();
        $this->assertInternalType('array', $subscribedEvents);
        $this->assertSame(array(
            'irc.received.privmsg' => 'handleIrcReceived',
        ), $subscribedEvents);
    }

    public function testLogDebug() {
        $logger = $this->getMock('Monolog\Logger', array(
            'debug',
        ), array(
            'test',
        ));
        $logger->expects($this->once())
            ->method('debug')
            ->with('[Url]foo:bar');

        $plugin = new Plugin();
        $plugin->setLogger($logger);
        $plugin->logDebug('foo:bar');
    }

    public function testGetHandler() {
        $plugin = new Plugin();
        $this->assertTrue(in_array('WyriHaximus\Phergie\Plugin\Url\UrlHandlerInterface', class_implements($plugin->getHandler())));
        $this->assertInstanceOf('\WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler', $plugin->getHandler());
    }

    public function testCustomHandler() {
        $handler = Phake::mock('\WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler');
        $plugin = new Plugin(array(
            'handler' => $handler,
        ));
        $this->assertTrue(in_array('WyriHaximus\Phergie\Plugin\Url\UrlHandlerInterface', class_implements($plugin->getHandler())));
        $this->assertSame($handler, $plugin->getHandler());
    }

    public function testStdClassHandler() {
        $handler = new \stdClass();
        $plugin = new Plugin(array(
            'handler' => $handler,
        ));
        $this->assertInstanceOf('\WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler', $plugin->getHandler());
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
        );
    }

    /**
     * @dataProvider testExtractHostProvider
     */
    public function testExtractHost($input, $expected) {
        $method = self::getMethod('extractHost');
        $this->assertSame($expected, $method->invokeArgs(new Plugin(), array($input)));
    }
}
