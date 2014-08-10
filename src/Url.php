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
    protected $headers = array();
    protected $code;
    protected $timing;
    protected $shortUrl;

    /**
     * @param string $url
     * @param string $body
     * @param array $headers
     * @param int $code
     * @param float $timing
     */
    public function __construct($url, $body, array $headers, $code, $timing, $shortUrl = null) {
        $this->url = $url;
        $this->body = $body;
        $this->code = $code;
        $this->timing = $timing;

        if ($shortUrl === null) {
            $shortUrl = $url;
        }
        $this->shortUrl = $shortUrl;

        foreach ($headers as $key => $value) {
            if (is_array($value)) {
                $this->headers[strtolower($key)] = array();
                foreach ($value as $bit) {
                    $this->headers[strtolower($key)][] = explode(';', $bit);
                }
            } else {
                $this->headers[strtolower($key)] = explode(';', $value);
            }
        }
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

    /**
     * @return float
     */
    public function getTiming() {
        return $this->timing;
    }
    /**
     * @return string
     */
    public function getShortUrl() {
        return $this->shortUrl;
    }


    /**
     * @param string $url
     *
     * @return string
     */
    public static function extractHost($url) {
        $parsedUrl = parse_url($url);

        if (count($parsedUrl) == 1 && isset($parsedUrl['path'])) {
            $host = $parsedUrl['path'];
        } else {
            $host = $parsedUrl['host'];
        }

        if (substr($host, 0, 4) == 'www.') {
            $host = substr($host, 4);
        }

        return $host;
    }
}