<?php
require_once('fpdf17/fpdf.php');

/**
 * Container for the complete PDF of the Spendenbescheinigung.
 */
class PdfToolbox extends FPDF
{
    const PAGE_HEADER = "Spendenliste";
    const BOX_SIZE = 190;

    private $_pageHeader = self::PAGE_HEADER;

    public function __construct()
    {
        parent::__construct();
        $this->initialSettings();
    }

    // override
    function Header()
    {
        if (!empty( $this->_pageHeader)) {
            $this->SetFont('Arial', 'B', 15);
            $this->Cell(80);
            $this->Cell(30, 10, $this->_pageHeader, 0, 0, 'C');
            $this->Ln(15);
        }
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

    public function setPageHeader ($header) {
        $this->_pageHeader = $header;
    }
    public function topicBox($topic, $content, $lineHeight=4, $nl=true)
    {
        $this->styledText($topic, 'I');
        $this->boxedText($content, 1, 10, $lineHeight);
        if ($nl) $this->Ln();
    }

    public function info($title, $infoText)
    {
        $this->styledText($title, 'B', 7);
        $this->boxedText($infoText, 0, 7);
        $this->Ln();
    }

    public function boxedText($content,$withBorder,$fontSize, $lineHeight=4)
    {
        $this->SetFont('Arial', '', $fontSize);
        $this->MultiCell(self::BOX_SIZE, $lineHeight, utf8_decode($content), $withBorder);
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

    public function bulletList ($bullet, $items) {
        $bltList = array();
        $bltList['bullet'] = $bullet;
        $bltList['margin'] = '     ';
        $bltList['indent'] = 0;
        $bltList['spacer'] = 2;
        $bltList['text'] = $items;
        $this->SetX(10);
        $this->SetFont('Arial', '', 7);
        $this->MultiCellBltArray(self::BOX_SIZE, 4, $bltList);
    }

    /*************************************************************
     *                                                           *
     * Original: http://fpdf.de/downloads/addons/56/             *
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
     ************************************************************/

    private function MultiCellBltArray($w, $h, $blt_array, $border=0, $align='J', $fill=0)
    {
        if (!is_array($blt_array))
        {
            die('MultiCellBltArray requires an array with the following keys: bullet, margin, text, indent, spacer');
            return;
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
            $this->MultiCell($w-$blt_width, $h, utf8_decode($blt_array['text'][$i]), $border, $align, $fill);

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
}
