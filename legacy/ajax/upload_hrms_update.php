<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $updateToggle = trim(removeHTMLEntities($dataCred->UpdateToggle));
    $registrationNo = trim(removeHTMLEntities($dataCred->RegistrationNo));
    $caseStatus = trim(removeHTMLEntities($dataCred->CaseStatus));
    $loginUser = trim(removeHTMLEntities($dataCred->LoginUser));

    $fieldNameSet = "";
    if ($updateToggle == 1) {
        if ($caseStatus == 7) {
            $status = 8;
            $fieldNameSet = "FP_UPLOAD_DATE=SYSDATE, CASE_STATUS='$status'";
        } else {
            $status = 17;
            $fieldNameSet = "LTA_UPLOAD_DATE=SYSDATE, CASE_STATUS='$status'";
        }
    } else {
        $fieldNameSet = "DLIS_UPLOAD_DATE=SYSDATE";
    }

    $caseStatusQuery = "UPDATE GPF_CASE_STATUS SET $fieldNameSet WHERE REGD_NO='$registrationNo'";
    $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");
    $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo',$status, SYSDATE, '$loginUser')";

    $caseStatusBool = sqlCUDData($connection, $caseStatusQuery);
    $caseLogBool = sqlCUDData($connection, $caseLogQuery);

    if ($caseStatusBool || $caseLogBool) {
        $resultArray = array("StatusCode" => 200, "Message" => "Sucessfully updated");
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "Failed to update");
    }
    echo json_encode($resultArray, JSON_PRETTY_PRINT);
}
