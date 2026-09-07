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
    $pdfFileName = trim($registrationNo . '.pdf');
    $filePath = "../pdf_files/extra_pdf/$type/" . $pdfFileName;

    if ($type == "minus_balance") {
        $textWaterMark = "MINUS BALANCE";
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
    $mpdf->showWatermarkText = true;
    $mpdf->WriteHTML($css, 1);
    $mpdf->WriteHTML($letter, 2);

    $mpdf->Output($filePath);
    if (file_exists($filePath)) {
        $link = "pdf_files/extra_pdf/$type/" . $pdfFileName;
        $resultArray = array("StatusCode" => 200, "Message" => "Succesfully uploaded", "Link" => $link);
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "Failed to upload authority");
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
