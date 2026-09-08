<?php
$pageName = "Settled cases report";
require_once "./top.php";
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
                    $fromDate = trim($_REQUEST['from_date']);
                    $toDate = trim($_REQUEST['to_date']);
                    $signature = trim($_REQUEST['signature']);

                    if (empty($signature)) {
                        $signatureErr = "Required";
                    }
                    if (empty($fromDate)) {
                        $fromDateErr = "Required";
                    } else {
                        $fromDate = date("d-M-Y", strtotime($fromDate));
                    }
                    if (empty($toDate)) {
                        $toDateErr = "Required";
                    } else {
                        $toDate = date("d-M-Y", strtotime($toDate));
                    }
                    if (($fromDateErr == "") && ($toDateErr == "") && ($signatureErr == "")) {
                        $sql = "SELECT a.REGD_NO, 'T/'||c.SERIES_DESCR||'/'||a.ACCOUNT_NO GPF_ACC_NO, a.SUBSCRIBER_NAME,
                                a.FP_SIGNED_DATE, a.FP_UPLOAD_DATE, a.DLIS_SIGNED_DATE, a.DLIS_UPLOAD_DATE, a.LTA_SIGNED_DATE, 
                                a.LTA_UPLOAD_DATE FROM GPF_CASE_STATUS a INNER JOIN VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID
                                WHERE TO_DATE(a.FP_SIGNED_DATE) BETWEEN '$fromDate' AND '$toDate' OR TO_DATE(a.LTA_SIGNED_DATE)
                                BETWEEN '$fromDate' AND '$toDate' ORDER BY a.FP_SIGNED_DATE, a.DLIS_SIGNED_DATE, a.LTA_SIGNED_DATE";
                        $count = sqlCountData($connection, $sql);
                    } else {
                        $message = "";
                        $textColor = "danger";
                    }

                ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <button id="upload_btn" onclick="download()" class="btn btn-primary">
                                        DOWNLOAD</button>
                                    <div class="row">
                                        <div class="col-lg-2"></div>
                                        <div class="col-lg-8">
                                            <div id="printArea">
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
                                                            No. MIS / FP / Settled cases /
                                                        </td>
                                                        <td style="width: 40%; text-align:right;">
                                                            Date : <?php echo date("d / m / Y"); ?></td>
                                                    </tr>
                                                </table><br>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">
                                                    <div style="font-weight: bold; text-align:center;">
                                                        GPF Final Payment Settled and Upload Cases
                                                        <br />
                                                        <i style="font-weight:bold;">
                                                            From <?php echo $fromDate; ?> To <?php echo $toDate; ?>
                                                        </i>
                                                    </div>
                                                    <br>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>REGISTRATION NUMBER</th>
                                                                <th>ACCOUNT NUMBER</th>
                                                                <th>SUBSCRIBER NAME</th>
                                                                <th>FP SIGNED DATE</th>
                                                                <th>FP UPLOAD DATE</th>
                                                                <th>DLIS SIGNED DATE</th>
                                                                <th>DLIS UPLOAD DATE</th>
                                                                <th>LTA SIGNED DATE</th>
                                                                <th>LTA UPLOAD DATE</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $fetchData = sqlFetchData($connection, $sql);
                                                            foreach ($fetchData as $fetchList) {
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $fetchList['REGD_NO']; ?></td>
                                                                    <td><?php echo $fetchList['GPF_ACC_NO']; ?></td>
                                                                    <td><?php echo $fetchList['SUBSCRIBER_NAME']; ?></td>
                                                                    <td><?php echo $fetchList['FP_SIGNED_DATE'] == "" ? "" : date("d/m/Y", strtotime($fetchList['FP_SIGNED_DATE'])); ?></td>
                                                                    <td><?php echo $fetchList['FP_UPLOAD_DATE'] == "" ? "" : date("d/m/Y", strtotime($fetchList['FP_UPLOAD_DATE'])); ?></td>
                                                                    <td><?php echo $fetchList['DLIS_SIGNED_DATE'] == "" ? "" : date("d/m/Y", strtotime($fetchList['DLIS_SIGNED_DATE'])); ?></td>
                                                                    <td><?php echo $fetchList['DLIS_UPLOAD_DATE'] == "" ? "" : date("d/m/Y", strtotime($fetchList['DLIS_UPLOAD_DATE'])); ?></td>
                                                                    <td><?php echo $fetchList['LTA_SIGNED_DATE'] == "" ? "" : date("d/m/Y", strtotime($fetchList['LTA_SIGNED_DATE'])); ?></td>
                                                                    <td><?php echo $fetchList['LTA_UPLOAD_DATE'] == "" ? "" : date("d/m/Y", strtotime($fetchList['LTA_UPLOAD_DATE'])); ?></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                    <div style="font-weight:bold; margin-top:50px; text-align:right;">
                                                        <?php echo $signature; ?>
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
                                                    <label for="from_date">From date <b class="text-danger">* <?php echo $fromDateErr; ?> </b></label>
                                                    <input type="date" name="from_date" id="from_date" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="signature">Signature <b class="text-danger">* <?php echo $signatureErr; ?> </b></label>
                                                    <input type="text" name="signature" id="signature" class="form-control" required value="Sr. Accounts Officer">
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="generate_report" id="generate_report" tabindex="15">Generate report</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="to_date">To date <b class="text-danger">* <?php echo $toDateErr; ?> </b></label>
                                                    <input type="date" name="to_date" id="to_date" class="form-control" required>
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
        let registrationNo = "<?php echo date("YmdHis"); ?>";
        let authority = $("#printArea").html();

        let authorityData = {
            RegistrationNo: registrationNo,
            Authority: authority,
            AuthorityType: "settled_cases"
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