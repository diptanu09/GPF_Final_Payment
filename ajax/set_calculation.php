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
    $uniqueID = date("YmdHis");

    $detailsSQL = "SELECT b.FIN_YEAR_CODE, a.CLOSING_FIN_YEAR, a.INTEREST_ALLOWED_UPTO FROM GPF_AMOUNT_INFO a 
                   INNER JOIN GPF_CASE_STATUS b ON a.REGD_NO=b.REGD_NO WHERE a.REGD_NO='$registrationNo' AND
                   b.CASE_STATUS!=11";
    $fetchDetail = sqlFetchData($connection, $detailsSQL);
    foreach ($fetchDetail as $fetchDetails) {
        $retFinYear = $fetchDetails['FIN_YEAR_CODE'];
        $closingFinYear = $fetchDetails['CLOSING_FIN_YEAR'];
        $intAllowedUpto = date("d-M-Y", strtotime($fetchDetails['INTEREST_ALLOWED_UPTO']));
    }

    // $myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
    // fwrite($myfile, $addMonthSQL . "\n");


    $insertAccountCalSQL = "INSERT INTO GPF_ACCOUNT_CALCULATION(CALCULATION_ID, REGD_NO, FIN_YEAR_CODE,
                            PAY_SLIP_DATE, INTEREST_DATE, SUBSCRIPTION_AMT, REFUND_AMT, OTHERS_AMT,WITHDRAWAL_AMT,ADVANCE_AMT,
                            RATE_OF_INTEREST, CUT_MONTH, INTEREST_ON_DEPOSIT, ADJUSTMENT, DEPOSIT, WITHDRAWAL)
                            SELECT ($registrationNo+ROWNUM)||'-'||$uniqueID, D.REG_NO, D.FIN_YEAR_CODE,
                            D.PAY_SLIP_DATE, D.INTEREST_DATE, D.SUBSCRIPTION_AMT, D.REFUND_AMT, D.OTHERS_AMT, D.WITHDRAWAL_AMT, D.ADVANCE_AMT,
                            D.RATE_OF_INTEREST, D.CUT_MONTH, D.INT_DEP, D.ADJUSTMENT, D.TOT_DEPOSIT, D.TOT_WITHDRAWAL FROM
                            (SELECT $registrationNo REG_NO, T.FIN_YEAR_CODE, 
                            T.PAY_SLIP_DATE, T.INTEREST_DATE, T.SUBSCRIPTION_AMT, T.REFUND_AMT, T.OTHERS_AMT, T.WITHDRAWAL_AMT, T.ADVANCE_AMT, 
                            S.RATE_OF_INTEREST, 'N' CUT_MONTH, 'Y' INT_DEP,CASE WHEN NVL(T.ADJUSTMENT_NO,'0')='0' THEN 'N' ELSE 'Y' END ADJUSTMENT, 0 TOT_DEPOSIT, 0 TOT_WITHDRAWAL FROM 
                            (SELECT FIN_YEAR_CODE, PAY_SLIP_DATE, INTEREST_DATE, NVL(SUM(SUBSCRIPTION_AMT),0)SUBSCRIPTION_AMT, NVL(SUM(REFUND_AMT),0)REFUND_AMT, 
                            NVL(SUM(OTHERS_AMT),0)OTHERS_AMT, NVL(SUM(WITHDRAWAL_AMT),0)WITHDRAWAL_AMT, 
                            NVL(SUM(ADVANCE_AMT),0)ADVANCE_AMT, ADJUSTMENT_NO FROM (SELECT FIN_YEAR_CODE, PAY_SLIP_DATE, INTEREST_DATE, NVL(SUBSCRIPTION_AMT,0) SUBSCRIPTION_AMT, NVL(REFUND_AMT,0) REFUND_AMT,
                            NVL(OTHERS_AMT,0) OTHERS_AMT, NVL(WITHDRAWAL_AMT,0) WITHDRAWAL_AMT,
                            NVL(ADVANCE_AMT,0) ADVANCE_AMT, ADJUSTMENT_NO FROM GPF_SUBSCRIPTION WHERE REGD_NO='$registrationNo'
                            AND FIN_YEAR_CODE > '$closingFinYear') GROUP BY FIN_YEAR_CODE, PAY_SLIP_DATE, INTEREST_DATE, ADJUSTMENT_NO ORDER BY PAY_SLIP_DATE, INTEREST_DATE)T INNER JOIN MAS_INTEREST S
                            ON T.PAY_SLIP_DATE=S.YEAR_DESC UNION ALL                            
                            SELECT $registrationNo REG_NO, FIN_YEAR_CODE, YEAR_DESC PAY_SLIP_DATE, YEAR_DESC INTEREST_DATE,
                            0 SUBSCRIPTION_AMT,0 REFUND_AMT,0 OTHERS_AMT, 0 WITHDRAWAL_AMT, 0 ADVANCE_AMT, RATE_OF_INTEREST, 
                            'N' CUT_MONTH, 'Y' INT_DEP, 'N' ADJUSTMENT, 0 TOT_DEPOSIT, 0 TOT_WITHDRAWAL FROM MAS_INTEREST WHERE YEAR_DESC>
                            (SELECT MAX(PAY_SLIP_DATE) PAY_SLIP_DATE FROM GPF_SUBSCRIPTION WHERE REGD_NO='$registrationNo')
                            AND YEAR_DESC<='$intAllowedUpto' AND FIN_YEAR_CODE > '$closingFinYear')D ORDER BY D.PAY_SLIP_DATE, D.INTEREST_DATE";

    $insertBool = sqlCUDData($connection, $insertAccountCalSQL);

    $muniqueID = date("miHYsd");
    $missingSQL = "INSERT INTO GPF_ACCOUNT_CALCULATION(CALCULATION_ID, REGD_NO, FIN_YEAR_CODE,
    PAY_SLIP_DATE, INTEREST_DATE, SUBSCRIPTION_AMT, REFUND_AMT, OTHERS_AMT,WITHDRAWAL_AMT,ADVANCE_AMT,
    RATE_OF_INTEREST, CUT_MONTH, INTEREST_ON_DEPOSIT, ADJUSTMENT, DEPOSIT, WITHDRAWAL)SELECT ($registrationNo+ROWNUM)||'-'||$muniqueID, 
    $registrationNo REG_NO, A.FIN_YEAR_CODE, A.YEAR_DESC PAY_SLIP_DATE, A.YEAR_DESC INTEREST_DATE, 0 SUBSCRIPTION_AMT,
    0 REFUND_AMT,0 OTHERS_AMT,0 WITHDRAWAL_AMT,0 ADVANCE_AMT, A.RATE_OF_INTEREST, 'N' CUT_MONTH, 'Y' INT_DEP, 'N' ADJUSTMENT,
    0 TOT_DEPOSIT, 0 TOT_WITHDRAWAL FROM MAS_INTEREST A WHERE A.YEAR_DESC NOT IN (SELECT B.PAY_SLIP_DATE FROM GPF_ACCOUNT_CALCULATION B
    WHERE B.REGD_NO='$registrationNo' AND B.ADJUSTMENT='N') AND A.FIN_YEAR_CODE> '$closingFinYear' AND A.YEAR_DESC<='$intAllowedUpto'
    ORDER BY A.YEAR_DESC";

    $missingBool = sqlCUDData($connection, $missingSQL);



    if ($insertBool || $missingBool) {
        $amtSumSQL = "SELECT FIN_YEAR_CODE, INTEREST_DATE, SUM(NVL(SUBSCRIPTION_AMT,0)+NVL(REFUND_AMT,0)+NVL(OTHERS_AMT,0)) DEPOSIT,
                      SUM(NVL(WITHDRAWAL_AMT,0)+NVL(ADVANCE_AMT,0)) WITHDRAWAL FROM GPF_ACCOUNT_CALCULATION 
                      WHERE REGD_NO='$registrationNo' AND ADJUSTMENT='N' GROUP BY FIN_YEAR_CODE, INTEREST_DATE ORDER BY INTEREST_DATE";
        $fetchSumAmt = sqlFetchData($connection, $amtSumSQL);
        foreach ($fetchSumAmt as $amount) {
            $deposit = $amount['DEPOSIT'];
            $withdrawl = $amount['WITHDRAWAL'];
            $psDate = date("d-M-Y", strtotime($amount['INTEREST_DATE']));
            $updateSQL = "UPDATE GPF_ACCOUNT_CALCULATION SET DEPOSIT='$deposit', WITHDRAWAL='$withdrawl'
                          WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE='$psDate' AND ADJUSTMENT='N'";
            sqlCUDData($connection,  $updateSQL);
        }
        $accMonthSQL = "SELECT b.ACCOUNTING_MONTH, b.YEAR_DESC FROM GPF_ACCOUNT_CALCULATION a INNER JOIN MAS_INTEREST b ON 
                            a.PAY_SLIP_DATE=b.YEAR_DESC WHERE a.REGD_NO='$registrationNo'";
        $fetchAccMonth = sqlFetchData($connection, $accMonthSQL);
        foreach ($fetchAccMonth as $accMonths) {
            $accountingMonth = $accMonths['ACCOUNTING_MONTH'];
            $psDate = date("d-M-Y", strtotime($accMonths['YEAR_DESC']));
            $updateAccMonthSQL = "UPDATE GPF_ACCOUNT_CALCULATION SET ACCOUNTING_MONTH='$accountingMonth'
                          WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE='$psDate'";
            sqlCUDData($connection,  $updateAccMonthSQL);
        }

        $retAdjSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND ADJUSTMENT='Y'
                      AND FIN_YEAR_CODE>'$retFinYear'";
        $exists = sqlCountData($connection, $retAdjSQL);
        if ($exists > 0) {
            $updateAccRetMonthSQL = "UPDATE GPF_ACCOUNT_CALCULATION SET FIN_YEAR_CODE='$retFinYear'
                                     WHERE REGD_NO='$registrationNo' AND FIN_YEAR_CODE>'$retFinYear'
                                     AND ADJUSTMENT='Y'";
            sqlCUDData($connection,  $updateAccRetMonthSQL);
        }

        $dupSQL = "SELECT PAY_SLIP_DATE, DEPOSIT, WITHDRAWAL,  COUNT(*) FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' GROUP BY PAY_SLIP_DATE, DEPOSIT, WITHDRAWAL
                   HAVING COUNT(*)>1";
        $dupExists = sqlCountData($connection, $dupSQL);
        if ($dupExists > 0) {
            $fetchDupMnth = sqlFetchData($connection, $dupSQL);
            foreach ($fetchDupMnth as $fetchDupMnths) {
                $paySlip = date("d-M-Y", strtotime($fetchDupMnths['PAY_SLIP_DATE']));
                $updateDupSQL = "UPDATE GPF_ACCOUNT_CALCULATION SET DEPOSIT=0, WITHDRAWAL=0 WHERE REGD_NO='$registrationNo'
                                 AND PAY_SLIP_DATE!=INTEREST_DATE AND PAY_SLIP_DATE='$paySlip'";
                sqlCUDData($connection,  $updateDupSQL);
            }
        }

        $sqlMonthMising = "SELECT FIN_YEAR_CODE, ACCOUNTING_MONTH, YEAR_DESC, RATE_OF_INTEREST FROM MAS_INTEREST WHERE
        FIN_YEAR_CODE> '$closingFinYear' AND YEAR_DESC<='$intAllowedUpto' ORDER BY FIN_YEAR_CODE, YEAR_DESC";

        $fetchMonthMissing = sqlFetchData($connection, $sqlMonthMising);
        $uniqID = date("miHYsd");
        $regdNo = 0;
        foreach ($fetchMonthMissing as $mnthLists) {
            $regdNo += $registrationNo;
            $slID = $regdNo . "-" . $uniqID;
            $mAccountingMonth = $mnthLists['ACCOUNTING_MONTH'];
            $mFinYear = $mnthLists['FIN_YEAR_CODE'];
            $mYearDesc = $mnthLists['YEAR_DESC'];
            $mROI = $mnthLists['RATE_OF_INTEREST'];

            $existSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND 
                        FIN_YEAR_CODE='$mFinYear' AND PAY_SLIP_DATE='$mYearDesc' AND ACCOUNTING_MONTH='$mAccountingMonth'";
            $existRecords = sqlCountData($connection, $existSQL);
            if ($existRecords == 0) {
                $addMonthSQL = "INSERT INTO GPF_ACCOUNT_CALCULATION(CALCULATION_ID, REGD_NO, FIN_YEAR_CODE, ACCOUNTING_MONTH,
                                PAY_SLIP_DATE, INTEREST_DATE, SUBSCRIPTION_AMT, REFUND_AMT, OTHERS_AMT,WITHDRAWAL_AMT,ADVANCE_AMT,
                                RATE_OF_INTEREST, CUT_MONTH, INTEREST_ON_DEPOSIT, ADJUSTMENT, DEPOSIT, WITHDRAWAL) VALUES('$slID', '$registrationNo',
                                '$mFinYear', '$mAccountingMonth', '$mYearDesc', '$mYearDesc',0,0,0,0,0,'$mROI','N','Y','N',0,0)";
                $myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
                fwrite($myfile, $addMonthSQL . "\n");
                sqlCUDData($connection, $addMonthSQL);
            }

            $delAccMonthSQL = "DELETE FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND  ACCOUNTING_MONTH IS NULL";
            sqlCUDData($connection, $delAccMonthSQL);
        }
        $resultArray = array("StatusCode" => 200, "Message" => "Successfully added");
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "Failed");
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
