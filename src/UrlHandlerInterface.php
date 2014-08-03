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

use React\Promise\DeferredPromise;

interface UrlHandlerInterface
{
    public function handle(UrlInterface $url, DeferredPromise $promise);
}