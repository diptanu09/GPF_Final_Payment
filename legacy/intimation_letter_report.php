<?php
$pageName = "Intimation Letter";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);
    $intimationSQL = "SELECT 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, A.MOBILE_NO, C.TITLE, A.SUBSCRIBER_NAME, C.SPOUSE_NAME, K.SECTION_DESCR,
    A.APPROVED_DATE, C.DESG_TITLE, C.RELATION SPOUSE_RELATION, E.FIN_YEAR OPENING_FIN_YEAR, F.PENSION_SHORT_DESCR, B.APPLIED_DATE, B.LETTER_NO,
    G.DDO_DESG, G.DDO_CODE, I.TRES_CODE TREASURY_CODE, I.TRES_NAME TREASURY, H.TRES_CODE SUB_TREASURY_CODE, B.CASE_TYPE,
    H.TRES_NAME SUB_TREASURY, Z.INTEREST_ALLOWED_UPTO, A.EMPLOYEE_CODE, C.PERSONAL_ADDRESS, Z.WITHDRAWAL,
    Z.OPENING_BAL_AMOUNT, A.BENEFICIARY_CODE, Z.FINAL_PAYMENT_AMOUNT, Z.ACTUAL_DEPOSIT, Z.ACTUAL_INTEREST, C.DESIGNATION,
    Z.EXCESS_DEPOSIT, Z.DELAYED_INTEREST FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD B ON A.REGD_NO=B.REGD_NO INNER JOIN
    GPF_APPLICATION C ON A.REGD_NO=C.REGD_NO INNER JOIN GPF_AMOUNT_INFO Z ON A.REGD_NO=Z.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES 
    D ON A.SERIES_ID=D.SERIES_ID INNER JOIN VLCS.MM_FINANCIAL_YEAR E ON A.FIN_YEAR_CODE=E.FIN_YEAR_CODE INNER JOIN MAS_PENSION_TYPE F
    ON A.PENSION_TYPE=F.PENSION_ID INNER JOIN VLCS.STATE_DDO G ON C.DDO_CODE=G.DDO_CODE INNER JOIN VLCS.STATE_TREASURY H ON
    C.TREASURY_CODE=H.TRES_CODE INNER JOIN VLCS.STATE_TREASURY I ON H.CNTR_TRES=I.TRES_CODE INNER JOIN MAS_SECTION K ON B.SECTION=K.SECTION_ID
    WHERE A.REGD_NO='$registrationNo' AND A.CASE_STATUS!=11 AND ROWNUM =1 ORDER BY B.CREATE_DATE DESC";

    $fetchInfo = sqlFetchData($connection, $intimationSQL);
    foreach ($fetchInfo as $details) {
        $letterNo = $details['LETTER_NO'];
        $appliedDate = $details['APPLIED_DATE'];
        $sectionName = $details['SECTION_DESCR'];
        $accountNo = $details['GPF_ACCOUNT_NO'];
        $subscriberInitial = $details['TITLE'];
        $subscriberName = $details['SUBSCRIBER_NAME'];
        $desgTitle = $details['DESG_TITLE'];
        $designation = $details['DESIGNATION'];
        $caseType = $details['CASE_TYPE'];
        $spouseName = $details['SPOUSE_NAME'];
        $spouseRelation = $details['SPOUSE_RELATION'];
        $personalAddress = $details['PERSONAL_ADDRESS'];
        $pensionType = $details['PENSION_SHORT_DESCR'];
        $ddo = $details['DDO_DESG'];
        $ddoCode = $details['DDO_CODE'];
        $tresuryCode = $details['TREASURY_CODE'];
        $treasury = $details['TREASURY'];
        $subTresuryCode = $details['SUB_TREASURY_CODE'];
        $subTresury = $details['SUB_TREASURY'];
        $approvedDate = $details['APPROVED_DATE'];
        $interestAllowed = $details['INTEREST_ALLOWED_UPTO'];
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

    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <?php
                require_once "./includes/breadcumb.php";
                ?>
                <section id="main-content">
                    <?php
                    if (isset($_REQUEST['generate_report'])) {
                        $signature = trim($_REQUEST['signature']);
                    ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <button id="upload_btn" onclick="download()" class="btn btn-primary">
                                            DOWNLOAD</button>
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
                                                <hr />
                                                <table style="margin-top: 20px; margin-bottom:10px; width:100%;">
                                                    <tr>
                                                        <td style="text-align: left; width:70%;">
                                                            No. <?php echo $sectionName; ?> / FP / Intimation / <?php echo $caseType == "R" ? "Revised / " : ""; ?> <?php echo $pensionType; ?> / <?php echo $openingFinYear; ?> / <?php echo $registrationNo; ?> /
                                                        <td style="text-align: right; width:30%;">Date : <?php echo date("d / m / Y");
                                                                                                            ?></td>
                                                    </tr>
                                                </table>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">
                                                    <div style="text-align: center; text-decoration: underline; font-weight: bold;">
                                                        <span style="text-decoration:underline;">Annexure - 5.24</span><br /><br />
                                                        <span style="text-decoration:underline;">Intimation to subscribers on issue of authorization</span>
                                                    </div>
                                                    <div style="padding-left: 20px;margin-bottom:20px;">

                                                        To<br />
                                                        <?php echo $subscriberInitial . " " . $subscriberName; ?><br /><br />
                                                        <span style="font-weight: bold;">Subject : </span>Final Payment in the accumulation in the General Provident Fund
                                                        Account of <span style="font-weight: bold;"><?php echo $subscriberInitial . " " . $subscriberName; ?></span>, <span style="font-weight: bold;">
                                                            Account no. <?php echo $accountNo; ?></span><br /><br />

                                                        <table style="width: 100%;">
                                                            <tr>
                                                                <td><span style="font-weight:bold;">Ref no. : </span> <?php echo $letterNo; ?> </td>
                                                                <td><span style="font-weight:bold;">Dated : </span> <?php echo date("d/m/Y", strtotime($appliedDate)); ?></td>
                                                            </tr>
                                                        </table><br />
                                                        Sir / Madam, <br /><br />

                                                        Neccessary authoiztion for the payment of <span style="font-weight:bold;">&#8377; <?php echo $finalPaymentAmount; ?>/-
                                                            (Rupees <?php echo trim(convertToWords($finalPaymentAmount)) ?>)</span> has been issued in this office vide this office letter no.
                                                        <span style="font-weight:bold;"><?php echo $sectionName; ?> / FP / <?php echo $caseType == "R" ? "Revised / " : ""; ?> <?php echo $pensionType; ?> / <?php echo $openingFinYear; ?> / <?php echo $registrationNo; ?> </span>
                                                        dated <span style="font-weight:bold;"><?php echo date("d/m/Y", strtotime($approvedDate)); ?></span>. <br />
                                                        The authorization is currently <span style="font-weight:bold;">six months</span> from the date of its issue. Please contact the officer concerned
                                                        and take the payment within the period of currency of the authorization. <br /><br />

                                                        2. The following credits have not been authorized and an authorization for payment of the residual balance will be issued in due course
                                                        on receipt of particulars of missing credits.<br />
                                                        <span style="font-weight:bold;"><?php echo $misCreMonths . "<br/>"; ?></span>
                                                        Please furnish the details of credits for the above months within 90 days through the Departmental officer to enable
                                                        this office to trace the missing credits and issue authorization for the residual balance, if any due.<br /><br />

                                                        Certificate of non-drawal of withdrawal/advance after <span style="font-weight:bold;"><?php echo date("d/m/Y", strtotime($appliedDate)); ?></span> may be attached to the bill.

                                                        <div style="text-align:right; margin-top:50px;">
                                                            <div style="font-weight: bold;"> <?php echo $signature; ?></div>
                                                        </div>
                                                    </div>
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
                                                        <label for="signature">Signature <b class="text-danger"> * <?php echo $sectionErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Signature" name="signature" tabindex="11" id="signature" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                echo $section;
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo "Accounts Officer/PF(FP)";
                                                                                                                                                                                            } ?>">

                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary" name="generate_report" id="generate_report" tabindex="15">Generate report</button>

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
                AuthorityType: "intimation_letter"
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
<?php
}
?>