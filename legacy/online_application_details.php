<?php
$pageName = "Online application details";
require_once "./top.php";
if ($_REQUEST['series'] && $_REQUEST['account_no']) {
    $seriesName = trim($_REQUEST['series']);
    $accountNo = trim($_REQUEST['account_no']);
    $registrationNo = $seriesName . "_" . $accountNo;
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
                                    <button id="upload_btn" onclick="download()" class="btn btn-primary">DOWNLOAD</button>
                                    <div class="row">
                                        <div class="col-lg-2"></div>
                                        <div class="col-lg-8">
                                            <div id="printArea">
                                                <?php
                                                $onlineAppDetailsSQL = "";
                                                $fetchOnlineData = sqlPGFetchData($connection, $onlineAppDetailsSQL);
                                                ?>
                                                <table width="100%" cellpadding="5px" cellspacing="0" border="0">
                                                    <tr>
                                                        <td width="8%" align="right"><img src="assets/images/cag_logo.png" width="64" height="64" /></td>
                                                        <td width="92%">
                                                            <center>
                                                                <b style="font-size: 19px;">महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला</b><br />
                                                                <span style="font-weight: bold;font-size: 12px;">OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA</span>
                                                            </center>
                                                        </td>
                                                        <td width="8%" align="left" valign="middle"><br /><img src="assets/images/ashok_stambh.png" width="44" height="64" /></td>
                                                    </tr>
                                                </table>
                                                <hr>
                                                <span style="float: left;">No. Online application / <?php echo $seriesName . "-" . $accountNo; ?> </span>
                                                <span style="float: right;">Date : <?php echo date("d / m / Y"); ?></span> <br><br>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">

                                                    <?php
                                                    $onlineAppQuery = "select a.subscriber_name,a.subscriber_beneficiary_code,a.subscriber_employee_code,a.subscriber_date_of_birth,a.series_code,a.gpf_account_no,
                                                    a.subscriber_mobile_no,a.subscriber_email_id ,a.subscriber_designation ,a.subscriber_postal_address,a.f_nominee_name ,
                                                    a.f_nominee_beneficiary_code ,a.relation_with_f_nominee ,a.s_nominee_name ,a.s_nominee_beneficiary_code ,a.relation_with_s_nominee ,
                                                    a.certificate,a.pension_type ,a.date_of_effect,a.treasury_code ,a.details_gpf_subscription,a.details_gpf_withdrawal,a.closing_balance, a.apply_date, a.ddo_code from gpf_final_payment.online_application a where a.series_code='$seriesName' AND a.gpf_account_no='$accountNo'";
                                                    $fetchonlineApp = sqlPGFetchData($conn_pgsql, $onlineAppQuery);
                                                    foreach ($fetchonlineApp as $onlineAppList) {
                                                        $subscriberName = $onlineAppList[0];
                                                        $seriesName = $onlineAppList[4];
                                                        $accountNo = $onlineAppList[5];
                                                        $employeeCode = $onlineAppList[2];
                                                        $beneficiaryCode = $onlineAppList[1];
                                                        $pensionType = $onlineAppList[17];
                                                        $treasuryCode = $onlineAppList[19];
                                                        $groupD = $onlineAppList[22];
                                                        $appliedDate = $onlineAppList[23];
                                                        $personalAddress = $onlineAppList[9];
                                                        $doe = $onlineAppList[18];
                                                        $designation = $onlineAppList[8];
                                                        $ddoCode = $onlineAppList[24];
                                                        $gpfSubscriptionDetails = $onlineAppList[20];
                                                        $gpfWithdrwalDetails = $onlineAppList[21];
                                                        $certificate = $onlineAppList[16];
                                                        $mobile = $onlineAppList[6];

                                                        $firstNomineeName = $onlineAppList[10];
                                                        $firstNomineeRelation = $onlineAppList[12];
                                                        $firstNomineeBencode = $onlineAppList[11];

                                                        $secondNomineeName = $onlineAppList[13];
                                                        $secondNomineeRelation = $onlineAppList[15];
                                                        $secondNomineeBencode = $onlineAppList[14];

                                                        $ddoSQL = "SELECT * FROM VLCS.STATE_DDO WHERE DDO_CODE='$ddoCode'";
                                                        $fetchDDO = sqlFetchData($connection, $ddoSQL);
                                                        foreach ($fetchDDO as $ddos) {
                                                            $ddoDesg = $ddos['DDO_DESG'];
                                                            $ddoEmail = $ddos['DDO_EMAIL_ID'];
                                                            $ddoContact = $ddos['PHONE_NO'];
                                                        }
                                                    }
                                                    ?>
                                                    <table class="table-bordered" cellpadding="10px">
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                Name
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $subscriberName;
                                                                ?>
                                                            </td>
                                                            <td style="font-weight: bold;">
                                                                GPF Account No.
                                                            </td>
                                                            <td style="text-align:left">
                                                                T /
                                                                <?php
                                                                echo $seriesName;
                                                                ?> /
                                                                <?php
                                                                echo $accountNo;
                                                                ?>
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                Designation
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $designation;
                                                                ?>
                                                            </td>
                                                            <td style="font-weight: bold;">
                                                                Mobile no.
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $mobile;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                Employee Code
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $employeeCode;
                                                                ?>
                                                            </td>
                                                            <td style="font-weight: bold;">
                                                                Beneficiary Code
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $beneficiaryCode;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                Personal address
                                                            </td>
                                                            <td colspan="3" style="text-align:left">
                                                                <?php
                                                                echo $personalAddress;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                Pension type
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $pensionType;
                                                                ?>
                                                            </td>
                                                            <td style="font-weight: bold;">
                                                                Date of effect
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo date("d/ m / Y", strtotime($doe));
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                DDO address
                                                            </td>
                                                            <td colspan="3" style="text-align:left">
                                                                <?php
                                                                echo $ddoCode . " - " . $ddoDesg;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>

                                                            <td style="font-weight: bold;">
                                                                DDO Email / Phone
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $ddoEmail;
                                                                ?> / <?php
                                                                        echo $ddoContact;
                                                                        ?>
                                                            </td>
                                                            <td style="font-weight: bold;">
                                                                Treasury
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $treasuryCode;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>

                                                            <td style="font-weight: bold;">
                                                                Appliation Date
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo date("d/ m / Y", strtotime($appliedDate));
                                                                ?>
                                                            </td>
                                                            <td style="font-weight: bold;">
                                                                Closing Balance of Gr- D <br> (2018-19), if any
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $groupD;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                First Nominee Name , Relation, Beneficiary Code
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $firstNomineeName;
                                                                ?>, <?php
                                                                    echo $firstNomineeRelation;
                                                                    ?>,
                                                                <?php
                                                                echo $firstNomineeBencode;
                                                                ?>
                                                            </td>
                                                            <td style="font-weight: bold;">
                                                                Second Nominee Name , Relation, Beneficiary Code
                                                            </td>
                                                            <td style="text-align:left">
                                                                <?php
                                                                echo $secondNomineeName;
                                                                ?>, <?php
                                                                    echo $secondNomineeRelation;
                                                                    ?>, <?php
                                                                        echo $secondNomineeBencode;
                                                                        ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                Subscription
                                                            </td>
                                                            <td style="text-align:left" colspan="3">
                                                                <?php
                                                                echo $gpfSubscriptionDetails;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                Withdrawal
                                                            </td>
                                                            <td style="text-align:left" colspan="3">
                                                                <?php
                                                                echo $gpfWithdrwalDetails;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: bold;">
                                                                Certificate
                                                            </td>
                                                            <td style="text-align:left" colspan="3">
                                                                <?php
                                                                echo $certificate;
                                                                ?>
                                                            </td>
                                                    </table>
                                                    <p style=" font-weight:bolder; margin-top:8px;">
                                                        <span style="float: right;">Sr. Accounts Officer</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2"></div>
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
    <script type="text/javascript">
        const download = () => {
            html2canvas(document.querySelector('#printArea')).then(canvas => {
                let registrationNo = "<?php echo $registrationNo; ?>";
                let authority = canvas.toDataURL();
                //console.log(authority);
                let authorityData = {
                    RegistrationNo: registrationNo,
                    Authority: authority,
                    AuthorityType: "ddo_application"
                }
                jQuery.ajax({
                    type: 'POST',
                    url: 'ajax/other_reports.php',
                    data: JSON.stringify(authorityData),
                    success: function(returnValue) {
                        console.log(returnValue);
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
            });
        }
    </script>
<?php
    require_once "./bottom.php";
}
?>