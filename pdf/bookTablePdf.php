<?php
require_once('fpdf17/fpdf.php');

class BookTablePDF
{
    const PAGE_HEADER = "Spendenliste";
    const COLUMN_HEIGHT = 6;
    const HEADER_HEIGHT = 7;
    const MAX_TITLE_LENGTH = 90;

    private static $HEADER_TEXT = array('isbn' => 'ISBN', 'title' => 'Titel', 'profit' => 'Preis/EUR');
    private static $COLUMN_WIDTH = array('isbn' => 30, 'title' => 140, 'profit' => 20);

    private $_books;
    private $_pdf;

    public function __construct($books, $pdf)
    {
        $this->_books = $books;
        $this->_pdf = $pdf;

        $pdf->setPageHeader("Spendenliste");
        $this->_pdf->AddPage();
    }

    function printTable()
    {
        // Header
        foreach (array_keys(self::$HEADER_TEXT) as $key)
        $this->_pdf->Cell(self::$COLUMN_WIDTH[$key], self::HEADER_HEIGHT,self::$HEADER_TEXT[$key],1,0,'C');
        $this->_pdf->Ln();

        // Data
        foreach($this->_books as $book)
        {
            $profit = (float)str_replace(',', '.', $book['profit']);

            if ($profit>0) {
                $this->_pdf->Cell(self::$COLUMN_WIDTH['isbn'], self::COLUMN_HEIGHT, $book['isbn'], 'LR');
                $this->_pdf->Cell(self::$COLUMN_WIDTH['title'], self::COLUMN_HEIGHT, self::shortened($book['title'], self::MAX_TITLE_LENGTH), 'LR');
                $this->_pdf->Cell(self::$COLUMN_WIDTH['profit'], self::COLUMN_HEIGHT, $book['profit'], 'LR', 0, 'R');
                $this->_pdf->Ln();
            }
        }

        // Closing line
        $this->_pdf->Cell(array_sum(self::$COLUMN_WIDTH),0,'','T');
    }

    private static function shortened ($strValue, $maxLength) {
        $len = strlen($strValue);
        if ($len>$maxLength) {
            $shortenedString = utf8_decode(substr($strValue,0,($maxLength-4)) . ' ...');
        } else {
            $shortenedString = utf8_decode($strValue);
        }
        return $shortenedString;
    }
}
