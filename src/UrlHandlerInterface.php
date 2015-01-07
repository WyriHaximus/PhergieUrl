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

interface UrlHandlerInterface
{
    /**
     * Handle an $url and generate a message for it.
     *
     * @param UrlInterface $url URL to work with and generate a message for.
     *
     * @return string
     */
    public function handle(UrlInterface $url);
}
