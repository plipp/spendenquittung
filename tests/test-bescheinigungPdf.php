<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("pdf/bescheinigungPdf.php");

class BescheinigungPdfTest extends PHPUnit_Framework_TestCase
{

    function setUp() {
        echo "setUp\n";
    }

    function tearDown() {
        echo "tearDown\n";
    }


    function testPdfToFile()
    {
        $outFile = '/tmp/sample.pdf';// tempnam(sys_get_temp_dir(), 'tBs');

        $bescheinigungPdf = new BescheinigungPDF($this->addressData());
        $bescheinigungPdf->printBescheinigung();

        $bescheinigungPdf->Output($outFile);
        echo("please cleanup:" . $outFile . "\n");
    }

    private function addressData ()
    {
        $addressData = json_decode('{"lastname":"Bala","firstname":"Bakoshi","street":"Lutherplatz 2","zip":"13585","city":"Berlin","phone":"099876","email":"bala@gmx.de"}',true);
        return $addressData;
    }
}