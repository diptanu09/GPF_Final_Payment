<?php

require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";
require_once '../assets/lib/pdflib/autoload.php';
require_once '../assets/lib/phpqrcode/qrlib.php';


if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $registrationNo = trim(removeHTMLEntities($dataCred->RegistrationNo));
    $letter = trim($dataCred->Authority);
    $type = trim($dataCred->AuthorityType);
    $pdfFileName = trim($registrationNo . '_' . $type . '.pdf');
    $filePath = "../pdf_files/unsigned_pdf/" . $pdfFileName;
    if ($type == "authority") {
        $textWaterMark = "GPF FINAL PAYMENT AUTHORITY";
    } elseif ($type == "dlis") {
        $textWaterMark = "DEPOSIT LINKED INSURENCE SCHEME AUTHORITY";
    } else {
        $textWaterMark = "LTA OF GPF FINAL PAYMENT AUTHORITY";
    }

    $css = "";
    $css .= file_get_contents("../assets/css/font-awesome.min.css");
    $css .= file_get_contents("../assets/css/bootstrap.min.css");
    $css .= file_get_contents("../assets/css/report_format.css");
    $css .= file_get_contents("../assets/css/style.css");

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->autoScriptToLang = true;
    $mpdf->baseScript = 1;
    $mpdf->autoVietnamese = true;
    $mpdf->autoArabic = true;

    $mpdf->autoLangToFont = true;
    $mpdf->AddPageByArray([
        'margin-top' => 10,
        'margin-bottom' => 0,
    ]);
    $mpdf->SetWatermarkImage(
        '../assets/images/cag_logo.png',
        -1,
        '',
        'F',
        ''
    );
    $mpdf->showWatermarkImage = false;
    $mpdf->SetWatermarkText($textWaterMark);
    $mpdf->showWatermarkText = true;
    $mpdf->WriteHTML($css, 1);
    $mpdf->WriteHTML($letter, 2);

    $mpdf->Output($filePath);
    if (file_exists($filePath)) {
        //deleteFile("../assets/images/qr/" . $registrationNo . "_" . $type . ".png");
        $resultArray = array("StatusCode" => 200, "Message" => "Succesfully uploaded");
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "Failed to upload authority");
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
