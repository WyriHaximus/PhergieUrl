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

class Html implements MimeInterface {

    public function getMatchingList() {
        return array(
            'text/html',
            'text/xhtml',
            'application/xhtml+xml',
        );
    }

    public function extract(array $replacements, UrlInterface $url) {
        if (preg_match('#<title[^>]*>(.*?)</title>#is', $url->getBody(), $match)) {
            $replacements['%composed-title%'] = $replacements['%title%'] = html_entity_decode(preg_replace('/[\s\v]+/', ' ', trim($match[1])));
        }

        return $replacements;
    }

}