<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("pdf/bookTablePdf.php");

class BookTablePdfTest extends PHPUnit_Framework_TestCase
{

    function setUp() {
        echo "setUp\n";
    }

    function tearDown() {
        echo "tearDown\n";
    }

    function testPdfForManyBooksWithLongTitles()
    {
        $bookTablePdf = new BookTablePDF($this->booksForTesting(100));
        $bookTablePdf->printTable();

        $actualPdf = $bookTablePdf->Output('', 'S');
        $this->assertFalse(empty($actualPdf));

        $expectedPdf = file_get_contents("tests/test-many-books-as-pdf.pdf");
        $this->assertEqualsIgnoringCreationDate($expectedPdf, $actualPdf);
    }

    function testPdfForFewBooksWithShortTitles()
    {
        $bookTablePdf = new BookTablePDF($this->booksForTesting(10,20));
        $bookTablePdf->printTable();

        $actualPdf = $bookTablePdf->Output('', 'S');
        $this->assertFalse(empty($actualPdf));

        $expectedPdf = file_get_contents("tests/test-few-books-as-pdf.pdf");
        $this->assertEqualsIgnoringCreationDate($expectedPdf, $actualPdf);
    }

    function testPdfFor0Books()
    {
        $bookTablePdf = new BookTablePDF(array());
        $bookTablePdf->printTable();

        $actualPdf = $bookTablePdf->Output('', 'S');
        $this->assertFalse(empty($actualPdf));

        $expectedPdf = file_get_contents("tests/test-no-books-as-pdf.pdf");
        $this->assertEqualsIgnoringCreationDate($expectedPdf, $actualPdf);
    }

    function xTestPdfToFile()
    {
        $outFile = tempnam(sys_get_temp_dir(), 'tBt');

        $bookTablePdf = new BookTablePDF(array());;
        $bookTablePdf->printTable();

        $bookTablePdf->Output($outFile);
        echo("please cleanup:" . $outFile . "\n");
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

    private function assertEqualsIgnoringCreationDate($expectedPdf, $actualPdf)
    {
        $this->assertEquals(preg_replace('/\/CreationDate.*/ie', '', $expectedPdf),
            preg_replace('/\/CreationDate.*/ie', '', $actualPdf));
    }
}