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

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Event\EventInterface;

/**
 * Plugin for Display URL information about links.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Url
 */
class Plugin extends AbstractPlugin
{
    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     *
     *
     * @param array $config
     */
    public function __construct(array $config)
    {

    }

    /**
     *
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(

        );
    }

    
}
