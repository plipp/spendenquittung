<?php
require_once('fpdf17/fpdf.php');

/**
 * Container for the complete PDF of the Spendenbescheinigung.
 */
class BasePdf extends FPDF
{
    const PAGE_HEADER = "Spendenbescheinigung";

    public function __construct()
    {
        parent::__construct();
        $this->initialPdfSettings();
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

    public function initialPdfSettings()
    {
        $this->SetFont('Arial','',10);
        $this->AliasNbPages();
    }
}
