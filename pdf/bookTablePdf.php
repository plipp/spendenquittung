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
    private $_amount;

    public function __construct($books, $amount, $pdf)
    {
        $this->_books = $books;
        $this->_pdf = $pdf;
        $this->_amount = $amount;

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
                $this->printColumn($book['isbn'], utf8_decode(self::shortened($book['title'], self::MAX_TITLE_LENGTH)), $book['profit']);
            }
        }

        // Closing line with complete sum
        $needsColumnForSum = (float)str_replace(',', '.', $this->_amount) > 0;
        if ($needsColumnForSum) {
            $this->printColumn("Summe","",$this->_amount,'LTB','TBR','TBR');
        }
    }

    private static function shortened ($strValue, $maxLength) {
        $len = strlen($strValue);
        if ($len>$maxLength) {
            $shortenedString = substr($strValue,0,($maxLength-4)) . ' ...';
        } else {
            $shortenedString = $strValue;
        }
        return $shortenedString;
    }

    private function printColumn($col1value, $col2value,$col3value, $col1format= 'LR', $col2format='LR',$col3format = 'LR')
    {
        $this->_pdf->Cell(self::$COLUMN_WIDTH['isbn'], self::COLUMN_HEIGHT, $col1value, $col1format);
        $this->_pdf->Cell(self::$COLUMN_WIDTH['title'], self::COLUMN_HEIGHT, $col2value, $col2format);
        $this->_pdf->Cell(self::$COLUMN_WIDTH['profit'], self::COLUMN_HEIGHT, $col3value, $col3format, 0, 'R');
        $this->_pdf->Ln();
    }
}
