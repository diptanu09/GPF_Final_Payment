<?php
$pageName = "Calculation sheet";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = $_REQUEST['regd_no'];
    $infoQuery = "SELECT a.SUBSCRIBER_NAME, a.SERIES_ID, a.ACCOUNT_NO, a.BENEFICIARY_CODE, b.PENSION_ID, a.FIN_YEAR_CODE, b.PENSION_LONG_DESCR, d.FINAL_PAYMENT_AMOUNT,
                  c.DATE_OF_EFFECT, a.CASE_STATUS, d.DLIS_ADMISSIBLE, d.DLIS_AMOUNT FROM GPF_CASE_STATUS a INNER JOIN MAS_PENSION_TYPE b ON 
                  a.PENSION_TYPE=b.PENSION_ID INNER JOIN GPF_APPLICATION c ON a.REGD_NO=c.REGD_NO INNER JOIN 
                  GPF_AMOUNT_INFO d ON a.REGD_NO=d.REGD_NO WHERE a.REGD_NO='$registrationNo'";
    $fetchInfo = sqlFetchData($connection, $infoQuery);
    foreach ($fetchInfo as $info) {
        $seriesID = $info['SERIES_ID'];
        $accountNo = $info['ACCOUNT_NO'];
        $retdFinYear = $info['FIN_YEAR_CODE'];
        $beneficiaryCode = $info['BENEFICIARY_CODE'];
        $subscriberName = $info['SUBSCRIBER_NAME'];
        $pension = $info['PENSION_ID'];
        $pensionType = $info['PENSION_LONG_DESCR'];
        $dateOfEffect = date("d/m/Y", strtotime($info['DATE_OF_EFFECT']));
        $dlisDateOfEffect = date("d-M-Y", strtotime($info['DATE_OF_EFFECT']));
        $caseStatus = $info['CASE_STATUS'];
        $finalPayment = $info['FINAL_PAYMENT_AMOUNT'];
        $dlisAmount = $info['DLIS_AMOUNT'];
        $dlisAdmissible = $info['DLIS_ADMISSIBLE'];
    }
?>

    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <?php
                require_once "./includes/breadcumb.php";
                ?>
                <section id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <center>
                                        <b>Registration number</b> : <?php echo $registrationNo; ?>,
                                        <b>Subscriber name</b> : <?php echo $subscriberName; ?>,
                                        <b>Date of <?php echo $pensionType; ?></b> : <?php echo $dateOfEffect; ?>
                                    </center><br>
                                    <?php
                                    $errors = 0;
                                    $remarkFetchSQL = "SELECT * FROM GPF_CALCULATION_REMARKS WHERE REGD_NO='$registrationNo'";
                                    if (isset($_POST['save_remarks'])) {
                                        $remarksID = date("YmdHis");
                                        $remarks = trim($_POST['remarks']);
                                        if (empty($remarks)) {
                                            $remarksErr = "Required";
                                        } else {
                                            if (!textPatternValidation($remarks, "^'")) {
                                                $remarksErr = "Apostophe are not allowed";
                                            } else {
                                                if (strlen($remarks) > 255) {
                                                    $remarksErr = "Maximum length : 255";
                                                }
                                            }
                                        }
                                        if ($remarksErr == "") {
                                            $errors = 0;
                                            $countRemark = sqlCountData($connection, $remarkFetchSQL);
                                            if ($countRemark > 0) {
                                                $remarkCUDSQL = "UPDATE GPF_CALCULATION_REMARKS SET REMARKS='$remarks', CREATE_MODIFY_USER='$loginUser', CREATE_MODIFY_DATE=SYSDATE
                                                         WHERE REGD_NO='$registrationNo'";
                                            } else {
                                                $remarkCUDSQL = "INSERT INTO GPF_CALCULATION_REMARKS VALUES('$remarksID', '$registrationNo','$remarks','$loginUser',SYSDATE)";
                                            }
                                            if (sqlCUDData($connection, $remarkCUDSQL)) {
                                                $remarksSuccess = "Successfully updated remarks";
                                            } else {
                                                $remarksErr = "Data not saved. Try again later";
                                            }
                                        } else {
                                            $errors = 1;
                                        }
                                    }
                                    if (isset($_POST['delete_remarks'])) {
                                        $remarkSQL = "DELETE FROM GPF_CALCULATION_REMARKS WHERE REGD_NO='$registrationNo'";
                                        sqlCUDData($connection, $remarkSQL);
                                    }
                                    $remarkList = sqlFetchData($connection, $remarkFetchSQL);
                                    foreach ($remarkList as $remarkLists) {
                                        $remarkDesc = $remarkLists['REMARKS'];
                                    }
                                    ?>
                                    <br><br>
                                    <form action="" method="post">
                                        <div class="form-inline">
                                            <label for="remarks"><b>Remarks :
                                                    <span class="text-danger">* <?php echo $remarksErr; ?></span>
                                                    <span class="text-success"><?php echo $remarksSuccess; ?></span>
                                                </b>
                                            </label>
                                            <input type="text" name="remarks" maxlength="255" class="form-control" style="width: 800px;" value="<?php
                                                                                                                                                if ($errors == 1) {
                                                                                                                                                    echo $remarks;
                                                                                                                                                } else {
                                                                                                                                                    echo $remarkDesc;
                                                                                                                                                }
                                                                                                                                                ?>" required />
                                            &nbsp;&nbsp;

                                            <?php
                                            if ($caseStatus < 5) {
                                            ?>
                                                <input type="submit" name="save_remarks" class="btn btn-primary" value="SAVE" />&nbsp;&nbsp;
                                                <input type="submit" name="delete_remarks" class="btn btn-danger" value="DELETE" />

                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </form>
                                    <!---------------------------- CALCULATION SHEET STARTS --------------------------->
                                    <div id="calculation_sheet" style="margin-top: 50px;">
                                        <?php
                                        /*                                         * ******************************* FOR FETCHING CUT-MONTH  ******************************** */
                                        $cutMonthSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND CUT_MONTH='Y'";
                                        $cdetail = sqlFetchData($connection, $cutMonthSQL);
                                        foreach ($cdetail as $cdetails) {
                                            $cutMonthDate = date("d-M-Y", strtotime($cdetails['PAY_SLIP_DATE']));
                                        }
                                        /*                                         * ******************************* FOR FETCHING CUT-MONTH  ******************************** */

                                        /*                                         * *************** FOR FETCHING FINYEAR FROM ACCOUNT CALCULATION  **************** */
                                        $distinctFinYearSQL = "SELECT DISTINCT(FIN_YEAR_CODE) FIN_YEAR_CODE FROM GPF_ACCOUNT_CALCULATION 
                                                               WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE<='$cutMonthDate' ORDER BY FIN_YEAR_CODE";
                                        $fdetail = sqlFetchData($connection, $distinctFinYearSQL);
                                        foreach ($fdetail as $fdetails) {
                                            $finYears[] = $fdetails['FIN_YEAR_CODE'];
                                        }
                                        /*                                         * *************** FOR FETCHING FINYEAR FROM ACCOUNT CALCULATION  **************** */


                                        /*                                         * ******************************* CHECKING MONTHS AFTER CUT-MONTH  ******************************** */
                                        $monthAfterCutMonthSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND PAY_SLIP_DATE>'$cutMonth'";
                                        $countAfterCutMonth = sqlCountData($connection, $monthAfterCutMonthSQL);
                                        /*                                         * ******************************* CHECKING MONTHS AFTER CUT-MONTH  ******************************** */


                                        for ($i = 0; $i < sizeof($finYears); $i++) {
                                            $finYearSQL = "SELECT * FROM VLCS.MM_FINANCIAL_YEAR WHERE FIN_YEAR_CODE='$finYears[$i]'";
                                            $ffy = sqlFetchData($connection, $finYearSQL);
                                            foreach ($ffy as $ffys) {
                                                $financialYear = $ffys['FIN_YEAR'];
                                            }
                                        ?>
                                            <center>
                                                <div style="font-weight: bolder;" class="alert alert-info">
                                                    CALCULATION FOR THE FINANCIAL YEAR : <?php echo $financialYear; ?></div>
                                            </center>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <table class="table table-bordered table-hover table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>PAY SLIP DATE</th>
                                                                <th>INTEREST DATE</th>
                                                                <th>OPENING BALANCE</th>
                                                                <th>SUBSCRIPTION</th>
                                                                <th>REFUND</th>
                                                                <th>OTHERS</th>
                                                                <th>WITHDRAWAL</th>
                                                                <th>ADVANCE</th>
                                                                <th>RATE OF INTEREST</th>
                                                                <th>PROGRESSIVE</th>
                                                                <th>INTEREST</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $openingBalance = 0;
                                                            $subscription = 0;
                                                            $refund = 0;
                                                            $others = 0;
                                                            $withdrawal = 0;
                                                            $advance = 0;
                                                            $interest = 0;
                                                            $calQuery = "SELECT PAY_SLIP_DATE, INTEREST_DATE, OPENING_BALANCE, NVL(SUBSCRIPTION_AMT,0) SUBSCRIPTION_AMT, 
                                                            NVL(REFUND_AMT,0) REFUND_AMT, NVL(OTHERS_AMT,0) OTHERS_AMT,NVL(WITHDRAWAL_AMT,0) WITHDRAWAL_AMT,
                                                            NVL(ADVANCE_AMT,0) ADVANCE_AMT, RATE_OF_INTEREST, NVL(PROGRESSIVE,0) PROGRESSIVE, CUT_MONTH, INTEREST_ON_DEPOSIT,
                                                         NVL(ACTUAL_INTEREST,0) ACTUAL_INTEREST, NVL(DELAY_INTEREST,0) DELAY_INTEREST, ADJUSTMENT FROM GPF_ACCOUNT_CALCULATION 
                                                         WHERE REGD_NO='$registrationNo' AND DELAY_INTEREST IS NULL AND FIN_YEAR_CODE='$finYears[$i]' ORDER BY ADJUSTMENT, PAY_SLIP_DATE, INTEREST_DATE";
                                                            $fetchCalculation = sqlFetchData($connection, $calQuery);
                                                            foreach ($fetchCalculation as $calculationList) {
                                                                $openingBalance += $calculationList['OPENING_BALANCE'];
                                                                $subscription += $calculationList['SUBSCRIPTION_AMT'];
                                                                $refund += $calculationList['REFUND_AMT'];
                                                                $others += $calculationList['OTHERS_AMT'];
                                                                $withdrawal += $calculationList['WITHDRAWAL_AMT'];
                                                                $advance += $calculationList['ADVANCE_AMT'];

                                                                $span++;
                                                                $cutMonth = $calculationList['CUT_MONTH'];
                                                                $adjTogg = $calculationList['ADJUSTMENT'];
                                                                $intOnDep = $calculationList['INTEREST_ON_DEPOSIT'];
                                                                if ($cutMonth == "Y") {
                                                                    $styleName = "background-color:#FFA4A4;";
                                                                } else {
                                                                    if ($intOnDep == "N") {
                                                                        $styleName = "background-color:#8EFEFD;";
                                                                    } else {
                                                                        $styleName = "";
                                                                    }
                                                                }
                                                                if ($adjTogg == "Y") {
                                                                    $asterisk = " <span style='color:#FF0000; font-weight:bolder;'>*</span> ";
                                                                } else {
                                                                    $asterisk = "  ";
                                                                }
                                                                $actualInterest = $calculationList['ACTUAL_INTEREST'];
                                                                $delayInterest = $calculationList['DELAY_INTEREST'];
                                                                if ($actualInterest > 0) {
                                                                    $interest += $actualInterest;
                                                                } else {
                                                                    $interest += $delayInterest;
                                                                }
                                                            ?>
                                                                <tr style="<?php echo $styleName; ?>">
                                                                    <td style="text-align: left;"><?php echo $asterisk . date("F, Y", strtotime($calculationList['PAY_SLIP_DATE'])); ?></td>
                                                                    <td style="text-align: left;"><?php echo $asterisk . date("F, Y", strtotime($calculationList['INTEREST_DATE'])); ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['OPENING_BALANCE']; ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['SUBSCRIPTION_AMT']; ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['REFUND_AMT']; ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['OTHERS_AMT']; ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['WITHDRAWAL_AMT']; ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['ADVANCE_AMT']; ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['RATE_OF_INTEREST']; ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['PROGRESSIVE']; ?></td>
                                                                    <td style="text-align: right;"><?php echo $calculationList['ACTUAL_INTEREST']; ?></td>

                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                            <tr>
                                                                <th colspan="2" style="text-align: left; font-weight:bolder;">TOTAL</th>
                                                                <th style="text-align: right; font-weight:bolder;"><?php echo $openingBalance; ?></th>
                                                                <th style="text-align: right; font-weight:bolder;"><?php echo $subscription; ?></th>
                                                                <th style="text-align: right; font-weight:bolder;"><?php echo $refund; ?></th>
                                                                <th style="text-align: right; font-weight:bolder;"><?php echo $others; ?></th>
                                                                <th style="text-align: right; font-weight:bolder;"><?php echo $withdrawal; ?></th>
                                                                <th style="text-align: right; font-weight:bolder;"><?php echo $advance; ?></th>
                                                                <td colspan="2"></td>
                                                                <th style="text-align: right; font-weight:bolder;"><?php echo $interest; ?></th>
                                                            </tr>
                                                        </tbody>
                                                    </table><br />
                                                </div>
                                                <div class="col-lg-12">
                                                    <?php
                                                    $amountSQL = "SELECT SUM(NVL(OPENING_BALANCE,0)) OPENING_BALANCE FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' 
                                                         AND DELAY_INTEREST IS NULL AND FIN_YEAR_CODE='$finYears[$i]'";
                                                    $fetchAmt = sqlFetchData($connection, $amountSQL);
                                                    foreach ($fetchAmt as $amountDetails) {
                                                        $openingBalance = $amountDetails['OPENING_BALANCE'];
                                                    }

                                                    $depositQuery = "SELECT SUM(NVL(DEPOSIT,0)) AS DEPOSIT FROM GPF_ACCOUNT_CALCULATION
                                                    WHERE REGD_NO='$registrationNo' AND INTEREST_ON_DEPOSIT='Y' AND 
                                                    DELAY_INTEREST IS NULL AND FIN_YEAR_CODE='$finYears[$i]'";
                                                    $cdepositQuery = sqlFetchData($connection, $depositQuery);
                                                    foreach ($cdepositQuery as $cdepositQuerys) {
                                                        $deposit = $cdepositQuerys['DEPOSIT'];
                                                    }
                                                    $adjustmentDeposit = 0;
                                                    $adjustmentInterest = 0;
                                                    $adjustmentMessage = "";

                                                    $adjExistsSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'
                                                                     AND ADJUSTMENT='Y' AND FIN_YEAR_CODE='$finYears[$i]'";
                                                    $countAdj = sqlCountData($connection, $adjExistsSQL);
                                                    if ($countAdj > 0) {
                                                        $adjQuery = "SELECT * FROM GPF_ADJ_PRE_CAL WHERE REGD_NO='$registrationNo' AND 
                                                                            FIN_YEAR_CODE='$finYears[$i]'";
                                                        $fetchAdj = sqlFetchData($connection, $adjQuery);
                                                        foreach ($fetchAdj as $fetchAdjs) {
                                                            $adjustmentDeposit = $fetchAdjs['TOTAL_DEPOSIT'];
                                                            $adjustmentWithdrawal = $fetchAdjs['TOTAL_WITHDRAWAL'];
                                                            $adjustmentInterest = $fetchAdjs['ADJ_INTEREST'];
                                                            $adjustmentMessage = "Adjustment interest of &#8377;" . $adjustmentInterest . " has been added";
                                                        }
                                                    } else {
                                                        $adjustmentDeposit = 0;
                                                        $adjustmentInterest = 0;
                                                        $adjustmentMessage = "";
                                                    }

                                                    $aintQuery = "SELECT SUM(NVL(ACTUAL_INTEREST,0)) AS INTEREST FROM GPF_ACCOUNT_CALCULATION
                                                                  WHERE REGD_NO='$registrationNo'AND FIN_YEAR_CODE='$finYears[$i]'";
                                                    $cintQuery = sqlFetchData($connection, $aintQuery);
                                                    foreach ($cintQuery as $cintQuerys) {
                                                        $interestAmt = round($cintQuerys['INTEREST']);
                                                    }
                                                    $withdrawlAmountSQL = "SELECT SUM(NVL(WITHDRAWAL,0)) WITHDRAWAL FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND 
                                                                             FIN_YEAR_CODE='$finYears[$i]' AND PAY_SLIP_DATE<='$cutMonthDate'";
                                                    $fetchWithdrawlAmt = sqlFetchData($connection, $withdrawlAmountSQL);
                                                    foreach ($fetchWithdrawlAmt as $fetchWithdrawlAmts) {
                                                        $withdrawl = $fetchWithdrawlAmts['WITHDRAWAL'];
                                                    }
                                                    $excessAmountSQL = "SELECT SUM(NVL(DEPOSIT,0)) DEPOSIT FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND 
                                                                            FIN_YEAR_CODE='$finYears[$i]' AND INTEREST_ON_DEPOSIT='N' AND PAY_SLIP_DATE<='$cutMonthDate'";
                                                    $fetchExcessAmt = sqlFetchData($connection, $excessAmountSQL);
                                                    foreach ($fetchExcessAmt as $excessAmountDetails) {
                                                        $excessDeposit = $excessAmountDetails['DEPOSIT'];
                                                    }
                                                    $closingBalance = $openingBalance + $deposit + $adjustmentDeposit + $interestAmt + $adjustmentInterest - $withdrawl;

                                                    $vlcAmountSQL = "SELECT * FROM VLCS.GP_YEARLY_BALANCES WHERE SERIES_ID='$seriesID' AND ACCOUNT_NO='$accountNo'
                                                    AND FIN_YEAR_CODE='$finYears[$i]'";
                                                    $vlcExists = sqlCountData($connection, $vlcAmountSQL);
                                                    if ($vlcExists > 0) {
                                                        $rowSpan = 0;
                                                        $fetchVLC = sqlFetchData($connection, $vlcAmountSQL);
                                                        foreach ($fetchVLC as $fetchVLCAmt) {
                                                            $obVLC = $fetchVLCAmt['OP_BALANCE_WITHDRAWL'];
                                                            $cbVLC = $fetchVLCAmt['CL_BAL_WITHDRAWL'];
                                                            $intVLC = $fetchVLCAmt['INTR_WITHDRAWL'];
                                                        }

                                                        $vlcDepDebSQL = "SELECT SUM(T.SUBSCRIPTION_AMT+T.REFUND_AMT+T.OTHERS_AMT) DEPOSIT, SUM(T.WITHDRAWAL_AMT) DEBIT FROM
                                                                        (SELECT NVL(a.SUBSCRIPTION_AMT,0)SUBSCRIPTION_AMT,
                                                                        NVL(a.REFUND_AMT,0)REFUND_AMT,NVL(a.OTHERS_AMT,0)OTHERS_AMT,NVL(a.WITHDRAWAL_AMT,0)WITHDRAWAL_AMT 
                                                                        FROM VLCS.GP_VOUCHER_ACC_DETAILS a, VLCS.GP_ABSTRACTS b WHERE a.ABSTRACT_NO=b.ABSTRACT_NO AND 
                                                                        a.SERIES_ID='$seriesID' AND a.ACCOUNT_NO='$accountNo' AND b.FIN_YEAR_CODE='$finYears[$i]' AND a.POSTING_TYPE!='F' AND a.TAG='Y' UNION ALL
                                                                        SELECT NVL(SUBSCRIPTION_AMT,0)SUBSCRIPTION_AMT, NVL(REFUND_AMT,0)REFUND_AMT, NVL(OTHERS_AMT,0)OTHERS_AMT,
                                                                        NVL(WITHDRAWAL_AMT,0)WITHDRAWAL_AMT FROM VLCS.GP_ADJUSTMENT_ACC_DETAILS 
                                                                        WHERE SERIES_ID='$seriesID' AND ACCOUNT_NO='$accountNo' AND FIN_YEAR_CODE='$finYears[$i]')T";
                                                        $fetchVLCDepDeb = sqlFetchData($connection, $vlcDepDebSQL);
                                                        foreach ($fetchVLCDepDeb as $fetchVLCDepDebAmt) {
                                                            $depVLC = $fetchVLCDepDebAmt['DEPOSIT'];
                                                            $debVLC = $fetchVLCDepDebAmt['DEBIT'];
                                                        }
                                                    } else {
                                                        $rowSpan = 5;
                                                    }
                                                    ?>
                                                    <table class="table table-bordered table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>PARTICULARS</th>
                                                                <th style="background-color: aquamarine;">FETCHED FROM VLC UPTO MARCH<br> ( in &#8377; )</th>
                                                                <th>AMOUNT UPTO FP <br> ( in &#8377; )</th>
                                                                <th>EXCESS DEPOSIT <br> ( in &#8377; )</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            <?php
                                                            if ($rowSpan == 0) {
                                                            ?>
                                                                <tr>
                                                                    <td>OPENING BALANCE</td>
                                                                    <td style="text-align: right; background-color: aquamarine;"><?php echo round($obVLC); ?></td>
                                                                    <td style="text-align: right;"><?php echo round($openingBalance); ?></td>
                                                                    <td style="text-align: right;"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td> DEPOSIT</td>
                                                                    <td style="text-align: right; background-color: aquamarine;"><?php echo round($depVLC); ?></td>
                                                                    <td style="text-align: right;"><?php echo round($deposit + $adjustmentDeposit); ?></td>
                                                                    <td style="text-align: right;"><?php echo round($excessDeposit); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>WITHDRAWAL</td>
                                                                    <td style="text-align: right; background-color: aquamarine;"><?php echo round($debVLC); ?></td>
                                                                    <td style="text-align: right;"><?php echo round($withdrawl); ?></td>
                                                                    <td style="text-align: right;"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>INTEREST</td>
                                                                    <td style="text-align: right; background-color: aquamarine;"><?php echo round($intVLC); ?></td>
                                                                    <td style="text-align: right;">
                                                                        <?php echo round($interestAmt + $adjustmentInterest); ?><br />
                                                                        <b class="text-danger"><?php echo $adjustmentMessage; ?></b>
                                                                    </td>
                                                                    <td style="text-align: right;"></td>
                                                                </tr>

                                                                <tr style="font-weight:bold;">
                                                                    <td>CLOSING BALANCE </td>
                                                                    <td style="text-align: right; background-color: aquamarine;"><?php echo round($cbVLC); ?></td>
                                                                    <td style="text-align: right;"><?php echo round($closingBalance); ?></td>
                                                                    <td style="text-align: right;"><?php echo round($excessDeposit); ?></td>
                                                                </tr>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <tr>
                                                                    <td>OPENING BALANCE</td>
                                                                    <td rowspan="<?php echo $rowSpan; ?>" style="text-align: center; background-color: aquamarine;">
                                                                        No data</td>
                                                                    <td style="text-align: right;"><?php echo round($openingBalance); ?></td>
                                                                    <td style="text-align: right;"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td> DEPOSIT</td>
                                                                    <td style="text-align: right;"><?php echo round($deposit + $adjustmentDeposit); ?></td>
                                                                    <td style="text-align: right;"><?php echo round($excessDeposit); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>WITHDRAWAL</td>
                                                                    <td style="text-align: right;"><?php echo round($withdrawl); ?></td>
                                                                    <td style="text-align: right;"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>INTEREST</td>
                                                                    <td style="text-align: right;">
                                                                        <?php echo round($interestAmt + $adjustmentInterest); ?><br />
                                                                        <b class="text-danger"><?php echo $adjustmentMessage; ?></b>
                                                                    </td>
                                                                    <td style="text-align: right;"></td>
                                                                </tr>

                                                                <tr style="font-weight:bold;">
                                                                    <td>CLOSING BALANCE </td>
                                                                    <td style="text-align: right;"><?php echo round($closingBalance); ?></td>
                                                                    <td style="text-align: right;"><?php echo round($excessDeposit); ?></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- COL_LG-5 ends -->
                                            </div><br /><br />
                                            <!-- ROW ends -->
                                        <?php
                                        }
                                    }
                                    /*                                     * ******************************** DELAY INTEREST STARTS *********************** */
                                    $delaySQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' 
                                                    AND DELAY_INTEREST IS NOT NULL";
                                    $checkDelay = sqlCountData($connection, $delaySQL);
                                    if ($checkDelay > 0) {
                                        ?>
                                        <center>
                                            <div style="font-weight: bolder;" class="alert alert-warning text-primary">
                                                DELAY INTEREST CALCULATION</div>
                                        </center>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <table class="table table-bordered table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>PAY SLIP DATE</th>
                                                            <th>INTEREST DATE</th>
                                                            <th>OPENING BALANCE</th>
                                                            <th>SUBSCRIPTION</th>
                                                            <th>REFUND</th>
                                                            <th>OTHERS</th>
                                                            <th>WITHDRAWAL</th>
                                                            <th>ADVANCE</th>
                                                            <th>RATE OF INTEREST</th>
                                                            <th>PROGRESSIVE</th>
                                                            <th>INTEREST</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $fetchDelay = sqlFetchData($connection, $delaySQL);
                                                        $span = 0;
                                                        $openingBalance = 0;
                                                        $subscription = 0;
                                                        $refund = 0;
                                                        $others = 0;
                                                        $withdrawal = 0;
                                                        $advance = 0;
                                                        $interest = 0;
                                                        foreach ($fetchDelay as $fetchDelays) {
                                                            $openingBalance += $fetchDelays['OPENING_BALANCE'];
                                                            $subscription += $fetchDelays['SUBSCRIPTION_AMT'];
                                                            $refund += $fetchDelays['REFUND_AMT'];
                                                            $others += $fetchDelays['OTHERS_AMT'];
                                                            $withdrawal += $fetchDelays['WITHDRAWAL_AMT'];
                                                            $advance += $fetchDelays['ADVANCE_AMT'];

                                                            $span++;
                                                            $cutMonth = $fetchDelays['CUT_MONTH'];
                                                            $intOnDep = $fetchDelays['INTEREST_ON_DEPOSIT'];
                                                            if ($cutMonth == "Y") {
                                                                $styleName = "background-color:#FFA4A4;";
                                                            } else {
                                                                if ($intOnDep == "N") {
                                                                    $styleName = "background-color:#8EFEFD;";
                                                                } else {
                                                                    $styleName = "";
                                                                }
                                                            }
                                                            $actualInterest = $fetchDelays['ACTUAL_INTEREST'];
                                                            $delayInterest = $fetchDelays['DELAY_INTEREST'];
                                                            if ($actualInterest > 0) {
                                                                $dinterest += $actualInterest;
                                                            } else {
                                                                $dinterest += $delayInterest;
                                                            }
                                                        ?>
                                                            <tr style="<?php echo $styleName; ?>">
                                                                <td style="text-align: left;"><?php echo date("F, Y", strtotime($fetchDelays['PAY_SLIP_DATE'])); ?></td>
                                                                <td style="text-align: left;"><?php echo date("F, Y", strtotime($fetchDelays['INTEREST_DATE'])); ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['OPENING_BALANCE']; ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['SUBSCRIPTION_AMT']; ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['REFUND_AMT']; ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['OTHERS_AMT']; ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['WITHDRAWAL_AMT']; ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['ADVANCE_AMT']; ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['RATE_OF_INTEREST']; ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['PROGRESSIVE']; ?></td>
                                                                <td style="text-align: right;"><?php echo $fetchDelays['DELAY_INTEREST']; ?></td>

                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td colspan="2" style="text-align: left; font-weight:bolder;">TOTAL</td>
                                                            <td style="text-align: right; font-weight:bolder;"><?php echo $openingBalance; ?></td>
                                                            <td style="text-align: right; font-weight:bolder;"><?php echo $subscription; ?></td>
                                                            <td style="text-align: right; font-weight:bolder;"><?php echo $refund; ?></td>
                                                            <td style="text-align: right; font-weight:bolder;"><?php echo $others; ?></td>
                                                            <td style="text-align: right; font-weight:bolder;"><?php echo $withdrawal; ?></td>
                                                            <td style="text-align: right; font-weight:bolder;"><?php echo $advance; ?></td>
                                                            <td colspan="2" style="text-align: right; font-weight:bolder;"></td>
                                                            <td style="text-align: right; font-weight:bolder;"><?php echo $dinterest; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-lg-12">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>PARTICULARS</th>
                                                            <th>AMOUNT <br> ( in &#8377; )</th>
                                                            <th>EXCESS DEPOSIT <br> ( in &#8377; )</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $delayAmountSQL = "SELECT SUM(NVL(OPENING_BALANCE,0)) OPENING_BALANCE, SUM(NVL(DEPOSIT,0)) DEPOSIT, 
                                                                                           SUM(NVL(WITHDRAWAL,0)) WITHDRAWAL, SUM(NVL(DELAY_INTEREST,0)) TOTAL_INTEREST
                                                                                           FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND DELAY_INTEREST 
                                                                                           IS NOT NULL AND PAY_SLIP_DATE>'$cutMonthDate'";
                                                        $fetchDelayAmt = sqlFetchData($connection, $delayAmountSQL);
                                                        foreach ($fetchDelayAmt as $delayAmountDetails) {
                                                            $delayOpeningBalance = $delayAmountDetails['OPENING_BALANCE'];
                                                            $excessDelayDeposit = $delayAmountDetails['DEPOSIT'];
                                                            $delayWithdrawl = $delayAmountDetails['WITHDRAWAL'];
                                                            $delayInterestAmt = round($delayAmountDetails['TOTAL_INTEREST']);
                                                        }
                                                        $totalExcessDeposit = $excessDelayDeposit + $excessDeposit;
                                                        $finalClosingBalance = $delayOpeningBalance + $delayInterestAmt - $delayWithdrawl;
                                                        ?>
                                                        <tr>
                                                            <td>OPENING BALANCE</td>
                                                            <td style="text-align: right;"><?php echo round($delayOpeningBalance); ?></td>
                                                            <td style="text-align: right;"><?php echo round($excessDeposit); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td> DEPOSIT</td>
                                                            <td style="text-align: right;">0</td>
                                                            <td style="text-align: right;"><?php echo round($excessDelayDeposit); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td>WITHDRAWAL</td>
                                                            <td style="text-align: right;"><?php echo round($delayWithdrawl); ?></td>
                                                            <td style="text-align: right;"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>INTEREST</td>
                                                            <td style="text-align: right;"><?php echo round($delayInterestAmt); ?></td>
                                                            <td style="text-align: right;"></td>
                                                        </tr>

                                                        <tr style="font-weight:bold;">
                                                            <td>CLOSING BALANCE </td>
                                                            <td style="text-align: right;"><?php echo round($finalClosingBalance); ?></td>
                                                            <td style="text-align: right;"><?php echo round($totalExcessDeposit); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!---------------------------- DELAY INTEREST ENDS--------------------------->

                                    <?php
                                    }
                                    ?>
                                    <br /> <br />
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <span class="alert alert-info">
                                                <?php
                                                if ($pension == 7) {
                                                ?>
                                                    <b>FINAL PAYMENT AMOUNT (REVISED) : &#8377; <?php echo $finalPayment; ?>
                                                        (<?php echo $finalPayment < 0 ? "-" : "";  ?>Rupees <?php echo trim(convertToWords(abs($finalPayment))); ?>)
                                                    </b>
                                                <?php
                                                } else if ($pension == 6) {
                                                ?>
                                                    <b>BALANCE TRANSFER AMOUNT : &#8377; <?php echo $finalPayment; ?>
                                                        (<?php echo $finalPayment < 0 ? "-" : "";  ?>Rupees <?php echo trim(convertToWords(abs($finalPayment))); ?>)
                                                    </b>
                                                <?php
                                                } else {
                                                ?>
                                                    <b>FINAL PAYMENT AMOUNT : &#8377; <?php echo $finalPayment; ?>
                                                        (<?php echo $finalPayment < 0 ? "-" : "";  ?>Rupees <?php echo trim(convertToWords(abs($finalPayment))); ?>)
                                                    </b>
                                                <?php
                                                }
                                                ?>
                                            </span>
                                        </div>

                                        <?php
                                        /*************************************** DLIS CALCULATION ******************************************* */
                                        $dlisAmt = 0;
                                        if ($pension == 2 || $pension == 7) {
                                            if ($dlisAdmissible == "Y") {
                                                $dateOfEffect = date('01-M-Y', strtotime($dlisDateOfEffect));
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
                                                    $dlisAmt = 10000;
                                                } else {
                                                    $dlisAmt = $averageProgressive;
                                                }
                                        ?>
                                                <div class="col-lg-3" style="margin-top: 20px;">

                                                </div>
                                                <div class="col-lg-6" style="margin-top: 20px;">
                                                    <table class="table table-bordered table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>PARTICULARS</th>
                                                                <th>AMOUNT (in &#8377;)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Total Progressive (36 months)</td>
                                                                <td><?php echo $totProgAmount; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Average progressive</td>
                                                                <td><?php echo $averageProgressive; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>DLIS Admissible</td>
                                                                <td><?php echo $dlisAmt; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-lg-3" style="margin-top: 20px;">

                                                </div>
                                                <div class="col-lg-12" style="margin-top: 20px;">
                                                    <span class="alert alert-info">
                                                        <b>DEPOSIT LINKED INSURENCE SCHEME (DLIS) AMOUNT : &#8377; <?php echo $dlisAmount; ?>
                                                            (Rupees <?php echo trim(convertToWords($dlisAmount)); ?>)</b>
                                                    </span>
                                                </div>
                                            <?php
                                            } else {
                                            ?>
                                                <div class="col-lg-12" style="margin-top: 20px;">
                                                    <span class="alert alert-danger">
                                                        <b>No DLIS Admissible</b>
                                                    </span>
                                                </div>
                                        <?php
                                            }
                                        }

                                        /*************************************** DLIS CALCULATION ******************************************* */
                                        ?>
                                    </div>
                                    <!---------------------------- GPF DEBIT STARTS--------------------------->
                                    <?php
                                    $getMonthSQL = "SELECT YEAR_DESC FROM MAS_INTEREST WHERE FIN_YEAR_CODE='$retdFinYear' AND 
                                                    ACCOUNTING_MONTH=1";
                                    $fetchMonth = sqlFetchData($connection, $getMonthSQL);
                                    foreach ($fetchMonth as $months) {
                                        $voucherDate = $months['YEAR_DESC'];
                                    }
                                    $debitListSQL = "SELECT * FROM FP_DEBIT_LIST WHERE BENEFICIARY_CODE='$beneficiaryCode' AND
                                                    VOUCHER_DATE>='$voucherDate' ORDER BY VOUCHER_DATE DESC";
                                    $debitListExists = sqlCountData($connection, $debitListSQL);
                                    if ($debitListExists > 0) {
                                    ?><br><br>
                                        <center>
                                            <div style="font-weight: bolder;" class="alert alert-secondary">
                                                GPF DEBIT FROM THE YEAR OF <?php echo strtoupper($pensionType); ?></div>
                                        </center>
                                        <table class="table table-bordered table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>BILL DATE </th>
                                                    <th>VOUCHER NO</th>
                                                    <th>VOUCHER DATE</th>
                                                    <th>AMOUNT ( in &#8377; )</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $fetchDebitList = sqlFetchData($connection, $debitListSQL);
                                                foreach ($fetchDebitList as $fetchDebitLists) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo date("d/m/Y", strtotime($fetchDebitLists['BILL_DATE'])); ?></td>
                                                        <td><?php echo $fetchDebitLists['VOUCHER_NO']; ?></td>
                                                        <td><?php echo date("d/m/Y", strtotime($fetchDebitLists['VOUCHER_DATE'])); ?></td>
                                                        <td><?php echo $fetchDebitLists['TR_BENEFICIARY_AMT']; ?></td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>

                                        <!---------------------------- GPF DEBIT ENDS--------------------------->
                                    </div>
                                    <!---------------------------- CALCULATION SHEET ENDS--------------------------->
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <?php
                                        require_once "./includes/copyright.php";
            ?>
            </section>
        </div>
    </div>
    </div>

    <?php
                                        require_once "./bottom.php";
    ?>

<?php
                                    }
?>