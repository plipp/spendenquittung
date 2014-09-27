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

        $this->topicBox("Tag der Zuwendung, Wert der Zuwendung:", date("d.m.Y"). ", TODO\n");
        $this->topicBox("Genaue Bezeichnung der Sachzuwendung mit Alter, Zustand, Kaufpreis usw.:","\ns. Anhang 'Spendenliste'\n");

        $this->bulletText("Zutreffendes Bitte Ankreuzen", array("Die Sachzuwendung stammt nach den Angaben des Zuwendenden aus dem Betriebsvermögen. ".
            "Die Zuwendung wurde nach dem Wert der Entnahme (ggf. mit dem niedrigeren gemeinen Wert) und nach der Umsatzsteuer, ".
            "die auf die Entnahme entfällt, bewertet.",
            "Die Sachzuwendung stammt nach den Angaben des Zuwendenden aus dem Privatvermögen.",
            "Der Zuwendende hat trotz Aufforderung keine Angaben zur Herkunft der Sachzuwendung gemacht.",
            "Geeignete Unterlagen, die zur Wertermittlung gedient haben, z. B. Rechnung, Gutachten, liegen vor."
        ));
        $this->topicBox("",
            "Es wird bestätigt, dass die Zuwendung nur zur Förderung des Berliner Büchertisches verwendet wird.
            \n\n\n\n Ort, Datum, Unterschrift des Zuwendungsempfängers");

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

    private function bulletText ($title, $text_array) {
        $column_width = $this->w-30;

        $bulletList = array();
        $bulletList['bullet'] = 'o';
        $bulletList['margin'] = ' ';
        $bulletList['indent'] = 2;
        $bulletList['spacer'] = 2;
        $bulletList['text'] = array_map("utf8_decode",$text_array);
        $this->SetX(20);
        $this->MultiCellBltArray($column_width-$this->x, 6, $bulletList);
        $this->Ln(10);

    }
    /************************************************************
     *                                                           *
     *    MultiCell with bullet (array)                          *
     *                                                           *
     *    Requires an array with the following  keys:            *
     *                                                           *
     *        Bullet -> String or Number                         *
     *        Margin -> Number, space between bullet and text    *
     *        Indent -> Number, width from current x position    *
     *        Spacer -> Number, calls Cell(x), spacer=x          *
     *        Text -> Array, items to be bulleted                *
     *                                                           *
     *    see http://fpdf.de/downloads/addons/56/
     ************************************************************/

    private function MultiCellBltArray($w, $h, $blt_array, $border=0, $align='J', $fill=0)
    {
        if (!is_array($blt_array))
        {
            die('MultiCellBltArray requires an array with the following keys: bullet, margin, text, indent, spacer');
            exit;
        }

        //Save x
        $bak_x = $this->x;

        for ($i=0; $i<sizeof($blt_array['text']); $i++)
        {
            //Get bullet width including margin
            $blt_width = $this->GetStringWidth($blt_array['bullet'] . $blt_array['margin'])+$this->cMargin*2;

            // SetX
            $this->SetX($bak_x);

            //Output indent
            if ($blt_array['indent'] > 0)
                $this->Cell($blt_array['indent']);

            //Output bullet
            $this->Cell($blt_width, $h, $blt_array['bullet'] . $blt_array['margin'], 0, '', $fill);

            //Output text
            $this->MultiCell($w-$blt_width, $h, $blt_array['text'][$i], $border, $align, $fill);

            //Insert a spacer between items if not the last item
            if ($i != sizeof($blt_array['text'])-1)
                $this->Ln($blt_array['spacer']);

            //Increment bullet if it's a number
            if (is_numeric($blt_array['bullet']))
                $blt_array['bullet']++;
        }

        //Restore x
        $this->x = $bak_x;
    }

    private function initialPdfSettings()
    {
        $this->AddPage();
        $this->SetFont('Arial','',10);
        $this->AliasNbPages();
    }
}
