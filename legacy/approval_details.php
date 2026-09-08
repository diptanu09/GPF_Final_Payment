<?php
$pageName = "Details for approval";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = $_REQUEST['regd_no'];

    $basicInfoQuery = "SELECT A.REGD_NO, 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, A.SUBSCRIBER_NAME, B.LTA_TO_WHOM, B.DATE_OF_LTA, A.LTA_REGISTERED_DATE,
                       A.EMPLOYEE_CODE, A.BENEFICIARY_CODE, F.PENSION_LONG_DESCR, F.PENSION_SHORT_DESCR, B.DDO_CODE, A.APPROVED_DATE,A.LTA_CHECKED_DATE,
                       B.SPOUSE_NAME, B.RELATION SPOUSE_RELATION, B.PERSONAL_ADDRESS, B.LAST_FUND_DEDUCTION, B.DEBIT_DURING_YEAR, A.CHECKED_DATE,
                       C.DLIS_ADMISSIBLE, C.MISSING_CREDIT, F.PENSION_ID, C.MISSING_DEBIT, B.TREASURY_CODE, G.DDO_DESG, B.DATE_OF_EFFECT, A.LTA_APPROVED_DATE,
                       I.TRES_NAME TREASURY, H.TRES_NAME SUB_TREASURY, C.MISSING_CREDIT, C.MISSING_DEBIT, C.INTEREST_ALLOWED_UPTO, 
                       J.FULL_NAME CHECKED_BY, K.FULL_NAME APPROVED_BY, A.CASE_STATUS FROM GPF_CASE_STATUS A LEFT JOIN GPF_APPLICATION B ON A.REGD_NO=B.REGD_NO  
                       LEFT JOIN GPF_AMOUNT_INFO C ON A.REGD_NO=C.REGD_NO LEFT JOIN VLCS.MM_GPF_SERIES D ON A.SERIES_ID=D.SERIES_ID 
                       LEFT JOIN VLCS.MM_FINANCIAL_YEAR E ON A.FIN_YEAR_CODE=E.FIN_YEAR_CODE LEFT JOIN MAS_PENSION_TYPE F ON F.PENSION_ID=A.PENSION_TYPE 
                       LEFT JOIN VLCS.STATE_DDO G ON B.DDO_CODE=G.DDO_CODE LEFT JOIN VLCS.STATE_TREASURY H ON B.TREASURY_CODE=H.TRES_CODE 
                       LEFT JOIN VLCS.STATE_TREASURY I ON H.CNTR_TRES=I.TRES_CODE LEFT JOIN USER_ACCOUNTS J ON C.CHECKED_BY = J.USERNAME 
                       LEFT JOIN USER_ACCOUNTS K ON C.APPROVED_BY = K.USERNAME WHERE A.REGD_NO='$registrationNo'";
    $infoDetail = sqlFetchData($connection, $basicInfoQuery);
    foreach ($infoDetail as $infoDetails) {
        $caseStatus = $infoDetails['CASE_STATUS'];
        $ltaRegDate = $infoDetails['LTA_REGISTERED_DATE'];
        $accountNo = $infoDetails['GPF_ACCOUNT_NO'];
        $subscriberName = $infoDetails['SUBSCRIBER_NAME'];
        $employeeCode = $infoDetails['EMPLOYEE_CODE'];
        $beneficiaryCode = $infoDetails['BENEFICIARY_CODE'];
        $pensionID = $infoDetails['PENSION_ID'];
        $pensionType = $infoDetails['PENSION_LONG_DESCR'];
        $ddoCode = $infoDetails['DDO_CODE'];
        $ddoDesignation = $infoDetails['DDO_DESG'];
        $debitAmountYear = $infoDetails['DEBIT_DURING_YEAR'];
        $personalAddress = $infoDetails['PERSONAL_ADDRESS'];
        $treasuryCode = $infoDetails['TREASURY_CODE'];
        $treasuryName = $infoDetails['TREASURY'];
        $subTreasuryName = $infoDetails['SUB_TREASURY'];
        $missingCredit = $infoDetails['MISSING_CREDIT'] == "Y" ? "Yes" : "No";
        $missingDebit = $infoDetails['MISSING_DEBIT'] == "Y" ? "Yes" : "No";
        $lastFund = date("F, Y", strtotime($infoDetails['LAST_FUND_DEDUCTION']));
        $interestAllowedUpto = date("F, Y", strtotime($infoDetails['INTEREST_ALLOWED_UPTO']));
        $dateOfEffect = date("d-m-Y", strtotime($infoDetails['DATE_OF_EFFECT']));
        $spouseName = $infoDetails['SPOUSE_NAME'];
        $spouseRelation = $infoDetails['SPOUSE_RELATION'];
        $approvedDate = $infoDetails['APPROVED_DATE'] == "" ? "" : date("jS F, Y", strtotime($infoDetails['APPROVED_DATE']));
        $checkedDate = $infoDetails['CHECKED_DATE'] == "" ? "" : date("jS F, Y", strtotime($infoDetails['CHECKED_DATE']));
        $checkedBy = $infoDetails['CHECKED_BY'];
        $approvedBy = $infoDetails['APPROVED_BY'];
        $dlisAdmissible = $infoDetails['DLIS_ADMISSIBLE'] == "Y" ? "Yes" : "No";
        $dateOfDeathAfterSUP = $infoDetails['DATE_OF_LTA'] == "" ? "" : date("d-m-Y", strtotime($infoDetails['DATE_OF_LTA']));
        $ltaWhom = $infoDetails['LTA_TO_WHOM'];
        $checkedLTADate = $infoDetails['LTA_CHECKED_DATE'] == "" ? "" : date("jS F, Y", strtotime($infoDetails['LTA_CHECKED_DATE']));
        $approvedLTADate = $infoDetails['LTA_APPROVED_DATE'] == "" ? "" : date("jS F, Y", strtotime($infoDetails['LTA_APPROVED_DATE']));
    }

    if (empty($ltaRegDate) || $ltaRegDate == "") {
        if (empty($checkedDate)) {
            $appBtnText = "CHECK";
        } else {
            $appBtnText = "APPROVE";
            if ($loginRole != 2) {
                $disabledClass = "style='display:none;'";
            } else {
                $disabledClass = "style='display:block;'";
            }
        }
    } else {
        if (empty($checkedLTADate)) {
            $appBtnText = "LTA-CHECK";
        } else {
            $appBtnText = "LTA-APPROVE";
            if ($loginRole != 2) {
                $disabledClass = "style='display:none;'";
            } else {
                $disabledClass = "style='display:block;'";
            }
        }
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

                                    <a href="revert.php?regd_no=<?php echo $registrationNo; ?>">
                                        <button class="btn btn-primary" style="<?php echo $disabledClass; ?>">REVERT</button>
                                    </a>

                                    <?php
                                    if (empty($ltaRegDate) || $ltaRegDate == "") {
                                        if (($checkedDate == "") || ($approvedDate == "")) {
                                    ?>
                                            <button id="app_check_btn" <?php echo $disabledClass; ?> class="btn btn-info"></button><br>
                                        <?php
                                        }
                                    } else {
                                        if (($checkedLTADate == "") || ($approvedLTADate == "")) {
                                        ?>
                                            <button id="app_check_btn" <?php echo $disabledClass; ?> class="btn btn-info"></button><br>
                                    <?php
                                        }
                                    }
                                    ?>

                                    <?php
                                    if (empty($ltaRegDate) || $ltaRegDate == "") {
                                        if (!((empty($checkedDate)) || ($checkedDate == null) || ($checkedDate == ""))) {
                                    ?><br>
                                            <label for="checked" class="alert alert-info"><b>Checked by <?php echo $checkedBy; ?> on <?php echo $checkedDate; ?></b></label>
                                        <?php
                                        }
                                        if (!((empty($approvedDate)) || ($approvedDate == null) || ($approvedDate == ""))) {
                                        ?>
                                            <label for="checked" class="alert alert-primary"><b>Approved by <?php echo $approvedBy; ?> on <?php echo $approvedDate; ?></b></label>
                                        <?php
                                        }
                                    } else {
                                        if (!((empty($checkedLTADate)) || ($checkedLTADate == null) || ($checkedLTADate == ""))) {
                                        ?><br>
                                            <label for="checked" class="alert alert-info"><b>LTA Checked by <?php echo $checkedBy; ?> on <?php echo $checkedLTADate; ?></b></label>
                                        <?php
                                        }
                                        if (!((empty($approvedLTADate)) || ($approvedLTADate == null) || ($approvedLTADate == ""))) {
                                        ?>
                                            <label for="checked" class="alert alert-primary"><b>LTA Approved by <?php echo $approvedBy; ?> on <?php echo $approvedLTADate; ?></b></label>
                                    <?php
                                        }
                                    }
                                    ?>
                                    <br>
                                    <ul class="nav nav-tabs customtab" role="tablist">
                                        <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#basic_info" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Basic info</span></a> </li>
                                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#amount_info" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Amount info</span></a> </li>
                                    </ul>
                                    <!-- Tab panes -->
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="basic_info" role="tabpanel">
                                            <div class="p-20">
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <b>Registration Number : </b><?php echo $registrationNo; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Account number : </b><?php echo $accountNo; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Subscriber name : </b><?php echo $subscriberName; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Employee code : </b><?php echo $employeeCode; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Beneficiary code : </b><?php echo $beneficiaryCode; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Debit during the year : </b><?php echo $debitAmountYear; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Spouse name : </b><?php echo $spouseName; ?> (<?php echo $spouseRelation; ?>)
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Date of <?php echo $pensionID == 7 ? "revised superranuation " : $pensionType; ?> : </b><?php echo $dateOfEffect; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Last fund deduction : </b><?php echo $lastFund; ?>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <b>Personal address : </b><?php echo $personalAddress; ?>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <b>DDO Address : </b><?php echo $ddoCode; ?> - <?php echo $ddoDesignation; ?>
                                                    </div>
                                                    <div class="col-lg-5">
                                                        <b>Treasury : </b><?php echo $treasuryCode; ?> - <?php echo $subTreasuryName; ?> (<?php echo $treasuryName; ?>)
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Missing credit / debit : </b><?php echo $missingCredit; ?> / <?php echo $missingDebit; ?>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <b>DLIS Admissible : </b><?php echo $dlisAdmissible; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Interest allowed upto : </b><?php echo $interestAllowedUpto; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>Date of death after retirement : </b><?php echo $dateOfDeathAfterSUP; ?>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b>LTA to whom: </b><?php echo $ltaWhom; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane  p-20" id="amount_info" role="tabpanel">
                                            <?php
                                            $amtQuery = "SELECT * FROM GPF_AMOUNT_INFO WHERE REGD_NO='$registrationNo'";
                                            $amtDetail = sqlFetchData($connection, $amtQuery);
                                            foreach ($amtDetail as $amtDetails) {
                                                $openingFinYear = $amtDetails['OPENING_FIN_YEAR'];
                                                $openingBalAmt = $amtDetails['OPENING_BAL_AMOUNT'];
                                                $actualDeposit = $amtDetails['ACTUAL_DEPOSIT'];
                                                $excessDeposit = $amtDetails['EXCESS_DEPOSIT'];
                                                $withdrawal = $amtDetails['WITHDRAWAL'];
                                                $actualInterest = $amtDetails['ACTUAL_INTEREST'];
                                                $delayedInterest = $amtDetails['DELAYED_INTEREST'];
                                                $finalPaymentAmount = $amtDetails['FINAL_PAYMENT_AMOUNT'];
                                                $dlisAmount = $amtDetails['DLIS_AMOUNT'];
                                            }
                                            ?>
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>PARTICULARS</th>
                                                        <th>AMOUNT <br> ( in &#8377; )</th>
                                                        <th>EXCESS DEPOSIT <br> ( in &#8377; )</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <tr>
                                                        <td>OPENING BALANCE</td>
                                                        <td style="text-align: right;"><?php echo $openingBalAmt; ?></td>
                                                        <td style="text-align: right;"></td>
                                                    </tr>
                                                    <tr>
                                                        <td> DEPOSIT</td>
                                                        <td style="text-align: right;"><?php echo $actualDeposit; ?></td>
                                                        <td style="text-align: right;"><?php echo $excessDeposit; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>WITHDRAWAL</td>
                                                        <td style="text-align: right;"><?php echo $withdrawal; ?></td>
                                                        <td style="text-align: right;"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>INTEREST</td>
                                                        <td style="text-align: right;"><?php echo ($actualInterest + $delayedInterest); ?></td>
                                                        <td style="text-align: right;"></td>
                                                    </tr>

                                                    <tr style="font-weight:bold;">
                                                        <td>CLOSING BALANCE </td>
                                                        <td style="text-align: right;"><?php echo ($openingBalAmt + $actualDeposit - $withdrawal + $actualInterest + $delayedInterest); ?></td>
                                                        <td style="text-align: right;"><?php echo $excessDeposit; ?></td>
                                                    </tr>
                                                </tbody>
                                            </table><br>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <span class="alert alert-info">
                                                        <?php
                                                        if ($pensionID == 7) {
                                                        ?>
                                                            <b>FINAL PAYMENT AMOUNT (REVISED) : &#8377; <?php echo $finalPaymentAmount; ?>
                                                                (<?php echo $finalPaymentAmount < 0 ? "-" : "";  ?>Rupees <?php echo trim(convertToWords(abs($finalPaymentAmount))); ?>)
                                                            </b>
                                                        <?php
                                                        } else if ($pensionID == 6) {
                                                        ?>
                                                            <b>BALANCE TRANSFER AMOUNT : &#8377; <?php echo $finalPaymentAmount; ?>
                                                                (<?php echo $finalPaymentAmount < 0 ? "-" : "";  ?>Rupees <?php echo trim(convertToWords(abs($finalPaymentAmount))); ?>)
                                                            </b>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <b>FINAL PAYMENT AMOUNT : &#8377; <?php echo $finalPaymentAmount; ?>
                                                                (<?php echo $finalPaymentAmount < 0 ? "-" : "";  ?>Rupees <?php echo trim(convertToWords(abs($finalPaymentAmount))); ?>)
                                                            </b>
                                                        <?php
                                                        }
                                                        ?>
                                                </div>
                                                <?php
                                                if ($pensionID == 2) {
                                                    if ($dlisAdmissible  == "Yes") {
                                                ?>
                                                        <div class="col-lg-12" style="margin-top: 20px;">
                                                            <span class="alert alert-info"><b>DEPOSIT LINKED INSURENCE SCHEME (DLIS) AMOUNT : &#8377; <?php echo $dlisAmount; ?>
                                                                    (Rupees <?php echo trim(convertToWords($dlisAmount)); ?>)</b> </span>
                                                        </div>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <?php
                                            $remarksSQL = "SELECT * FROM GPF_CALCULATION_REMARKS WHERE REGD_NO='$registrationNo'";
                                            $fetchRemarks = sqlFetchData($connection, $remarksSQL);
                                            if (sizeof($fetchRemarks)) {
                                                foreach ($fetchRemarks as $fetchRemarkList) {
                                                    $remarks = $fetchRemarkList['REMARKS'];
                                                }
                                            ?><br>
                                                <p class="text-danger">
                                                    <b>Remarks : </b> <?php echo $remarks; ?>
                                                </p><br>
                                            <?php
                                            }
                                            ?>
                                            <br><br>
                                            <button class="btn btn-info" onclick="redirectPage('calculation_sheet.php?regd_no=<?php echo $registrationNo; ?>')">
                                                GO TO CALCULATION PAGE
                                            </button>
                                        </div>
                                    </div>
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
}
?>
<script>
    jQuery(document).ready(function() {
        jQuery("#app_check_btn").text("<?php echo $appBtnText; ?>");
        jQuery("#app_check_btn").click(function() {
            const registrationNo = "<?php echo $registrationNo; ?>";

            if ((registrationNo === "") || (registrationNo === null)) {
                swal({
                    title: "Oops!",
                    text: "Please provide registration number",
                    icon: "error",
                    button: "Close",
                });
            } else {
                const btnVal = jQuery("#app_check_btn").text();
                const approveData = {
                    RegistrationNo: registrationNo,
                    BtnValue: btnVal,
                    UserName: "<?php echo $loginUser; ?>"
                }
                jQuery.ajax({
                    type: 'POST',
                    url: 'ajax/approve_case.php',
                    data: JSON.stringify(approveData),
                    success: function(returnValue) {
                        console.log(returnValue);
                        const {
                            StatusCode,
                            Title,
                            Message
                        } = returnValue;

                        if (StatusCode === 200) {
                            window.location.href = "approval_details.php?regd_no=<?php echo $registrationNo; ?>";
                        } else {
                            swal({
                                title: Title,
                                text: Message,
                                icon: "error",
                                button: "Close",
                            });
                        }
                    },
                    error: function(a, b, c) {
                        console.log(a + " " + b + " " + c)
                    }
                });
            }
        });
    });
</script>