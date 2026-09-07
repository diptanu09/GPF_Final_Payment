<?php
$pageName = "Corrigendum report";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);
    $applicationQuery = "SELECT 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, A.SUBSCRIBER_NAME, H.SECTION_DESCR, B.DDO_CODE, 
                        C.PENSION_SHORT_DESCR, I.FINAL_PAYMENT_AMOUNT, F.DDO_DESG, B.PERSONAL_ADDRESS, E.FIN_YEAR, B.DESG_TITLE,
                        B.DESIGNATION FROM GPF_CASE_STATUS A INNER JOIN GPF_APPLICATION B ON A.REGD_NO=B.REGD_NO
                        INNER JOIN MAS_PENSION_TYPE C ON A.PENSION_TYPE=C.PENSION_ID INNER JOIN VLCS.MM_GPF_SERIES D 
                        ON A.SERIES_ID=D.SERIES_ID INNER JOIN VLCS.MM_FINANCIAL_YEAR E ON A.FIN_YEAR_CODE=E.FIN_YEAR_CODE 
                        INNER JOIN VLCS.STATE_DDO F ON B.DDO_CODE=F.DDO_CODE INNER JOIN GPF_INWARD G ON A.REGD_NO=G.REGD_NO
                        INNER JOIN MAS_SECTION H ON G.SECTION=H.SECTION_ID LEFT JOIN GPF_AMOUNT_INFO I ON A.REGD_NO=I.REGD_NO
                        WHERE a.REGD_NO='$registrationNo'";

    $fetchApplicationData = sqlFetchData($connection, $applicationQuery);
    foreach ($fetchApplicationData as $fetchApplicationList) {
        $fsectionName = $fetchApplicationList['SECTION_DESCR'];
        $fAccountNo = $fetchApplicationList['GPF_ACCOUNT_NO'];
        $fDesignation = $fetchApplicationList['DESIGNATION'];
        $fDesignationTitle = $fetchApplicationList['DESG_TITLE'];
        $fpensionType = $fetchApplicationList['PENSION_SHORT_DESCR'];
        $fsubscriberName = $fetchApplicationList['SUBSCRIBER_NAME'];
        $fddoName = $fetchApplicationList['DDO_DESG'];
        $fddoCode = $fetchApplicationList['DDO_CODE'];
        $fpersonalAddress = $fetchApplicationList['PERSONAL_ADDRESS'];
        $fFinYear = $fetchApplicationList['FIN_YEAR'];
        $fFinalPayment = $fetchApplicationList['FINAL_PAYMENT_AMOUNT'];
    }

    $fmatter = "Please read the name of treasury as <> instead of <> wherever appeared in the GPF Final Payment Authority for Rs. $fFinalPayment issued in favour of $fsubscriberName, $fDesignationTitle $fDesignation of GPF A/c No. $fAccountNo from this office vide no. $fsectionName / FP / $fpensionType / $fFinYear / $registrationNo /  dated ().
               
    Other terms and conditions will be remain unchanged.";

    $fmemoNo  = "$fsectionName / FP / $fpensionType / $fFinYear / $registrationNo /";

    $fCopyTo = "1. Write your matter here.

               2. Write your matter here.

               3. $fddoName ($fddoCode) - for making necessary payment.
               
               4. $fpersonalAddress";
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
                        $matter = trim($_REQUEST['matter']);
                        $copyTo = trim($_REQUEST['copy_to']);
                        $signature = trim($_REQUEST['signature']);
                        $memoNo = trim($_REQUEST['memo_no']);
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
                                                <hr>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">
                                                    <!-- <div id="logo_watermark" class="logo_watermark"><img src="./assets/images/cag_logo.png" /></div> -->
                                                    <div style="text-align: center; font-weight:bold;">CORRIGENDUM</div><br>
                                                    <?php echo nl2br($matter); ?><br><br>
                                                    <div style="text-align: right;">Yours faithfully<br /><br />
                                                        <div style="font-weight:bold;"> <?php echo $signature; ?></div>
                                                    </div>
                                                    </p><br />

                                                    <table style="margin-top: 100px; margin-bottom:10px; width:100%;">
                                                        <tr>
                                                            <td style="text-align: left; width:60%;">Memo no. <?php echo $memoNo; ?></td>
                                                            <td style="text-align: right; width:40%;">Date : <?php echo date("d / m / Y");
                                                                                                                ?></td>
                                                        </tr>
                                                    </table>
                                                    <table>
                                                        <tr>
                                                            <td width="70%">
                                                                <div style="font-weight:bold;">Copy forwarded for information and neccessary action
                                                                    to :- </div><br />
                                                                <?php
                                                                echo nl2br($copyTo);
                                                                ?>
                                                            </td>
                                                            <td style="font-weight: bold; vertical-align:bottom; text-align:right;" width="30%"><?php echo $signature;
                                                                                                                                                ?></td>
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
                                                        <label for="matter">Matter <b class="text-danger"> * <?php echo $matterErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <textarea required name="matter" id="matter" cols="10" rows="30" class="form-control" style="height: 150px;">
                                                        <?php if ($error === 1) {
                                                            echo $matter;
                                                        } else {
                                                            echo trim($fmatter);
                                                        } ?>
                                                        </textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="signature">Signature <b class="text-danger"> * <?php echo $sectionErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Signature" name="signature" tabindex="11" id="signature" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                echo $section;
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo "Accounts Officer";
                                                                                                                                                                                            } ?>">

                                                        </div>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary" name="generate_report" id="generate_report" tabindex="15">Generate report</button>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="copy_to">Copy to <b class="text-danger"> * <?php echo $copyToErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <textarea required name="copy_to" id="copy_to" cols="10" rows="30" class="form-control" style="height: 150px;">
                                                        <?php if ($error === 1) {
                                                            echo $copyTo;
                                                        } else {
                                                            echo trim($fCopyTo);
                                                        } ?>
                                                        </textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="memo_no">Memo no <b class="text-danger"> * <?php echo $memoNoErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Signature" name="memo_no" tabindex="11" id="memo_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $memoNo;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $fmemoNo;
                                                                                                                                                                                        } ?>">

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
                AuthorityType: "corrigendum"
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