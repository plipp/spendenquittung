<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("pdf/bescheinigungPdf.php");
require_once("pdf/pdfToolbox.php");

class BescheinigungPdfTest extends PHPUnit_Framework_TestCase
{

    function setUp() {
        echo "setUp\n";
        date_default_timezone_set( 'UTC' );
    }

    function tearDown() {
        echo "tearDown\n";
    }


    function testPdfToFile()
    {
        $outFile = '/tmp/sample.pdf';// tempnam(sys_get_temp_dir(), 'tBs');

        $pdf = new PdfToolbox();
        $bescheinigungPdf = new BescheinigungPDF($this->addressData(), "33,54", $pdf);
        $bescheinigungPdf->printBescheinigung();

        $pdf->Output($outFile);
        echo("please cleanup:" . $outFile . "\n");
    }

    private function addressData ()
    {
        $addressData = json_decode('{"lastname":"Bala","firstname":"Bakoshi","street":"Lutherplatz 2","zip":"13585","city":"Berlin","phone":"099876","email":"bala@gmx.de"}',true);
        return $addressData;
    }
}