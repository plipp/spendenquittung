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

require_once("platforms/amazon/AmazonProviderApi.php");

class AmazonProviderApiTest extends PHPUnit_Framework_TestCase
{

    function setUp() {
//        echo "setUp\n";
    }

    function tearDown() {
//        echo "tearDown\n";
    }


    function testSignatureFor() {
        $amazonEcs = new AmazonProviderApi('webservices.amazon.de', '/onca/xml');
        $this->assertEquals($amazonEcs->signatureFor("GET
webservices.amazon.de
/onca/xml
AWSAccessKeyId=AKIAIXLSBMDYE34PWSWA&AssociateTag=787279780545&Condition=All&IdType=ISBN&ItemId=3570303284&Operation=ItemLookup&ResponseGroup=ItemAttributes&SearchIndex=Books&Service=AWSECommerceService&Timestamp=2014-11-01T17%3A00%3A59.000Z&Version=2011-08-01"),
            "Z8fOuPQTu4P%2BTslfRzLMYGsXSADdZc4aveXRmXXrQYM%3D");
    }

    function testAmazonProductSearchIT()
    {
        $amazonEcs = new AmazonProviderApi('webservices.amazon.de', '/onca/xml');
        $signedRequest = $amazonEcs->urlBy('3570303284');

        // TODO: decomment, when real secrete is returned by get_option()
//        $response = file_get_contents($signedRequest);
//        $this->assertTrue (strpos($response,"<Title>Die Rebellin.")!=false);

    }
}