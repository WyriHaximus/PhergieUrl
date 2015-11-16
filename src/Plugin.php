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

use React\EventLoop\LoopInterface;
use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueue;
use Phergie\Irc\Client\React\LoopAwareInterface;
use Phergie\Irc\Event\UserEvent;
use React\Promise\Deferred;
use WyriHaximus\Phergie\Plugin\Http\Request;

/**
 * Plugin for Display URL information about links.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Url
 */
class Plugin extends AbstractPlugin implements LoopAwareInterface
{
    const URL_HANDLER_INTERFACE = 'WyriHaximus\Phergie\Plugin\Url\UrlHandlerInterface';

    /**
     * @var UrlHandlerInterface
     */
    protected $handler = null;
    /**
     * @var UrlHandlerInterface
     */
    protected $shortingTimeout = 15;
    /**
     * @var bool
     */
    protected $hostUrlEmitsOnly = false;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     * handler - handler to create a message for the given URL
     * shortingTimeout - timeout in seconds how long it can take to short an URL
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (
            isset($config['handler']) &&
            in_array(static::URL_HANDLER_INTERFACE, class_implements($config['handler']))
        ) {
            $this->handler = $config['handler'];
        } else {
            $this->handler = new DefaultUrlHandler();
        }
        if (isset($config['shortingTimeout'])) {
            $this->shortingTimeout = $config['shortingTimeout'];
        }
        if (isset($config['hostUrlEmitsOnly'])) {
            $this->hostUrlEmitsOnly = boolval($config['hostUrlEmitsOnly']);
        }
    }

    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
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

    /**
     * @return UrlHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param string $message
     */
    public function logDebug($message)
    {
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

    /**
     * @param string $url
     * @param UserEvent $event
     * @param EventQueue $queue
     *
     * @return bool
     */
    public function handleUrl($url, UserEvent $event, EventQueue $queue)
    {
        $parsedUrl = parse_url($url);

        if (
            (!isset($parsedUrl['host']) && !isset($parsedUrl['path'])) ||
            (!isset($parsedUrl['host']) && isset($parsedUrl['path']) && $parsedUrl['path'] == '')
           ) {
            return false;
        }

        $requestId = uniqid();
        $this->logDebug('[' . $requestId . ']Found url: ' . $url);

        if (count($parsedUrl) == 1 && isset($parsedUrl['path'])) {
            $url = 'http://' . $parsedUrl['path'] . '/';
            $this->logDebug('[' . $requestId . ']Corrected url: ' . $url);
        }

        if ($this->emitUrlEvents($requestId, $url, $event, $queue) && !$this->hostUrlEmitsOnly) {
            $this->logDebug('[' . $requestId . ']Emitting: http.request');
            $this->emitter->emit('http.request', array($this->createRequest($requestId, $url, $event, $queue)));
        }

        $this->logDebug('[' . $requestId . ']Emitting: url.host.all');
        $this->emitter->emit('url.host.all', array($url, $event, $queue));

        return true;
    }

    /**
     * @param string $requestId
     * @param string $url
     * @param UserEvent $event
     * @param EventQueue $queue
     *
     * @return Request
     */
    public function createRequest($requestId, $url, UserEvent $event, EventQueue $queue)
    {
        $start = microtime(true);
        $that = $this;
        return new Request(array(
            'url' => $url,
            'responseCallback' => function ($headers, $code) use ($requestId, $that, $start) {
                $end = microtime(true);
                $that->logDebug('[' . $requestId . ']Reponse (after ' . ($end - $start) . 's): ' . $code);
            },
            'resolveCallback' =>
                function ($data, $headers, $code) use ($requestId, $that, $url, $event, $queue, $start) {
                    $end = microtime(true);
                    $message = '[';
                    $message .= $requestId;
                    $message .= ']Download complete (after ';
                    $message .= ($end - $start);
                    $message .= 's): ';
                    $message .= strlen($data);
                    $message .= ' in length length';
                    $that->logDebug($message);
                    $that->emitShortningEvents($requestId, $url)->then(
                        function ($shortUrl) use ($that, $url, $data, $headers, $code, $end, $start, $event, $queue) {
                            $that->sendMessage(
                                new Url($url, $data, $headers, $code, $end - $start, $shortUrl),
                                $event,
                                $queue
                            );
                        },
                        function () use ($that, $url, $data, $headers, $code, $end, $start, $event, $queue) {
                            $that->sendMessage(new Url($url, $data, $headers, $code, $end - $start), $event, $queue);
                        }
                    );
                }
            ,
        ));
    }

    /**
     * @param string $requestId
     * @param string $url
     * @param UserEvent $event
     * @param EventQueue $queue
     *
     * @return bool
     */
    public function emitUrlEvents($requestId, $url, UserEvent $event, EventQueue $queue)
    {
        $host = Url::extractHost($url);

        $eventName = 'url.host.' . $host;
        if (count($this->emitter->listeners($eventName)) > 0) {
            $this->logDebug('[' . $requestId . ']Emitting: ' . $eventName);
            $this->emitter->emit($eventName, array($url, $event, $queue));
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $requestId
     * @param string $url
     *
     * @return \React\Promise\DeferredPromise
     */
    public function emitShortningEvents($requestId, $url)
    {
        $host = Url::extractHost($url);
        list($privateDeferred, $userFacingPromise) = $this->preparePromises();

        $eventName = 'url.shorting.';
        if (count($this->emitter->listeners($eventName . $host)) > 0) {
            $eventName .= $host;
            $this->logDebug('[' . $requestId . ']Emitting: ' . $eventName);
            $this->emitter->emit($eventName, array($url, $privateDeferred));
        } elseif (count($this->emitter->listeners($eventName . 'all')) > 0) {
            $eventName .= 'all';
            $this->logDebug('[' . $requestId . ']Emitting: ' . $eventName);
            $this->emitter->emit($eventName, array($url, $privateDeferred));
        } else {
            $this->loop->addTimer(0.1, function () use ($privateDeferred) {
                $privateDeferred->reject();
            });
        }

        return $userFacingPromise;
    }

    /**
     * @return array
     */
    public function preparePromises()
    {
        $userFacingDeferred = new Deferred();
        $privateDeferred = new Deferred();
        $userFacingPromise = $userFacingDeferred->promise();
        $privateDeferred->promise()->then(function ($shortUrl) use ($userFacingDeferred) {
            $userFacingDeferred->resolve($shortUrl);
        }, function () use ($userFacingDeferred) {
            $userFacingDeferred->reject();
        });
        $this->loop->addTimer($this->shortingTimeout, function () use ($privateDeferred) {
            $privateDeferred->reject();
        });

        return array(
            $privateDeferred,
            $userFacingPromise,
        );
    }

    /**
     * @param Url $url
     * @param UserEvent $event
     * @param EventQueue $queue
     */
    public function sendMessage(Url $url, UserEvent $event, EventQueue $queue)
    {
        $message = $this->getHandler()->handle($url);
        $queue->ircPrivmsg($event->getSource(), $message);
    }
}
