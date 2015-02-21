<?php
require_once('fpdf17/fpdf.php');

/**
 * Generates the PDF of the Spendenbescheinigung.
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

        $pdf->setPageHeader("");
        $pdf->AddPage();
    }

    function printBescheinigung()
    {
        $this->_pdf->Image("logo.jpg",134);

        $this->_pdf->Ln(5);
        $this->_pdf->topicBox('Aussteller (Bezeichnung und Anschrift der steuerbegünstigten Einrichtung)', "\nBerliner Büchertisch e.V.\nMehringdamm 51, 10961 Berlin\n ");

        $this->_pdf->Ln(1);
        $this->_pdf->info('Bestätigung über Sachzuwendungen', "im Sinne des § 10b des Einkommensteuergesetzes an eine der in § 5 Abs. 1 Nr. 9 des Körperschaftsteuergesetzes bezeichneten Körperschaften, Personenvereinigungen oder Vermögensmassen");
        $this->_pdf->Ln(1);

        $this->_pdf->topicBox('Name und Anschrift des Zuwendenden:',
            "\n" .
            $this->_addressData['firstname']." ".
            $this->_addressData['lastname']."\n".
            $this->_addressData['street'].", ".
            $this->_addressData['zip']." ".$this->_addressData['city'] . "\n ");

        $this->_pdf->topicBox("Wert der Zuwendung / Tag der Zuwendung:" , $this->_amount . " EUR / " . date("d.m.Y"), 6);
        $this->_pdf->topicBox("Genaue Bezeichnung der Sachzuwendung mit Alter, Zustand, Kaufpreis usw.", "s. Anhang 'Spendenliste'", 6);
        $this->_pdf->Ln(3);

        $this->_pdf->bulletList ('[_]', array(
                "Die Sachzuwendung stammt nach den Angaben des Zuwendenden aus dem Betriebsvermögen. Die Zuwendung wurde nach dem Wert der Entnahme (ggf. mit dem niedrigeren gemeinen Wert) und nach der Umsatzsteuer, die auf die Entnahme entfällt, bewertet.",
                "Die Sachzuwendung stammt nach den Angaben des Zuwendenden aus dem Privatvermögen.",
                "Der Zuwendende hat trotz Aufforderung keine Angaben zur Herkunft der Sachzuwendung gemacht.",
                "Geeignete Unterlagen, die zur Wertermittlung gedient haben, z. B. Rechnung, Gutachten, liegen vor.")
        );
        $this->_pdf->Ln(10);
        $this->_pdf->bulletList ('[X]', array(
                "Die Einhaltung der satzungsmäßigen Voraussetzungen nach den §§ 51, 59, 60 und 61 AO wurde vom Finanzamt für Körperschaften I StNr. DE24 28 22 805 mit Bescheid vom 19.12.2014 nach § 60a AO gesondert festgestellt. Wir fördern nach unserer Satzung Bildung, Kunst und Kultur.")
        );


        $this->_pdf->SetY(-98);
        $this->_pdf->topicBox("Es wird bestätigt, dass die Zuwendung nur zur Förderung (Angabe des begünstigten Zwecks /der begünstigten Zwecke)","- Bildung \n- Kunst und Kultur",6, false);
        $this->_pdf->styledText("verwendet wird", 'I');
        $this->_pdf->Ln(17);
        $this->_pdf->titledLine("(Ort, Datum, Unterschrift des Zuwendungsempfängers)");
        $this->_pdf->Ln(4);
        $this->_pdf->info("Hinweis:",
            "Wer vorsätzlich oder grob fahrlässig eine unrichtige Zuwendungsbestätigung erstellt oder veranlasst, dass Zuwendungen nicht zu den in der  " .
            "Zuwendungsbestätigung angegebenen steuerbegünstigten Zwecken verwendet werden, haftet für die entgangene Steuer (§ 10b Abs. 4 EStG, § 9 " .
            "Abs. 3 KStG, § 9 Nr. 5 GewStG).\n\n" .
            "Diese Bestätigung wird nicht als Nachweis für die steuerliche Berücksichtigung der Zuwendung anerkannt, wenn das Datum des " .
            " Freistellungsbescheides länger als 5 Jahre bzw. das Datum der Feststellung der Einhaltung der satzungsmäßigen Voraussetzungen nach § 60a Abs. 1 " .
            "AO länger als 3 Jahre seit Ausstellung des Bescheides zurückliegt (§ 63 Abs. 5 AO).");
    }
}
