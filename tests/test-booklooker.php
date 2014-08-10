<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("platforms/platforms.php");

class BooklookerTest extends PHPUnit_Framework_TestCase
{

    function testParsePrices()
    {
        $xml = file_get_contents("tests/booklooker-with-book.xml");
        $this->assertFalse(empty($xml));

        $booklooker = new BOOKLOOKER(json_decode(self::BOOKLOOKER_AS_JSON, TRUE));
        $this->assertEquals($booklooker->totalPricesFrom($xml),array(2.2));
    }

    function testParseTitle()
    {
        $xml = file_get_contents("tests/booklooker-with-book.xml");
        $this->assertFalse(empty($xml));

        $booklooker = new BOOKLOOKER(json_decode(self::BOOKLOOKER_AS_JSON, TRUE));
        $this->assertEquals($booklooker->titleFrom($xml),'Die Gilde der Schwarzen Magier - Die Rebellin');
    }

    function testParseEmptyContent()
    {
        $xml = "";

        $booklooker = new BOOKLOOKER(json_decode(self::BOOKLOOKER_AS_JSON, TRUE));
        $this->assertEquals($booklooker->totalPricesFrom($xml), array());
    }

    function testParsePageWithoutPrices()
    {
        $xml = file_get_contents("tests/booklooker-without-book.xml");

        $booklooker = new BOOKLOOKER(json_decode(self::BOOKLOOKER_AS_JSON, TRUE));
        $this->assertEquals(array(), $booklooker->totalPricesFrom($xml));
        $this->assertEquals(null, $booklooker->titleFrom($xml));
    }

    const BOOKLOOKER_AS_JSON = '{
      "id":"2",
      "name":"booklooker",
      "host":"www.booklooker.de",
      "urlpath":"/interface/search.php?pid=7654321&medium=book&limit=1&sortOrder=pricePlusShipping&isbn=${ISBN13}",
      "fixcosts":"1.50",
      "provision":"0.30",
      "porto_wcl1":"3.00",
      "porto_wcl2":"3.00",
      "porto_wcl3":"3.00",
      "percent_of_sales":"10.00",
      "is_active":"1"
   }';
}