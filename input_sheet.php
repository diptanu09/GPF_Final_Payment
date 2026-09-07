<?php
$pageName = "Input sheet report";
require_once "./top.php";
?>
<style>
    .inp_bold {
        font-weight: bold;
    }
</style>
<div class="content-wrap">
    <div class="main">
        <div class="container-fluid">
            <?php
            require_once "./includes/breadcumb.php";
            ?>
            <section id="main-content">
                <?php
                if (isset($_REQUEST['generate_report'])) {
                    $registrationNo = trim($_REQUEST['reg_no']);
                    $signatureFirst = trim($_REQUEST['sig_f']);
                    $signatureSecond = trim($_REQUEST['sig_s']);
                    $signatureThird = trim($_REQUEST['sig_t']);

                    $infoSQL = "SELECT 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, A.PENSION_TYPE, A.MOBILE_NO, C.TITLE, A.SUBSCRIBER_NAME, C.SPOUSE_NAME, K.SECTION_DESCR,
                    A.APPROVED_DATE, C.DESG_TITLE, C.RELATION SPOUSE_RELATION, E.FIN_YEAR OPENING_FIN_YEAR, F.PENSION_SHORT_DESCR, F.PENSION_LONG_DESCR,  A.MOBILE_NO,
                    G.DDO_DESG, G.DDO_CODE, I.TRES_CODE TREASURY_CODE, I.TRES_NAME TREASURY, H.TRES_CODE SUB_TREASURY_CODE, B.CASE_TYPE, Z.DLIS_AMOUNT, C.DEBIT_DURING_YEAR,
                    H.TRES_NAME SUB_TREASURY, C.DATE_OF_EFFECT, C.LAST_FUND_DEDUCTION, Z.INTEREST_ALLOWED_UPTO, A.EMPLOYEE_CODE, C.PERSONAL_ADDRESS, Z.WITHDRAWAL,
                    Z.OPENING_BAL_AMOUNT, A.BENEFICIARY_CODE, Z.FINAL_PAYMENT_AMOUNT, Z.ACTUAL_DEPOSIT, Z.ACTUAL_INTEREST, C.DESIGNATION, Z.DLIS_ADMISSIBLE,
                    C.DATE_OF_LTA, C.LTA_TO_WHOM, Z.EXCESS_DEPOSIT, Z.DELAYED_INTEREST FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD B ON A.REGD_NO=B.REGD_NO INNER JOIN
                    GPF_APPLICATION C ON A.REGD_NO=C.REGD_NO INNER JOIN GPF_AMOUNT_INFO Z ON A.REGD_NO=Z.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES 
                    D ON A.SERIES_ID=D.SERIES_ID INNER JOIN VLCS.MM_FINANCIAL_YEAR E ON A.FIN_YEAR_CODE=E.FIN_YEAR_CODE INNER JOIN MAS_PENSION_TYPE F
                    ON A.PENSION_TYPE=F.PENSION_ID INNER JOIN VLCS.STATE_DDO G ON C.DDO_CODE=G.DDO_CODE INNER JOIN VLCS.STATE_TREASURY H ON
                    C.TREASURY_CODE=H.TRES_CODE INNER JOIN VLCS.STATE_TREASURY I ON H.CNTR_TRES=I.TRES_CODE INNER JOIN MAS_SECTION K ON B.SECTION=K.SECTION_ID
                    WHERE A.REGD_NO='$registrationNo ' AND A.CASE_STATUS!=11 AND ROWNUM =1 ORDER BY B.CREATE_DATE DESC";

                    $fetchInfo = sqlFetchData($connection, $infoSQL);
                    foreach ($fetchInfo as $details) {
                        $sectionName = $details['SECTION_DESCR'];
                        $accountNo = $details['GPF_ACCOUNT_NO'];
                        $subscriberInitial = $details['TITLE'];
                        $subscriberName = $details['SUBSCRIBER_NAME'];
                        $dateOfEffect = $details['DATE_OF_EFFECT'];
                        $desgTitle = $details['DESG_TITLE'];
                        $designation = $details['DESIGNATION'];
                        $caseType = $details['CASE_TYPE'];
                        $spouseName = $details['SPOUSE_NAME'];
                        $spouseRelation = $details['SPOUSE_RELATION'];
                        $personalAddress = $details['PERSONAL_ADDRESS'];
                        $pensionID = $details['PENSION_TYPE'];
                        $pensionType = $details['PENSION_LONG_DESCR'];
                        $lastFundDeduction = $details['LAST_FUND_DEDUCTION'];
                        $ddoDesignation = $details['DDO_DESG'];
                        $ddoCode = $details['DDO_CODE'];
                        $tresuryCode = $details['TREASURY_CODE'];
                        $treasury = $details['TREASURY'];
                        $subTresuryCode = $details['SUB_TREASURY_CODE'];
                        $subTresury = $details['SUB_TREASURY'];
                        $approvedDate = $details['APPROVED_DATE'];
                        $interestAllowed = $details['INTEREST_ALLOWED_UPTO'];
                        $dateOfLTA = $details['DATE_OF_LTA'];
                        $ltaWhom = $details['LTA_TO_WHOM'];
                        $mobileNo = $details['MOBILE_NO'];
                        $employeeCode = $details['EMPLOYEE_CODE'];
                        $beneficiaryCode = $details['BENEFICIARY_CODE'];
                        $openingFinYear = $details['OPENING_FIN_YEAR'];
                        $openingBalance = $details['OPENING_BAL_AMOUNT'];
                        $withdrawlAmount = $details['WITHDRAWAL'];
                        $finalPaymentAmount = $details['FINAL_PAYMENT_AMOUNT'];
                        $actualDeposit = $details['ACTUAL_DEPOSIT'];
                        $actualInterest = $details['ACTUAL_INTEREST'];
                        $excessDeposit = $details['EXCESS_DEPOSIT'];
                        $delayedInterest = $details['DELAYED_INTEREST'];
                        $dlisAmount = $details['DLIS_AMOUNT'];
                        $dlisAdmissible = $details['DLIS_AMOUNT'] == "Y" ? "Yes" : "No";
                        $debitDuringYear = $details['DEBIT_DURING_YEAR'] == "" ? "" : "&#8377; " . $details['DEBIT_DURING_YEAR'];
                    }
                    $missingCreditSQL = "SELECT * FROM GPF_MISSING_CREDIT WHERE REGD_NO=$registrationNo ORDER BY SLIP_DATE";
                    $fetchMissingCredit = sqlFetchData($connection, $missingCreditSQL);
                    foreach ($fetchMissingCredit as $misCrdetails) {
                        $slipDate[] = date("M-Y", strtotime($misCrdetails['SLIP_DATE']));
                    }
                    if ($slipDate == null || sizeof($slipDate) == 0) {
                        $misCreMonths = "";
                    } else {
                        $misCreMonths = implode(", ", $slipDate);
                    }
                ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <button id="upload_btn" onclick="download()" class="btn btn-primary">DOWNLOAD</button>

                                    <div class="row">
                                        <div class="col-lg-2"></div>
                                        <div class="col-lg-8" id="printArea">
                                            <table width="100%" cellpadding="5px" cellspacing="0" border="0">
                                                <tr>
                                                    <td width="8%" align="right" valign="top"><img src="<?php echo getHostLink('GPF_Final_Payment'); ?>/assets/images/cag_logo.png" width="64" height="64" /></td>
                                                    <td width="92%">
                                                        <center>
                                                            <span style="font-size: 20px; font-weight: bold;">महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला</span><br />
                                                            <span style="font-weight: bold;font-size: 14px;">OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA</span>
                                                        </center>
                                                    </td>
                                                    <td width="8%" align="left" valign="top"><img src="<?php echo getHostLink('GPF_Final_Payment'); ?>/assets/images/ashok_stambh.png" width="44" height="64" /></td>

                                                </tr>
                                            </table>
                                            <hr>
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td style="width: 60%; text-align:left;">
                                                        No. <?php echo $sectionName; ?> / FP / Input sheet / <?php echo $caseType == "R" ? "Revised / " : ""; ?> <?php echo $pensionType; ?> / <?php echo $registrationNo; ?> /
                                                    </td>
                                                    <td style="width: 40%; text-align:right;">Date : <?php echo date("d / m / Y"); ?></td>
                                                </tr>
                                            </table><br>

                                            <div style="text-align:justify;padding:5px; word-spacing:0px;">
                                                <div style="padding-left: 0px;">
                                                    <div style="font-weight:bold; text-align:center;">
                                                        BASIC INFORMATION
                                                    </div><br>
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <td style="text-align:left;"><span class="inp_bold">Name :</span> <?php echo $nameTitle . " " . $subscriberName; ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">Account no. :</span> <?php echo $accountNo; ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">Designation :</span> <?php echo $desgTitle . " " . $designation; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:left;"><span class="inp_bold">Employee code :</span> <?php echo $employeeCode; ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">Beneficiary code :</span> <?php echo $beneficiaryCode; ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">Mobile number :</span> <?php echo $mobileNo; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3" style="text-align:left;"><span class="inp_bold">Personal address :</span> <?php echo $personalAddress; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3" style="text-align:left;"><span class="inp_bold">DDO address :</span> <?php echo $ddoCode; ?> - <?php echo $ddoDesignation; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:left;"><span class="inp_bold">Treasury :</span> <?php echo $treasury ?><?php echo $subTresuryCode == $tresuryCode ? " ($tresuryCode)" : ", payable to " . $subTresury . " ($subTresuryCode)" ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">Date of <?php echo $pensionType; ?> :</span> <?php echo $dateOfEffect == "" ? "" : date("d/m/Y", strtotime($dateOfEffect)); ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">Last fund deduction :</span> <?php echo $lastFundDeduction == "" ? "" : date("F, Y", strtotime($lastFundDeduction)); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:left;"><span class="inp_bold">Interest allowed upto :</span> <?php echo $interestAllowed == "" ? "" : date("F, Y", strtotime($interestAllowed)); ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">DLIS Admissible :</span> <?php echo $dlisAdmissible; ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">Debit during the year :</span> <?php echo $debitDuringYear; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:left;"><span class="inp_bold">Spouse name with relation :</span> <?php echo $spouseName; ?> - <?php echo $spouseRelation; ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">Date of death after retirement :</span> <?php echo $dateOfLTA == "" ? "" : date("d/m/Y", strtotime($dateOfLTA)); ?></td>
                                                            <td style="text-align:left;"><span class="inp_bold">LTA to whom :</span> <?php echo $ltaWhom; ?></td>
                                                        </tr>
                                                    </table>
                                                </div><br>
                                                <div style="font-weight:bold; text-align:center;">
                                                    FINANCIAL INFORMATION
                                                </div><br />
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th class="inp_bold" style="text-align:center;">PARTICULARS</th>
                                                        <th class="inp_bold" style="text-align:center;">AMOUNT (in &#8377;)</th>
                                                        <th class="inp_bold" style="text-align:center;">MISSING CREDITS</th>
                                                    </tr>
                                                    <tr>
                                                        <td>OPENING BALANCE</td>
                                                        <td style="text-align:right;"><?php echo $openingBalance; ?></td>
                                                        <td rowspan="5">
                                                            <?php
                                                            echo $misCreMonths;
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>DEPOSIT</td>
                                                        <td style="text-align:right;"><?php echo ($actualDeposit + $excessDeposit); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>INTEREST</td>
                                                        <td style="text-align:right;"><?php echo ($actualInterest + $delayedInterest); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>WITHDRAWAL</td>
                                                        <td style="text-align:right;"><?php echo $withdrawlAmount; ?></td>
                                                    </tr>
                                                    <tr style="font-weight: bolder;">
                                                        <td>CLOSING BALANCE</td>
                                                        <td style="text-align:right;">
                                                            <?php
                                                            echo ($openingBalance + $actualDeposit + $excessDeposit + $actualInterest + $delayedInterest - $withdrawal);
                                                            ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <div style="margin-top: 20px;">
                                                    <span style="float:left;">
                                                        <b>FINAL PAYMENT AMOUNT : </b> &#8377; <?php echo $finalPaymentAmount; ?>
                                                    </span>
                                                    <?php
                                                    if ($pensionID == 2) {
                                                    ?>
                                                        <span style="float:right;">
                                                            <b>DEPOSIT LINKED INSURENCE SCHEME AMOUNT : </b> &#8377; <?php echo $dlisAmount; ?>
                                                        </span>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                                <table class="table" style="margin-top:100px;">
                                                    <tr>
                                                        <th style="text-align: center;" class="inp_bold"><?php echo $signatureFirst; ?></th>
                                                        <th style="text-align: center;" class="inp_bold"><?php echo $signatureSecond; ?></th>
                                                        <th style="text-align: center;" class="inp_bold"><?php echo $signatureThird; ?></th>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                } else {
                ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">

                                    <b class="text-<?php echo $textColor; ?>">
                                        <?php echo $message; ?>
                                    </b>
                                    <br>
                                    <form action="" method="post">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="regd_no">Registration number<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input required type="text" placeholder="Registration number" name="reg_no" class="form-control" value="">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="sig_s">Second Signature<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input required type="text" placeholder="Second signature" name="sig_s" class="form-control" value="Supervisor">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="generate_report" id="generate_report" tabindex="15">Generate report</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="sig_f">First Signature<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input required type="text" placeholder="First signature" name="sig_f" class="form-control" value="Dealing Assistant">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="sig_t">Third Signature<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input required type="text" placeholder="Third signature" name="sig_t" class="form-control" value="Sr. AO">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
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
<script type="text/javascript">
    const download = () => {
        let registrationNo = "<?php echo $registrationNo; ?>";
        let authority = $("#printArea").html();

        let authorityData = {
            RegistrationNo: registrationNo,
            Authority: authority,
            AuthorityType: "input_sheet"
        }
        jQuery.ajax({
            type: 'POST',
            url: 'ajax/other_reports.php',
            data: JSON.stringify(authorityData),
            success: function(returnValue) {

                const {
                    StatusCode,
                    Message,
                    Link
                } = returnValue;

                if (StatusCode === 200) {
                    window.open(Link, '_blank');
                } else {
                    swal({
                        title: "Oops!",
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
</script>