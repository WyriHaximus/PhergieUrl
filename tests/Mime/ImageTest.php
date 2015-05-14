<?php
/**
 * This file is part of PhergieUrl.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WyriHaximus\Phergie\Tests\Plugin\Url\Mime;

use WyriHaximus\Phergie\Plugin\Url\Mime\Image;
use WyriHaximus\Phergie\Plugin\Url\Url;

class ImageTest extends \PHPUnit_Framework_TestCase {

    public function testMatchesProvider() {
        return array(
            array(
                false,
                'application/xhtml+xml',
            ),
            array(
                true,
                'image/*',
            ),
            array(
                true,
                'image/psd',
            ),
            array(
                true,
                'image/jpg',
            ),
            array(
                true,
                'image/png',
            ),
        );
    }

    /**
     * @dataProvider testMatchesProvider
     */
    public function testMatches($expected, $input) {
        $mime = new Image();
        $this->assertSame($expected, $mime->matches($input));
    }

    public function testExtractProvider() {
        return array(
            array(
                array(
                    '%image-width%' => 1,
                    '%image-height%' => 1,
                    '%image-channels%' => 3,
                    '%composed-title%' => 'image/gif',
                    '%image-mime%' => 'image/gif',
                ),
                new Url('', base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='), array(), 200, 1),
            ),
            array(
                array(),
                new Url('', '', array(), 200, 1),
            ),
        );
    }

    /**
     * @dataProvider testExtractProvider
     */
    public function testExtract($expected, $url) {
        $mime = new Image();
        $this->assertSame($expected, $mime->extract(array(), $url));
    }

}