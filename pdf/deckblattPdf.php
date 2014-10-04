<?php
require_once('fpdf17/fpdf.php');

/**
 * Generates the Deckblatt-PDF of the Spendenbescheinigung.
 *
 * Note:
 * If we need BulletPoints in future we should check out http://fpdf.de/downloads/addons/56/
 */
class DeckblattPDF
{
    private $_pdf;
    private $_addressData;

    public function __construct($addressData, $pdf)
    {
        $this->_addressData = $addressData;
        $this->_pdf=$pdf;

        $pdf->setPageHeader("Spende (Deckblatt)");
        $pdf->AddPage();
    }

    function printDeckblatt()
    {
        $this->_pdf->Ln(10);
        $this->_pdf->topicBox('An:', "Berliner Büchertisch\nMehringdamm 51\n\n10961 Berlin\n");
        $this->_pdf->Ln(30);
        $this->_pdf->topicBox('Von:',
            $this->_addressData['firstname'] . " " .
            $this->_addressData['lastname'] . "\n" .
            $this->_addressData['street'] . "\n\n" .
            $this->_addressData['zip'] . " " . $this->_addressData['city'] . $this->additionalAddrInfo());
    }

    /**
     * @return string
     */
    public function additionalAddrInfo()
    {
        $info = "";
        if (!empty($this->_addressData['phone'])) {
            $info .=   "Tel. : " . $this->_addressData['phone'];
        }
        if (!empty($this->_addressData['email'])) {
            if (!empty($info)) $info.= ", ";
            $info .=   "eMail : " . $this->_addressData['email'];
        }

        if (empty($info)) return "";

        return "\n\n" . $info . " (für eventuelle Rückfragen)";
    }
}
