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
 *
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Url
 */
class UrlShortningEvent
{
    protected $url;
    protected $promise;

    public function __construct(UrlInterface $url, $promise) {
        $this->url = $url;
        $this->promise = $promise;
    }

    public function getUrl() {
        return $this->url;
    }
    public function getPromise() {
        return $this->promise;
    }
}