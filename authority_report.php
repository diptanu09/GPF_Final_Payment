<?php
$pageName = "Authority report";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);

    $infoSQL = "SELECT 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, A.MOBILE_NO, C.TITLE, A.SUBSCRIBER_NAME, C.SPOUSE_NAME, K.SECTION_DESCR,
    A.APPROVED_DATE, C.DESG_TITLE, C.RELATION SPOUSE_RELATION, E.FIN_YEAR OPENING_FIN_YEAR, F.PENSION_SHORT_DESCR, 
    G.DDO_DESG, G.DDO_CODE, I.TRES_CODE TREASURY_CODE, I.TRES_NAME TREASURY, H.TRES_CODE SUB_TREASURY_CODE, B.CASE_TYPE,
    H.TRES_NAME SUB_TREASURY, Z.INTEREST_ALLOWED_UPTO, A.EMPLOYEE_CODE, C.PERSONAL_ADDRESS, Z.WITHDRAWAL,
    Z.OPENING_BAL_AMOUNT, A.BENEFICIARY_CODE, Z.FINAL_PAYMENT_AMOUNT, Z.ACTUAL_DEPOSIT, Z.ACTUAL_INTEREST, C.DESIGNATION,
    Z.EXCESS_DEPOSIT, Z.DELAYED_INTEREST FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD B ON A.REGD_NO=B.REGD_NO INNER JOIN
    GPF_APPLICATION C ON A.REGD_NO=C.REGD_NO INNER JOIN GPF_AMOUNT_INFO Z ON A.REGD_NO=Z.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES 
    D ON A.SERIES_ID=D.SERIES_ID INNER JOIN VLCS.MM_FINANCIAL_YEAR E ON A.FIN_YEAR_CODE=E.FIN_YEAR_CODE INNER JOIN MAS_PENSION_TYPE F
    ON A.PENSION_TYPE=F.PENSION_ID INNER JOIN VLCS.STATE_DDO G ON C.DDO_CODE=G.DDO_CODE INNER JOIN VLCS.STATE_TREASURY H ON
    C.TREASURY_CODE=H.TRES_CODE INNER JOIN VLCS.STATE_TREASURY I ON H.CNTR_TRES=I.TRES_CODE INNER JOIN MAS_SECTION K ON B.SECTION=K.SECTION_ID
    WHERE A.REGD_NO='$registrationNo' AND A.CASE_STATUS!=11 AND ROWNUM =1 ORDER BY B.CREATE_DATE DESC";

    $fetchInfo = sqlFetchData($connection, $infoSQL);
    foreach ($fetchInfo as $details) {
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
        $misCreMonths = "nil";
    } else {
        $misCreMonths = implode(", ", $slipDate);
    }

    $qrText = 'Office of the Accountant General ( A & E ), Tripura. Your Registration number : ' . $registrationNo .
        '. Date of approval : ' . date("d/m/Y", strtotime($approvedDate)) . '. Your final payment amount : Rs. ' . $finalPaymentAmount;
    $fileName = $registrationNo . "_authority";
    $setQRCodeImage = "assets/images/qr/" . $fileName . ".png";
    $qrCodeImage = getHostLink("GPF_Final_Payment") . "/assets/images/qr/" . $fileName . ".png";
    $ecc = "L";
    $pixelSize = 15;
    $frameSize = 5;
    QRcode::png($qrText, $setQRCodeImage, $ecc, $pixelSize, $frameSize);
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
                                    <button id="upload_btn" onclick="download()" class="btn btn-primary">CLICK FOR DIGITAL SIGNATURE</button>
                                    <div class="row">
                                        <div class="col-lg-2"></div>
                                        <div class="col-lg-8" id="printArea">
                                            <div style="text-align: right;color:#C0C0C0;font-style:italic;"><?php echo $caseType == "R" ? "REVISED" : ""; ?></div>
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
                                                        No. <?php echo $sectionName; ?> / FP / <?php echo $caseType == "R" ? "Revised / " : ""; ?> <?php echo $pensionType; ?> / <?php echo $openingFinYear; ?> / <?php echo $registrationNo; ?> /
                                                    </td>
                                                    <td style="width: 40%; text-align:right;">Date : <?php echo date("d / m / Y", strtotime($approvedDate)); ?></td>
                                                </tr>
                                            </table><br>
                                            <div style="text-align: center; font-weight:bold; text-decoration:underline;">Authorization letter</div>
                                            <div style="text-align:justify; line-height:1.8em; padding:10px;">
                                                <!-- <div class="rotate">Below Rupees <?php //echo trim(convertToWords($finalPaymentAmount + 1))
                                                                                        ?> only</div> -->
                                                In terms of Rule 31 / 32 / 33 of Central GPF Rule 1960 ( As adopted by the state ) / Rule 28 of All India
                                                Service GPF Rules 1955, as applicable, the authorization for payment of <span style="font-weight:bold;">&#8377; <?php echo $finalPaymentAmount; ?>/-
                                                    (Rupees <?php echo trim(convertToWords($finalPaymentAmount)) ?>)</span> only is hereby accorded towards final
                                                withdrawal from GPF account of <span style="font-weight:bold;"><?php echo $subscriberInitial . " " . $subscriberName; ?></span>, <span style="font-weight:bold;"><?php echo $desgTitle . " " . $designation; ?></span>,
                                                <span style="font-weight:bold;">Account no. <?php echo $accountNo; ?><?php echo $employeeCode == "---" ? "" : ", Employee code: " . $employeeCode; ?><?php echo $beneficiaryCode == "---" ? "" : ", Beneficiary code: " . $beneficiaryCode; ?> </span>
                                                with interest calculated upto <span style="font-weight:bold;"><?php echo date("F, Y", strtotime($interestAllowed)); ?></span>.<br />
                                                2. Authority for the payment of the residual balance, if any,
                                                will be issued as soon as credit(s) for <?php echo $misCreMonths; ?> is/are traced and adjusted
                                                in his/her ledger account. <br />
                                                3. The payment is debitable to the head of account 8009-01-101
                                                (for Government of Tripura employees) and 8009-01-104 (for All India
                                                Service Officers). <br />
                                                4. The payable sum has been worked out as follows :<br /><br />
                                                <table style="text-align:center; width:100%" border="1" cellspacing=0 cellpadding="10px">
                                                    <tr>
                                                        <th width="30%" style="font-weight: bold;">O.B. as at the begining of the year <br />
                                                            ( Rs )</th>
                                                        <th width="30%" style="font-weight: bold;">Subscription / Refund during the year <br />( Rs )</th>
                                                        <th width="10%" style="font-weight: bold;">Withdrawal / Advance<br />( Rs )</th>
                                                        <th width="10%" style="font-weight: bold;">Interest<br />( Rs )</th>
                                                        <th width="20%" style="font-weight: bold;">Closing balance<br />( Rs )</th>
                                                    </tr>
                                                    <tr>
                                                        <td width="20%"><?php echo $openingBalance; ?></td>
                                                        <td width="20%"><?php echo ($actualDeposit + $excessDeposit); ?></td>
                                                        <td width="20%"><?php echo $withdrawlAmount; ?></td>
                                                        <td width="20%"><?php echo ($actualInterest + $delayedInterest); ?></td>
                                                        <td width="20%"><?php echo $finalPaymentAmount; ?></td>
                                                    </tr>
                                                </table><br />
                                                <span style="font-weight:bold;">5. Payment is subject to adjustment of any payment made by the DDO between the date of forwarding the application to the date of actual payment.<br />
                                                </span>
                                                6. The whole amount may be paid to <?php
                                                                                    if ($pensionType == "FAM") {
                                                                                        echo $spouseName . ', ' . $spouseRelation . ' of ' . $subscriberInitial . " " . $subscriberName;
                                                                                    } else {
                                                                                        echo $subscriberInitial . " " . $subscriberName;
                                                                                    }
                                                                                    ?>.

                                                <br />
                                                7. The amount is payable on or after <span style="font-weight:bold;"><?php echo date("d-M-Y", strtotime($interestAllowed)); ?></span> only and authorization is valid for six months from the date of issue.<br /><br />
                                                <div style="text-align: right;">Authorized Signatory</div>

                                                <table style="width: 100%;">
                                                    <tr>
                                                        <td width="80%" valign="top" style="text-align:left ;">
                                                            Copy forwarded for information and necessary action to :-<br /><br />
                                                            1. Treasury Officer - <?php echo $treasury ?><?php echo $subTresuryCode == $tresuryCode ? " ($tresuryCode)" : ", payable to " . $subTresury . " ($subTresuryCode)" ?>.<br /><br />
                                                            2. <?php echo $ddo; ?> (<?php echo $ddoCode; ?>).<br /><br />
                                                            3. <?php
                                                                if ($pensionType == "FAM") {
                                                                    echo $spouseName . ', ' . $spouseRelation . ' of ' . $subscriberInitial . " " . $subscriberName;
                                                                } else {
                                                                    echo $subscriberInitial . " " . $subscriberName;
                                                                }
                                                                ?>, <?php echo $desgTitle . " " . $designation; ?>, <?php echo $personalAddress; ?>. <br /><br />
                                                            Mobile: <?php echo $mobileNo; ?>

                                                        </td>
                                                        <td width="20%" valign="top" style="text-align:right ;">

                                                            <img src="<?php echo $qrCodeImage;
                                                                        ?>" style="width:100px; height:100px;" />
                                                        </td>

                                                    </tr>
                                                </table>
                                                <div style="text-align: right;">Authorized Signatory</div>
                                            </div>
                                            </span>
                                            </ol>
                                        </div>
                                    </div>
                                    <div class="col-lg-2"></div>
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

<script type="text/javascript">
    const download = () => {
        let registrationNo = "<?php echo $registrationNo; ?>";
        let authority = $("#printArea").html();

        let authorityData = {
            RegistrationNo: registrationNo,
            Authority: authority,
            AuthorityType: "authority"
        }
        jQuery.ajax({
            type: 'POST',
            url: 'ajax/authority.php',
            data: JSON.stringify(authorityData),
            success: function(returnValue) {

                const {
                    StatusCode,
                    Message,
                    Link
                } = returnValue;

                if (StatusCode === 200) {
                    const urlLink = `digital_signature.php?regd_no=${registrationNo}&&type=authority`;
                    window.location.href = urlLink;
                    // window.open(Link, '_blank');
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