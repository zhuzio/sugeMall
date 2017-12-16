<?php

class QrcodeApp extends MallbaseApp {

    function index() {
        import('phpqrcode');
        $value = $_GET['url'];
        $length = $_GET['length']?$_GET['length']:4;
        $errorCorrectionLevel = "L";
        $matrixPointSize = $length;
        QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
        exit;
    }
}

?>
