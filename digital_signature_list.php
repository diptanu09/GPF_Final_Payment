<?php
$pageName = "Signature list";
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
                                                <td><?php echo $inwardList['PENSION_LONG_DESCR']; ?></td>
                                                <td>
                                                    <?php
                                                    if ($inwardList['CASE_STATUS'] > 12) {
                                                        if ($inwardList['CASE_STATUS'] < 16) {
                                                            $ltaAuthorityFileFound = 0;
                                                            $ltaAuthorityFile = array_values(array_diff(scandir('pdf_files/unsigned_pdf/'), array('..', '.')));
                                                            foreach ($ltaAuthorityFile as $ltaAuthorityFiles) {
                                                                if ($ltaAuthorityFiles == $inwardList['REGD_NO'] . "_lta_authority.pdf") {
                                                                    $ltaAuthorityFileFound = 1;
                                                                    break;
                                                                }
                                                            }
                                                            if ($ltaAuthorityFileFound == 1) {
                                                    ?>
                                                                <a href="digital_signature.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>&&type=lta_authority">
                                                                    <button class="btn btn-info btn-sm">
                                                                        LTA AUTHORITY SIGN</button>
                                                                </a>
                                                                </a>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <b class="badge badge-info">LTA authority file yet not uploaded for DSC</b>
                                                            <?php
                                                            }
                                                        } else {
                                                            ?>

                                                            <span class="badge badge-info">
                                                                LTA Authority report signed on <?php echo $inwardList['LTA_SIGNED_DATE'] == "" ? "" : date("d/m/Y", strtotime($inwardList['LTA_SIGNED_DATE'])); ?>
                                                            </span>
                                                            <a href="./pdf_files/signed_pdf/lta_authority/<?php echo $inwardList['REGD_NO']; ?>.pdf" target="_blank">
                                                                <button class="btn btn-primary">Download LTA Authority</button>
                                                            </a>
                                                            <?php
                                                        }
                                                    } else {
                                                        if ($inwardList['CASE_STATUS'] < 7) {
                                                            $authorityFileFound = 0;
                                                            $authorityFile = array_values(array_diff(scandir('pdf_files/unsigned_pdf/'), array('..', '.')));
                                                            foreach ($authorityFile as $authorityFiles) {
                                                                if ($authorityFiles == $inwardList['REGD_NO'] . "_authority.pdf") {
                                                                    $authorityFileFound = 1;
                                                                    break;
                                                                }
                                                            }
                                                            if ($authorityFileFound == 1) {
                                                            ?>
                                                                <a href="digital_signature.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>&&type=authority">
                                                                    <button class="btn btn-info btn-sm">
                                                                        AUTHORITY SIGN</button>
                                                                </a>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <b class="badge badge-info">Authority file yet not uploaded for DSC</b>
                                                            <?php
                                                            }
                                                            ?>

                                                        <?php
                                                        } else {
                                                        ?>
                                                            <span class="badge badge-info">
                                                                Authority report signed on <?php echo $inwardList['FP_SIGNED_DATE'] == "" ? "" : date("d/m/Y", strtotime($inwardList['FP_SIGNED_DATE'])); ?>
                                                            </span>
                                                            <a href="./pdf_files/signed_pdf/authority/<?php echo $inwardList['REGD_NO']; ?>.pdf" target="_blank">
                                                                <button class="btn btn-primary">Download Authority</button>
                                                            </a>

                                                            <?php
                                                        }
                                                        if ($inwardList['PENSION_TYPE'] == 2) {
                                                            if ($inwardList['CASE_STATUS'] == 7 && $inwardList['DLIS_SIGNED_DATE'] == "") {
                                                                $dlisAuthorityFileFound = 0;
                                                                $dlisAuthorityFile = array_values(array_diff(scandir('pdf_files/unsigned_pdf/'), array('..', '.')));
                                                                foreach ($dlisAuthorityFile as $dlisAuthorityFiles) {
                                                                    if ($dlisAuthorityFiles == $inwardList['REGD_NO'] . "_dlis.pdf") {
                                                                        $dlisAuthorityFileFound = 1;
                                                                        break;
                                                                    }
                                                                }
                                                                if ($dlisAuthorityFileFound == 1) {
                                                            ?>
                                                                    <a href="digital_signature.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>&&type=dlis">
                                                                        <button class="btn btn-warning btn-sm">
                                                                            DLIS SIGN</button>
                                                                    </a>
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <b class="badge badge-info">DLIS authority file yet not uploaded for DSC</b>
                                                                <?php
                                                                }
                                                                ?>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <span class="badge badge-warning">
                                                                    <?php echo $inwardList['DLIS_SIGNED_DATE'] == "" ? "First sign authority report" : "DLIS report signed on " . date("d/m/Y", strtotime($inwardList['DLIS_SIGNED_DATE'])); ?>
                                                                </span>
                                                                <?php
                                                                if ($inwardList['DLIS_SIGNED_DATE'] != "") {
                                                                ?>
                                                                    <a href="./pdf_files/signed_pdf/dlis/<?php echo $inwardList['REGD_NO']; ?>.pdf" target="_blank">
                                                                        <button class="btn btn-warning">Download DLIS</button>
                                                                    </a>
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