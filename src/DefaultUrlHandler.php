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
 * Default URL handler to create a message about a
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Url
 */
class DefaultUrlHandler implements UrlHandlerInterface
{
    const HTTP_STATUS_OK = 200;

    /**
     * Pattern used to format feed items
     *
     * @var string
     */
    protected $pattern;

    /**
     * Default pattern used to format feed items if none is provided via
     * configuration
     *
     * @var string
     */
    const DEFAULT_PATTERN = '[ %url% ] %composed-title%';

    /**
     * Accepts format pattern.
     *
     * @param string $pattern
     */
    public function __construct($pattern = null)
    {
        $this->pattern = $pattern ? $pattern : static::DEFAULT_PATTERN;
    }

    public function getPattern() {
        return $this->pattern;
    }

    public function handle(UrlInterface $url) {
        $headers = $url->getHeaders();
        $body = $url->getBody();

        $replacements = $this->getDefaultReplacements($url);

        if ($url->getCode() == static::HTTP_STATUS_OK) {
            if (isset($headers['content-type']) && in_array($headers['content-type'], array(
                'text/html',
                'text/xhtml',
                'application/xhtml+xml',
            ))) {
                if (preg_match('#<title[^>]*>(.*?)</title>#is', $body, $match)) {
                    $replacements['%composed-title%'] = $replacements['%title%'] = preg_replace('/[\s\v]+/', ' ', trim($match[1]));
                }
            }
        }

        $formatted = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->pattern
        );

        return $formatted;
    }

    public function getDefaultReplacements(UrlInterface $url) {
        $headers = $url->getHeaders();

        return array(
            '%url%' => $url->getUrl(),
            '%http-status-code%' => $url->getCode(),
            /**
             * Selection of response headers from: http://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Response_Headers
             */
            '%header-age%' => isset($headers['age']) ? $headers['age'] : '',
            '%header-content-type%' => isset($headers['content-type']) ? $headers['content-type'] : '',
            '%header-content-length%' => isset($headers['content-length']) ? $headers['content-length'] : '',
            '%header-content-language%' => isset($headers['content-language']) ? $headers['content-language'] : '',
            '%header-date%' => isset($headers['date']) ? $headers['date'] : '',
            '%header-etag%' => isset($headers['etag']) ? $headers['etag'] : '',
            '%header-expires%' => isset($headers['expires']) ? $headers['expires'] : '',
            '%header-last-modified%' => isset($headers['last-modified']) ? $headers['last-modified'] : '',
            '%header-server%' => isset($headers['server']) ? $headers['server'] : '',
            '%header-x-powered-by%' => isset($headers['x-powered-by']) ? $headers['x-powered-by'] : '',
            '%title%' => '',
            '%composed-title%' => '',
        );
    }
}