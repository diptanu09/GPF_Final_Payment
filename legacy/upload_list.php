<?php
$pageName = "Upload in HRMS list";
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
                                <table class="table table-bordered table-hover" id="view_inward">
                                    <thead>
                                        <tr>
                                            <th>REGISTRATION NO</th>
                                            <th>ACCOUNT NO</th>
                                            <th>FULL NAME</th>
                                            <th>EMPLOYEE CODE</th>
                                            <th>DOWNLOAD FILES</th>
                                            <th>UPDATE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $inwardQuery = "SELECT T.REGD_NO, T.ACCOUNT_NO, T.PENSION_TYPE, T.SUBSCRIBER_NAME, T.EMPLOYEE_CODE, T.CASE_STATUS, 
                                                       T.DLIS_UPLOAD_DATE, T.FP_UPLOAD_DATE, T.LTA_SIGNED_DATE  FROM
                                                       (SELECT b.REGD_NO, 'T/'||e.SERIES_DESCR||'/'||b.ACCOUNT_NO ACCOUNT_NO, b.PENSION_TYPE, b.SUBSCRIBER_NAME, b.DLIS_UPLOAD_DATE, 
                                                       b.FP_UPLOAD_DATE, b.LTA_SIGNED_DATE, c.PENSION_LONG_DESCR, b.EMPLOYEE_CODE, b.CASE_STATUS FROM GPF_CASE_STATUS b INNER JOIN VLCS.MM_GPF_SERIES e ON b.SERIES_ID=e.SERIES_ID INNER JOIN 
                                                        MAS_PENSION_TYPE c ON b.PENSION_TYPE=c.PENSION_ID WHERE b.CASE_STATUS IN (7, 16) AND PENSION_TYPE!=2 
                                                        UNION ALL SELECT b.REGD_NO, 'T/'||e.SERIES_DESCR||'/'||b.ACCOUNT_NO ACCOUNT_NO, b.PENSION_TYPE, b.SUBSCRIBER_NAME, b.DLIS_UPLOAD_DATE, 
                                                        b.FP_UPLOAD_DATE, b.LTA_SIGNED_DATE, c.PENSION_LONG_DESCR, b.EMPLOYEE_CODE, b.CASE_STATUS FROM GPF_CASE_STATUS b INNER JOIN VLCS.MM_GPF_SERIES e ON b.SERIES_ID=e.SERIES_ID INNER JOIN 
                                                        MAS_PENSION_TYPE c ON b.PENSION_TYPE=c.PENSION_ID WHERE b.CASE_STATUS IN (7, 8, 16) AND PENSION_TYPE=2 AND DLIS_UPLOAD_DATE IS NULL)T WHERE T.EMPLOYEE_CODE>0 GROUP BY T.REGD_NO, 
                                                        T.ACCOUNT_NO, T.PENSION_TYPE, T.SUBSCRIBER_NAME, T.EMPLOYEE_CODE, T.CASE_STATUS, T.DLIS_UPLOAD_DATE, T.FP_UPLOAD_DATE, T.LTA_SIGNED_DATE";
                                        $fetchinward = sqlFetchData($connection, $inwardQuery);
                                        foreach ($fetchinward as $inwardList) {
                                        ?>
                                            <tr>
                                                <td><?php echo $inwardList['REGD_NO']; ?></td>
                                                <td><?php echo $inwardList['ACCOUNT_NO']; ?></td>
                                                <td><?php echo $inwardList['SUBSCRIBER_NAME']; ?></td>
                                                <td><?php echo $inwardList['EMPLOYEE_CODE']; ?></td>
                                                <td><?php
                                                    if ($inwardList['PENSION_TYPE'] == 2) {
                                                    ?>
                                                        <a class="badge badge-warning" href="pdf_files/signed_pdf/dlis/<?php echo $inwardList['REGD_NO']; ?>.pdf" target="_blank">
                                                            DLIS AUTHORITY</a>
                                                    <?php
                                                    }
                                                    ?>
                                                    <?php
                                                    if ($inwardList['CASE_STATUS'] == 16) {
                                                    ?>
                                                        <a class="badge badge-info" href="pdf_files/signed_pdf/lta_authority/<?php echo $inwardList['REGD_NO']; ?>.pdf" target="_blank">
                                                            LTA AUTHORITY</a>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <a class="badge badge-primary" href="pdf_files/signed_pdf/authority/<?php echo $inwardList['REGD_NO']; ?>.pdf" target="_blank">
                                                            FP AUTHORITY</a>
                                                    <?php
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($inwardList['PENSION_TYPE'] == 2) {
                                                        if ($inwardList['FP_UPLOAD_DATE'] == "") {
                                                    ?>
                                                            <button class="btn btn-primary" onclick="uploadUpdate('<?php echo $inwardList['REGD_NO']; ?>','1', '<?php echo $inwardList['CASE_STATUS']; ?>')">
                                                                AUTHORITY</button>
                                                            <?php
                                                        } else {
                                                            if ($inwardList['DLIS_UPLOAD_DATE'] == "") {
                                                            ?>
                                                                <button class="btn btn-info" onclick="uploadUpdate('<?php echo $inwardList['REGD_NO']; ?>','2', '<?php echo $inwardList['CASE_STATUS']; ?>')">
                                                                    DLIS </button>
                                                        <?php
                                                            }
                                                        }
                                                        ?>


                                                    <?php
                                                    } else {
                                                    ?>
                                                        <button class="btn btn-primary" onclick="uploadUpdate('<?php echo $inwardList['REGD_NO']; ?>','1', '<?php echo $inwardList['CASE_STATUS']; ?>')">
                                                            AUTHORITY / LTA</button>
                                                    <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
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
?>
<script>
    $(document).ready(function() {
        $('#view_inward').DataTable();
    });

    function uploadUpdate(registrationNo, togg, caseStatus) {
        let loginUser = "<?php echo $loginUser; ?>";
        if (registrationNo == "") {
            swal({
                title: "Oops!",
                text: "Please provide registration number",
                icon: "error",
                button: "Close",
            });
        } else {
            const credentials = {
                UpdateToggle: togg,
                RegistrationNo: registrationNo,
                CaseStatus: caseStatus,
                LoginUser: loginUser
            }
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/upload_hrms_update.php',
                data: JSON.stringify(credentials),
                success: function(returnValue) {
                    const {
                        StatusCode,
                        Message,
                        ...Others
                    } = returnValue;
                    if (StatusCode === 200) {
                        window.location.href = "upload_list.php";
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
    }
</script>