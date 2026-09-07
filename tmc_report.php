<?php
$pageName = "TMC report";
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
                if (isset($_REQUEST['generate_cag_report'])) {
                    $fromDate = trim($_REQUEST['from_date']);
                    $toDate = trim($_REQUEST['to_date']);
                    $circularNoDate = trim($_REQUEST['circular_no_date']);
                    $signature = trim($_REQUEST['signature']);
                    $finYear = trim($_REQUEST['fin_year']);
                    $months = trim($_REQUEST['month']);
                    $remarks = trim($_REQUEST['remarks']);
                    if (empty($months)) {
                        $monthErr = "Required";
                    }
                    if (empty($finYear)) {
                        $finYearErr = "Required";
                    }
                    if (empty($circularNoDate)) {
                        $circularNoDateErr = "Required";
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

                    if (($fromDateErr == "") && ($toDateErr == "") && ($finYearErr == "") && ($monthErr == "") && ($circularNoDateErr == "") && ($signatureErr == "")) {

                        $monthRegSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(REGD_DATE) BETWEEN '$fromDate' AND '$toDate' 
                                        AND LTA_REGISTERED_DATE IS NULL AND CASE_STATUS!='11' UNION ALL SELECT * FROM GPF_CASE_STATUS 
                                        WHERE TO_DATE(LTA_REGISTERED_DATE) BETWEEN '$fromDate' AND '$toDate' AND REGD_DATE IS NULL
                                        AND CASE_STATUS!='11' UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(REGD_DATE) BETWEEN
                                         '$fromDate' AND '$toDate' AND LTA_REGISTERED_DATE IS NOT NULL AND CASE_STATUS!='11' ";
                        $monthRegCount = sqlCountData($connection, $monthRegSQL);

                        $wholeCaseClearedSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(FP_SIGNED_DATE) BETWEEN '$fromDate' AND '$toDate' 
                                                AND LTA_SIGNED_DATE IS NULL AND CASE_STATUS!='11' UNION ALL SELECT * FROM GPF_CASE_STATUS 
                                                WHERE TO_DATE(LTA_SIGNED_DATE) BETWEEN '$fromDate' AND '$toDate' AND REGD_DATE IS NULL
                                                AND CASE_STATUS!='11' UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(LTA_SIGNED_DATE) BETWEEN
                                                '$fromDate' AND '$toDate' AND REGD_DATE IS NOT NULL AND CASE_STATUS!='11' ";
                        $wholeCaseCleared = sqlCountData($connection, $wholeCaseClearedSQL);

                        $wholeCaseClearedPercentage = $monthRegCount > 0 ? round(($wholeCaseCleared / $monthRegCount) * 100, 2) : "0";

                        $actualCaseClearedSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(FP_SIGNED_DATE) BETWEEN '$fromDate' AND '$toDate' 
                                                 AND TO_DATE(REGD_DATE) BETWEEN '$fromDate' AND '$toDate' AND LTA_SIGNED_DATE IS NULL AND CASE_STATUS!='11'
                                                 UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(LTA_SIGNED_DATE) BETWEEN '$fromDate' AND '$toDate' AND
                                                 TO_DATE(LTA_REGISTERED_DATE) BETWEEN '$fromDate' AND '$toDate' AND FP_SIGNED_DATE IS NULL
                                                 AND CASE_STATUS!='11' UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(LTA_SIGNED_DATE) BETWEEN
                                                '$fromDate' AND '$toDate' AND TO_DATE(LTA_REGISTERED_DATE) BETWEEN '$fromDate' AND '$toDate' AND REGD_DATE
                                                 IS NOT NULL AND CASE_STATUS!='11' ";
                        $actualCaseCleared = sqlCountData($connection, $actualCaseClearedSQL);

                        $actualCaseClearedPercentage = $monthRegCount > 0 ? round(($actualCaseCleared / $monthRegCount) * 100, 2) : "0";

                        $totalminusBalCaseSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(MINUS_BAL_DATE) BETWEEN '$fromDate' AND '$toDate' 
                                                 AND CASE_STATUS!='11'";
                        $totalminusBalCase = sqlCountData($connection, $totalminusBalCaseSQL);

                        $actualminusBalCaseSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(MINUS_BAL_DATE) BETWEEN '$fromDate' AND '$toDate' 
                                                 AND TO_DATE(REGD_DATE) BETWEEN '$fromDate' AND '$toDate' AND LTA_SIGNED_DATE IS NULL AND CASE_STATUS!='11'
                                                 UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(MINUS_BAL_DATE) BETWEEN '$fromDate' AND '$toDate' AND
                                                 TO_DATE(LTA_REGISTERED_DATE) BETWEEN '$fromDate' AND '$toDate' AND REGD_DATE IS NULL
                                                 AND CASE_STATUS!='11' ";
                        $actualminusBalCase = sqlCountData($connection, $actualminusBalCaseSQL);

                        $tmbCaseClearedSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(MINUS_BAL_CLOSED_DATE) BETWEEN '$fromDate' AND '$toDate' 
                                             AND CASE_STATUS!='11'";
                        $tmbCaseCleared = sqlCountData($connection, $tmbCaseClearedSQL);
                        $tmbCaseClearedPercentage = $totalminusBalCase > 0 ? round(($tmbCaseCleared / $totalminusBalCase) * 100, 2) : "0";

                        $mbCaseClearedSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(MINUS_BAL_DATE) BETWEEN '$fromDate' AND '$toDate' 
                                            AND TO_DATE(REGD_DATE) BETWEEN '$fromDate' AND '$toDate' AND LTA_SIGNED_DATE IS NULL AND TO_DATE(MINUS_BAL_CLOSED_DATE)
                                            BETWEEN '$fromDate' AND '$toDate' AND CASE_STATUS!='11' UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE
                                            TO_DATE(MINUS_BAL_DATE) BETWEEN '$fromDate' AND '$toDate' AND TO_DATE(LTA_REGISTERED_DATE) BETWEEN '$fromDate'
                                            AND '$toDate' AND REGD_DATE IS NULL AND TO_DATE(MINUS_BAL_CLOSED_DATE) BETWEEN '$fromDate' AND '$toDate' AND 
                                            CASE_STATUS!='11' ";
                        $mbCaseCleared = sqlCountData($connection, $mbCaseClearedSQL);
                        $mbCaseClearedPercentage = $actualminusBalCase > 0 ? round(($mbCaseCleared / $actualminusBalCase) * 100, 2) : "0";


                        $totalObjCaseSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(OBJECTION_DATE) BETWEEN '$fromDate' AND '$toDate' 
                                                 AND CASE_STATUS!='11'";
                        $totalObjCase = sqlCountData($connection, $totalObjCaseSQL);

                        $actualminusBalCaseSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(OBJECTION_DATE) BETWEEN '$fromDate' AND '$toDate' 
                        AND TO_DATE(REGD_DATE) BETWEEN '$fromDate' AND '$toDate' AND LTA_SIGNED_DATE IS NULL AND CASE_STATUS!='11'
                        UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(OBJECTION_DATE) BETWEEN '$fromDate' AND '$toDate' AND
                        TO_DATE(LTA_REGISTERED_DATE) BETWEEN '$fromDate' AND '$toDate' AND REGD_DATE IS NULL
                        AND CASE_STATUS!='11' ";
                        $actualObjCase = sqlCountData($connection, $actualminusBalCaseSQL);

                        $totalObjCaseClearedSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(OBJECTION_DATE) BETWEEN '$fromDate' AND '$toDate' 
                        AND FP_REGD_DATE IS NOT NULL AND CASE_STATUS!='11' UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(OBJECTION_DATE) BETWEEN '$fromDate' AND '$toDate' 
                        AND LTA_REGISTERED_DATE IS NOT NULL AND CASE_STATUS!='11'";
                        $totalObjCaseCleared = sqlCountData($connection, $totalObjCaseClearedSQL);
                        $totalObjCaseClearedPercentage = $totalObjCase > 0 ? round(($totalObjCaseClearedSQL / $totalObjCase) * 100, 2) : "0";

                        $actualObjCaseClearedSQL = "SELECT * FROM GPF_CASE_STATUS WHERE TO_DATE(OBJECTION_DATE) BETWEEN '$fromDate' AND '$toDate' 
                        AND TO_DATE(REGD_DATE) BETWEEN '$fromDate' AND '$toDate' AND LTA_REGISTERED_DATE IS NULL AND TO_DATE(FP_SIGNED_DATE)
                        IS NOT NULL AND LTA_SIGNED_DATE IS NULL AND CASE_STATUS!='11' UNION ALL SELECT * FROM GPF_CASE_STATUS WHERE
                        TO_DATE(OBJECTION_DATE) BETWEEN '$fromDate' AND '$toDate' AND TO_DATE(LTA_REGISTERED_DATE) BETWEEN '$fromDate'
                        AND '$toDate' AND REGD_DATE IS NULL AND TO_DATE(LTA_SIGNED_DATE) BETWEEN '$fromDate' AND '$toDate' AND REGD_DATE IS NULL 
                        AND CASE_STATUS!='11' ";
                        $actualObjCaseCleared = sqlCountData($connection, $actualObjCaseClearedSQL);
                        $actualObjCaseClearedPercentage = $actualObjCase > 0 ? round(($actualObjCaseCleared / $actualObjCase) * 100, 2) : "0";
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
                                        <div class="col-lg-8" id="printArea">
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
                                            <span style="float: left;">No. GPF FP Cell/Target/<?php echo $finYear; ?>/KRA</span>
                                            <span style="float: right;">Date : <?php echo date("d / m / Y"); ?></span> <br><br>
                                            <div style="text-align:justify;padding:5px; line-height:1.8em;">
                                                <!-- <h1 id="watermark" class="watermark" style="margin-top: 600px;">INPUT SHEET OF GPF ACCOUNT NO <?php echo $gpfAccountNo; ?></h1> -->
                                                To <br>
                                                The Sr. Accounts Officer<br />
                                                I/C, TMC Section<br /><br />
                                                Sir,<br />
                                                <div style="padding-left: 20px;">
                                                    With reference to your circular no. <?php echo $circularNoDate; ?> regarding submission of
                                                    Monthly report through Google Form template along with acheivements/short-coming in R/O
                                                    fund section during the month of <b><?php echo $months; ?></b> are as follows: <br><br>
                                                    <center>
                                                        <b>General Provident Fund</b>
                                                    </center><br>
                                                    <table cellpadding="10px" cellspacing="0" border="1">
                                                        <tr>
                                                            <td style="text-align: center; font-weight:bold;">Sl </td>
                                                            <td style="text-align: center; font-weight:bold;">HQ query</td>
                                                            <td style="text-align: left; text-align: center; font-weight:bold;">Reply</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center; font-weight:bold;">1</td>
                                                            <td style="text-align: left; font-weight:bold;" colspan="2">
                                                                GPF Final Payment cases due for finalization/authorization (except defective,
                                                                incomplete and advance cases etc.) during the month as per Citizen Charter.
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">a)</td>
                                                            <td style="text-align: left;">
                                                                GPF Final Payment cases due for finalization/authorization (except defective,
                                                                incomplete and advance cases etc.) during the month as per Citizen Charter.
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $monthRegCount; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">b)</td>
                                                            <td style="text-align: left;">
                                                                Total number of cases cleared with percentage
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $wholeCaseCleared; ?> (<?php echo $wholeCaseClearedPercentage; ?>%)</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">c)</td>
                                                            <td style="text-align: left;">
                                                                Actual number of cases cleared with percentage
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $actualCaseCleared; ?> (<?php echo $actualCaseClearedPercentage; ?>%)</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center; font-weight:bold;">2</td>
                                                            <td style="text-align: left; font-weight:bold;" colspan="2">
                                                                Clearence of minus balance cases of GPF during month</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">a)</td>
                                                            <td style="text-align: left;">
                                                                Total number of minus balance cases
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $totalminusBalCase; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">b)</td>
                                                            <td style="text-align: left;">
                                                                Actual number of minus balance cases
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $actualminusBalCase; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">c)</td>
                                                            <td style="text-align: left;">
                                                                Total number of minus balance cases cleared with percentage
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $tmbCaseCleared; ?> (<?php echo $tmbCaseClearedPercentage; ?>%)</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">d)</td>
                                                            <td style="text-align: left;">
                                                                Actual number of minus balance cases cleared with percentage
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $mbCaseCleared; ?> (<?php echo $mbCaseClearedPercentage; ?>%)</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center; font-weight:bold;">3</td>
                                                            <td style="text-align: left; font-weight:bold;" colspan="2">
                                                                GPF complaint cases and clearence as per Citizen Charter during month</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">a)</td>
                                                            <td style="text-align: left;">
                                                                Total number of GPF complaint cases
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $totalObjCase; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">b)</td>
                                                            <td style="text-align: left;">
                                                                Actual number of GPF complaint cases
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $actualObjCase; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">c)</td>
                                                            <td style="text-align: left;">
                                                                Total number of GPF complaint cases cleared with percentage
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $totalObjCaseCleared; ?> (<?php echo $totalObjCaseClearedPercentage; ?>%)</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;">d)</td>
                                                            <td style="text-align: left;">
                                                                Actual number of GPF complaint cases cleared with percentage
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $actualObjCaseCleared; ?> (<?php echo $actualObjCaseClearedPercentage; ?>%)</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div style="margin-top:70px;">
                                                    <b>
                                                        <?php echo $remarks == "" ? "" : " Remarks :"; ?>
                                                    </b> <?php echo $remarks; ?>
                                                    <span style="float:right;">
                                                        Yours faithfully<br /><br />
                                                        <b><?php echo $signature; ?></b>
                                                    </span>

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
                                                    <label for="circular_no_date">Circular no. and date <b class="text-danger">* <?php echo $signatureErr; ?> </b></label>
                                                    <input type="text" name="circular_no_date" id="circular_no_date" class="form-control" required value="(Circular no) dt. (date)">
                                                </div>
                                                <div class="form-group">
                                                    <label for="fin_year">Fin year <b class="text-danger">* <?php echo $finYearErr; ?> </b></label>
                                                    <input type="text" name="fin_year" id="fin_year" class="form-control" required value="2022-23">
                                                </div>
                                                <div class="form-group">
                                                    <label for="remarks">Remarks</b></label>
                                                    <input type="text" name="remarks" id="remarks" class="form-control" value="">
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="generate_cag_report" id="generate_cag_report" tabindex="15">Generate CAG report</button>
                                                <!-- <button type="submit" class="btn btn-info" name="generate_monthly_report" id="generate_monthly_report" tabindex="15">Generate monthly report</button> -->
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
                                                <div class="form-group">
                                                    <label for="month">Month <b class="text-danger">* <?php echo $monthErr; ?> </b></label>
                                                    <input type="text" name="month" id="month" class="form-control" required value="<?php echo date("F, Y"); ?>">
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