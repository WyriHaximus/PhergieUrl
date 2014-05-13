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
use React\Promise\Deferred;

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
    public function __construct(array $config = array())
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
            'irc.received.privmsg' => 'handleIrcReceived',
        );
    }

    public function handleIrcReceived(\Phergie\Irc\Event\UserEvent $event, \Phergie\Irc\Bot\React\EventQueue $queue)
    {
        $params = $event->getParams();
        $extractor = new \Twitter_Extractor($params['text']);
        $urls = $extractor->extractURLs();
        array_walk($urls, array($this, 'handleUrl'));
    }

    public function handleUrl($url) {
        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['host']) && !isset($parsedUrl['path'])) {
            return;
        }

        echo $url;
        if ($this->emitUrlEvents($url)) {
            $this->emitter->emit('http.request', array(new \WyriHaximus\Phergie\Plugin\Http\Request(array(
                'url' => $url,
                'responseCallback' => function($headers, $code) {
                    var_export([$headers, $code]);
                },
                'resolveCallback' => function($data, $headers, $code) {
                    var_export([$data, $headers, $code]);
                },
            ))));
        }
    }

    protected function emitUrlEvents($url) {
        $parsedUrl = parse_url($url);

        $host = $parsedUrl['host'];
        if (count($parsedUrl) == 1 && isset($parsedUrl['path'])) {
            $host = $parsedUrl['path'];
        }

        if (substr($host, 0, 4) == 'www.') {
            $host = substr($host, 4);
        }

        if (count($this->emitter->listeners('url.host.' . $host)) > 0) {
            $this->emitter->emit('url.host.' . $host, array($url));
            return false;
        } else {
            return true;
        }
    }
    
}
