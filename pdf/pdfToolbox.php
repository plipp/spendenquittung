<?php
require_once('fpdf17/fpdf.php');

/**
 * Container for the complete PDF of the Spendenbescheinigung.
 */
class PdfToolbox extends FPDF
{
    const PAGE_HEADER = "Spendenliste";
    const BOX_SIZE = 190;

    public function __construct()
    {
        parent::__construct();
        $this->initialSettings();
    }

    // override
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, self::PAGE_HEADER, 0, 0, 'C');
        $this->Ln(15);
    }

    // override
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Seite ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    public function initialSettings()
    {
        $this->SetFont('Arial','',10);
        $this->AliasNbPages();
    }

    public function topicBox($topic, $content, $lineHeight=4)
    {
        $this->styledText($topic, 'I');
        $this->boxedText($content, 1, 10, $lineHeight);
    }

    public function info($title, $infoText)
    {
        $this->styledText($title, 'B');
        $this->boxedText($infoText, 0, 8);
    }

    public function boxedText($content,$withBorder,$fontSize, $lineHeight=4)
    {
        $this->SetFont('Arial', '', $fontSize);
        $this->MultiCell(self::BOX_SIZE, $lineHeight, utf8_decode($content), $withBorder);
        $this->Ln();
    }

    public function styledText($text, $style='', $size=8)
    {
        $this->SetFont('Arial', $style, $size);
        $this->Cell(0, 5, utf8_decode($text));
        $this->Ln();
    }

    public function titledLine($title)
    {
        $y = $this->getY();
        $this->Line($this->lMargin, $y, $this->w/2, $y);
        $this->styledText($title);
    }    
}
