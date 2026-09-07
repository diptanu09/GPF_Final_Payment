<?php
$pageName = "MIS user report";
require_once "./top.php";
$_GLOBAL['types'] = [
    "1" => "Registered", "2" => "Entered", "3" => "Calculated", "4" => "Checked",
    "5" => "Approved", "6" => "Signed", "7" => "Objectioned", "8" => "Minus Balance"
];
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
                    $type = trim($_REQUEST['type']);
                    $signature = trim($_REQUEST['signature']);

                    if (empty($type)) {
                        $typeErr = "Required";
                    }
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
                    if (($fromDateErr == "") && ($toDateErr == "") && ($typeErr == "") && ($signatureErr == "")) {
                        switch ($type) {
                            case "1":
                                $sql = "SELECT c.FULL_NAME, COUNT(CASE WHEN a.REGD_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_REGISTERED_DATE IS NULL THEN 1 END) FRESH_CASE,
                                        COUNT(CASE WHEN a.LTA_REGISTERED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.REGD_DATE IS NULL THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN USER_ACCOUNTS c ON b.CREATE_USER=c.USERNAME
                                        WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS!='11' GROUP BY c.FULL_NAME ORDER BY d.FULL_NAME";
                                $countRecords = sqlFetchData($connection, $sql);
                                break;
                            case "2":
                                $sql = "SELECT d.FULL_NAME, COUNT(CASE WHEN a.ENTERED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_ENTERED_DATE IS NULL THEN 1 END) FRESH_CASE,
                                        COUNT(CASE WHEN a.LTA_ENTERED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.ENTERED_DATE IS NULL THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN GPF_APPLICATION c ON a.REGD_NO=c.REGD_NO
                                        INNER JOIN USER_ACCOUNTS d ON c.CREATE_USER=d.USERNAME WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS!='11' GROUP BY d.FULL_NAME ORDER BY d.FULL_NAME";
                                $countRecords = sqlFetchData($connection, $sql);
                                break;
                            case "3":
                                $sql = "SELECT d.FULL_NAME, COUNT(CASE WHEN a.CALCULATION_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_ENTERED_DATE IS NULL THEN 1 END) FRESH_CASE,
                                        COUNT(CASE WHEN a.CALCULATION_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_ENTERED_DATE IS NOT NULL THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN GPF_AMOUNT_INFO c ON a.REGD_NO=c.REGD_NO
                                        INNER JOIN USER_ACCOUNTS d ON c.CREATE_USER=d.USERNAME WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS!='11' GROUP BY d.FULL_NAME ORDER BY d.FULL_NAME";
                                $countRecords = sqlFetchData($connection, $sql);
                                break;
                            case "4":
                                $sql = "SELECT d.FULL_NAME, COUNT(CASE WHEN a.CHECKED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_CHECKED_DATE IS NULL AND a.LTA_REGISTERED_DATE IS NULL
                                        THEN 1 END) FRESH_CASE, COUNT(CASE WHEN a.LTA_CHECKED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.CHECKED_DATE IS NULL AND a.REGD_DATE IS NULL THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN GPF_AMOUNT_INFO c ON a.REGD_NO=c.REGD_NO
                                        INNER JOIN USER_ACCOUNTS d ON c.CHECKED_BY=d.USERNAME OR c.LTA_CHECKED_BY=d.USERNAME WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS!='11'
                                        GROUP BY d.FULL_NAME ORDER BY d.FULL_NAME";
                                $countRecords = sqlFetchData($connection, $sql);
                                break;
                            case "5":
                                $sql = "SELECT d.FULL_NAME, COUNT(CASE WHEN a.APPROVED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_APPROVED_DATE IS NULL AND a.LTA_REGISTERED_DATE IS NULL
                                        THEN 1 END) FRESH_CASE, COUNT(CASE WHEN a.LTA_APPROVED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.APPROVED_DATE IS NULL AND a.REGD_DATE IS NULL THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN GPF_AMOUNT_INFO c ON a.REGD_NO=c.REGD_NO
                                        INNER JOIN USER_ACCOUNTS d ON c.APPROVED_BY=d.USERNAME OR c.LTA_APPROVED_BY=d.USERNAME WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS!='11' 
                                        GROUP BY d.FULL_NAME ORDER BY d.FULL_NAME";
                                $countRecords = sqlFetchData($connection, $sql);
                                break;
                            case "6":
                                $sql = "SELECT T.FULL_NAME, T.FRESH_CASE, T.LTA_CASE FROM (SELECT d.FULL_NAME, COUNT(CASE WHEN a.FP_SIGNED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.REGD_DATE IS NOT NULL AND a.LTA_REGISTERED_DATE IS NULL THEN 1 END) FRESH_CASE,
                                        COUNT(CASE WHEN a.LTA_SIGNED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_REGISTERED_DATE IS NOT NULL AND a.REGD_DATE IS NULL THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN GPF_AUTHORITY_SIGN c ON a.REGD_NO=c.REGD_NO
                                        INNER JOIN USER_ACCOUNTS d ON c.SIGNED_BY=d.USERNAME WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS IN (7, 16) 
                                        GROUP BY d.FULL_NAME UNION ALL SELECT d.FULL_NAME, COUNT(CASE WHEN a.FP_SIGNED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.REGD_DATE IS NOT NULL AND a.LTA_REGISTERED_DATE IS NULL THEN 1 END) FRESH_CASE,
                                        COUNT(CASE WHEN a.LTA_SIGNED_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_REGISTERED_DATE IS NOT NULL AND a.REGD_DATE IS NULL THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN GPF_LTA_AUTHORITY_SIGN c ON a.REGD_NO=c.REGD_NO
                                        INNER JOIN USER_ACCOUNTS d ON c.SIGNED_BY=d.USERNAME WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS IN (7, 16) 
                                        GROUP BY d.FULL_NAME)T ORDER BY T.FULL_NAME";
                                $countRecords = sqlFetchData($connection, $sql);
                                break;
                            case "7":
                                $sql = "SELECT d.FULL_NAME, COUNT(CASE WHEN a.OBJECTION_DATE BETWEEN '$fromDate' AND '$toDate' AND a.REGD_DATE IS NOT NULL AND a.LTA_REGISTERED_DATE IS NULL  THEN 1 END) FRESH_CASE,
                                        COUNT(CASE WHEN a.OBJECTION_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_REGISTERED_DATE IS NOT NULL AND a.REGD_DATE IS NULL  THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN GPF_CASES_LOG c ON a.REGD_NO=c.REGD_NO
                                        INNER JOIN USER_ACCOUNTS d ON c.ACTION_BY=d.USERNAME WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS='9' AND a.CASE_STATUS !='11'
                                        GROUP BY d.FULL_NAME ORDER BY d.FULL_NAME";
                                $countRecords = sqlFetchData($connection, $sql);
                                break;
                            case "8":
                                $sql = "SELECT d.FULL_NAME, COUNT(CASE WHEN a.MINUS_BAL_DATE BETWEEN '$fromDate' AND '$toDate' AND a.REGD_DATE IS NOT NULL AND a.LTA_REGISTERED_DATE IS NULL  THEN 1 END) FRESH_CASE,
                                        COUNT(CASE WHEN a.MINUS_BAL_DATE BETWEEN '$fromDate' AND '$toDate' AND a.LTA_REGISTERED_DATE IS NOT NULL AND a.REGD_DATE IS NULL THEN 1 END) LTA_CASE
                                        FROM GPF_CASE_STATUS A INNER JOIN GPF_INWARD b ON a.REGD_NO=b.REGD_NO INNER JOIN GPF_CASES_LOG c ON a.REGD_NO=c.REGD_NO
                                        INNER JOIN USER_ACCOUNTS d ON c.ACTION_BY=d.USERNAME WHERE b.CASE_TYPE IN ('F', 'L') AND a.CASE_STATUS='10' AND a.CASE_STATUS !='11'
                                        GROUP BY d.FULL_NAME ORDER BY d.FULL_NAME";
                                $countRecords = sqlFetchData($connection, $sql);
                                break;
                            default:
                                break;
                        }
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
                                                            No. MIS / FP / User-reports / <?php echo $_GLOBAL['types'][$type]; ?> / Count
                                                        </td>
                                                        <td style="width: 40%; text-align:right;">
                                                            Date : <?php echo date("d / m / Y"); ?></td>
                                                    </tr>
                                                </table><br>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">

                                                    <div style="font-weight: bold; text-align:center;">
                                                        GPF Final Payment <?php echo $_GLOBAL['types'][$type]; ?> Cases<br />
                                                        <i>
                                                            From <?php echo $fromDate; ?> To <?php echo $toDate; ?>
                                                        </i>
                                                    </div><br>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight:bold;">User</th>
                                                                <th colspan="2" style="text-align: center; font-weight:bold;">No. of cases</th>
                                                                <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight:bold;">Total</th>
                                                            </tr>
                                                            <tr>
                                                                <th style="text-align: center;font-weight:bold;">Fresh</th>
                                                                <th style="text-align: center;font-weight:bold;">Fresh LTA</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $totalCase = 0;
                                                            $totalFCase = 0;
                                                            $totalLCase = 0;
                                                            foreach ($countRecords as $countList) {
                                                                $totalUserCase = $countList['FRESH_CASE'] + $countList['LTA_CASE'];
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $countList['FULL_NAME']; ?></td>
                                                                    <td style="text-align: center;"><?php echo $countList['FRESH_CASE']; ?></td>
                                                                    <td style="text-align: center;"><?php echo $countList['LTA_CASE']; ?></td>
                                                                    <td style="text-align: center; font-weight:bold;"><?php echo $totalUserCase; ?></td>
                                                                </tr>
                                                            <?php
                                                                $totalCase += $totalUserCase;
                                                                $totalFCase += $countList['FRESH_CASE'];
                                                                $totalLCase += $countList['LTA_CASE'];
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td style="text-align: center;font-weight:bold;">TOTAL</td>
                                                                <td style="text-align: center;font-weight:bold;"><?php echo $totalFCase; ?></td>
                                                                <td style="text-align: center;font-weight:bold;"><?php echo $totalLCase; ?></td>
                                                                <td style="text-align: center;font-weight:bold;"><?php echo $totalCase; ?></td>
                                                            </tr>
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
                                                    <label for="type">Type <b class="text-danger">* <?php echo $typeErr; ?> </b></label>
                                                    <select name="type" id="type" class="form-control">
                                                        <option value="">Select type</option>
                                                        <?php
                                                        foreach ($_GLOBAL['types'] as $key => $types) {
                                                        ?>
                                                            <option value="<?php echo $key; ?>">
                                                                <?php echo $types; ?>
                                                            </option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="generate_report" id="generate_report" tabindex="15">Generate report</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="to_date">To date <b class="text-danger">* <?php echo $toDateErr; ?> </b></label>
                                                    <input type="date" name="to_date" id="to_date" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="signature">Signature <b class="text-danger">* <?php echo $signatureErr; ?> </b></label>
                                                    <input type="text" name="signature" id="signature" class="form-control" required value="Sr. Accounts Officer">
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
            AuthorityType: "user_report"
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