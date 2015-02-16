<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("platforms/platforms.php");

class PlatformsTest extends PHPUnit_Framework_TestCase
{

    function testPlatforms()
    {
        $platforms = new PlatformRegistry(json_decode(self::PLATFORMS_AS_JSON, TRUE));

        $this->assertEquals(6, count($platforms->all()));

    }

    function testPlatformByName()
    {
        $platforms = new PlatformRegistry(json_decode(self::PLATFORMS_AS_JSON, TRUE));

        $amazon = $platforms->by("amazon");
        $this->assertNotNull($amazon);
        $this->assertEquals("amazon", $amazon->name);
    }

    function testPlatformAsString()
    {
        $platforms = new PlatformRegistry(json_decode(self::PLATFORMS_AS_JSON, TRUE));

        $amazon = $platforms->by("amazon");
        $this->assertNotNull($amazon);
        $this->assertEquals('{"id":"1","name":"amazon","host":"webservices.amazon.de","urlpath":"\/onca\/xml","fixcosts":"1.50","provision":"0.30","porto_wcl1":"2.10","porto_wcl2":"4.40","porto_wcl3":"6.50","percent_of_sales":"50.00","is_active":"1","protocol":"http"}', $amazon->__toString());
    }

    function testUrlByIsbn()
    {
        $platforms = new PlatformRegistry(json_decode(self::PLATFORMS_AS_JSON, TRUE));

        $booklooker = $platforms->by("booklooker");
        $this->assertNull($booklooker->urlBy("978-4321"), "null for invalid isbn (shouldn't happen)");
        $this->assertEquals("http://www.booklooker.de/interface/search.php?pid=7654321&medium=book&limit=1&sortOrder=pricePlusShipping&isbn=9783570303283", $booklooker->urlBy("978-3570303283"));
        $this->assertEquals("http://www.booklooker.de/interface/search.php?pid=7654321&medium=book&limit=1&sortOrder=pricePlusShipping&isbn=9783570303283", $booklooker->urlBy("9783570303283"));
        $this->assertEquals("http://www.booklooker.de/interface/search.php?pid=7654321&medium=book&limit=1&sortOrder=pricePlusShipping&isbn=9783570303283", $booklooker->urlBy("3570303284"));
    }

    function testProfitCalculationForAmazon()
    {
        $platforms = new PlatformRegistry(json_decode(self::PLATFORMS_AS_JSON, TRUE));

        $amazon = $platforms->by("amazon");
        $profit = $amazon->profitByWeightClasses(9.95);
        $this->assertEquals($profit[Weight::WEIGHT_CLASS_450], 3.89035, '', 0.0001);
        $this->assertEquals($profit[Weight::WEIGHT_CLASS_950], 4.09305, '', 0.0001);
        $this->assertEquals($profit[Weight::WEIGHT_CLASS_MAX], 1.24595, '', 0.0001);
    }

    function testProfitCalculationForZVAB()
    {
        $platforms = new PlatformRegistry(json_decode(self::PLATFORMS_AS_JSON, TRUE));

        $amazon = $platforms->by("zvab");
        $profit = $amazon->profitByWeightClasses(2.65);
        $this->assertEquals(0.903325, $profit[Weight::WEIGHT_CLASS_450], '', 0.0001);
        $this->assertEquals(0.303325, $profit[Weight::WEIGHT_CLASS_950], '', 0.0001);
        $this->assertEquals(-3.276675, $profit[Weight::WEIGHT_CLASS_MAX], '', 0.0001);
    }

    function testPortoDeclBy()
    {
        $platforms = new PlatformRegistry(json_decode(self::PLATFORMS_AS_JSON, TRUE));

        $amazon = $platforms->by("amazon");

        $this->assertEquals($amazon->portoDeclBy(Weight::WEIGHT_CLASS_450), 2.10);
        $this->assertEquals($amazon->portoDeclBy(Weight::WEIGHT_CLASS_950), 4.40);
        $this->assertEquals($amazon->portoDeclBy(Weight::WEIGHT_CLASS_MAX), 6.50);
    }

    const PLATFORMS_AS_JSON = '[
   {
      "id":"1",
      "name":"amazon",
      "host":"webservices.amazon.de",
      "urlpath":"/onca/xml",
      "fixcosts":"1.50",
      "provision":"0.30",
      "porto_wcl1":"2.10",
      "porto_wcl2":"4.40",
      "porto_wcl3":"6.50",
      "percent_of_sales":"50.00",
      "is_active":"1"
   },
   {
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
   },
   {
      "id":"3",
      "name":"buchfreund",
      "host":"www.buchfreund.de",
      "urlpath":"\/results.php?used=1&detail=1&isbn=${ISBN13}&sO=5",
      "fixcosts":"0.00",
      "provision":"0.00",
      "porto_wcl1":"3.00",
      "porto_wcl2":"3.00",
      "porto_wcl3":"3.00",
      "percent_of_sales":"0.00",
      "is_active":"1"
   },
   {
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
   },
   {
      "id":"5",
      "name":"ebay",
      "host":"svcs.ebay.com",
      "urlpath":"/services/search/FindingService/v1?SECURITY-APPNAME=some-sec-name&OPERATION-NAME=findItemsByProduct&SERVICE-VERSION=1.0.0&RESPONSE-DATA-FORMAT=XML&REST-PAYLOAD=&productId.@type=ISBN&productId=${ISBN13}&sortOrder=PricePlusShippingLowest&GLOBAL-ID=EBAY-DE&itemFilter(0).name=country&itemFilter(0).value=DE&itemFilter(0).paramName=Currency&itemFilter(0).paramValue=EUR&itemFilter(1).name=ListingType&itemFilter(1).value(0)=FixedPrice&paginationInput.entriesPerPage=1",
      "fixcosts":"0.00",
      "provision":"0.00",
      "porto_wcl1":"3.00",
      "porto_wcl2":"3.00",
      "porto_wcl3":"3.00",
      "percent_of_sales":"20.00",
      "is_active":"1"
   },
   {
      "id":"6",
      "name":"Easyankauf",
      "host":"www.easy-ankauf.de",
      "urlpath":"\/ajax\/angebote",
      "fixcosts":"0.00",
      "provision":"0.00",
      "porto_wcl1":"0.00",
      "porto_wcl2":"0.00",
      "porto_wcl3":"0.00",
      "percent_of_sales":"0.00",
      "is_active":"1"
   }
]';
}