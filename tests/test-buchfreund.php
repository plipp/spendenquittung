<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("platforms/platforms.php");

class BuchfreundTest extends PHPUnit_Framework_TestCase
{

    function testParsePrices()
    {
        $html = file_get_contents("tests/buchfreund-with-books.html");
        $this->assertFalse(empty($html));

        $buchfreund = new Buchfreund(json_decode(self::BUCHFREUND_AS_JSON, TRUE));
        $this->assertEquals(array(4.5, 4.5, 4.45, 4, 4.5, 4, 4.5, 4), $buchfreund->totalPricesFrom($html));
    }

    function testParseEmptyContent()
    {
        $html = "";

        $buchfreund = new Buchfreund(json_decode(self::BUCHFREUND_AS_JSON, TRUE));
        $this->assertEquals($buchfreund->totalPricesFrom($html), array());
    }

    function testParsePageWithoutPrices()
    {
        $html = file_get_contents("tests/buchfreund-without-books.html");

        $buchfreund = new Buchfreund(json_decode(self::BUCHFREUND_AS_JSON, TRUE));
        $this->assertEquals($buchfreund->totalPricesFrom($html), array());
    }

    const BUCHFREUND_AS_JSON = '{"name":"buchfreund",
        "host":"www.buchfreund.de",
        "urlpath":"\/results.php?q=${ISBN13}&onlyIsbn=1&sO=5",
        "fixcosts":0,
        "provision":0.1,
        "porto_wcl1":3,
        "porto_wcl2":3,
        "porto_wcl3":3,
        "percent_of_sales":0,
        "is_active":0
        }';
}