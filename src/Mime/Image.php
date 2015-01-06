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

class Image implements MimeInterface
{
    const MIME = 'image/';
    const LMIME = 6;

    public function matches($mimeType)
    {
        return (substr($mimeType, 0, static::LMIME) == static::MIME);
    }

    public function extract(array $replacements, UrlInterface $url)
    {
        $size = @\getimagesize('data://application/octet-stream;base64,'  . base64_encode($url->getBody()));
        if ($size) {
            $replacements['%image-width%'] = $size[0];
            $replacements['%image-height%'] = $size[1];
            if (isset($size['channels'])) {
                $replacements['%image-channels%'] = $size['channels'];
            }
            if (isset($size['mime'])) {
                $replacements['%image-mime%'] = $size['mime'];
            }
        }

        return $replacements;
    }
}
