<?php
$pageName = "Check and Approve";
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
                                <table class="table table-bordered table-hover" id="view_Approve">
                                    <thead>
                                        <tr>
                                            <th>REGISTRATION NO</th>
                                            <th>ACCOUNT NO</th>
                                            <th>FULL NAME</th>
                                            <th>PENSION TYPE</th>
                                            <th>DATE OF EFFECT</th>
                                            <th>STATUS</th>
                                            <th>OPTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $approveQuery = "SELECT T.REGD_NO, T.ACCOUNT_NO, T.SUBSCRIBER_NAME, T.CASE_STATUS, T.PENSION_LONG_DESCR, 
                                                        T.DATE_OF_EFFECT, T.PENSION_SHORT_DESCR, T.STATUS_DESCR FROM
                                                        (SELECT a.REGD_NO, 'T/'||e.SERIES_DESCR||'/'||a.ACCOUNT_NO ACCOUNT_NO, b.SUBSCRIBER_NAME,b.CASE_STATUS,
                                                        d.PENSION_LONG_DESCR, f.DATE_OF_EFFECT, d.PENSION_SHORT_DESCR, g.STATUS_DESCR FROM GPF_INWARD a INNER JOIN
                                                        GPF_CASE_STATUS b ON a.REGD_NO=b.REGD_NO INNER JOIN MAS_PENSION_TYPE d ON b.PENSION_TYPE=d.PENSION_ID 
                                                        INNER JOIN VLCS.MM_GPF_SERIES e ON a.SERIES_ID=e.SERIES_ID INNER JOIN GPF_APPLICATION f ON
                                                        a.REGD_NO=f.REGD_NO INNER JOIN MAS_STATUS G ON b.CASE_STATUS=g.STATUS_ID WHERE b.CASE_STATUS IN (3,4,5,6,7,13,14,15) 
                                                        GROUP BY a.REGD_NO, e.SERIES_DESCR, a.ACCOUNT_NO, b.SUBSCRIBER_NAME, d.PENSION_LONG_DESCR, d.PENSION_SHORT_DESCR,
                                                        f.DATE_OF_EFFECT, b.CASE_STATUS, g.STATUS_DESCR)T";
                                        $fetchApprove = sqlFetchData($connection, $approveQuery);
                                        foreach ($fetchApprove as $ApproveList) {
                                        ?>
                                            <tr>
                                                <td><?php echo $ApproveList['REGD_NO']; ?></td>
                                                <td><?php echo $ApproveList['ACCOUNT_NO']; ?></td>
                                                <td><?php echo $ApproveList['SUBSCRIBER_NAME']; ?></td>
                                                <td> <?php echo $ApproveList['PENSION_LONG_DESCR']; ?> (<?php echo $ApproveList['PENSION_SHORT_DESCR']; ?>)</td>
                                                <td><?php echo date("d-m-Y", strtotime($ApproveList['DATE_OF_EFFECT'])); ?></td>
                                                <td><span class="badge badge-warning"><?php echo $ApproveList['STATUS_DESCR']; ?></span></td>
                                                <td>
                                                    <a href="approval_details.php?regd_no=<?php echo $ApproveList['REGD_NO']; ?>">
                                                        <button class="btn btn-info btn-sm">
                                                            CHECK</button>
                                                    </a>
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
        $('#view_Approve').DataTable();
    });
</script>