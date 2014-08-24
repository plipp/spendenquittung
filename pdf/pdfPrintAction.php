<?php


class PdfPrintAction
{

    public function PdfPrintAction()
    {
        add_action('wp_ajax_as_pdf', array($this, 'as_pdf'));
        add_action('wp_ajax_nopriv_as_pdf', array($this, 'as_pdf'));
    }

    public function as_pdf()
    {
        error_log("PDF-POST:" . json_encode($_POST));
        require('fpdf17/fpdf.php');

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10,'Hello World!');
        $pdf->Output();

        exit; // !!! REQUIRED !!!
    }
}