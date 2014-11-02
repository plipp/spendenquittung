<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("platforms/platforms.php");

class AmazonTest extends PHPUnit_Framework_TestCase
{

    function testParsePrices()
    {
        $xml = file_get_contents("tests/amazon-with-book.xml");
        $this->assertFalse(empty($xml));

        $amazon = new Amazon(json_decode(self::AMAZON_AS_JSON, TRUE));
        $this->assertEquals($amazon->totalPricesFrom($xml),array(0.11));
    }

    function testParsePricesOfOnlyNewBooks()
    {
        $xml = file_get_contents("tests/amazon-with-only-new-book.xml");
        $this->assertFalse(empty($xml));

        $amazon = new Amazon(json_decode(self::AMAZON_AS_JSON, TRUE));
        $this->assertEquals($amazon->totalPricesFrom($xml),array(6.95));
    }

    function testParseTitle()
    {
        $xml = file_get_contents("tests/amazon-with-book.xml");
        $this->assertFalse(empty($xml));

        $amazon = new Amazon(json_decode(self::AMAZON_AS_JSON, TRUE));
        $this->assertEquals('Die Rebellin. Die Gilde der Schwarzen Magier 01', $amazon->titleFrom($xml));
    }


    function testParseAuthor()
    {
        $xml = file_get_contents("tests/amazon-with-book.xml");
        $this->assertFalse(empty($xml));

        $amazon = new Amazon(json_decode(self::AMAZON_AS_JSON, TRUE));
        $this->assertEquals('Trudi Canavan', $amazon->authorFrom($xml));
    }

    function testParseWrongIsbn()
    {
        $xml = file_get_contents("tests/amazon-wrong-isbn.xml");
        $this->assertFalse(empty($xml));

        $amazon = new Amazon(json_decode(self::AMAZON_AS_JSON, TRUE));
        $this->assertEquals($amazon->totalPricesFrom($xml),array());
    }

    function testParseEmptyContent()
    {
        $xml = "";

        $amazon = new Amazon(json_decode(self::AMAZON_AS_JSON, TRUE));
        $this->assertEquals($amazon->totalPricesFrom($xml), array());
    }

    function testParsePageWithoutPrices()
    {
        $xml = file_get_contents("tests/amazon-without-book.xml");

        $amazon = new Amazon(json_decode(self::AMAZON_AS_JSON, TRUE));
        $this->assertEquals(array(), $amazon->totalPricesFrom($xml));
        $this->assertEquals(null, $amazon->titleFrom($xml));
        $this->assertEquals(null, $amazon->authorFrom($xml));
    }

    const AMAZON_AS_JSON = '   {
      "id":"5",
      "host":"svcs.ebay.com",
      "urlpath":"/services/search/FindingService/v1?SECURITY-APPNAME=some-sec-name&OPERATION-NAME=findItemsByProduct&SERVICE-VERSION=1.0.0&RESPONSE-DATA-FORMAT=XML&REST-PAYLOAD=&productId.@type=ISBN&productId=${ISBN13}&sortOrder=PricePlusShippingLowest&GLOBAL-ID=EBAY-DE&itemFilter(0).name=country&itemFilter(0).value=DE&itemFilter(0).paramName=Currency&itemFilter(0).paramValue=EUR&itemFilter(1).name=ListingType&itemFilter(1).value(0)=FixedPrice&paginationInput.entriesPerPage=1",
      "fixcosts":"0.00",
      "provision":"0.00",
      "porto_wcl1":"3.00",
      "porto_wcl2":"3.00",
      "porto_wcl3":"3.00",
      "percent_of_sales":"20.00",
      "is_active":"1"
   }';
}