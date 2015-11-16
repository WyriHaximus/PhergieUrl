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
        $this->assertInstanceOf('WyriHaximus\Phergie\Plugin\Url\UrlHandlerInterface', $plugin->getHandler());
        $this->assertInstanceOf('WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler', $plugin->getHandler());
    }

    public function testCustomHandler() {
        $handler = Phake::mock('WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler');
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
        $this->assertInstanceOf('WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler', $plugin->getHandler());
    }

    public function testHandleIrcReceived() {
        $queue = Phake::mock('Phergie\Irc\Bot\React\EventQueue');

        $event = Phake::mock('Phergie\Irc\Event\UserEvent');
        Phake::when($event)->getParams()->thenReturn(array(
            'text' => 'test www.google.com test',
        ));

        $plugin = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Plugin');
        Phake::when($plugin)->handleIrcReceived($event, $queue)->thenCallParent();

        $plugin->handleIrcReceived($event, $queue);

        Phake::verify($plugin)->handleUrl('www.google.com', $event, $queue);
    }

    public function testPreparePromises() {
        $plugin = new Plugin();
        $plugin->setLoop(Phake::mock('React\EventLoop\LoopInterface'));

        list($privateDeferred, $userFacingPromise) = self::getMethod('preparePromises')->invoke($plugin);

        $this->assertInstanceOf('React\Promise\Deferred', $privateDeferred);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $userFacingPromise);
    }

    public function testSendMessage() {
        $target = '#foobar';
        $message = 'foo:bar';

        $url = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Url');
        $handler = Phake::mock('WyriHaximus\Phergie\Plugin\Url\UrlHandlerInterface');
        Phake::when($handler)->handle($url)->thenReturn($message);

        $plugin = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Plugin');
        Phake::when($plugin)->getHandler()->thenReturn($handler);

        $event = Phake::mock('Phergie\Irc\Event\UserEvent');
        Phake::when($event)->getSource()->thenReturn($target);

        $queue = Phake::mock('Phergie\Irc\Bot\React\EventQueue');

        self::getMethod('sendMessage')->invokeArgs($plugin, array($url, $event, $queue));

        Phake::verify($queue)->ircPrivmsg($target, $message);
    }

    public function testEmitUrlEvents() {
        $host = 'google.com';
        $eventName = 'url.host.' . $host;
        $url = 'http://' . $host . '/';

        $queue = Phake::mock('Phergie\Irc\Bot\React\EventQueue');
        $event = Phake::mock('Phergie\Irc\Event\UserEvent');

        $emitter = Phake::mock('Evenement\EventEmitterInterface');
        Phake::when($emitter)->listeners($eventName)->thenReturn(array('foo' => 'bar'));

        $plugin = new Plugin();
        $plugin->setEventEmitter($emitter);
        $plugin->setLogger(Phake::mock('Monolog\Logger'));

        $this->assertNotTrue(self::getMethod('emitUrlEvents')->invokeArgs($plugin, array(
            'foo:bar',
            $url,
            $event,
            $queue,
        )));

        Phake::inOrder(
            Phake::verify($emitter)->listeners($eventName),
            Phake::verify($emitter)->emit($eventName, array($url, $event, $queue))
        );
    }

    public function testEmitShortningEventsProvider() {
        return array(
            array(
                'url.shorting.google.com',
                'http://google.com/',
            ),
            array(
                'url.shorting.all',
                'http://google.com/',
            ),
        );
    }

    /**
     * @dataProvider testEmitShortningEventsProvider
     */
    public function testEmitShortningEvents($eventName, $url) {
        $logger = Phake::mock('Monolog\Logger');
        $privateDeferred = Phake::mock('React\Promise\Deferred');

        $emitter = Phake::mock('Evenement\EventEmitterInterface');
        Phake::when($emitter)->listeners($eventName)->thenReturn(array('foo' => 'bar'));

        $plugin = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Plugin');
        Phake::when($plugin)->preparePromises()->thenReturn(array(
            $privateDeferred,
            Phake::mock('React\Promise\PromiseInterface'),
        ));
        Phake::when($plugin)->setEventEmitter($emitter)->thenCallParent();
        Phake::when($plugin)->setLogger($logger)->thenCallParent();

        $plugin->setEventEmitter($emitter);
        $plugin->setLogger($logger);

        $this->assertInstanceOf('React\Promise\PromiseInterface', self::getMethod('emitShortningEvents')->invokeArgs($plugin, array(
            'foo:bar',
            $url,
        )));

        Phake::inOrder(
            Phake::verify($emitter)->listeners($eventName),
            Phake::verify($emitter)->emit($eventName, array($url, $privateDeferred))
        );
    }

    public function testEmitShortningNone() {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $url = 'http://google.com/';
        $logger = Phake::mock('Monolog\Logger');
        $privateDeferred = Phake::mock('React\Promise\Deferred');

        Phake::when($loop)->listeners('url.shorting.google.com')->thenReturn(array());

        $emitter = Phake::mock('Evenement\EventEmitterInterface');
        $plugin = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Plugin');
        Phake::when($plugin)->preparePromises()->thenReturn(array(
            $privateDeferred,
            Phake::mock('React\Promise\PromiseInterface'),
        ));
        Phake::when($plugin)->setEventEmitter($emitter)->thenCallParent();
        Phake::when($plugin)->setLogger($logger)->thenCallParent();
        Phake::when($plugin)->setLoop($loop)->thenCallParent();

        $plugin->setEventEmitter($emitter);
        $plugin->setLogger($logger);
        $plugin->setLoop($loop);

        $this->assertInstanceOf('React\Promise\PromiseInterface', self::getMethod('emitShortningEvents')->invokeArgs($plugin, array(
            'foo:bar',
            $url,
        )));

        Phake::inOrder(
            Phake::verify($loop)->addTimer(0.1, $this->isType('callable'))
        );
    }

    public function testCreateRequest() {
        $url = 'http://example.com/';

        $queue = Phake::mock('Phergie\Irc\Bot\React\EventQueue');
        $event = Phake::mock('Phergie\Irc\Event\UserEvent');

        $plugin = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Plugin');
        $request = self::getMethod('createRequest')->invokeArgs($plugin, array(
            'foo:bar',
            $url,
            $event,
            $queue,
        ));
        $this->assertInstanceOf('WyriHaximus\Phergie\Plugin\Http\Request', $request);

        $request->callResponse(array(
            'foo' => 'bar',
        ), 200);

        Phake::when($plugin)->emitShortningEvents($this->isType('string'), $this->isType('string'))->thenReturn(Phake::mock('React\Promise\PromiseInterface'));

        $request->callResolve('', array(
            'foo' => 'bar',
        ), 200);

        Phake::inOrder(
            Phake::verify($plugin, Phake::times(2))->logDebug($this->isType('string')),
            Phake::verify($plugin)->emitShortningEvents($this->isType('string'), $this->isType('string'))
        );
    }

    public function testHandleUrlUselessUrlProvider() {
        return array(
            array(''),
            array('http://'),
        );
    }

    /**
     * @dataProvider testHandleUrlUselessUrlProvider
     */
    public function testHandleUrlUselessUrl($url) {
        $this->assertNotTrue(self::getMethod('handleUrl')->invokeArgs(Phake::mock('WyriHaximus\Phergie\Plugin\Url\Plugin'), array(
            $url,
            Phake::mock('Phergie\Irc\Event\UserEvent'),
            Phake::mock('Phergie\Irc\Bot\React\EventQueue'),
        )));
    }

    public function testHandleUrlNoRequest() {
        $url = 'example.com';
        $correctedUrl = 'http://example.com/';

        $queue = Phake::mock('Phergie\Irc\Bot\React\EventQueue');
        $event = Phake::mock('Phergie\Irc\Event\UserEvent');

        $emitter = Phake::mock('Evenement\EventEmitterInterface');

        $plugin = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Plugin');
        Phake::when($plugin)->setEventEmitter($emitter)->thenCallParent();
        Phake::when($plugin)->emitUrlEvents($this->isType('string'), $correctedUrl, $event, $queue)->thenReturn(false);
        $plugin->setEventEmitter($emitter);


        $this->assertTrue(self::getMethod('handleUrl')->invokeArgs($plugin, array(
            $url,
            $event,
            $queue,
        )));

        Phake::inOrder(
            Phake::verify($plugin)->emitUrlEvents($this->isType('string'), $correctedUrl, $event, $queue),
            Phake::verify($emitter)->emit('url.host.all', array(
                $correctedUrl,
                $event,
                $queue,
            ))
        );
    }

    public function testHandleUrlRequest() {
        $url = 'http://example.com/';

        $queue = Phake::mock('Phergie\Irc\Bot\React\EventQueue');
        $event = Phake::mock('Phergie\Irc\Event\UserEvent');
        $emitter = Phake::mock('Evenement\EventEmitterInterface');
        $request = Phake::mock('WyriHaximus\Phergie\Plugin\Http\Request');

        $plugin = Phake::mock('WyriHaximus\Phergie\Plugin\Url\Plugin');
        Phake::when($plugin)->setEventEmitter($emitter)->thenCallParent();
        Phake::when($plugin)->emitUrlEvents($this->isType('string'), $url, $event, $queue)->thenReturn(true);
        Phake::when($plugin)->createRequest($this->isType('string'), $url, $event, $queue)->thenReturn($request);
        $plugin->setEventEmitter($emitter);


        $this->assertTrue(self::getMethod('handleUrl')->invokeArgs($plugin, array(
            $url,
            $event,
            $queue,
        )));

        Phake::inOrder(
            Phake::verify($plugin)->emitUrlEvents($this->isType('string'), $url, $event, $queue),
            Phake::verify($emitter)->emit('http.request', array($request)),
            Phake::verify($emitter)->emit('url.host.all', array(
                $url,
                $event,
                $queue,
            ))
        );
    }
}
