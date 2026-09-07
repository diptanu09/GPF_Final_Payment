<?php
$pageName = "Authority report lists";
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
                                            <th>PENSION TYPE</th>
                                            <th>OPTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $inwardQuery = "SELECT T.REGD_NO, T.GPF_NO, T.SUBSCRIBER_NAME, T.PENSION_LONG_DESCR, T.PENSION_TYPE, T.DLIS_SIGNED_DATE, T.FP_SIGNED_DATE, T.LTA_SIGNED_DATE, T.CASE_STATUS FROM
                                        (SELECT a.REGD_NO, 'T/'||c.SERIES_DESCR||'/'||a.ACCOUNT_NO GPF_NO, a.SUBSCRIBER_NAME, b.PENSION_LONG_DESCR, a.PENSION_TYPE,
                                        a.FP_SIGNED_DATE, a.DLIS_SIGNED_DATE, a.LTA_SIGNED_DATE, a.CASE_STATUS FROM GPF_CASE_STATUS a INNER JOIN MAS_PENSION_TYPE b ON a.PENSION_TYPE=b.PENSION_ID INNER JOIN
                                        VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID WHERE a.PENSION_TYPE NOT IN (2,6,7) AND a.CASE_STATUS IN (6,15)
                                        UNION ALL SELECT a.REGD_NO, 'T/'||c.SERIES_DESCR||'/'||a.ACCOUNT_NO GPF_NO, a.SUBSCRIBER_NAME, b.PENSION_LONG_DESCR, a.PENSION_TYPE,
                                        a.FP_SIGNED_DATE, a.DLIS_SIGNED_DATE, a.LTA_SIGNED_DATE, a.CASE_STATUS FROM GPF_CASE_STATUS a INNER JOIN MAS_PENSION_TYPE b ON a.PENSION_TYPE=b.PENSION_ID INNER JOIN
                                        VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID WHERE a.PENSION_TYPE NOT IN (6,7) AND a.PENSION_TYPE=2 AND a.DLIS_SIGNED_DATE IS NULL)T ORDER BY T.FP_SIGNED_DATE, T.LTA_SIGNED_DATE";
                                        $fetchinward = sqlFetchData($connection, $inwardQuery);
                                        foreach ($fetchinward as $inwardList) {
                                        ?>
                                            <tr>
                                                <td><?php echo $inwardList['REGD_NO']; ?></td>
                                                <td><?php echo $inwardList['GPF_NO']; ?></td>
                                                <td><?php echo $inwardList['SUBSCRIBER_NAME']; ?></td>
                                                <td> <?php echo $inwardList['PENSION_LONG_DESCR']; ?></td>
                                                <td>
                                                    <?php
                                                    if ($inwardList['PENSION_TYPE'] == 2) {
                                                        if ($inwardList['CASE_STATUS'] > 5) {
                                                            if ($inwardList['CASE_STATUS'] <= 7) {
                                                                if ($inwardList['FP_SIGNED_DATE'] == "") {
                                                    ?>
                                                                    <a href="authority_report.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                        <button class="btn btn-primary btn-sm">
                                                                            FP AUTHORITY
                                                                        </button>
                                                                    </a>
                                                                <?php
                                                                } else {
                                                                ?>

                                                                    <a href="dlis_report.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                        <button class="btn btn-info btn-sm">
                                                                            DLIS
                                                                        </button>
                                                                    </a>
                                                                <?php
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        if ($inwardList['CASE_STATUS'] > 14) {
                                                            if ($inwardList['CASE_STATUS'] < 16) {
                                                                ?>
                                                                <a href="lta_authority_report.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                    <button class="btn btn-primary btn-sm">
                                                                        LTA AUTHORITY
                                                                    </button>
                                                                </a>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <span class="badge badge-info">DIGITALLY SIGNED</span>
                                                                <?php
                                                            }
                                                        } else {
                                                            if ($inwardList['CASE_STATUS'] > 5) {
                                                                if ($inwardList['CASE_STATUS'] < 7) {
                                                                ?>
                                                                    <a href="authority_report.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                        <button class="btn btn-primary btn-sm">
                                                                            FP AUTHORITY
                                                                        </button>
                                                                    </a>
                                                                <?php

                                                                } else {
                                                                ?>
                                                                    <span class="badge badge-info">DIGITALLY SIGNED</span>
                                                    <?php
                                                                }
                                                            }
                                                        }
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
</script>