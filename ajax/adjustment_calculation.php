<?php

function adjustmentCalculation($connection, $registrationNo, $finyear)
{
    $deleteAdjCalculationQuery = "DELETE FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'";
    sqlCUDData($connection, $deleteAdjCalculationQuery);

    $adjSQL = "SELECT b.FIN_YEAR_CODE, MIN(a.PAY_SLIP_DATE) PAY_SLIP_DATE FROM GPF_ACCOUNT_CALCULATION a INNER JOIN
               MAS_INTEREST b ON a.PAY_SLIP_DATE=b.YEAR_DESC WHERE a.REGD_NO='$registrationNo'
               AND a.FIN_YEAR_CODE='$finyear' AND a.ADJUSTMENT='Y' GROUP BY b.FIN_YEAR_CODE";
    $fetchAdjMonth = sqlFetchData($connection, $adjSQL);
    foreach ($fetchAdjMonth as $fetchAdjMonths) {
        $minFinYear = $fetchAdjMonths['FIN_YEAR_CODE'];
    }

    $cutMonthSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND CUT_MONTH='Y'";
    $fetchCutMonth = sqlFetchData($connection, $cutMonthSQL);
    foreach ($fetchCutMonth as $fetchCutMonths) {
        $cutMonth = $fetchCutMonths['PAY_SLIP_DATE'];
    }

    $selectionQuery = "SELECT S.REGD_NO, M.FIN_YEAR_CODE, M.ACCOUNTING_MONTH, S.PAY_SLIP_DATE,
S.INTEREST_DATE, S.SUBSCRIPTION_AMT, S.REFUND_AMT, S.OTHERS_AMT,S.WITHDRAWAL_AMT,
S.ADVANCE_AMT, S.RATE_OF_INTEREST, S.CUT_MONTH, S.INTEREST_ON_DEPOSIT, 0, 0 FROM
(SELECT T.REGD_NO, T.PAY_SLIP_DATE, T.INTEREST_DATE, SUM(NVL(T.OPENING_BALANCE,0))OPENING_BALANCE, SUM(NVL(T.SUBSCRIPTION_AMT,0))SUBSCRIPTION_AMT, 
SUM(NVL(T.REFUND_AMT,0)) REFUND_AMT, SUM(NVL(T.OTHERS_AMT,0)) OTHERS_AMT, SUM(NVL(T.WITHDRAWAL_AMT,0))WITHDRAWAL_AMT, SUM(NVL(T.ADVANCE_AMT,0))ADVANCE_AMT,
T.RATE_OF_INTEREST, T.CUT_MONTH, T.INTEREST_ON_DEPOSIT FROM 
(SELECT REGD_NO, FIN_YEAR_CODE, PAY_SLIP_DATE, INTEREST_DATE, OPENING_BALANCE, SUBSCRIPTION_AMT, REFUND_AMT, OTHERS_AMT, WITHDRAWAL_AMT, ADVANCE_AMT,
RATE_OF_INTEREST, CUT_MONTH, INTEREST_ON_DEPOSIT
FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND adjustment='Y' AND FIN_YEAR_CODE='$finyear' AND PAY_SLIP_DATE<='$cutMonth' 
UNION ALL SELECT $registrationNo REGD_NO, FIN_YEAR_CODE, YEAR_DESC PAY_SLIP_DATE, YEAR_DESC INTEREST_DATE, 0 OPENING_BALANCE, 0 subscription_amt, 0 refund_amt, 
0 others_amt, 0 withdrawal_amt, 0 advance_amt, rate_of_interest, 'N' cut_month, 'Y' interest_on_deposit FROM MAS_INTEREST WHERE FIN_YEAR_CODE BETWEEN 
'$minFinYear' AND '$finyear' AND YEAR_DESC<='$cutMonth')T group by T.REGD_NO, T.PAY_SLIP_DATE, T.INTEREST_DATE, T.RATE_OF_INTEREST, T.CUT_MONTH, T.INTEREST_ON_DEPOSIT ORDER BY T.PAY_SLIP_DATE)S
INNER JOIN MAS_INTEREST M ON S.PAY_SLIP_DATE=M.YEAR_DESC ";


    $adjInsertSQL = "INSERT INTO GPF_ADJ_ACC_CALC(REGD_NO, FIN_YEAR_CODE, ACCOUNTING_MONTH, PAY_SLIP_DATE, INTEREST_DATE,
                     SUBSCRIPTION_AMT, REFUND_AMT, OTHERS_AMT,WITHDRAWAL_AMT,ADVANCE_AMT, RATE_OF_INTEREST, 
                     CUT_MONTH, INTEREST_ON_DEPOSIT, DEPOSIT, WITHDRAWAL) $selectionQuery";

    sqlCUDData($connection, $adjInsertSQL);

    $amtSumSQL = "SELECT FIN_YEAR_CODE, INTEREST_DATE, SUM(NVL(SUBSCRIPTION_AMT,0)+NVL(REFUND_AMT,0)+NVL(OTHERS_AMT,0)) DEPOSIT,
                  SUM(NVL(WITHDRAWAL_AMT,0)+NVL(ADVANCE_AMT,0)) WITHDRAWAL FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo' GROUP BY
                  FIN_YEAR_CODE, INTEREST_DATE ORDER BY INTEREST_DATE";
    $fetchSumAmt = sqlFetchData($connection, $amtSumSQL);
    foreach ($fetchSumAmt as $amount) {
        $deposit = $amount['DEPOSIT'];
        $withdrawl = $amount['WITHDRAWAL'];
        $psDate = date("d-M-Y", strtotime($amount['INTEREST_DATE']));
        $updateSQL = "UPDATE GPF_ADJ_ACC_CALC SET DEPOSIT='$deposit', WITHDRAWAL='$withdrawl'
                      WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE='$psDate'";
        sqlCUDData($connection,  $updateSQL);
    }

    $dupSQL = "SELECT PAY_SLIP_DATE, DEPOSIT, WITHDRAWAL,  COUNT(*) FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo' GROUP BY PAY_SLIP_DATE, DEPOSIT, WITHDRAWAL
               HAVING COUNT(*)>1";
    $dupExists = sqlCountData($connection, $dupSQL);
    if ($dupExists > 0) {
        $fetchDupMnth = sqlFetchData($connection, $dupSQL);
        foreach ($fetchDupMnth as $fetchDupMnths) {
            $paySlip = date("d-M-Y", strtotime($fetchDupMnths['PAY_SLIP_DATE']));
            $updateDupSQL = "UPDATE GPF_ADJ_ACC_CALC SET DEPOSIT=0, WITHDRAWAL=0 WHERE REGD_NO='$registrationNo'
                  AND PAY_SLIP_DATE!=INTEREST_DATE AND PAY_SLIP_DATE='$paySlip'";
            sqlCUDData($connection,  $updateDupSQL);
        }
    }

    $openingBalAmount = 0;



    $cutMonthSQL = "SELECT * FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo' AND CUT_MONTH='Y'";
    $cdetail = sqlFetchData($connection, $cutMonthSQL);
    foreach ($cdetail as $cdetails) {
        $cutMonth = date("d-M-Y", strtotime($cdetails['PAY_SLIP_DATE']));
    }
    $distinctFinYearSQL = "SELECT DISTINCT(FIN_YEAR_CODE) FIN_YEAR_CODE FROM GPF_ADJ_ACC_CALC 
                           WHERE REGD_NO='$registrationNo' ORDER BY FIN_YEAR_CODE";
    $fdetail = sqlFetchData($connection, $distinctFinYearSQL);
    foreach ($fdetail as $fdetails) {
        $finYears[] = $fdetails['FIN_YEAR_CODE'];
    }
    for ($i = 0; $i < sizeof($finYears); $i++) {
        $updtOBSQL = "UPDATE GPF_ADJ_ACC_CALC SET OPENING_BALANCE='$openingBalAmount' WHERE REGD_NO='$registrationNo' 
                    AND ACCOUNTING_MONTH=1 AND FIN_YEAR_CODE='$finYears[$i]'";
        sqlCUDData($connection, $updtOBSQL);

        $gpfCalcSQL = "SELECT PAY_SLIP_DATE, ACCOUNTING_MONTH, NVL(OPENING_BALANCE, 0) OPENING_BALANCE,
                       NVL(DEPOSIT, 0) DEPOSIT, NVL(WITHDRAWAL, 0) WITHDRAWAL, NVL(RATE_OF_INTEREST, 0) RATE_OF_INTEREST, 
                       INTEREST_ON_DEPOSIT, CUT_MONTH FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'
                       AND FIN_YEAR_CODE='$finYears[$i]' GROUP BY PAY_SLIP_DATE, ACCOUNTING_MONTH, OPENING_BALANCE, DEPOSIT, WITHDRAWAL, RATE_OF_INTEREST,
                       INTEREST_ON_DEPOSIT, CUT_MONTH ORDER BY PAY_SLIP_DATE";



        $gpfCalcDetail = sqlFetchData($connection, $gpfCalcSQL);
        $progressive = 0;

        foreach ($gpfCalcDetail as $gpfCalcDetails) {
            $month = date("d-M-Y", strtotime($gpfCalcDetails['PAY_SLIP_DATE']));
            $openingBalance = $gpfCalcDetails['OPENING_BALANCE'];
            $deposit = $gpfCalcDetails['DEPOSIT'];
            $withdrawal = $gpfCalcDetails['WITHDRAWAL'];
            $rateOfInterest = $gpfCalcDetails['RATE_OF_INTEREST'];
            $intOnDeposit = $gpfCalcDetails['INTEREST_ON_DEPOSIT'];
            $cutMonthTogg = $gpfCalcDetails['CUT_MONTH'];

            if ($intOnDeposit == "N") {
                $deposit = 0;
            }
            if ($cutMonthTogg == "Y") {
                $deposit = 0;
                $withdrawal = 0;
                $rateOfInterest = 0;
                $progressive = 0;
            }
            $progressive += $openingBalance + $deposit - $withdrawal;
            $interest =  round(($progressive * $rateOfInterest) / 1200, 2);
            $eachMonthIntUpdate = "UPDATE GPF_ADJ_ACC_CALC SET PROGRESSIVE='$progressive', INTEREST='$interest'
                       WHERE REGD_NO='$registrationNo' AND  PAY_SLIP_DATE='$month' AND FIN_YEAR_CODE='$finYears[$i]'";
            sqlCUDData($connection, $eachMonthIntUpdate);




            $dupSQL = "SELECT PAY_SLIP_DATE, COUNT(*) FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'
            AND FIN_YEAR_CODE='$finYears[$i]' GROUP BY PAY_SLIP_DATE HAVING COUNT(*)>1";
            $dupExists = sqlCountData($connection, $dupSQL);
            if ($dupExists > 0) {
                $fetchDupMnth = sqlFetchData($connection, $dupSQL);
                foreach ($fetchDupMnth as $fetchDupMnths) {
                    $paySlip = date("d-M-Y", strtotime($fetchDupMnths['PAY_SLIP_DATE']));
                    $updateDupSQL = "UPDATE GPF_ADJ_ACC_CALC SET PROGRESSIVE=0, ACTUAL_INTEREST=0
                                     WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE!=INTEREST_DATE AND 
                                     PAY_SLIP_DATE='$paySlip'";
                    sqlCUDData($connection,  $updateDupSQL);
                }
            }
        }

        $depositQuery = "SELECT SUM(NVL(DEPOSIT,0)) AS DEPOSIT FROM GPF_ADJ_ACC_CALC
                         WHERE REGD_NO='$registrationNo' AND INTEREST_ON_DEPOSIT='Y' AND 
                         FIN_YEAR_CODE='$finYears[$i]'";
        $cdepositQuery = sqlFetchData($connection, $depositQuery);
        foreach ($cdepositQuery as $cdepositQuerys) {
            $ydeposit = $cdepositQuerys['DEPOSIT'];
        }

        $aintQuery = "SELECT SUM(NVL(INTEREST,0)) AS INTEREST FROM GPF_ADJ_ACC_CALC
                      WHERE REGD_NO='$registrationNo'AND FIN_YEAR_CODE='$finYears[$i]'";
        $cintQuery = sqlFetchData($connection, $aintQuery);
        foreach ($cintQuery as $cintQuerys) {
            $yactualInt = round($cintQuerys['INTEREST']);
        }
        $damountQuery = "SELECT SUM(NVL(WITHDRAWAL,0)) AS WITHDRAWAL FROM GPF_ADJ_ACC_CALC 
                         WHERE REGD_NO='$registrationNo' AND FIN_YEAR_CODE='$finYears[$i]'";
        $damountQuery = sqlFetchData($connection, $damountQuery);
        foreach ($damountQuery as $damountQuerys) {
            $ywithdrawal = $damountQuerys['WITHDRAWAL'];
        }
        $openingBalAmount = $openingBalAmount + $ydeposit - $ywithdrawal + $yactualInt;
    }




    $actualDepSQL = "SELECT SUM(NVL(DEPOSIT,0)) DEPOSIT FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'
    AND FIN_YEAR_CODE>='$finyear'";
    $fetchActualDeposit = sqlFetchData($connection, $actualDepSQL);
    foreach ($fetchActualDeposit as $fetchActualDeposits) {
        $actualDeposit = round($fetchActualDeposits['DEPOSIT']);
    }

    $actualIntSQL = "SELECT SUM(NVL(INTEREST,0)) INTEREST FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'
    AND FIN_YEAR_CODE>='$finyear' AND INTEREST > 0 ";
    $fetchActualInterest = sqlFetchData($connection, $actualIntSQL);
    foreach ($fetchActualInterest as $fetchActualInterests) {
        $actualInterest = round($fetchActualInterests['INTEREST']);
    }

    $debitSQL = "SELECT SUM(NVL(WITHDRAWAL,0)) WITHDRAWAL FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'
AND FIN_YEAR_CODE>='$finyear'";
    $fetchDebit = sqlFetchData($connection, $debitSQL);
    foreach ($fetchDebit as $fetchDebits) {
        $debit = $fetchDebits['WITHDRAWAL'];
    }
    $obSQL = "SELECT SUM(NVL(OPENING_BALANCE,0)) OPENING_BALANCE FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'
AND FIN_YEAR_CODE='$finyear' AND ACCOUNTING_MONTH=1";
    $fetchOB = sqlFetchData($connection, $obSQL);
    foreach ($fetchOB as $fetchOBs) {
        $openingBal = $fetchOBs['OPENING_BALANCE'];
    }

    $adjustmentAmount = $openingBal + $actualDeposit - $debit + $actualInterest;

    $totalDepositSQL = "SELECT SUM(NVL(DEPOSIT,0)) DEPOSIT FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'";
    $fetchTotalDeposit = sqlFetchData($connection, $totalDepositSQL);
    foreach ($fetchTotalDeposit as $fetchTotalDeposits) {
        $totalDeposit = round($fetchTotalDeposits['DEPOSIT']);
    }

    $adjInterest = $adjustmentAmount - $totalDeposit;

    $adjPreCalUpdate = "INSERT INTO GPF_ADJ_PRE_CAL VALUES('$registrationNo', '$finyear', '$totalDeposit',0,
                       '$adjustmentAmount', '$adjInterest', '" . $_SESSION['GPF_USERNAME'] . "', SYSDATE)";
    sqlCUDData($connection, $adjPreCalUpdate);


    $rdeleteAdjCalculationQuery = "DELETE FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'";
    sqlCUDData($connection, $rdeleteAdjCalculationQuery);
}
