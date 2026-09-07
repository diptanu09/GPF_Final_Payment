<?php
$pageName = "Calculation report";
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
                    $type = trim($_REQUEST['type']);

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
                    if (empty($type)) {
                        $typeErr = "Required";
                    } else {
                        switch ($type) {
                            case 1:
                                $whereClause = "";
                                break;
                            case 2:
                                $whereClause = "AND (APPROVED_DATE IS NOT NULL OR LTA_APPROVED_DATE IS NOT NULL) ";
                                break;
                            default:
                                $typeErr = "Wrong input";
                                break;
                        }
                    }


                    if (($fromDateErr == "") && ($toDateErr == "") && ($signatureErr == "") && ($typeErr == "")) {
                        $sql = "SELECT a.REGD_NO, 'T/'||c.SERIES_DESCR||'/'||a.ACCOUNT_NO GPF_ACC_NO, a.SUBSCRIBER_NAME, f.DDO_DESG,
                        a.CALCULATION_DATE, b.OPENING_BAL_AMOUNT, b.ACTUAL_DEPOSIT, b.EXCESS_DEPOSIT, b.WITHDRAWAL, g.TRES_NAME,
                        b.ACTUAL_INTEREST, b.DELAYED_INTEREST, b.FINAL_PAYMENT_AMOUNT FROM GPF_CASE_STATUS a                                 
                        INNER JOIN GPF_AMOUNT_INFO b ON a.REGD_NO=b.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID  
                        INNER JOIN GPF_APPLICATION e ON a.REGD_NO=e.REGD_NO INNER JOIN VLCS.STATE_DDO f ON 
                        e.DDO_CODE=f.DDO_CODE INNER JOIN VLCS.STATE_TREASURY g ON e.TREASURY_CODE=g.TRES_CODE
                        WHERE TO_DATE(a.CALCULATION_DATE) BETWEEN '$fromDate' AND '$toDate' $whereClause ORDER BY a.CALCULATION_DATE";
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
                                        <div class="col-lg-12">
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
                                                            No. MIS / FP / Calculated cases / <?php echo $count; ?>
                                                        </td>
                                                        <td style="width: 40%; text-align:right;">
                                                            Date : <?php echo date("d / m / Y"); ?></td>
                                                    </tr>
                                                </table><br>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">

                                                    <div style="font-weight: bold; text-align:center;">
                                                        GPF Final Payment
                                                        <?php
                                                        echo $type == 1 ? "" : "Approved"
                                                        ?>
                                                        Calculated Cases
                                                        <br />
                                                        <i style="font-weight:bold;">
                                                            From <?php echo $fromDate; ?> To <?php echo $toDate; ?>
                                                        </i>
                                                    </div><br>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th rowspan="2">ACCOUNT NUMBER</th>
                                                                <th rowspan="2">SUBSCRIBER NAME</th>
                                                                <th rowspan="2">DDO</th>
                                                                <th rowspan="2">TREASURY</th>
                                                                <th rowspan="2">O.B.</th>
                                                                <th colspan="2">DEPOSIT</th>
                                                                <th rowspan="2">DEBIT</th>
                                                                <th colspan="2">INTEREST</th>
                                                                <th rowspan="2">FINAL PAYMENT</th>
                                                            </tr>
                                                            <tr>
                                                                <th>ACTUAL</th>
                                                                <th>EXCESS</th>
                                                                <th>ACTUAL</th>
                                                                <th>DELAYED</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $fetchData = sqlFetchData($connection, $sql);
                                                            foreach ($fetchData as $fetchList) {
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $fetchList['GPF_ACC_NO']; ?></td>
                                                                    <td><?php echo $fetchList['SUBSCRIBER_NAME']; ?></td>
                                                                    <td><?php echo $fetchList['DDO_DESG']; ?></td>
                                                                    <td><?php echo $fetchList['TRES_NAME']; ?></td>
                                                                    <td><?php echo $fetchList['OPENING_BAL_AMOUNT']; ?></td>
                                                                    <td><?php echo $fetchList['ACTUAL_DEPOSIT']; ?></td>
                                                                    <td><?php echo $fetchList['EXCESS_DEPOSIT']; ?></td>
                                                                    <td><?php echo $fetchList['WITHDRAWAL']; ?></td>
                                                                    <td><?php echo $fetchList['ACTUAL_INTEREST']; ?></td>
                                                                    <td><?php echo $fetchList['DELAYED_INTEREST']; ?></td>
                                                                    <td><?php echo $fetchList['FINAL_PAYMENT_AMOUNT']; ?></td>
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
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                } else if (isset($_REQUEST['generate_mb_report'])) {
                    $fromDate = trim($_REQUEST['from_date']);
                    $toDate = trim($_REQUEST['to_date']);
                    $signature = trim($_REQUEST['signature']);
                    $type = trim($_REQUEST['type']);

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
                    if (empty($type)) {
                        $typeErr = "Required";
                    } else {
                        switch ($type) {
                            case 1:
                                $whereClause = "";
                                break;
                            case 2:
                                $whereClause = "AND (APPROVED_DATE IS NOT NULL OR LTA_APPROVED_DATE IS NOT NULL) ";
                                break;
                            default:
                                $typeErr = "Wrong input";
                                break;
                        }
                    }


                    if (($fromDateErr == "") && ($toDateErr == "") && ($signatureErr == "") && ($typeErr == "")) {

                        $sql = "SELECT a.REGD_NO, 'T/'||c.SERIES_DESCR||'/'||a.ACCOUNT_NO GPF_ACC_NO, a.SUBSCRIBER_NAME, f.DDO_DESG,
                                a.CALCULATION_DATE, b.OPENING_BAL_AMOUNT, b.ACTUAL_DEPOSIT, b.EXCESS_DEPOSIT, b.WITHDRAWAL, g.TRES_NAME,
                                b.ACTUAL_INTEREST, b.DELAYED_INTEREST, b.FINAL_PAYMENT_AMOUNT FROM GPF_CASE_STATUS a                                 
                                INNER JOIN GPF_AMOUNT_INFO b ON a.REGD_NO=b.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID  
                                INNER JOIN GPF_APPLICATION e ON a.REGD_NO=e.REGD_NO INNER JOIN VLCS.STATE_DDO f ON 
                                e.DDO_CODE=f.DDO_CODE INNER JOIN VLCS.STATE_TREASURY g ON e.TREASURY_CODE=g.TRES_CODE
                                WHERE TO_DATE(a.CALCULATION_DATE) BETWEEN '$fromDate' AND '$toDate' AND b.FINAL_PAYMENT_AMOUNT<0 $whereClause
                                ORDER BY a.CALCULATION_DATE";
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
                                        <div class="col-lg-12">
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
                                                            No. MIS / FP / Minus Balance cases / <?php echo $count; ?>
                                                        </td>
                                                        <td style="width: 40%; text-align:right;">
                                                            Date : <?php echo date("d / m / Y"); ?></td>
                                                    </tr>
                                                </table><br>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">

                                                    <div style="font-weight: bold; text-align:center;">
                                                        GPF Final Payment
                                                        <?php
                                                        echo $type == 1 ? "" : "Approved"
                                                        ?>
                                                        Minus Balance Cases
                                                        <br />
                                                        <i style="font-weight:bold;">
                                                            From <?php echo $fromDate; ?> To <?php echo $toDate; ?>
                                                        </i>
                                                    </div><br>
                                                    <table class="table table-bordered">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th rowspan="2">ACCOUNT NUMBER</th>
                                                                    <th rowspan="2">SUBSCRIBER NAME</th>
                                                                    <th rowspan="2">DDO</th>
                                                                    <th rowspan="2">TREASURY</th>
                                                                    <th rowspan="2">O.B.</th>
                                                                    <th colspan="2">DEPOSIT</th>
                                                                    <th rowspan="2">DEBIT</th>
                                                                    <th colspan="2">INTEREST</th>
                                                                    <th rowspan="2">FINAL PAYMENT</th>
                                                                </tr>
                                                                <tr>
                                                                    <th>ACTUAL</th>
                                                                    <th>EXCESS</th>
                                                                    <th>ACTUAL</th>
                                                                    <th>DELAYED</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $fetchData = sqlFetchData($connection, $sql);
                                                                foreach ($fetchData as $fetchList) {
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $fetchList['GPF_ACC_NO']; ?></td>
                                                                        <td><?php echo $fetchList['SUBSCRIBER_NAME']; ?></td>
                                                                        <td><?php echo $fetchList['DDO_DESG']; ?></td>
                                                                        <td><?php echo $fetchList['TRES_NAME']; ?></td>
                                                                        <td><?php echo $fetchList['OPENING_BAL_AMOUNT']; ?></td>
                                                                        <td><?php echo $fetchList['ACTUAL_DEPOSIT']; ?></td>
                                                                        <td><?php echo $fetchList['EXCESS_DEPOSIT']; ?></td>
                                                                        <td><?php echo $fetchList['WITHDRAWAL']; ?></td>
                                                                        <td><?php echo $fetchList['ACTUAL_INTEREST']; ?></td>
                                                                        <td><?php echo $fetchList['DELAYED_INTEREST']; ?></td>
                                                                        <td><?php echo $fetchList['FINAL_PAYMENT_AMOUNT']; ?></td>
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
                                                <button type="submit" class="btn btn-primary" name="generate_report" id="generate_report" tabindex="15">Generate calculation report</button>
                                                <button type="submit" class="btn btn-info" name="generate_mb_report" id="generate_mb_report" tabindex="16">Generate minus balance report</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="to_date">To date <b class="text-danger">* <?php echo $toDateErr; ?> </b></label>
                                                    <input type="date" name="to_date" id="to_date" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="type">Type <b class="text-danger">* <?php echo $typeErr; ?> </b></label>
                                                    <select name="type" id="type" class="form-control" required>
                                                        <option value="">Select type</option>
                                                        <?php
                                                        $type = array(1 => "All", 2 => "Approved");
                                                        foreach ($type as $key => $types) {
                                                        ?>
                                                            <option value="<?php echo $key; ?>">
                                                                <?php echo $types; ?>
                                                            </option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
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
            AuthorityType: "calculated_cases"
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