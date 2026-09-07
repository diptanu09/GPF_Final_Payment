<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";
require_once "adjustment_calculation.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));
    $loginUser = trim(removeHTMLEntities($dataCred->LoginUser));
    $registrationNo = trim(removeHTMLEntities($dataCred->RegistrationNo));



    /***************** FOR FETCHING PENSION_TYPE, CLOSING_FIN_YEAR, CLOSING_BAL_AMOUNT  *****************/

    $listSQL = "SELECT * FROM GPF_CASE_STATUS a INNER JOIN GPF_AMOUNT_INFO b ON a.REGD_NO=b.REGD_NO 
                INNER JOIN GPF_APPLICATION c ON a.REGD_NO=c.REGD_NO WHERE a.REGD_NO='$registrationNo'";
    $detail = sqlFetchData($connection, $listSQL);
    foreach ($detail as $details) {
        $effectFinYear = $details['FIN_YEAR_CODE'];
        $dateOfEffect = $details['DATE_OF_EFFECT'];
        $pension = $details['PENSION_TYPE'];
        $dlisAdmissible = $details['DLIS_ADMISSIBLE'];
        $closingFinYear = $details['CLOSING_FIN_YEAR'];
        $openingBalAmount = $details['CLOSING_BAL_AMOUNT'];
    }

    /***************** FOR FETCHING PENSION_TYPE, CLOSING_FIN_YEAR, CLOSING_BAL_AMOUNT  *****************/

    /********************************* FOR FETCHING CUT-MONTH  *********************************/
    $cutMonthSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND CUT_MONTH='Y'";
    $cdetail = sqlFetchData($connection, $cutMonthSQL);
    foreach ($cdetail as $cdetails) {
        $cutMonth = date("d-M-Y", strtotime($cdetails['PAY_SLIP_DATE']));
    }
    /********************************* FOR FETCHING CUT-MONTH  *********************************/

    /***************** FOR FETCHING FINYEAR FROM ACCOUNT CALCULATION  *****************/
    $distinctFinYearSQL = "SELECT DISTINCT(FIN_YEAR_CODE) FIN_YEAR_CODE FROM GPF_ACCOUNT_CALCULATION 
                           WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE<='$cutMonth' ORDER BY FIN_YEAR_CODE";
    $fdetail = sqlFetchData($connection, $distinctFinYearSQL);
    foreach ($fdetail as $fdetails) {
        $finYears[] = $fdetails['FIN_YEAR_CODE'];
    }
    /***************** FOR FETCHING FINYEAR FROM ACCOUNT CALCULATION  *****************/



    /********************************* CHECKING MONTHS AFTER CUT-MONTH  *********************************/
    $monthAfterCutMonthSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE>'$cutMonth'";
    $countAfterCutMonth = sqlCountData($connection, $monthAfterCutMonthSQL);
    /********************************* CHECKING MONTHS AFTER CUT-MONTH  *********************************/

    /*************************************** CALCULATION STARTS ******************************************* */

    /********************************************** BEFORE DELAY INTEREST ************************************ */
    for ($i = 0; $i < sizeof($finYears); $i++) {
        $updtOBSQL = "UPDATE GPF_ACCOUNT_CALCULATION SET OPENING_BALANCE='$openingBalAmount' WHERE REGD_NO='$registrationNo' 
                    AND ACCOUNTING_MONTH=1 AND FIN_YEAR_CODE='$finYears[$i]' AND ADJUSTMENT='N'";
        sqlCUDData($connection, $updtOBSQL);
        $gpfCalcSQL = "SELECT PAY_SLIP_DATE, ACCOUNTING_MONTH, NVL(OPENING_BALANCE, 0) OPENING_BALANCE,
                       NVL(DEPOSIT, 0) DEPOSIT, NVL(WITHDRAWAL, 0) WITHDRAWAL,  NVL(RATE_OF_INTEREST, 0) RATE_OF_INTEREST, 
                       INTEREST_ON_DEPOSIT, CUT_MONTH, ADJUSTMENT FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                       AND FIN_YEAR_CODE='$finYears[$i]' GROUP BY PAY_SLIP_DATE, ACCOUNTING_MONTH, OPENING_BALANCE, DEPOSIT, WITHDRAWAL, RATE_OF_INTEREST,
                       INTEREST_ON_DEPOSIT, CUT_MONTH, ADJUSTMENT ORDER BY PAY_SLIP_DATE";
        $gpfCalcDetail = sqlFetchData($connection, $gpfCalcSQL);
        $progressive = 0;

        /******************************** CALCULATION WITHIN A FINANCIAL YEAR *********************************/
        foreach ($gpfCalcDetail as $gpfCalcDetails) {
            $month = date("d-M-Y", strtotime($gpfCalcDetails['PAY_SLIP_DATE']));
            $accountingMonth = $gpfCalcDetails['ACCOUNTING_MONTH'];
            $openingBalance = $gpfCalcDetails['OPENING_BALANCE'];
            $deposit = $gpfCalcDetails['DEPOSIT'];
            $withdrawal = $gpfCalcDetails['WITHDRAWAL'];
            $rateOfInterest = $gpfCalcDetails['RATE_OF_INTEREST'];
            $intOnDeposit = $gpfCalcDetails['INTEREST_ON_DEPOSIT'];
            $cutMonthTogg = $gpfCalcDetails['CUT_MONTH'];
            $adjustment = $gpfCalcDetails['ADJUSTMENT'];

            if ($intOnDeposit == "N") {
                $deposit = 0;
            }
            if ($cutMonthTogg == "Y") {
                $deposit = 0;
                $withdrawal = 0;
                $rateOfInterest = 0;
                $progressive = 0;
            }
            if ($adjustment == "Y") {
                $deposit = 0;
                $withdrawal = 0;
                $rateOfInterest = 0;
                $progressive = 0;
            }

            $progressive += $openingBalance + $deposit - $withdrawal;
            $interest =  round(($progressive * $rateOfInterest) / 1200, 2);
            $eachMonthIntUpdate = "UPDATE GPF_ACCOUNT_CALCULATION SET PROGRESSIVE='$progressive', ACTUAL_INTEREST='$interest'
                       WHERE REGD_NO='$registrationNo' AND  PAY_SLIP_DATE='$month' AND FIN_YEAR_CODE='$finYears[$i]'";
            sqlCUDData($connection, $eachMonthIntUpdate);

            $dupSQL = "SELECT PAY_SLIP_DATE, COUNT(*) FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
            AND FIN_YEAR_CODE='$finYears[$i]' GROUP BY PAY_SLIP_DATE HAVING COUNT(*)>1";
            $dupExists = sqlCountData($connection, $dupSQL);
            if ($dupExists > 0) {
                $fetchDupMnth = sqlFetchData($connection, $dupSQL);
                foreach ($fetchDupMnth as $fetchDupMnths) {
                    $paySlip = date("d-M-Y", strtotime($fetchDupMnths['PAY_SLIP_DATE']));
                    $updateDupSQL = "UPDATE GPF_ACCOUNT_CALCULATION SET PROGRESSIVE=0, ACTUAL_INTEREST=0
                                     WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE!=INTEREST_DATE AND 
                                     PAY_SLIP_DATE='$paySlip'";
                    sqlCUDData($connection,  $updateDupSQL);
                }
            }
        }
        /******************************** CALCULATION WITHIN A FINANCIAL YEAR *********************************/

        $depositQuery = "SELECT SUM(NVL(DEPOSIT,0)) AS DEPOSIT FROM GPF_ACCOUNT_CALCULATION
                         WHERE REGD_NO='$registrationNo' AND INTEREST_ON_DEPOSIT='Y' AND 
                         DELAY_INTEREST IS NULL AND FIN_YEAR_CODE='$finYears[$i]' AND PAY_SLIP_DATE<='$cutMonth'";
        $cdepositQuery = sqlFetchData($connection, $depositQuery);
        foreach ($cdepositQuery as $cdepositQuerys) {
            $ydeposit = $cdepositQuerys['DEPOSIT'];
        }


        $adjExistsSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                         AND ADJUSTMENT='Y' AND FIN_YEAR_CODE='$finYears[$i]'";
        $countAdj = sqlCountData($connection, $adjExistsSQL);
        if ($countAdj > 0) {
            adjustmentCalculation($connection, $registrationNo, $finYears[$i]);
            $adjQuery = "SELECT * FROM GPF_ADJ_PRE_CAL WHERE REGD_NO='$registrationNo' AND 
                                FIN_YEAR_CODE='$finYears[$i]'";
            $fetchAdj = sqlFetchData($connection, $adjQuery);
            foreach ($fetchAdj as $fetchAdjs) {
                $adjustmentDeposit = $fetchAdjs['TOTAL_DEPOSIT'];
                $adjustmentWithdrawal = $fetchAdjs['TOTAL_WITHDRAWAL'];
                $adjustmentInterest = $fetchAdjs['ADJ_INTEREST'];
            }
        } else {
            $adjustmentDeposit = 0;
            $adjustmentInterest = 0;
        }


        $aintQuery = "SELECT SUM(NVL(ACTUAL_INTEREST,0)) AS INTEREST FROM GPF_ACCOUNT_CALCULATION
                      WHERE REGD_NO='$registrationNo'AND FIN_YEAR_CODE='$finYears[$i]' AND PAY_SLIP_DATE<='$cutMonth'";
        $cintQuery = sqlFetchData($connection, $aintQuery);
        foreach ($cintQuery as $cintQuerys) {
            $yactualInt = round($cintQuerys['INTEREST']);
        }
        $damountQuery = "SELECT SUM(NVL(WITHDRAWAL,0)) AS WITHDRAWAL FROM GPF_ACCOUNT_CALCULATION 
                         WHERE REGD_NO='$registrationNo' AND FIN_YEAR_CODE='$finYears[$i]' AND PAY_SLIP_DATE<='$cutMonth'";
        $damountQuery = sqlFetchData($connection, $damountQuery);
        foreach ($damountQuery as $damountQuerys) {
            $ywithdrawal = $damountQuerys['WITHDRAWAL'];
        }
        $openingBalAmount = $openingBalAmount + $ydeposit + $adjustmentDeposit - $ywithdrawal + $yactualInt + $adjustmentInterest;
    }
    /********************************************** BEFORE DELAY INTEREST ************************************ */
    /**********************************************  DELAY INTEREST ************************************ */

    $nextMonth = date('d-M-Y', strtotime('+1 month', strtotime(date("Y-m-d", strtotime($cutMonth)))));
    if ($countAfterCutMonth > 0) {
        $updtDOBSQL = "UPDATE GPF_ACCOUNT_CALCULATION SET OPENING_BALANCE='$openingBalAmount' WHERE REGD_NO='$registrationNo' 
                      AND PAY_SLIP_DATE='$nextMonth'";
        sqlCUDData($connection, $updtDOBSQL);

        $dgpfCalcSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE>'$cutMonth' ORDER BY PAY_SLIP_DATE";
        $dgpfCalcDetail = sqlFetchData($connection, $dgpfCalcSQL);
        $progressive = 0;

        /******************************** CALCULATION FOR DELAY INTEREST *********************************/
        foreach ($dgpfCalcDetail as $dgpfCalcDetails) {
            $month = $dgpfCalcDetails['PAY_SLIP_DATE'];
            $accountingMonth = $dgpfCalcDetails['ACCOUNTING_MONTH'];
            $openingBalance = $dgpfCalcDetails['OPENING_BALANCE'];
            $deposit = $dgpfCalcDetails['DEPOSIT'];
            $withdrawal = $dgpfCalcDetails['WITHDRAWAL'];
            $rateOfInterest = $dgpfCalcDetails['RATE_OF_INTEREST'];
            $intOnDeposit = $dgpfCalcDetails['INTEREST_ON_DEPOSIT'];

            if ($intOnDeposit == "N") {
                $deposit = 0;
            }
            $progressive += $openingBalance + $deposit - $withdrawal;
            $interest =  round(($progressive * $rateOfInterest) / 1200, 2);
            $eachMonthIntUpdate = "UPDATE GPF_ACCOUNT_CALCULATION SET PROGRESSIVE='$progressive', ACTUAL_INTEREST='', DELAY_INTEREST='$interest' 
                                   WHERE REGD_NO='$registrationNo' AND  ACCOUNTING_MONTH='$accountingMonth' AND PAY_SLIP_DATE>'$cutMonth'";
            sqlCUDData($connection, $eachMonthIntUpdate);
        }
        /******************************** CALCULATION FOR DELAY INTEREST *********************************/
    }
    /**********************************************  DELAY INTEREST ************************************ */

    /********************************************** FINAL PAYMENT CALCULATION ************************************ */
    $actualDepSQL = "SELECT SUM(NVL(DEPOSIT,0)) DEPOSIT FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                     AND FIN_YEAR_CODE>='$effectFinYear' AND INTEREST_ON_DEPOSIT='Y' ";
    $fetchActualDeposit = sqlFetchData($connection, $actualDepSQL);
    foreach ($fetchActualDeposit as $fetchActualDeposits) {
        $actualDeposit = round($fetchActualDeposits['DEPOSIT']);
    }
    $excessDepSQL = "SELECT SUM(NVL(DEPOSIT,0)) DEPOSIT FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                     AND FIN_YEAR_CODE>='$effectFinYear' AND INTEREST_ON_DEPOSIT='N' ";
    $fetchExcessDeposit = sqlFetchData($connection, $excessDepSQL);
    foreach ($fetchExcessDeposit as $fetchExcessDeposits) {
        $excessDeposit = round($fetchExcessDeposits['DEPOSIT']);
    }

    $actualIntSQL = "SELECT SUM(NVL(ACTUAL_INTEREST,0)) ACTUAL_INTEREST FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                     AND FIN_YEAR_CODE>='$effectFinYear' AND ACTUAL_INTEREST > 0 ";
    $fetchActualInterest = sqlFetchData($connection, $actualIntSQL);
    foreach ($fetchActualInterest as $fetchActualInterests) {
        $actualInterest = round($fetchActualInterests['ACTUAL_INTEREST']);
    }


    $delIntQuery = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND DELAY_INTEREST IS NOT NULL";
    $delIntExists = sqlCountData($connection, $delIntQuery);
    if ($delIntExists == 0) {
        $obSQL = "SELECT SUM(NVL(OPENING_BALANCE,0)) OPENING_BALANCE FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                  AND FIN_YEAR_CODE='$effectFinYear' AND ACCOUNTING_MONTH=1";
        $fetchOB = sqlFetchData($connection, $obSQL);
        foreach ($fetchOB as $fetchOBs) {
            $openingBal = $fetchOBs['OPENING_BALANCE'];
        }
        $delayInterest = 0;

        $debitSQL = "SELECT SUM(NVL(WITHDRAWAL,0)) WITHDRAWAL FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                     AND FIN_YEAR_CODE>='$effectFinYear'";
        $fetchDebit = sqlFetchData($connection, $debitSQL);
        foreach ($fetchDebit as $fetchDebits) {
            $debit = $fetchDebits['WITHDRAWAL'];
        }
    } else {
        $obSQL = "SELECT SUM(NVL(OPENING_BALANCE,0)) OPENING_BALANCE FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                  AND NVL(OPENING_BALANCE,0)!=0 AND DELAY_INTEREST IS NOT NULL";
        $fetchOB = sqlFetchData($connection, $obSQL);
        foreach ($fetchOB as $fetchOBs) {
            $openingBal = $fetchOBs['OPENING_BALANCE'];
        }

        $delayIntSQL = "SELECT SUM(NVL(DELAY_INTEREST,0)) DELAY_INTEREST FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                     AND FIN_YEAR_CODE>='$effectFinYear' AND DELAY_INTEREST IS NOT NULL ";
        $fetchDelayInterest = sqlFetchData($connection, $delayIntSQL);
        foreach ($fetchDelayInterest as $fetchDelayInterests) {
            $delayInterest = round($fetchDelayInterests['DELAY_INTEREST']);
        }
        $actualDeposit = 0;
        $actualInterest = 0;

        $debitSQL = "SELECT SUM(NVL(WITHDRAWAL,0)) WITHDRAWAL FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                     AND FIN_YEAR_CODE>='$effectFinYear' AND DELAY_INTEREST IS NOT NULL";
        $fetchDebit = sqlFetchData($connection, $debitSQL);
        foreach ($fetchDebit as $fetchDebits) {
            $debit = $fetchDebits['WITHDRAWAL'];
        }
    }



    $finalPayment = $openingBal + $actualDeposit + $excessDeposit - $debit + $actualInterest + $delayInterest;



    /********************************************** FINAL PAYMENT CALCULATION ************************************ */
    /*************************************** DLIS CALCULATION ******************************************* */
    $dlisAmount = 0;
    if ($pension == 2 || $pension == 7) {
        if ($dlisAdmissible == "Y") {
            $myFile = fopen("", "w");
            fwrite($myFile, $dlisAdmissible);
            fclose($myFile);
            $dateOfEffect = date('01-M-Y', strtotime($dateOfEffect));
            $pdateOfEffect = date('01-M-Y', strtotime("-36 month", strtotime($dateOfEffect)));

            $eDLISSQL = "SELECT SUM(PROGRESSIVE) AS PROGRESSIVE, SUM(ACTUAL_INTEREST) AS ACTUAL_INTEREST FROM 
                          GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE<'$dateOfEffect'";

            $pDLISSQL = "SELECT SUM(PROGRESSIVE) AS PROGRESSIVE FROM GPF_ACCOUNT_CALCULATION
                           WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE<'$pdateOfEffect'";
            $fetcheDLIS = sqlFetchData($connection, $eDLISSQL);
            foreach ($fetcheDLIS as $fetcheDLISs) {
                $totalProgressive = $fetcheDLISs['PROGRESSIVE'];
                $totalInterest = round($fetcheDLISs['ACTUAL_INTEREST']);
            }
            $fetchpDLIS = sqlFetchData($connection, $pDLISSQL);
            foreach ($fetchpDLIS as $fetchpDLISs) {
                $totalRestProgressive = $fetchpDLISs['PROGRESSIVE'];
            }

            $totProgAmount = $totalProgressive + $totalInterest;

            $averageProgressive = round(($totProgAmount - $totalRestProgressive) / 36);

            if ($averageProgressive >= 10000) {
                $dlisAmount = 10000;
            } else {
                $dlisAmount = $averageProgressive;
            }
        }
    }

    /*************************************** DLIS CALCULATION ******************************************* */
    /*************************************** CALCULATION ENDS ******************************************* */


    $caseStatusQuery = "UPDATE GPF_CASE_STATUS SET CASE_STATUS=4, CALCULATION_DATE=SYSDATE WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
    $caseInfoQuery = "UPDATE GPF_AMOUNT_INFO SET CALCULATION_DONE_BY='$loginUser', OPENING_FIN_YEAR='$effectFinYear', 
                      OPENING_BAL_AMOUNT='$openingBal', ACTUAL_DEPOSIT='$actualDeposit', EXCESS_DEPOSIT='$excessDeposit',
                       WITHDRAWAL='$debit', ACTUAL_INTEREST='$actualInterest', DELAYED_INTEREST='$delayInterest',
                       FINAL_PAYMENT_AMOUNT='$finalPayment', DLIS_AMOUNT='$dlisAmount' WHERE REGD_NO='$registrationNo'";
    $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");
    $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo',4, SYSDATE, '$loginUser')";

    $caseStatusBool = sqlCUDData($connection, $caseStatusQuery);
    $caseInfoBool = sqlCUDData($connection, $caseInfoQuery);
    $caseLogBool = sqlCUDData($connection, $caseLogQuery);

    if ($caseStatusBool && $caseInfoBool && $caseLogBool) {
        $resultArray = array("StatusCode" => 200, "Message" => "Successfully calculated");
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "Try again");
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
