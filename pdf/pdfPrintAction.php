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
        require_once('fpdf17/fpdf.php');
        require_once('bookTablePdf.php');

        $pdfTable = new BookTablePDF(json_decode(base64_decode($_POST['books']), true));
        $pdfTable->printTable();
        $pdfTable->Output();

        exit; // !!! REQUIRED !!!
    }
}