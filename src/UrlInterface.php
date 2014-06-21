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
 * Interface UrlInterface
 *
 * @package WyriHaximus\Phergie\Plugin\Url
 */
interface UrlInterface
{
    public function getHeaders();
    public function getCode();
    public function getBody();
    public function getUrl();
    public function getTiming();
}