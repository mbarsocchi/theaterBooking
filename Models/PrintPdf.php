<?php

require __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Booking.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Shows.php';

use Spipu\Html2Pdf\Html2Pdf;

class PrintPdf {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    function handlePrint() {
        switch (filter_input(INPUT_POST, 'f')) {
            case 'pr':
                $arrayOfShows = $_POST['showId'];
                $shows = new Shows();
                $booking = new Booking();
                $head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_header_print.php');
                $data['allBookings'] = $booking->getBookings($shows->retriveShowByShowIds($arrayOfShows));
                $tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'print_view.php', $data);
                $footer['includeFooter'] = false;
                $foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php',$footer);
                $html = $head->render() . $tmpl->render(). $foot->render();
                $this->createPdf($html);
                break;
        }
        header('Location: print_list.php');
    }

    public static function createPdf($html) {
        $pdfName = "booking.pdf";
        try {
            $html2pdf = new HTML2PDF('P', 'A4', 'it');
            //$html2pdf->setModeDebug(true);
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($html);
            if (file_exists($pdfName)) {
                unlink($pdfName);
            }
            $html2pdf->Output($pdfName, 'D');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }

    function wp_teather_mb_writeOnTicket($name, $ref, $code, $showDate) {
        //header( 'Content-type: image/jpeg' );
        $im = imagecreatefromjpeg(BASE_IMAGE_FOLDER . "ticket.jpg");
        $black = imagecolorallocate($im, 0, 0, 0);
        imagestring($im, 5, 160, 18, $name, $black);
        imagestring($im, 5, 184, 55, $ref, $black);
        imagestring($im, 5, 168, 93, $showDate, $black);
        imagestring($im, 5, 103, 129, $code, $black);
        $filename = TMP . $code . ".jpg";
        imagejpeg($im, $filename, 100);
        return $filename;
    }

}
