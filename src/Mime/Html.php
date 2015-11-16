<?php
/**
 * This file is part of PhergieUrl.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WyriHaximus\Phergie\Plugin\Url\Mime;

use WyriHaximus\Phergie\Plugin\Url\UrlInterface;

class Html implements MimeInterface
{

    /**
     * Return whether this mimetype is supported by this handler.
     *
     * @param string $mimeType The mimetype to check.
     *
     * @return boolean
     */
    public function matches($mimeType)
    {
        return in_array($mimeType, array(
            'text/html',
            'text/xhtml',
            'application/xhtml+xml',
        ));
    }

    /**
     * Extract all possible useful information from the given url.
     *
     * @param array        $replacements Message replacements.
     * @param UrlInterface $url          URL to extract data from.
     *
     * @return array
     */
    public function extract(array $replacements, UrlInterface $url)
    {
        if (preg_match('#<title[^>]*>(.*?)</title>#is', $url->getBody(), $match)) {
            $replacements['%composed-title%'] =
            $replacements['%title%'] =
            preg_replace('/[\s\v]+/', ' ', trim(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5)));
        }

        return $replacements;
    }
}
