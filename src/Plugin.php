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
use Phergie\Irc\Event\UserEvent;
use Phergie\Irc\Bot\React\EventQueue;
use WyriHaximus\Phergie\Plugin\Http\Request;

/**
 * Plugin for Display URL information about links.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Url
 */
class Plugin extends AbstractPlugin
{
    /**
     * @var UrlHandlerInterface
     */
    protected $handler = null;

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     * handler - handler to create a message for the given URL
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (isset($config['handler'])) {
            $this->handler = $config['handler'];
        } else {
            $this->handler = new DefaultUrlHandler();
        }
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

    public function getHandler() {
        return $this->handler;
    }

    public function logDebug($message) {
        $this->logger->debug('[Url]' . $message);
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
        $this->logDebug('[' . $requestId . ']Found url: ' . $url);

        if (count($parsedUrl) == 1 && isset($parsedUrl['path'])) {
            $url = 'http://' . $parsedUrl['path'] . '/';
            $this->logDebug('[' . $requestId . ']Corrected url: ' . $url);
        }

        if ($this->emitUrlEvents($requestId, $url, $event, $queue)) {
            $this->logDebug('[' . $requestId . ']Emitting: http.request');
            $that = $this;
            $this->emitter->emit('http.request', array(new Request(array(
                'url' => $url,
                'responseCallback' => function($headers, $code) use($requestId, $that) {
                    $that->logDebug('[' . $requestId . ']Reponse: ' . $code);
                },
                'resolveCallback' => function($data, $headers, $code) use($requestId, $that, $url, $event, $queue) {
                    $that->logDebug('[' . $requestId . ']Download complete: ' . strlen($data) . ' in length length');
                    $message = $that->getHandler()->handle(new Url($url, $data, $headers, $code));
                    foreach ($event->getTargets() as $target) {
                        $queue->ircPrivmsg($target, $message);
                    }
                },
            ))));
        }

        $this->logDebug('[' . $requestId . ']Emitting: url.host.all');
        $this->emitter->emit('url.host.all', array($url, $event, $queue));
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
            $this->logDebug('[' . $requestId . ']Emitting: ' . $eventName);
            $this->emitter->emit($eventName, array($url, $event, $queue));
            return false;
        } else {
            return true;
        }
    }
    
}
