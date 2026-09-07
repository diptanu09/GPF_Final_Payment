<?php
$pageName = "Broad sheet";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);

    $subscriberQuery = "SELECT a.SUBSCRIBER_NAME, b.SERIES_DESCR, a.ACCOUNT_NO FROM GPF_CASE_STATUS a INNER JOIN
                       VLCS.MM_GPF_SERIES b ON a.SERIES_ID=b.SERIES_ID WHERE a.REGD_NO='$registrationNo'";
    $fetchSubscriber = sqlFetchData($connection, $subscriberQuery);
    foreach ($fetchSubscriber as $fetchSubscriberList) {
        $subscriberName = $fetchSubscriberList['SUBSCRIBER_NAME'];
        $gpfAccountNo = "T/" . $fetchSubscriberList['SERIES_DESCR'] . "/" . $fetchSubscriberList['ACCOUNT_NO'];;
    }

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
                                    <center>
                                        <b><?php echo $subscriberName; ?> (<?php echo $gpfAccountNo; ?>)</b>
                                    </center><br>
                                    <div class="fixTableHead">
                                        <!-- <table class="table table-bordered table-hover" id="view_broadSheet"> -->
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>FIN YEAR</th>
                                                    <th>PAY SLIP DATE</th>
                                                    <th>INTEREST DATE</th>
                                                    <th>SUBSCRIPTION AMOUNT</th>
                                                    <th>REFUND AMOUNT</th>
                                                    <th>OTHERS AMOUNT</th>
                                                    <th>WITHDRAWAL AMOUNT</th>
                                                    <th>ADJUSTMENT TYPE</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $broadSheetQuery = "SELECT c.FIN_YEAR, b.PAY_SLIP_DATE, b.INTEREST_DATE, NVL(b.SUBSCRIPTION_AMT,0)SUBSCRIPTION_AMT,
                                                                NVL(b.REFUND_AMT,0)REFUND_AMT,NVL(b.OTHERS_AMT,0)OTHERS_AMT, NVL(b.WITHDRAWAL_AMT,0)WITHDRAWAL_AMT,
                                                                b.ADJUSTMENT_TYPE FROM GPF_CASE_STATUS a INNER JOIN GPF_SUBSCRIPTION b ON a.REGD_NO=b.REGD_NO  
                                                                INNER JOIN VLCS.MM_FINANCIAL_YEAR c ON b.FIN_YEAR_CODE=c.FIN_YEAR_CODE WHERE a.REGD_NO='$registrationNo'
                                                                ORDER BY c.FIN_YEAR, b.PAY_SLIP_DATE, b.INTEREST_DATE";
                                                $fetchbroadSheet = sqlFetchData($connection, $broadSheetQuery);
                                                foreach ($fetchbroadSheet as $broadSheetList) {

                                                ?>
                                                    <tr>
                                                        <td><?php echo $broadSheetList['FIN_YEAR']; ?></td>
                                                        <td><?php echo date("F, Y", strtotime($broadSheetList['PAY_SLIP_DATE'])); ?></td>
                                                        <td><?php echo date("F, Y", strtotime($broadSheetList['INTEREST_DATE'])); ?></td>
                                                        <td> <?php echo $broadSheetList['SUBSCRIPTION_AMT']; ?></td>
                                                        <td> <?php echo $broadSheetList['REFUND_AMT']; ?></td>
                                                        <td> <?php echo $broadSheetList['OTHERS_AMT']; ?></td>
                                                        <td> <?php echo $broadSheetList['WITHDRAWAL_AMT']; ?></td>
                                                        <td> <?php echo $broadSheetList['ADJUSTMENT_TYPE']; ?></td>
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
            $('#view_broadSheet').DataTable();
        });
    </script>
<?php
}
?>