<?php
$mainPage = "Admin";
$pageName = "Pre-calculation entry/edit";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);
    if (isset($_REQUEST['save_precal'])) {
        $seriesCode = trim(removeHTMLEntities($_REQUEST['series_code']));
        $accountNumber = trim($_REQUEST['account_number']);
        $openingFinYear = trim($_REQUEST['opening_fin_year']);
        $closingBalanceAmount = trim(removeHTMLEntities($_REQUEST['closing_balance_amount']));
        $missingDebit = trim(removeHTMLEntities($_REQUEST['missing_debit']));
        $closingFinYear = trim(removeHTMLEntities($_REQUEST['closing_fin_year']));
        $missingCredit = trim(removeHTMLEntities($_REQUEST['missing_credit']));
        $intAllowedUpto = trim(removeHTMLEntities($_REQUEST['int_allowed_upto']));
        $dlisAdmissible = trim(removeHTMLEntities($_REQUEST['dlis_admissible']));

        if (empty($closingBalanceAmount) && $closingBalanceAmount != 0) {
            $closingBalanceAmountErr = "Required";
        } else {
            if (!textPatternValidation($closingBalanceAmount, "0-9.")) {
                $closingBalanceAmountErr = "Only numbers and dot are allowed";
            }
        }
        if (empty($missingDebit)) {
            $missingDebitErr = "Required";
        }
        if (empty($missingCredit)) {
            $missingCreditErr = "Required";
        }
        if (empty($closingFinYear)) {
            $closingFinYearErr = "Required";
        } else {
            if (!textPatternValidation($closingFinYear, "0-9")) {
                $closingFinYearErr = "Only numbers and dot are allowed";
            }
        }
        if (empty($intAllowedUpto)) {
            $intAllowedUptoErr = "Required";
        } else {
            $intAllowedUpto = date("01-M-Y", strtotime($intAllowedUpto));
            $intQuery = "SELECT * FROM MAS_INTEREST WHERE YEAR_DESC='$intAllowedUpto'";
            $countRows = sqlCountData($connection, $intQuery);
            if ($countRows == 0) {
                $intAllowedUptoErr = "Interest for this month is not entered";
            }
        }
        if (empty($dlisAdmissible)) {
            $dlisAdmissibleErr = "Required";
        }
        if (($dlisAdmissibleErr == "") && ($closingBalanceAmountErr == "") && ($missingDebitErr == "") && ($closingFinYearErr == "") && ($intAllowedUptoErr == "") && ($missingCreditErr == "")) {

            $precalSearchQuery = "SELECT * FROM GPF_AMOUNT_INFO WHERE REGD_NO='$registrationNo'";
            $countRows = sqlCountData($connection, $precalSearchQuery);
            if ($countRows > 0) {
                $preCalCudQuery = "UPDATE GPF_AMOUNT_INFO SET CLOSING_FIN_YEAR='$closingFinYear', CLOSING_BAL_AMOUNT='$closingBalanceAmount',
                                   INTEREST_ALLOWED_UPTO='$intAllowedUpto', DLIS_ADMISSIBLE='$dlisAdmissible', MISSING_CREDIT='$missingCredit', 
                                   MISSING_DEBIT='$missingDebit', MODIFY_USER='$loginUser', MODIFY_DATE=SYSDATE WHERE REGD_NO='$registrationNo'";
            } else {
                $slNo = sqlSerialNo($connection, "GPF_AMOUNT_INFO", "SL_NO");
                $preCalCudQuery = "INSERT INTO GPF_AMOUNT_INFO VALUES('$slNo','$registrationNo','$seriesCode', '$accountNumber','$closingFinYear','$closingBalanceAmount', '$intAllowedUpto', 
                '', '', '', '', '', '', '', '', '$dlisAdmissible','', '$missingCredit', '$missingDebit', '', '', '', '','', '$loginUser',SYSDATE,'$loginUser',SYSDATE)";

                $caseQuery = "UPDATE GPF_CASE_STATUS SET PRE_CAL_DATE=SYSDATE, CASE_STATUS=3 WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";

                $subscriptionQuery = "INSERT INTO GPF_SUBSCRIPTION (REGD_NO,SERIES_ID,ACCOUNT_NO,FIN_YEAR_CODE,ABSTRACT_NO,VOUCHER_NO,
                                       PAY_SLIP_DATE,INTEREST_DATE,POSTING_TYPE,SUBSCRIPTION_AMT,REFUND_AMT,OTHERS_AMT,WITHDRAWAL_AMT,DC_FLAG,
                                       CATEGORY_CODE,INT_ALLOW,CREATE_USER,CREATE_DATE)SELECT '$registrationNo',a.SERIES_ID,a.ACCOUNT_NO,
                                       b.FIN_YEAR_CODE,a.ABSTRACT_NO,a.VOUCHER_NO,a.PAY_SLIP_DATE,a.INTEREST_DATE,a.POSTING_TYPE,NVL(a.SUBSCRIPTION_AMT,0)SUBSCRIPTION_AMT,
                                       NVL(a.REFUND_AMT,0)REFUND_AMT,NVL(a.OTHERS_AMT,0)OTHERS_AMT,NVL(a.WITHDRAWAL_AMT,0)WITHDRAWAL_AMT,a.DC_FLAG,a.CATEGORY_CODE,'Y','$loginUser',SYSDATE 
                                       FROM VLCS.GP_VOUCHER_ACC_DETAILS a, VLCS.GP_ABSTRACTS b WHERE a.ABSTRACT_NO=b.ABSTRACT_NO AND 
                                       a.SERIES_ID='$seriesCode' AND a.ACCOUNT_NO='$accountNumber' AND a.POSTING_TYPE!='F' AND a.TAG='Y'";

                $adjustmentQuery = "INSERT INTO GPF_SUBSCRIPTION (REGD_NO,SERIES_ID,ACCOUNT_NO,FIN_YEAR_CODE,ADJUSTMENT_NO,ADJUSTMENT_TYPE,
                                    PAY_SLIP_DATE,INTEREST_DATE,SUBSCRIPTION_AMT,REFUND_AMT,OTHERS_AMT,WITHDRAWAL_AMT,CATEGORY_CODE,INT_ALLOW,
                                    CREATE_USER,CREATE_DATE)SELECT '$registrationNo',SERIES_ID,ACCOUNT_NO,FIN_YEAR_CODE,ADJUSTMENT_NO,ADJUSTMENT_FLAG,
                                    PAY_SLIP_DATE,INTEREST_DATE,NVL(SUBSCRIPTION_AMT,0)SUBSCRIPTION_AMT,NVL(REFUND_AMT,0)REFUND_AMT,NVL(OTHERS_AMT,0)OTHERS_AMT,
                                    NVL(WITHDRAWAL_AMT,0)WITHDRAWAL_AMT,CATEGORY_CODE,'Y','$loginUser', SYSDATE FROM VLCS.GP_ADJUSTMENT_ACC_DETAILS 
                                    WHERE SERIES_ID='$seriesCode' AND ACCOUNT_NO='$accountNumber'";

                $missingCreditQuery = "INSERT INTO GPF_MISSING_CREDIT(REGD_NO,SERIES_ID,ACCOUNT_NO,SLIP_DATE,FIN_YEAR_CODE,CREATE_USER,CREATE_DATE)SELECT '$registrationNo',
                                       SERIES_ID,ACCOUNT_NO,SLIP_DATE,FIN_YEAR_CODE,'$loginUser',SYSDATE FROM VLCS.GP_MISSING_CREDIT WHERE SERIES_ID='$seriesCode' AND
                                        ACCOUNT_NO='$accountNumber' AND NVL(CLEAR_TAG,'N')!='Y'";
                $subsBool = sqlCUDData($connection, $subscriptionQuery);
                $adjBool = sqlCUDData($connection, $adjustmentQuery);
                $misCrBool = sqlCUDData($connection, $missingCreditQuery);
                $caseBool = sqlCUDData($connection, $caseQuery);
            }
            $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");
            $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo',3,SYSDATE, '$loginUser')";
            $preCalBool = sqlCUDData($connection, $preCalCudQuery);
            $caseLogBool = sqlCUDData($connection, $caseLogQuery);
            if (($subsBool == true) || ($adjBool == true) || ($caseLogBool == true) || ($misCrBool == true) || ($caseBool == true) || ($preCalBool == true)) {
                $error = 0;
                $message = "Successfully saved";
                $textColor = "success";
            } else {
                $error = 1;
                $message = "Data not saved, try again";
                $textColor = "danger";
            }
        } else {
            $error = 1;
            $message = "Recorrect errors";
            $textColor = "danger";
        }
    }

    $applicationQuery = "SELECT d.SERIES_ID, d.ACCOUNT_NO, a.FIN_YEAR_CODE, c.SERIES_DESCR, f.FIN_YEAR, g.CLOSING_FIN_YEAR, g.INTEREST_ALLOWED_UPTO,
                         d.PENSION_TYPE, e.PENSION_LONG_DESCR, b.DATE_OF_EFFECT, e.PENSION_SHORT_DESCR, g.MISSING_CREDIT, g.MISSING_DEBIT, g.CLOSING_BAL_AMOUNT,
                         g.DLIS_ADMISSIBLE FROM GPF_INWARD a INNER JOIN GPF_APPLICATION b ON a.REGD_NO=b.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES c 
                         ON a.SERIES_ID=c.SERIES_ID INNER JOIN GPF_CASE_STATUS d ON a.REGD_NO=d.REGD_NO INNER JOIN
                          MAS_PENSION_TYPE e ON d.PENSION_TYPE=e.PENSION_ID INNER JOIN VLCS.MM_FINANCIAL_YEAR f ON 
                          d.FIN_YEAR_CODE=f.FIN_YEAR_CODE LEFT JOIN GPF_AMOUNT_INFO g ON a.REGD_NO=g.REGD_NO WHERE a.REGD_NO='$registrationNo'";

    $fetchApplicationData = sqlFetchData($connection, $applicationQuery);
    foreach ($fetchApplicationData as $fetchApplicationList) {
        $fPensionType = $fetchApplicationList['PENSION_TYPE'];
        $fDateOfEffect = $fetchApplicationList['DATE_OF_EFFECT'];
        $fseriesCode = $fetchApplicationList['SERIES_ID'];
        $faccountNumber = $fetchApplicationList['ACCOUNT_NO'];
        $feffectedFinYear = $fetchApplicationList['FIN_YEAR_CODE'];
        $fclosingFinYear = $fetchApplicationList['CLOSING_FIN_YEAR'];
        $fpensionLongDesc = $fetchApplicationList['PENSION_LONG_DESCR'];
        $fpensionShortDesc = $fetchApplicationList['PENSION_SHORT_DESCR'];
        $fpensionType = $fpensionLongDesc . " (" . $fpensionShortDesc . ")";
        $fgpfSeries = "T/" . $fetchApplicationList['SERIES_DESCR'] . '/' . $fetchApplicationList['ACCOUNT_NO'];
        $ffinYear = $fetchApplicationList['FIN_YEAR'];
        $fmissingDebit = $fetchApplicationList['MISSING_DEBIT'];
        $fmissingCredit = $fetchApplicationList['MISSING_CREDIT'];
        $fclosingBalanceAmount = $fetchApplicationList['CLOSING_BAL_AMOUNT'];
        $fintAllowedUpto = $fetchApplicationList['INTEREST_ALLOWED_UPTO'];
        $fdlisAdmissible = $fetchApplicationList['DLIS_ADMISSIBLE'] == "" ? "N" : $fetchApplicationList['DLIS_ADMISSIBLE'];
    }
    if (empty($fmissingCredit) || $fmissingCredit == "" || $fmissingCredit == null) {
        $misCrdtSQL = "SELECT * FROM VLCS.GP_MISSING_CREDIT WHERE SERIES_ID='$seriesCode' AND
                       ACCOUNT_NO='$accountNumber' AND NVL(CLEAR_TAG,'N')!='Y'";
        $misCrdtExist = sqlCountData($connection, $misCrdtSQL);
        if ($misCrdtExist > 0) {
            $fmissingCredit = "Y";
        } else {
            $fmissingCredit = "N";
        }
    }
    if (empty($fmissingDebit) || $fmissingDebit == "" || $fmissingDebit == null) {
        $fmissingDebit = "N";
    }

?>

    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <?php
                require_once "./includes/breadcumb.php";
                ?>
                <desgTitle id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">

                                    <b class="text-<?php echo $textColor; ?>">
                                        <?php echo $message == "" ? "" : $message . "<br>"; ?>
                                    </b>
                                    <b class="text-primary">
                                        <?php
                                        $subscriptionQuery = "SELECT * FROM GPF_SUBSCRIPTION a INNER JOIN VLCS.MM_FINANCIAL_YEAR b
                                                             ON a.FIN_YEAR_CODE=b.FIN_YEAR_CODE WHERE a.REGD_NO='$registrationNo' AND
                                                          a.ADJUSTMENT_TYPE IS NOT NULL AND a.FIN_YEAR_CODE >= '$fclosingFinYear'";
                                        $count = sqlCountData($connection, $subscriptionQuery);
                                        $fetchSubscriptionData = sqlFetchData($connection, $subscriptionQuery);
                                        if ($count > 0) {
                                            foreach ($fetchSubscriptionData as $fetchSubscriptionList) {
                                                $months[] = date("F-Y", strtotime($fetchSubscriptionList['INTEREST_DATE'])) . " (" . $fetchSubscriptionList['FIN_YEAR'] . ")";
                                            }
                                            echo "Adjustment year(s) : " . implode(", ", $months);
                                        }
                                        ?>
                                    </b>
                                    <form action="" method="post">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="account_no">GPF Account number<b class="text-danger"> *</b></label>
                                                    <input type="hidden" name="series_code" id="series_code" value="<?php if ($error === 1) {
                                                                                                                        echo $seriesCode;
                                                                                                                    } else {
                                                                                                                        echo $fseriesCode;
                                                                                                                    } ?>" />
                                                    <input type="hidden" name="account_number" id="account_number" value="<?php if ($error === 1) {
                                                                                                                                echo $accountNumber;
                                                                                                                            } else {
                                                                                                                                echo $faccountNumber;
                                                                                                                            } ?>" />
                                                    <div class=" input-group input-group-default">
                                                        <input type="text" placeholder="GPF Account number" readonly class="form-control" value="<?php echo $fgpfSeries; ?>" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="pension_type">Pension type<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Pension type" readonly class="form-control" value="<?php echo $fpensionType; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mb-10">
                                                    <label for="closing_fin_year">Closing financial year <b class="text-danger">* <?php echo $closingFinYearErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="closing_fin_year" id="closing_fin_year" class="form-control select-search" tabindex="1">
                                                            <option value="">Select financial year</option>
                                                            <?php
                                                            $fin_yearQuery = "SELECT * FROM VLCS.MM_FINANCIAL_YEAR WHERE FIN_YEAR_CODE>13 AND FIN_YEAR_CODE<'$feffectedFinYear' ORDER BY FIN_YEAR_CODE DESC";
                                                            $fin_years = sqlFetchData($connection, $fin_yearQuery);
                                                            foreach ($fin_years as $fin_yearsList) {
                                                            ?>
                                                                <option value="<?php echo $fin_yearsList['FIN_YEAR_CODE']; ?>" <?php
                                                                                                                                if ($error == 1) {
                                                                                                                                    if ($fin_yearsList['FIN_YEAR_CODE'] == $closingFinYear) {
                                                                                                                                        echo "selected='selected'";
                                                                                                                                    }
                                                                                                                                } else {
                                                                                                                                    if ($fin_yearsList['FIN_YEAR_CODE'] == $fclosingFinYear) {
                                                                                                                                        echo "selected='selected'";
                                                                                                                                    }
                                                                                                                                }
                                                                                                                                ?>>
                                                                    <?php echo $fin_yearsList['FIN_YEAR']; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="missing_credit">Missing credit <b class="text-danger"> * <?php echo $missingCreditErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="missing_credit" id="missing_credit" class="form-control" tabindex="3">
                                                            <option value="">Select missing credit</option>
                                                            <?php
                                                            $missingCreditArr = ["Y" => "Yes", "N" => "No"];
                                                            foreach ($missingCreditArr as $missingCreditKeys => $missingCreditVals) {
                                                            ?>
                                                                <option value="<?php echo $missingCreditKeys; ?>" <?php
                                                                                                                    if ($error == 1) {
                                                                                                                        if ($missingCreditKeys == $missingCredit) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    } else {
                                                                                                                        if ($missingCreditKeys == $fmissingCredit) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    }
                                                                                                                    ?>>
                                                                    <?php echo $missingCreditVals; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="int_allowed_upto">Interest allowed upto <b class="text-danger">* <?php echo $intAllowedUptoErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="month" placeholder="Interest allowed upto" tabindex="5" name="int_allowed_upto" id="int_allowed_upto" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                            echo $intAllowedUpto == "" ? "" : date("Y-m", strtotime($intAllowedUpto));
                                                                                                                                                                                                        } else {
                                                                                                                                                                                                            echo $fintAllowedUpto == "" ? "" : date("Y-m", strtotime($fintAllowedUpto));
                                                                                                                                                                                                        } ?>">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="save_precal" id="save_precal" tabindex="6">Save</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="regd_no">Registration number<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Registration number" readonly class="form-control" value="<?php echo $registrationNo; ?>">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="financial_year">Date of <?php if ($fPensionType == 7) {
                                                                                            echo "superranuation (revised)";
                                                                                        } else {
                                                                                            echo $fpensionLongDesc;
                                                                                        } ?><b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Date of <?php echo $fpensionLongDesc; ?>" readonly class="form-control" value="<?php echo date("d-F-Y", strtotime($fDateOfEffect)) . " (" . $ffinYear . ")"; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="closing_balance_amount">Closing balance amount <b class="text-danger"> * <?php echo $closingBalanceAmountErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Closing balance amount" tabindex="2" name="closing_balance_amount" id="closing_balance_amount" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                                        echo $closingBalanceAmount;
                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                        echo $fclosingBalanceAmount;
                                                                                                                                                                                                                    } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="missing_debit">Missing debit <b class="text-danger"> * <?php echo $missingDebitErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="missing_debit" id="missing_debit" class="form-control" tabindex="4">
                                                            <option value="">Select missing debit</option>
                                                            <?php
                                                            $missingDebitArr = ["Y" => "Yes", "N" => "No"];
                                                            foreach ($missingDebitArr as $missingDebitKeys => $missingDebitVals) {
                                                            ?>
                                                                <option value="<?php echo $missingDebitKeys; ?>" <?php
                                                                                                                    if ($error == 1) {
                                                                                                                        if ($missingDebitKeys == $missingDebit) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    } else {
                                                                                                                        if ($missingDebitKeys == $fmissingDebit) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    }
                                                                                                                    ?>>
                                                                    <?php echo $missingDebitVals; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dlis_admissible">DLIS Admissible <b class="text-danger"> * <?php echo $dlisAdmissibleErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="dlis_admissible" id="dlis_admissible" class="form-control" tabindex="4">
                                                            <option value="">Select admissible</option>
                                                            <?php
                                                            $dlisAdmissibleArr = ["Y" => "Yes", "N" => "No"];
                                                            foreach ($dlisAdmissibleArr as $dlisAdmissibleKeys => $dlisAdmissibleVals) {
                                                            ?>
                                                                <option value="<?php echo $dlisAdmissibleKeys; ?>" <?php
                                                                                                                    if ($error == 1) {
                                                                                                                        if ($dlisAdmissibleKeys == $dlisAdmissible) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    } else {
                                                                                                                        if ($dlisAdmissibleKeys == $fdlisAdmissible) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    }
                                                                                                                    ?>>
                                                                    <?php echo $dlisAdmissibleVals; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <?php
            require_once "./includes/copyright.php";
            ?>
            </desgTitle>
        </div>
    </div>
    </div>

    <?php
    require_once "./bottom.php";
    ?>
    <script>
        jQuery(document).ready(function() {
            jQuery("#closing_fin_year").change(function() {
                const closingFinYear = jQuery(this).val();
                const registrationNo = "<?php echo $registrationNo; ?>";

                if ((closingFinYear == "") || (closingFinYear == null)) {
                    $("#closing_balance_amount").val("");
                    swal({
                        title: "Oops!",
                        text: "Please provide closing finanacial year",
                        icon: "error",
                        button: "Close",
                    });

                } else {
                    const closingAmountCred = {
                        RegistrationNo: registrationNo,
                        ClosingFinYear: closingFinYear
                    }
                    jQuery.ajax({
                        type: 'POST',
                        url: 'ajax/closing_balance_amount.php',
                        data: JSON.stringify(closingAmountCred),
                        success: function(returnValue) {
                            const {
                                StatusCode,
                                Message,
                                ...Others
                            } = returnValue;

                            if (StatusCode === 200) {
                                const {
                                    ClosingBalanceAmount
                                } = Others;
                                jQuery("#closing_balance_amount").val(ClosingBalanceAmount);
                            } else {
                                jQuery("#closing_balance_amount").val("");
                                swal({
                                    title: "Oops!",
                                    text: Message,
                                    icon: "error",
                                    button: "Close",
                                });
                            }
                        }
                    });
                }
            });
        });
    </script>
<?php
}
?>