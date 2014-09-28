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
        require_once('bescheinigungPdf.php');
        require_once('pdfToolbox.php');

        $pdf = new PdfToolbox();
        $bescheinigungPdf = new BescheinigungPDF($this->addressData($_POST), $pdf);
        $bescheinigungPdf->printBescheinigung();

        $pdfTable = new BookTablePDF(json_decode(base64_decode($_POST['books']), true), $pdf);
        $pdfTable->printTable();
        $pdf->Output();

        exit; // !!! REQUIRED !!!
    }

    private function addressData($postData) {
        $address = array();
        $address["lastname"]=sanitize_text_field($postData["lastname"]);
        $address["firstname"]=sanitize_text_field($postData["firstname"]);
        $address["street"]=sanitize_text_field($postData["street"]);
        $address["zip"]=sanitize_text_field($postData["zip"]);
        $address["city"]=sanitize_text_field($postData["city"]);
        $address["phone"]=sanitize_text_field($postData["phone"]);
        $address["email"]=sanitize_text_field($postData["email"]);
        return $address;
    }
}