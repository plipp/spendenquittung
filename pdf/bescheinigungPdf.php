<?php
require_once('fpdf17/fpdf.php');

class BescheinigungPDF extends FPDF
{
    const PAGE_HEADER = "Spendenbescheinigung";

    private $_addressData;

    const BOX_SIZE = 190;

    public function __construct($addressData)
    {
        parent::__construct();
        $this->_addressData = $addressData;

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

    function printBescheinigung()
    {
        $this->topicBox('Aussteller:', "\nBerliner Büchertisch\nMehringdamm 51\n\n10961 Berlin\n");

        $this->info('Bestätigung über Sachzuwendungen', "im Sinne des § 10b des Einkommensteuergesetzes an eine der in § 5 Abs. 1 Nr. 9 des Körperschaftsteuergesetzes bezeichneten Körperschaften, Personenvereinigungen oder Vermögensmassen");

        $this->topicBox('Name und Anschrift des Zuwendenden:',"\n".
            $this->_addressData['firstname']." ".
            $this->_addressData['lastname']."\n".
            $this->_addressData['street']."\n\n".
            $this->_addressData['zip']." ".$this->_addressData['city'] ."\n",
            1);

        $this->topicBox("Genaue Bezeichnung der Sachzuwendung mit Alter, Zustand, Kaufpreis usw.:","\ns. Anhang 'Spendenliste'\n");

        // todo http://fpdf.de/downloads/addons/56/ ...
    }

    private function topicBox($topic, $content)
    {
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, utf8_decode($topic));
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        $this->MultiCell(self::BOX_SIZE, 4, utf8_decode($content), 1);
        $this->Ln();
    }

    private function info($title, $infoText)
    {
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 5, utf8_decode($title));
        $this->Ln();

        $this->SetFont('Arial', '', 8);
        $this->MultiCell(self::BOX_SIZE, 4, utf8_decode($infoText));
        $this->Ln();
    }

    private function initialPdfSettings()
    {
        $this->AddPage();
        $this->SetFont('Arial','',10);
        $this->AliasNbPages();
    }
}
