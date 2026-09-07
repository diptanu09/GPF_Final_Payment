<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $registrationNo = trim(removeHTMLEntities($dataCred->RegistrationNo));
    $user = trim(removeHTMLEntities($dataCred->UserName));
    $contents = trim(removeHTMLEntities($dataCred->Authority));
    $authorityType = trim(removeHTMLEntities($dataCred->AuthorityType));

    $fileName = $registrationNo . '.pdf';
    $pdf = fopen($fileName, 'w');
    fwrite($pdf, base64_decode($contents));
    fclose($pdf);
    $destination = "../pdf_files/signed_pdf/" . $authorityType . "/" . $fileName;
    if (rename($fileName, $destination)) {
        if ($authorityType == "dlis") {
            $status = 7;
            $caseLogSLNo = sqlSerialNo($connection, "GPF_DLIS_SIGN", "SL_NO");
            $caseSignedQuery = "INSERT INTO GPF_DLIS_SIGN VALUES('$caseLogSLNo','$registrationNo', '$user', SYSDATE, '')";
            $statusUpdate = "UPDATE GPF_CASE_STATUS SET DLIS_SIGNED_DATE=SYSDATE, CASE_STATUS='$status' WHERE REGD_NO = '$registrationNo' AND CASE_STATUS!='11'";
        } else if ($authorityType == "authority") {
            $status = 7;
            $caseLogSLNo = sqlSerialNo($connection, "GPF_AUTHORITY_SIGN", "SL_NO");
            $caseSignedQuery = "INSERT INTO GPF_AUTHORITY_SIGN VALUES('$caseLogSLNo','$registrationNo', '$user', SYSDATE, '')";
            $statusUpdate = "UPDATE GPF_CASE_STATUS SET FP_SIGNED_DATE=SYSDATE, CASE_STATUS='$status' WHERE REGD_NO = '$registrationNo' AND CASE_STATUS!='11'";
        } else {
            $status = 16;
            $caseLogSLNo = sqlSerialNo($connection, "GPF_LTA_AUTHORITY_SIGN", "SL_NO");
            $caseSignedQuery = "INSERT INTO GPF_LTA_AUTHORITY_SIGN VALUES('$caseLogSLNo','$registrationNo', '$user', SYSDATE, '')";
            $statusUpdate = "UPDATE GPF_CASE_STATUS SET LTA_SIGNED_DATE=SYSDATE, CASE_STATUS='$status' WHERE REGD_NO = '$registrationNo' AND CASE_STATUS!='11'";
        }
        $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");
        $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo',$status, SYSDATE, '$user')";

        $statusUpdateBool = sqlCUDData($connection, $statusUpdate);
        $caseSignedBool = sqlCUDData($connection, $caseSignedQuery);
        $caseLogBool = sqlCUDData($connection, $caseLogQuery);

        if ($authorityType != "dlis") {
            $detailSQL = "SELECT * FROM GPF_CASE_STATUS a INNER JOIN GPF_AMOUNT_INFO b ON a.REGD_NO=b.REGD_NO 
            INNER JOIN VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID WHERE a.REGD_NO='$registrationNo'";
            $fetchDetail = sqlFetchData($connection, $detailSQL);
            foreach ($fetchDetail as $fetchDetails) {
                $finYear = $fetchDetails['FIN_YEAR_CODE'];
                $mobileNo = $fetchDetails['MOBILE_NO'];
                $subscriberName = $fetchDetails['SUBSCRIBER_NAME'];
                $seriesCode = $fetchDetails['SERIES_ID'];
                $seriesName = $fetchDetails['SERIES_DESCR'];
                $accountNo = $fetchDetails['ACCOUNT_NO'];
                $employeeNo = $fetchDetails['EMPLOYEE_CODE'];
                $beneficiaryNo = $fetchDetails['BENEFICIARY_CODE'];
                $openingBalance = $fetchDetails['OPENING_BAL_AMOUNT'];
                $finalPayment = $fetchDetails['FINAL_PAYMENT_AMOUNT'];
                $actualDeposit = $fetchDetails['ACTUAL_DEPOSIT'];
                $excessDeposit = $fetchDetails['EXCESS_DEPOSIT'];
                $withdrawal = $fetchDetails['WITHDRAWAL'];
                $actualInterest = $fetchDetails['ACTUAL_INTEREST'];
                $delayedInterest = $fetchDetails['DELAYED_INTEREST'];
            }
            $totalDeposit = $actualDeposit + $excessDeposit;
            $totalInterest = $actualInterest + $delayedInterest;

            if ($mobileNo > 0) {
                $message = "";
                $message .= "$subscriberName T/$seriesName/$accountNo: Ur case finalized for Rs. $finalPayment. ";
                $message .= "Contact ur DDO for payment. To download authority visit https://gpfagartala.agtripura.gov.in/GpfAgartala/. ";
                $message .= "Available on DDOs HRMS, CSC portal.";
                $smsSLNo = sqlSerialNo($connection, "GPF_SMS", "SL_NO");

                $smsQuery = "INSERT INTO GPF_SMS VALUES('$smsSLNo','$registrationNo','$seriesCode','$accountNo',
                             '$mobileNo','$message','N',SYSDATE)";
                sqlCUDData($connection, $smsQuery);
            }

            /* $updateGPAccSQL = "UPDATE VLCS.GP_ACCOUNTS SET ACCOUNT_CLOSED_TAG='Y', DATE_OF_AUTHORITY = SYSDATE, 
            DATE_OF_CLOSURE = SYSDATE, REASON_FOR_CLOSURE='FP' WHERE SERIES_ID='$seriesCode' AND ACCOUNT_NO='$accountNo'";

            $deleteFPSQL = "DELETE FROM VLCS.FINAL_PAYMENT_DETAILS WHERE SERIES_ID='$seriesCode' AND
                            ACCOUNT_NO='$accountNo'";
            sqlCUDData($connection, $deleteFPSQL);

            $vlcFinalPaymentSQL = "INSERT INTO VLCS.FINAL_PAYMENT_DETAILS VALUES('$finYear', '$seriesCode', 
                                  '$accountNo', '$subscriberName', '$employeeNo', '$beneficiaryNo', '$mobileNo', 
                                  '$openingBalance', '$totalDeposit', '$withdrawal', 0, '$totalInterest',
                                   '$finalPayment', SYSDATE, 0, '', '', '')";
            $updateGPAccBool = sqlCUDData($connection, $updateGPAccSQL);
            $vlcFinalPaymentBool = sqlCUDData($connection, $vlcFinalPaymentSQL);*/
        }



        // if (($statusUpdateBool && $caseSignedBool && $caseLogBool) || ($updateGPAccBool && $vlcFinalPaymentBool)) {
        if ($statusUpdateBool && $caseSignedBool && $caseLogBool) {
            $link = "pdf_files/signed_pdf/" . $authorityType . "/" . $fileName;
            deleteFile("../pdf_files/unsigned_pdf/" . $registrationNo . "_" . $authorityType . ".pdf");
            emptyDirectory("../assets/images/qr");
            $resultArray = array(
                "StatusCode" => 200, "Message" => "Successfully generated PDF file", "Link" => $link
            );
        } else {
            $resultArray = array("StatusCode" => 205, "Message" => "PDF generated, but not saved in database");
        }
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "PDF file not generated");
    }
    echo json_encode($resultArray, JSON_PRETTY_PRINT);
}
