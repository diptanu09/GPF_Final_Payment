<?php
$pageName = "Authority report";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);

    $infoSQL = "SELECT 'T/'||E.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, C.TITLE, A.SUBSCRIBER_NAME, C.SPOUSE_NAME, A.APPROVED_DATE, C.DESIGNATION, C.DESG_TITLE, K.FIN_YEAR OPENING_FIN_YEAR,
                C.RELATION SPOUSE_RELATION, D.DLIS_AMOUNT, F.PENSION_SHORT_DESCR,  G.DDO_DESG, G.DDO_CODE, I.TRES_CODE TREASURY_CODE, I.TRES_NAME TREASURY,
                H.TRES_CODE SUB_TREASURY_CODE, H.TRES_NAME SUB_TREASURY, C.PERSONAL_ADDRESS, J.SECTION_DESCR FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD B ON A.REGD_NO=B.REGD_NO
                INNER JOIN GPF_APPLICATION C ON A.REGD_NO=C.REGD_NO INNER JOIN GPF_AMOUNT_INFO D ON A.REGD_NO=D.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES E
                ON A.SERIES_ID=E.SERIES_ID INNER JOIN MAS_PENSION_TYPE F ON A.PENSION_TYPE=F.PENSION_ID INNER JOIN VLCS.STATE_DDO G
                ON C.DDO_CODE=G.DDO_CODE INNER JOIN VLCS.STATE_TREASURY H ON C.TREASURY_CODE=H.TRES_CODE INNER JOIN VLCS.STATE_TREASURY I 
                ON H.CNTR_TRES=I.TRES_CODE INNER JOIN MAS_SECTION J ON B.SECTION=J.SECTION_ID INNER JOIN VLCS.MM_FINANCIAL_YEAR K ON a.FIN_YEAR_CODE=K.FIN_YEAR_CODE
                WHERE A.REGD_NO=$registrationNo AND A.CANCELED_DATE IS NULL";

    $fetchInfo = sqlFetchData($connection, $infoSQL);
    foreach ($fetchInfo as $details) {
        $sectionName = $details['SECTION_DESCR'];
        $accountNo = $details['GPF_ACCOUNT_NO'];
        $subscriberInitial = $details['TITLE'];
        $subscriberName = $details['SUBSCRIBER_NAME'];
        $desgTitle = $details['DESG_TITLE'];
        $designation = $details['DESIGNATION'];
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
        $openingFinYear = $details['OPENING_FIN_YEAR'];
        $dlisAmount = $details['DLIS_AMOUNT'];
    }


    $qrText = 'Office of the Accountant General ( A & E ), Tripura. Your Registration number : ' . $registrationNo .
        '. Date of approval : ' . date("d/m/Y", strtotime($approvedDate)) . '. Your dlis amount : Rs. ' . $dlisAmount;
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
                                                        No. <?php echo $sectionName; ?> / DLIS / <?php echo $caseType == "R" ? "Revised / " : ""; ?> <?php echo $pensionType; ?> / <?php echo $openingFinYear; ?> / <?php echo $registrationNo; ?> /
                                                    </td>
                                                    <td style="width: 40%; text-align:right;">Date : <?php echo date("d / m / Y", strtotime($approvedDate)); ?></td>
                                                </tr>
                                            </table><br>
                                            <div style="text-align: center; font-weight:bold; text-decoration:underline;">Authorization letter</div>
                                            <div style="text-align:justify; line-height:1.8em; padding:10px;">
                                                <!-- <h1 id="watermark" style="font-size:30px;" class="watermark">DEPOSIT LINKED INSURENCE SCHEME AUTHORITY</h1> -->
                                                In pursuance of Govt. of Tripura Finance Department O.M. No. F.12(7)/FIN(G)/75 dated 18-02-76, the authorization for
                                                <span style="font-weight:bold;">&#8377; <?php echo $dlisAmount; ?>/- (Rupees <?php echo trim(convertToWords($dlisAmount)) ?>)</span> only is hereby accorded towards Deposit Linked Insurance Scheme to
                                                <span style="font-weight:bold;"><?php echo $spouseName . " " . $spouseRelation; ?></span> of <span style="font-weight:bold;"><?php echo $subscriberInitial . " " . $subscriberName; ?></span>, <span style="font-weight:bold;"><?php echo $desgTitle . " " . $designation; ?></span>,
                                                <span style="font-weight:bold;">Account no. <?php echo $accountNo; ?></span>. <br /><br />
                                                2. The payment is debitable to the head of account 2235- Social Security and welfare, 60 other Social Security and Welfare programme, 104 - Deposit Linked Insurance Scheme Govt. Provident Fund.<br /><br />
                                                3. The authority shall remain valid for six months from the date of issue. In case of non-payment, the authority in original may be refunded to the undersigned for necessary action. <br /><br />
                                                4. A certificate to the effect that the amount has been drawn and duly disbursed to the entitled person/persons may please be to this office within one month from the date of drawal.<br /><br />
                                                5. The receipt of the authority may please be acknowledged.<br /><br />
                                                <div style="text-align: right;">Authorized Signatory</div> <br />
                                                <table style="width:100%">
                                                    <tr>
                                                        <td width="90%" valign="top">
                                                            Copy forwarded for information and necessary action to :-<br /><br />
                                                            1. Treasury Officer - <?php echo $treasury ?><?php echo $subTresuryCode == $tresuryCode ? " ($tresuryCode)" : ", payable to " . $subTresury . " ($subTresuryCode)" ?>.<br><br>
                                                            2. <?php echo $ddo; ?> (<?php echo $ddoCode; ?>) <br><br>
                                                            3. <?php echo $spouseName . ", " . $spouseRelation; ?> of
                                                            <?php echo $subscriberInitial . " " . $subscriberName; ?>, <?php echo $desgTitle . " " . $designation; ?>, <?php echo $personalAddress; ?>

                                                        </td>
                                                        <td width="10%" valign="top" align="right">

                                                            <img src="<?php echo $qrCodeImage; ?>" width=100 height=100 />
                                                        </td>

                                                    </tr>
                                                </table>
                                                <div style="text-align: right;">
                                                    Authorized Signatory
                                                </div>
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
            AuthorityType: "dlis"
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
                    const urlLink = `digital_signature.php?regd_no=${registrationNo}&&type=dlis`;
                    window.location.href = urlLink;
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