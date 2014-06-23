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
            if (isset($headers['content-type'][0]) && in_array($headers['content-type'][0], array(
                'text/html',
                'text/xhtml',
                'application/xhtml+xml',
            ))) {
                if (preg_match('#<title[^>]*>(.*?)</title>#is', $body, $match)) {
                    $replacements['%composed-title%'] = $replacements['%title%'] = html_entity_decode(preg_replace('/[\s\v]+/', ' ', trim($match[1])));
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

        $replacements = array(
            '%url%' => $url->getUrl(),
            '%http-status-code%' => $url->getCode(),
            '%timing%' => $url->getTiming(),
            '%timing2%' => round($url->getTiming(), 2),
            '%title%' => '',
            '%composed-title%' => '',
        );

        /**
         * Selection of response headers from: http://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Response_Headers
         */
        foreach (array(
            'age',
            'content-type',
            'content-length',
            'content-language',
            'date',
            'etag',
            'expires',
            'last-modified',
            'server',
            'x-powered-by',
        ) as $header) {
            $replacements['%header-' . $header . '%'] = isset($headers[$header][0]) ? $headers[$header][0] : '';
        }

        return $replacements;
    }
}