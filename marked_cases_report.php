<?php
$pageName = "Marked cases report";
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
                if (isset($_REQUEST['marked_report'])) {
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
                        $sql = "SELECT a.SL_NO INWARD_NO, a.REGD_NO, 'T/'||c.SERIES_DESCR||'/'||b.ACCOUNT_NO GPF_NO, b.SUBSCRIBER_NAME, d.FULL_NAME, a.MARK_DATE
                                FROM GPF_INWARD a INNER JOIN GPF_CASE_STATUS b ON a.REGD_NO=b.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES c ON b.SERIES_ID=c.SERIES_ID
                                INNER JOIN USER_ACCOUNTS d ON a.MARK_TO=d.USERNAME WHERE TO_DATE(a.MARK_DATE) BETWEEN '$fromDate' AND '$toDate' ORDER BY d.FULL_NAME";
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
                                    <button id="print_btn" onclick="printPage('printArea')" class="btn btn-primary">
                                        PRINT</button>
                                    <div class="row">
                                        <div class="col-lg-2"></div>
                                        <div class="col-lg-8">
                                            <div id="printArea">
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
                                                <span style="float: left;">No. FP / Marked cases / <?php echo $count; ?></span>
                                                <span style="float: right;">Date : <?php echo date("d / m / Y"); ?></span> <br><br>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">
                                                    <center>
                                                        <b style="text-decoration: underline;">
                                                            GPF Final Payment Marked cases
                                                        </b><br>
                                                        <i style="font-weight:bold;">
                                                            From <?php echo $fromDate; ?> To <?php echo $toDate; ?>
                                                        </i>
                                                    </center><br>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>INWARD NUMBER</th>
                                                                <th>REGISTRATION NUMBER</th>
                                                                <th>ACCOUNT NUMBER</th>
                                                                <th>SUBSCRIBER NAME</th>
                                                                <th>MARKED DATE </th>
                                                                <th>MARKED TO </th>
                                                                <th>SIGNATURE</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $fetchData = sqlFetchData($connection, $sql);
                                                            foreach ($fetchData as $fetchList) {
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $fetchList['INWARD_NO']; ?></td>
                                                                    <td><?php echo $fetchList['REGD_NO']; ?></td>
                                                                    <td><?php echo $fetchList['GPF_NO']; ?></td>
                                                                    <td><?php echo $fetchList['SUBSCRIBER_NAME']; ?></td>
                                                                    <td><?php echo date("d / m / Y", strtotime($fetchList['MARK_DATE'])); ?></td>
                                                                    <td><?php echo $fetchList['FULL_NAME']; ?></td>
                                                                    <td></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                    <p style=" font-weight:bolder; margin-top:50px;">
                                                        <span style="float: right;"><?php echo $signature; ?></span>
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
                <?php
                } else if (isset($_REQUEST['re_marked_report'])) {
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
                        $sql = "SELECT A.SL_NO INWARD_NO, 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_NO, C.REGD_NO, C.SUBSCRIBER_NAME,
                        B.CREATE_DATE MARK_DATE, E.FULL_NAME FROM_USER, F.FULL_NAME TO_USER, B.REMARKS FROM GPF_INWARD A INNER JOIN GPF_TRANSFER_REMARKS B
                        ON A.SL_NO=B.SL_NO INNER JOIN GPF_CASE_STATUS C ON A.REGD_NO=C.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES D ON
                        C.SERIES_ID=D.SERIES_ID INNER JOIN USER_ACCOUNTS E ON B.FROM_USER=E.USERNAME INNER JOIN USER_ACCOUNTS F
                        ON B.TO_USER=F.USERNAME WHERE TO_DATE(B.CREATE_DATE) BETWEEN '$fromDate' AND '$toDate' ORDER BY E.FULL_NAME, F.FULL_NAME";
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
                                    <button id="print_btn" onclick="printPage('printArea')" class="btn btn-primary">
                                        PRINT</button>
                                    <div class="row">
                                        <div class="col-lg-2"></div>
                                        <div class="col-lg-8">
                                            <div id="printArea">
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
                                                <span style="float: left;">No. FP / Re-marked cases / <?php echo $count; ?></span>
                                                <span style="float: right;">Date : <?php echo date("d / m / Y"); ?></span> <br><br>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">
                                                    <center>
                                                        <b style="text-decoration: underline;">
                                                            GPF Final Payment Remarked cases
                                                        </b><br>
                                                        <i style="font-weight:bold;">
                                                            From <?php echo $fromDate; ?> To <?php echo $toDate; ?>
                                                        </i>
                                                    </center><br>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>INWARD NUMBER</th>
                                                                <th>REGISTRATION NUMBER</th>
                                                                <th>ACCOUNT NUMBER</th>
                                                                <th>SUBSCRIBER NAME</th>
                                                                <th>MARKED DATE</th>
                                                                <th>MARKED FROM </th>
                                                                <th>MARKED TO </th>
                                                                <th>REMARKS </th>
                                                                <th>SIGNATURE</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $fetchData = sqlFetchData($connection, $sql);
                                                            foreach ($fetchData as $fetchList) {
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $fetchList['INWARD_NO']; ?></td>
                                                                    <td><?php echo $fetchList['REGD_NO']; ?></td>
                                                                    <td><?php echo $fetchList['GPF_NO']; ?></td>
                                                                    <td><?php echo $fetchList['SUBSCRIBER_NAME']; ?></td>
                                                                    <td><?php echo date("d / m / Y", strtotime($fetchList['MARK_DATE'])); ?></td>
                                                                    <td><?php echo $fetchList['FROM_USER']; ?></td>
                                                                    <td><?php echo $fetchList['TO_USER']; ?></td>
                                                                    <td><?php echo $fetchList['REMARKS']; ?></td>
                                                                    <td></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                    <p style=" font-weight:bolder; margin-top:50px;">
                                                        <span style="float: right;"><?php echo $signature; ?></span>
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
                                                <button type="submit" class="btn btn-primary" name="marked_report" id="marked_report" tabindex="15">Marked report</button>
                                                <button type="submit" class="btn btn-info" name="re_marked_report" id="re_marked_report" tabindex="16">Re-marked report</button>
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