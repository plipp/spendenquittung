<?php
require_once('fpdf17/fpdf.php');

class BookTablePDF extends FPDF
{
    const PAGE_HEADER = "Spendenliste";
    const COLUMN_HEIGHT = 6;
    const HEADER_HEIGHT = 7;
    const MAX_TITLE_LENGTH = 90;

    private static $HEADER_TEXT = array('isbn' => 'ISBN', 'title' => 'Titel', 'profit' => 'Preis/EUR');
    private static $COLUMN_WIDTH = array('isbn' => 30, 'title' => 140, 'profit' => 20);

    private $_books;

    public function __construct($books)
    {
        parent::__construct();
        $this->_books = $books;

        $this->AddPage();
        $this->SetFont('Arial','',10);
        $this->AliasNbPages();
    }

    // override
    function Header()
    {
        $this->SetFont('Arial','B',15);
        $this->Cell(80);
        $this->Cell(30,10,self::PAGE_HEADER,0,0,'C');
        $this->Ln(15);
    }

    // override
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Seite '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function printTable()
    {
        // Header
        foreach (array_keys(self::$HEADER_TEXT) as $key)
        $this->Cell(self::$COLUMN_WIDTH[$key], self::HEADER_HEIGHT,self::$HEADER_TEXT[$key],1,0,'C');
        $this->Ln();

        // Data
        foreach($this->_books as $book)
        {
            $this->Cell(self::$COLUMN_WIDTH['isbn'], self::COLUMN_HEIGHT,$book['isbn'],'LR');
            $this->Cell(self::$COLUMN_WIDTH['title'], self::COLUMN_HEIGHT,self::shortened($book['title'], self::MAX_TITLE_LENGTH),'LR');
            $this->Cell(self::$COLUMN_WIDTH['profit'], self::COLUMN_HEIGHT,$book['profit'],'LR',0,'R');
            $this->Ln();
        }

        // Closing line
        $this->Cell(array_sum(self::$COLUMN_WIDTH),0,'','T');
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
