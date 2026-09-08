<?php
$pageName = "Pre-calculation";
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
                                            <th>DATE OF EFFECT</th>
                                            <th>OPTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $inwardQuery = "SELECT a.REGD_NO, 'T/'||e.SERIES_DESCR||'/'||a.ACCOUNT_NO ACCOUNT_NO, b.SUBSCRIBER_NAME,
                                                        d.PENSION_LONG_DESCR, b.CASE_STATUS, f.DATE_OF_EFFECT, d.PENSION_SHORT_DESCR FROM GPF_INWARD a INNER JOIN
                                                        GPF_CASE_STATUS b ON a.REGD_NO=b.REGD_NO INNER JOIN MAS_PENSION_TYPE d ON b.PENSION_TYPE=d.PENSION_ID 
                                                        INNER JOIN VLCS.MM_GPF_SERIES e ON a.SERIES_ID=e.SERIES_ID INNER JOIN GPF_APPLICATION f ON
                                                        a.REGD_NO=f.REGD_NO  WHERE b.CASE_STATUS NOT IN (7,8,10,11,16,17,18,19) GROUP BY a.REGD_NO, e.SERIES_DESCR,
                                                         a.ACCOUNT_NO, b.SUBSCRIBER_NAME, d.PENSION_LONG_DESCR, d.PENSION_SHORT_DESCR, f.DATE_OF_EFFECT, b.CASE_STATUS
                                                         ";
                                        $fetchinward = sqlFetchData($connection, $inwardQuery);
                                        foreach ($fetchinward as $inwardList) {
                                            $appQuery = "SELECT * FROM GPF_AMOUNT_INFO WHERE REGD_NO='" . $inwardList['REGD_NO'] . "'";
                                            $count = sqlCountData($connection, $appQuery);
                                            if ($count > 0) {
                                                $btnbg = "warning";
                                                $btnText = "EDIT";
                                            } else {
                                                $btnbg = "primary";
                                                $btnText = "ENTRY";
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo $inwardList['REGD_NO']; ?></td>
                                                <td><?php echo $inwardList['ACCOUNT_NO']; ?></td>
                                                <td><?php echo $inwardList['SUBSCRIBER_NAME']; ?></td>
                                                <td> <?php echo $inwardList['PENSION_LONG_DESCR']; ?> (<?php echo $inwardList['PENSION_SHORT_DESCR']; ?>)</td>
                                                <td><?php echo date("d-m-Y", strtotime($inwardList['DATE_OF_EFFECT'])); ?></td>


                                                <td>

                                                    <?php
                                                    if ($inwardList['CASE_STATUS'] >= 12) {
                                                        if ($inwardList['CASE_STATUS'] > 14) {
                                                            if ($inwardList['CASE_STATUS'] == 15) {
                                                                echo "<b class='badge badge-warning'>Case fully approved</b>";
                                                            } else if ($inwardList['CASE_STATUS'] == 16) {
                                                                echo "<b class='badge badge-danger'>Case has been digitally signed. </b>";
                                                            } else {
                                                    ?>
                                                                <b class='badge badge-warning'>OBJECTIONED </b>
                                                                <a href="pre_calculation.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                    <button class="btn btn-<?php echo $btnbg; ?> btn-sm">
                                                                        <?php
                                                                        echo $btnText;
                                                                        ?></button>
                                                                </a>
                                                            <?php
                                                            }
                                                            ?>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <a href="pre_calculation.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                <button class="btn btn-<?php echo $btnbg; ?> btn-sm">
                                                                    <?php
                                                                    echo $btnText;
                                                                    ?></button>
                                                            </a>
                                                        <?php
                                                        }
                                                        if ($count > 0) {
                                                        ?>
                                                            <a href="view_broad_sheet.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                <button class="btn btn-info btn-sm"> BROAD SHEET</button>
                                                            </a>
                                                            <a href="view_missing_credit.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                <button class="btn btn-primary btn-sm"> MISSING CREDIT</button>
                                                            </a>
                                                            <?php
                                                        }
                                                    } else {
                                                        if ($inwardList['CASE_STATUS'] > 5) {
                                                            if ($inwardList['CASE_STATUS'] == 6) {
                                                                echo "<b class='badge badge-warning'>Case fully approved</b>";
                                                            } else if ($inwardList['CASE_STATUS'] == 7) {
                                                                echo "<b class='badge badge-danger'>Case has been digitally signed. </b>";
                                                            } else {
                                                            ?>
                                                                <b class='badge badge-warning'>OBJECTIONED </b>
                                                                <a href="pre_calculation.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                    <button class="btn btn-<?php echo $btnbg; ?> btn-sm">
                                                                        <?php
                                                                        echo $btnText;
                                                                        ?></button>
                                                                </a>
                                                            <?php
                                                            }
                                                            ?>
                                                            <?php
                                                        } else {
                                                            if ($inwardList['CASE_STATUS'] == 5) {
                                                                echo "<b class='badge badge-warning'>Case has been checked</b>";
                                                            } else {
                                                            ?>
                                                                <a href="pre_calculation.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                    <button class="btn btn-<?php echo $btnbg; ?> btn-sm">
                                                                        <?php
                                                                        echo $btnText;
                                                                        ?></button>
                                                                </a>
                                                            <?php
                                                            }
                                                        }
                                                        if ($count > 0) {
                                                            ?>
                                                            <a href="view_broad_sheet.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                <button class="btn btn-info btn-sm"> BROAD SHEET</button>
                                                            </a>
                                                            <a href="view_missing_credit.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                <button class="btn btn-primary btn-sm"> MISSING CREDIT</button>
                                                            </a>
                                                    <?php
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