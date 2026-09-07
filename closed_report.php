<?php
$pageName = "Closed report";
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
                                <button id="print_btn" onclick="printPage('printArea')" class="btn btn-primary">
                                    PRINT</button>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div id="printArea">
                                            <table width="100%" cellpadding="5px" cellspacing="0" border="0">
                                                <tr>
                                                    <td width="92%" style="text-align: center; font-size:30px;">
                                                        FORM -70<br />
                                                        (See Paragraph - 408)<br />
                                                        REGISTER OF CLOSED ACCOUNTS
                                                    </td>
                                                </tr>
                                            </table>
                                            <div style="text-align:justify; line-height:1.8em; padding:10px;">

                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align: center;" rowspan="2">Name of Subscriber</th>
                                                            <th style="text-align: center;" rowspan="2">Account no.</th>
                                                            <th style="text-align: center;" rowspan="2">Amount</th>
                                                            <th style="text-align: center;" rowspan="2">Date of closing</th>
                                                            <th style="text-align: center;" colspan="2">Date of authorizing payment</th>
                                                            <th style="text-align: center;" colspan="2">Try. Voucher No. & Date of Payment</th>
                                                            <th style="text-align: center;" colspan="2">Date of receipt of disbursment certificate</th>
                                                            <th style="text-align: center;" rowspan="2">Remarks</th>
                                                        </tr>
                                                        <tr>
                                                            <th>Available Balance</th>
                                                            <th>R.B.</th>
                                                            <th>Available Balance</th>
                                                            <th>R.B.</th>
                                                            <th>Available Balance</th>
                                                            <th>R.B.</th>
                                                        </tr>
                                                        <tr>
                                                            <th style="text-align: center;">1</th>
                                                            <th style="text-align: center;">2</th>
                                                            <th style="text-align: center;">3</th>
                                                            <th style="text-align: center;">4</th>
                                                            <th style="text-align: center;">5</th>
                                                            <th style="text-align: center;">6</th>
                                                            <th style="text-align: center;">7</th>
                                                            <th style="text-align: center;">8</th>
                                                            <th style="text-align: center;">9</th>
                                                            <th style="text-align: center;">10</th>
                                                            <th style="text-align: center;">11</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sql = "SELECT A.SUBSCRIBER_NAME, 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, B.FINAL_PAYMENT_AMOUNT,
                                                                A.FP_SIGNED_DATE, A.LTA_SIGNED_DATE, C.REMARKS FROM GPF_CASE_STATUS A INNER JOIN GPF_AMOUNT_INFO B ON A.REGD_NO=B.REGD_NO
                                                                LEFT JOIN GPF_CASES_REMARKS C ON A.REGD_NO=C.REGD_NO INNER JOIN VLCS.MM_GPF_SERIES D ON A.SERIES_ID=D.SERIES_ID
                                                                WHERE A.PENSION_TYPE NOT IN (6,7,8,15,16,17) AND B.FINAL_PAYMENT_AMOUNT>0 ORDER BY A.FP_SIGNED_DATE, A.LTA_SIGNED_DATE, C.REMARKS";
                                                        $fetchList = sqlFetchData($connection, $sql);
                                                        foreach ($fetchList as $list) {
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $list['SUBSCRIBER_NAME']; ?></td>
                                                                <td><?php echo $list['GPF_ACCOUNT_NO']; ?></td>
                                                                <td><?php echo $list['FINAL_PAYMENT_AMOUNT']; ?></td>
                                                                <td><?php echo $list['FP_SIGNED_DATE'] == "" ? $list['LTA_SIGNED_DATE'] : $list['FP_SIGNED_DATE']; ?></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td><?php echo $list['REMARKS']; ?></td>
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
                    </div>
                </div>
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