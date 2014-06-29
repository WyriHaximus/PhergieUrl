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

use WyriHaximus\Phergie\Plugin\Url\Url;

class HtmlTest extends \PHPUnit_Framework_TestCase {

    public function testGetMatchingList() {
        $mime = new Html();
        $this->assertSame(array(
            'text/html',
            'text/xhtml',
            'application/xhtml+xml',
        ), $mime->getMatchingList());
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