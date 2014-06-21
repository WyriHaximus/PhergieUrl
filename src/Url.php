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

/**
 * Url value object
 *
 * @package WyriHaximus\Phergie\Plugin\Url
 */
class Url implements UrlInterface
{
    protected $url;
    protected $body;
    protected $headers;
    protected $code;

    /**
     * @param string $url
     * @param string $body
     * @param array $headers
     * @param int $code
     */
    public function __construct($url, $body, array $headers, $code) {
        $this->url = $url;
        $this->body = $body;
        $this->headers = $headers;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getCode() {
        return $this->code;
    }
}