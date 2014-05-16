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
use \Phergie\Irc\Event\UserEvent;
use \Phergie\Irc\Bot\React\EventQueue;

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

    public function handleIrcReceived(UserEvent $event, EventQueue $queue)
    {
        $params = $event->getParams();
        $extractor = new \Twitter_Extractor($params['text']);
        $urls = $extractor->extractURLs();

        foreach ($urls as $url) {
            $this->handleUrl($url, $event, $queue);
        }
    }

    protected function handleUrl($url, $event, $queue) {
        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['host']) && !isset($parsedUrl['path'])) {
            return;
        }

        $requestId = uniqid();
        $this->logger->debug('[' . $requestId . ']Found url: ' . $url);

        if ($this->emitUrlEvents($requestId, $url, $event, $queue)) {
            $this->logger->debug('[' . $requestId . ']Emitting: http.request');
            $logger = $this->logger;
            $this->emitter->emit('http.request', array(new \WyriHaximus\Phergie\Plugin\Http\Request(array(
                'url' => $url,
                'responseCallback' => function($headers, $code) use($requestId, $logger) {
                    $logger->debug('[' . $requestId . ']Reponse: ' . $code);
                },
                'resolveCallback' => function($data, $headers, $code) use($requestId, $logger) {
                    $logger->debug('[' . $requestId . ']Download complete: ' . strlen($data) . ' in length length');
                },
            ))));
        }
    }

    protected function emitUrlEvents($requestId, $url, UserEvent $event, EventQueue $queue) {
        $parsedUrl = parse_url($url);

        if (count($parsedUrl) == 1 && isset($parsedUrl['path'])) {
            $host = $parsedUrl['path'];
        } else {
            $host = $parsedUrl['host'];
        }

        if (substr($host, 0, 4) == 'www.') {
            $host = substr($host, 4);
        }

        $eventName = 'url.host.' . $host;
        if (count($this->emitter->listeners($eventName)) > 0) {
            $this->logger->debug('[' . $requestId . ']Emitting: ' . $eventName);
            $this->emitter->emit($eventName, array($url, $event, $queue));
            return false;
        } else {
            return true;
        }
    }
    
}
