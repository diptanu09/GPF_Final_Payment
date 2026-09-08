<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $registrationNo = trim(removeHTMLEntities($dataCred->RegistrationNo));
    $loginUser = trim(removeHTMLEntities($dataCred->UserName));
    $buttonVal = trim(removeHTMLEntities($dataCred->BtnValue));
    $checkappBy = "";
    $updDate = "";
    if ($buttonVal == "CHECK") {
        $caseStatus = 5;
        $updDate = ", CHECKED_DATE = SYSDATE";
        $checkappBy = "CHECKED_BY='" . $_SESSION['GPF_USERNAME'] . "'";
        $error = "Failed to check";
        $success = "Successfully checked";
    } elseif ($buttonVal == "APPROVE") {
        $caseStatus = 6;
        $updDate = ", APPROVED_DATE = SYSDATE";
        $checkappBy = "APPROVED_BY='" . $_SESSION['GPF_USERNAME'] . "'";
        $error = "Failed to update";
        $success = "Successfully updated";
    } elseif ($buttonVal == "LTA-CHECK") {
        $caseStatus = 14;
        $updDate = ", LTA_CHECKED_DATE = SYSDATE";
        $checkappBy = "LTA_CHECKED_BY='" . $_SESSION['GPF_USERNAME'] . "'";
        $error = "Failed to update";
        $success = "Successfully updated";
    } elseif ($buttonVal == "LTA-APPROVE") {
        $caseStatus = 15;
        $updDate = ", LTA_APPROVED_DATE = SYSDATE";
        $checkappBy = "LTA_APPROVED_BY='" . $_SESSION['GPF_USERNAME'] . "'";
        $error = "Failed to update";
        $success = "Successfully updated";
    }



    $caseStatusQuery = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='$caseStatus' $updDate WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
    $caseInfoQuery = "UPDATE GPF_AMOUNT_INFO SET $checkappBy WHERE REGD_NO='$registrationNo'";
    $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");
    $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo','$caseStatus',SYSDATE, '$loginUser')";

    $caseStatusBool = sqlCUDData($connection, $caseStatusQuery);
    $caseInfoBool = sqlCUDData($connection, $caseInfoQuery);
    $caseLogBool = sqlCUDData($connection, $caseLogQuery);

    if (($caseStatus == 6) || ($caseStatus == 14)) {
        $detailSQL = "SELECT * FROM GPF_CASE_STATUS a INNER JOIN GPF_AMOUNT_INFO b ON a.REGD_NO=b.REGD_NO 
                      INNER JOIN VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID WHERE a.REGD_NO='$registrationNo'";
        $fetchDetail = sqlFetchData($connection, $detailSQL);
        foreach ($fetchDetail as $fetchDetails) {
            $mobileNo = $fetchDetails['MOBILE_NO'];
            $subscriberName = $fetchDetails['SUBSCRIBER_NAME'];
            $seriesCode = $fetchDetails['SERIES_ID'];
            $seriesName = $fetchDetails['SERIES_DESCR'];
            $accountNo = $fetchDetails['ACCOUNT_NO'];
            $finalPayment = $fetchDetails['FINAL_PAYMENT_AMOUNT'];
        }

        if ($mobileNo > 0) {
            $message = "";
            $message .= "$subscriberName T/$seriesName/$accountNo: Ur case has been approved for Rs. $finalPayment. ";
            $message .= "Ur case will shortly be finalized.";
            $smsSLNo = sqlSerialNo($connection, "GPF_SMS", "SL_NO");

            $smsQuery = "INSERT INTO GPF_SMS VALUES('$smsSLNo','$registrationNo','$seriesCode','$accountNo',
                         '$mobileNo','$message','N',SYSDATE)";
            sqlCUDData($connection, $smsQuery);
        }
    }
    if ($caseStatusBool && $caseInfoBool && $caseInfoBool) {
        $resultArray = array("StatusCode" => 200, "Title" => "Success", "Message" => $success);
    } else {
        $resultArray = array("StatusCode" => 205, "Title" => $error, "Message" => "Try again");
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
