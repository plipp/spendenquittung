<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("platforms/platforms.php");

class ZVABTest extends PHPUnit_Framework_TestCase
{

    function testParsePrices()
    {
        $html = file_get_contents("tests/zvab-with-book.html");
        $this->assertFalse(empty($html));

        $zvab = new ZVAB(json_decode(self::ZVAB_AS_JSON, TRUE));
        $this->assertEquals($zvab->totalPricesFrom($html),array(1.24,2.7));
    }

    function testParseEmptyContent()
    {
        $html = "";

        $zvab = new ZVAB(json_decode(self::ZVAB_AS_JSON, TRUE));
        $this->assertEquals($zvab->totalPricesFrom($html), array());
    }

    function testParsePageWithoutPrices()
    {
        $html = file_get_contents("tests/zvab-without-prices.html");

        $zvab = new ZVAB(json_decode(self::ZVAB_AS_JSON, TRUE));
        $this->assertEquals($zvab->totalPricesFrom($html), array());
    }

    const ZVAB_AS_JSON = '{
      "id":"4",
      "name":"zvab",
      "host":"www.zvab.com",
      "urlpath":"\/advancedSearch.do?isbn=${ISBN13}&displayCurrency=EUR&itemsPerPage=10&sortBy=6",
      "fixcosts":"0.00",
      "provision":"0.15",
      "porto_wcl1":"3.00",
      "porto_wcl2":"3.00",
      "porto_wcl3":"3.00",
      "percent_of_sales":"20.00",
      "is_active":"1"
   }';
}