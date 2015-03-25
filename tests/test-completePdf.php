<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

define( 'SPENDENQUITTUNG_PLUGIN_DIR', '.' );

require_once("pdf/deckblattPdf.php");
require_once("pdf/bescheinigungPdf.php");
require_once("pdf/bookTablePdf.php");
require_once("pdf/pdfToolbox.php");

class CompletePdfTest extends PHPUnit_Framework_TestCase
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

        $deckblattPdf = new DeckblattPDF($this->addressData(), $pdf);
        $deckblattPdf->printDeckblatt();

        $bescheinigungPdf = new BescheinigungPDF($this->addressData(), "13,48", $pdf);
        $bescheinigungPdf->printBescheinigung();

        $bookTablePdf = new BookTablePDF($this->booksForTesting(100,200), "1001,44", $pdf);
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