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
use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Bot\React\EventQueueInterface;

/**
 * Tests for the Plugin class.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Url
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{


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
}
