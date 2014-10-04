<?php
require_once('fpdf17/fpdf.php');

/**
 * Generates the PDF of the Spendenbescheinigung.
 *
 * Note:
 * If we need BulletPoints in future we should check out http://fpdf.de/downloads/addons/56/
 */
class BescheinigungPDF
{
    private $_pdf;
    private $_addressData;
    private $_amount;

    public function __construct($addressData, $amount, $pdf)
    {
        $this->_addressData = $addressData;
        $this->_amount = $amount;
        $this->_pdf=$pdf;

        $pdf->setPageHeader("Spendenbescheinigung");
        $pdf->AddPage();
    }

    function printBescheinigung()
    {
        $this->_pdf->topicBox('Aussteller:', "Berliner Büchertisch\nMehringdamm 51\n\n10961 Berlin\n");

        $this->_pdf->Ln(5);
        $this->_pdf->info('Bestätigung über Sachzuwendungen', "im Sinne des § 10b des Einkommensteuergesetzes an eine der in § 5 Abs. 1 Nr. 9 des Körperschaftsteuergesetzes bezeichneten Körperschaften, Personenvereinigungen oder Vermögensmassen");
        $this->_pdf->Ln(5);

        $this->_pdf->topicBox('Name und Anschrift des Zuwendenden:',
            $this->_addressData['firstname']." ".
            $this->_addressData['lastname']."\n".
            $this->_addressData['street']."\n\n".
            $this->_addressData['zip']." ".$this->_addressData['city']);

        $this->_pdf->topicBox("Wert der Zuwendung / Tag der Zuwendung:" , $this->_amount . " EUR / " . date("d.m.Y"), 6);
        $this->_pdf->topicBox("Titel der gespendeten Bücher mit Preis:","s. Anhang 'Spendenliste'", 6);

        $this->_pdf->SetY(-60);
        $this->_pdf->styledText("Es wird bestätigt, dass die Zuwendung nur zur Förderung des Berliner Büchertisches verwendet wird.",'',9);
        $this->_pdf->Ln(20);
        $this->_pdf->titledLine("(Ort, Datum, Unterschrift des Zuwendungsempfängers)");
    }
}
