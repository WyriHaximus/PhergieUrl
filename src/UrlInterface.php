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

    /**
     * @return integer
     */
    public function getCode();

    /**
     * @return string
     */
    public function getBody();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return double
     */
    public function getTiming();
}
