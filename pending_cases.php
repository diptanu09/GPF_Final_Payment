<?php
$pageName = "Pending cases report";
require_once "./top.php";
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
                                <button id="upload_btn" onclick="download()" class="btn btn-primary">
                                    DOWNLOAD</button>
                                <div class="row">
                                    <div class="col-lg-2"></div>
                                    <div class="col-lg-8">
                                        <div id="printArea">
                                            <?php
                                            $sql = "SELECT A.REGD_NO, 'T/'||C.SERIES_DESCR||'/'||B.ACCOUNT_NO GPF_NO, B.SUBSCRIBER_NAME, A.CASE_TYPE, A.RECORD_DAK_DATE, S.REMARKS, 
                                            D.PENSION_LONG_DESCR PENSION_TYPE, E.STATUS_DESCR, TO_DATE(SYSDATE)-TO_DATE(A.RECORD_DAK_DATE) PENDING_DAYS FROM GPF_INWARD A 
                                            INNER JOIN GPF_CASE_STATUS B ON A.REGD_NO=B.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES C ON B.SERIES_ID=C.SERIES_ID INNER JOIN MAS_PENSION_TYPE D
                                            ON B.PENSION_TYPE=D.PENSION_ID INNER JOIN MAS_STATUS E ON B.CASE_STATUS=E.STATUS_ID LEFT JOIN GPF_CASES_REMARKS S ON A.REGD_NO=S.REGD_NO
                                            AND S.REMARKS_TYPE='DL' WHERE B.CASE_STATUS NOT IN (7,8,11,16,17,18,19)  ORDER BY A.REGD_NO, A.RECORD_DAK_DATE";
                                            $count = sqlCountData($connection, $sql);
                                            ?>
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
                                                        No. MIS / FP / Pending cases / <?php echo $count; ?>
                                                    </td>
                                                    <td style="width: 40%; text-align:right;">
                                                        Date : <?php echo date("d / m / Y"); ?></td>
                                                </tr>
                                            </table><br>
                                            <div style="text-align:justify; line-height:1.8em; padding:10px;">

                                                <div style="text-decoration: underline; font-weight:bold; text-align:center;">
                                                    GPF Final Payment Pending Cases
                                                </div>
                                                <br>
                                                <table style="text-align:center; width:100%" border="1" cellspacing=0 cellpadding="10px">
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align: center; font-weight: bold;">REGISTRATION NUMBER</th>
                                                            <th style="text-align: center; font-weight: bold;">ACCOUNT NO</th>
                                                            <th style="text-align: center; font-weight: bold;">NAME</th>
                                                            <th style="text-align: center; font-weight: bold;">PENSION TYPE</th>
                                                            <th style="text-align: center; font-weight: bold;">RECORD DATE</th>
                                                            <th style="text-align: center; font-weight: bold;">CASE TYPE</th>
                                                            <th style="text-align: center; font-weight: bold;">STATUS</th>
                                                            <th style="text-align: center; font-weight: bold;">PENDING DURATION (in days)</th>
                                                            <th style="text-align: center; font-weight: bold;">DELAY REMARKS</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $fetchList = sqlFetchData($connection, $sql);
                                                        foreach ($fetchList as $list) {
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $list['REGD_NO']; ?></td>
                                                                <td><?php echo $list['GPF_NO']; ?></td>
                                                                <td><?php echo $list['SUBSCRIBER_NAME']; ?></td>
                                                                <td><?php echo $list['PENSION_TYPE']; ?></td>
                                                                <td><?php echo $list['RECORD_DAK_DATE'] == "" ? "" : date("d/m/Y", strtotime($list['RECORD_DAK_DATE'])); ?></td>
                                                                <td><?php
                                                                    if ($list['CASE_TYPE'] == "F") {
                                                                        echo "Fresh";
                                                                    } else if ($list['CASE_TYPE'] == "l") {
                                                                        echo "LTA";
                                                                    } else if ($list['CASE_TYPE'] == "C") {
                                                                        echo "Fresh-corresponding";
                                                                    } else if ($list['CASE_TYPE'] == "X") {
                                                                        echo "LTA-corresponding";
                                                                    } else {
                                                                        echo "Revised";
                                                                    }
                                                                    ?></td>
                                                                <td><?php echo $list['STATUS_DESCR']; ?></td>
                                                                <td><?php echo $list['PENDING_DAYS']; ?></td>
                                                                <td><?php echo $list['REMARKS']; ?></td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <div style="font-weight:bolder; margin-top:50px;">
                                                    <div style="text-align: right; font-weight:bold;">Sr. Accounts Officer</div>
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
        </div>

        <?php
        require_once "./includes/copyright.php";
        ?>

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
            AuthorityType: "pending_cases"
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