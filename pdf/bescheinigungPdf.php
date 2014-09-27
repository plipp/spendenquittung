<?php
require_once('fpdf17/fpdf.php');
require_once("basePdf.php");

/**
 * Generates the PDF of the Spendenbescheinigung.
 *
 * Note:
 * If we need BulletPoints in future we should check out http://fpdf.de/downloads/addons/56/
 */
class BescheinigungPDF
{
    const PAGE_HEADER = "Spendenbescheinigung";

    const BOX_SIZE = 190;

    private $_pdf;

    private $_addressData;

    public function __construct($addressData, $pdf)
    {
        $this->_addressData = $addressData;
        $this->_pdf=$pdf;

        $pdf->AddPage();
    }

    function printBescheinigung()
    {
        $this->topicBox('Aussteller:', "Berliner Büchertisch\nMehringdamm 51\n\n10961 Berlin\n");

        $this->_pdf->Ln(5);
        $this->info('Bestätigung über Sachzuwendungen', "im Sinne des § 10b des Einkommensteuergesetzes an eine der in § 5 Abs. 1 Nr. 9 des Körperschaftsteuergesetzes bezeichneten Körperschaften, Personenvereinigungen oder Vermögensmassen");
        $this->_pdf->Ln(5);

        $this->topicBox('Name und Anschrift des Zuwendenden:',
            $this->_addressData['firstname']." ".
            $this->_addressData['lastname']."\n".
            $this->_addressData['street']."\n\n".
            $this->_addressData['zip']." ".$this->_addressData['city']);

        $this->topicBox("Tag der Zuwendung, Wert der Zuwendung:", date("d.m.Y"). ", TODO", 6);
        $this->topicBox("Genaue Bezeichnung der Sachzuwendung mit Alter, Zustand, Kaufpreis usw.:","s. Anhang 'Spendenliste'", 6);

        $this->_pdf->SetY(-60);
        $this->styledText("Es wird bestätigt, dass die Zuwendung nur zur Förderung des Berliner Büchertisches verwendet wird.",'',9);
        $this->_pdf->Ln(20);
        $this->titledLine("(Ort, Datum, Unterschrift des Zuwendungsempfängers)");
    }

    private function topicBox($topic, $content, $lineHeight=4)
    {
        $this->styledText($topic, 'I');
        $this->boxedText($content, 1, 10, $lineHeight);
    }

    private function info($title, $infoText)
    {
        $this->styledText($title, 'B');
        $this->boxedText($infoText, 0, 8);
    }

    private function boxedText($content,$withBorder,$fontSize, $lineHeight=4)
    {
        $this->_pdf->SetFont('Arial', '', $fontSize);
        $this->_pdf->MultiCell(self::BOX_SIZE, $lineHeight, utf8_decode($content), $withBorder);
        $this->_pdf->Ln();
    }

    private function styledText($text, $style='', $size=8)
    {
        $this->_pdf->SetFont('Arial', $style, $size);
        $this->_pdf->Cell(0, 5, utf8_decode($text));
        $this->_pdf->Ln();
    }

    private function titledLine($title)
    {
        $y = $this->_pdf->getY();
        $this->_pdf->Line($this->_pdf->lMargin, $y, $this->_pdf->w/2, $y);
        $this->styledText($title);
    }
}
