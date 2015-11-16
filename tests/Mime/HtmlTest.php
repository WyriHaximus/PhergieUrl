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

use WyriHaximus\Phergie\Plugin\Url\Mime\Html;
use WyriHaximus\Phergie\Plugin\Url\Url;

class HtmlTest extends \PHPUnit_Framework_TestCase {

    public function testMatchesProvider() {
        return array(
            array(
                true,
                'text/html',
            ),
            array(
                true,
                'text/xhtml',
            ),
            array(
                true,
                'application/xhtml+xml',
            ),
            array(
                false,
                'image/*',
            ),
        );
    }

    /**
     * @dataProvider testMatchesProvider
     */
    public function testMatches($expected, $input) {
        $mime = new Html();
        $this->assertSame($expected, $mime->matches($input));
    }

    public function testExtractProvider() {
        return array(
            array(
                array(
                    '%title%' => 'foo',
                    '%composed-title%' => 'foo',
                ),
                new Url('', '<html><title>foo</title></html></html>', array(), 200, 1),
            ),
            array(
                array(
                    '%title%' => 'foo\'s wörk',
                    '%composed-title%' => 'foo\'s wörk',
                ),
                new Url('', '<html><title>foo&#39;s w&ouml;rk</title></html></html>', array(), 200, 1),
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
        $mime = new Html();
        $this->assertSame($expected, $mime->extract(array(), $url));
    }

}
