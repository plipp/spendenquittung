<?php



if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}


if (!function_exists('get_option')) {
    function get_option($key, $default="default")
    {
        return "somevalue";
    }
}

require_once("platforms/valueFromPlatformsAction.php");
require_once("platforms/platforms.php");

class ValueFromPlatformsActionTest extends PHPUnit_Framework_TestCase
{
    private $_valueFromPlatformsAction;

    protected function setUp()
    {
        $this->_valueFromPlatformsAction =
            new ValueFromPlatformsAction(new PlatformRegistry(json_decode(self::PLATFORMS_AS_JSON, TRUE)), array(), false);
    }

    function testAverageProfitOfEmptyArray()
    {
        $this->assertEquals(-1, $this->_valueFromPlatformsAction->averageFrom(array(), array()), 0.001);
    }

    function testAverageProfitWithAllZeros()
    {
        $profitByPlatform = array(PlatformRegistry::BOOKLOOKER => 0, PlatformRegistry::EBAY => 0, PlatformRegistry::ZVAB => 0, PlatformRegistry::BUCHFREUND => 0);
        $this->assertEquals(0,
            $this->_valueFromPlatformsAction->averageFrom($profitByPlatform, $this->_valueFromPlatformsAction->platformsWithValidProfit($profitByPlatform)),
            0.001);
    }

    function testAverageProfitWithZeros()
    {
        // (0.1*0 + 0.2*3 + 0.2*4)/0.5 = 2.8
        $profitByPlatform = array(PlatformRegistry::BOOKLOOKER => 0, PlatformRegistry::EBAY => 3, PlatformRegistry::ZVAB => 4, PlatformRegistry::BUCHFREUND => 0);
        $this->assertEquals(2.8,
            $this->_valueFromPlatformsAction->averageFrom($profitByPlatform, $this->_valueFromPlatformsAction->platformsWithValidProfit($profitByPlatform)),
            0.001);
    }

    function testAverageProfitWithUnavailables()
    {
        // (0.2*3 + 0.2*4)/0.4 = 3.5
        $profitByPlatform = array(PlatformRegistry::BOOKLOOKER => -1, PlatformRegistry::EBAY => 3, PlatformRegistry::ZVAB => 4, PlatformRegistry::BUCHFREUND => -1);
        $this->assertEquals(3.5,
            $this->_valueFromPlatformsAction->averageFrom($profitByPlatform, $this->_valueFromPlatformsAction->platformsWithValidProfit($profitByPlatform)),
            0.001);
    }

    function testAverageProfitWithoutZeros()
    {
        // (0.1*5 + 0.2*3 + 0.2*4 + 0*6)/0.5 = 3.8
        $profitByPlatform = array(PlatformRegistry::BOOKLOOKER => 5, PlatformRegistry::EBAY => 3, PlatformRegistry::ZVAB => 4, PlatformRegistry::BUCHFREUND => 6);
        $this->assertEquals(3.8,
            $this->_valueFromPlatformsAction->averageFrom($profitByPlatform, $this->_valueFromPlatformsAction->platformsWithValidProfit($profitByPlatform)),
            0.001);
    }

    function testTitleOfEmptyArray()
    {
        $this->assertEquals("", $this->_valueFromPlatformsAction->bestTitleFrom(array()));
    }

    function testBooklookerTitle()
    {
        $title = "a Title";
        $this->assertEquals($title, $this->_valueFromPlatformsAction->bestTitleFrom(array(PlatformRegistry::BOOKLOOKER => $title)));
    }

    function testOtherStoresTitle()
    {
        $title = "a Title";
        $this->assertEquals($title, $this->_valueFromPlatformsAction->bestTitleFrom(array(PlatformRegistry::ZVAB => $title)));
    }

    function testToProfitsByClass()
    {
        $platformWithProfitsPerClass = json_decode('{
          "booklooker"	:{ "1":0.307288, "2":-0.292712, "3":-3.872712 },
          "amazon"	:{ "1":0.08743, "2":-0.51257, "3":-4.09257 },
          "ebay":{ "1":0.98, "2":0.38, "3":-3.2 },
          "zvab":{ "1":0.8638, "2":0.2638, "3":-3.3162 },
          "buchfreund":{ "1":1.9333, "2":1.3333, "3":-2.2467 }
        }',true);

        $expectedProfitsByClass=json_decode('{
            "1":{"booklooker":0.307288,"amazon":0.08743,"ebay":0.98,"zvab":0.8638,"buchfreund":1.9333},
            "2":{"booklooker":-0.292712,"amazon":-0.51257,"ebay":0.38,"zvab":0.2638,"buchfreund":1.3333},
            "3":{"booklooker":-3.872712,"amazon":-4.09257,"ebay":-3.2,"zvab":-3.3162,"buchfreund":-2.2467}}
            ', true);

        $this->assertEquals($expectedProfitsByClass, $this->_valueFromPlatformsAction->toProfitsByClass($platformWithProfitsPerClass));
    }

    const PLATFORMS_AS_JSON = '[
   {
      "id":"1",
      "name":"amazon",
      "host":"webservices.amazon.de",
      "urlpath":"\/onca\/xml",
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
 