<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("pdf/bescheinigungPdf.php");
require_once("pdf/bookTablePdf.php");
require_once("pdf/pdfToolbox.php");

class CompletePdfTest extends PHPUnit_Framework_TestCase
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

        $pdf = new PdfToolbox();
        $bescheinigungPdf = new BescheinigungPDF($this->addressData(), $pdf);
        $bescheinigungPdf->printBescheinigung();

        $bookTablePdf = new BookTablePDF($this->booksForTesting(10,20),$pdf);
        $bookTablePdf->printTable();

        $pdf->Output($outFile);
        echo("please cleanup:" . $outFile . "\n");
    }

    private function addressData ()
    {
        $addressData = json_decode('{"lastname":"Bala","firstname":"Bakoshi","street":"Lutherplatz 2","zip":"13585","city":"Berlin","phone":"099876","email":"bala@gmx.de"}',true);
        return $addressData;
    }

    private function booksForTesting ($numberOfBooks, $maxTitleLength=200)
    {
        $books = array();
        for ($i=0;$i<$numberOfBooks;$i++) {
            $title = strval($i) . ': A function cannot be called with fewer arguments than is specified in its declaration, but';
            if (strlen($title)>$maxTitleLength) {
                $title = substr($title,0,$maxTitleLength);
            }
            $books[$i] = array('isbn' => '978-1557787903',
                'title' => $title,
                'profit' => strval($i) .'.21'
            );
        }
        return $books;
    }
}