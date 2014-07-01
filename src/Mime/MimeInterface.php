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

/**
 * Interface MimeInterface
 *
 * @package WyriHaximus\Phergie\Plugin\Url\Mime
 */
interface MimeInterface {

    /**
     * @return bool if MIME type matches
     */
    public function matches($mimeType);

    /**
     * Extract all possible usefull information from the given url
     *
     * @param array $replacements
     * @param UrlInterface $url
     *
     * @return array
     */
    public function extract(array $replacements, UrlInterface $url);

}