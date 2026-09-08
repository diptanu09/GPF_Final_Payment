<?php
$pageName = "Edit amount";
require_once "./top.php";
if (isset($_REQUEST['calculation_id'])) {
    $calculationID = $_REQUEST['calculation_id'];
    if (isset($_REQUEST['save_amount'])) {

        $subscription = trim(removeHTMLEntities($_REQUEST['subscription']));
        $others = trim(removeHTMLEntities($_REQUEST['others']));
        $refund = trim(removeHTMLEntities($_REQUEST['refund']));
        $withdrawal = trim(removeHTMLEntities($_REQUEST['withdrawal']));
        $advance = trim(removeHTMLEntities($_REQUEST['advance']));


        if (empty($subscription) && $subscription != 0) {
            $subscription = "0";
        } else {
            if (!textPatternValidation($subscription, "0-9")) {
                $subscriptionErr = "Only numeric are required";
            }
        }

        if (empty($others) && $others != 0) {
            $others = "0";
        } else {
            if (!textPatternValidation($others, "0-9")) {
                $othersErr = "Only numeric are required";
            }
        }
        if (empty($refund) && $refund != 0) {
            $refund = "0";
        } else {
            if (!textPatternValidation($refund, "0-9")) {
                $refundErr = "Only numeric are required";
            }
        }
        if (empty($withdrawal) && $withdrawal != 0) {
            $withdrawal = "0";
        } else {
            if (!textPatternValidation($withdrawal, "0-9")) {
                $withdrawalErr = "Only numeric are required";
            }
        }
        if (empty($advance) && $advance != 0) {
            $advance = "0";
        } else {
            if (!textPatternValidation($advance, "0-9")) {
                $advanceErr = "Only numeric are required";
            }
        }


        $deposit = $subscription + $refund + $others;
        $debit = $withdrawal + $advance;

        if (($subscriptionErr == "") && ($refundErr == "") && ($othersErr == "") && ($withdrawalErr == "") && ($advanceErr == "")) {
            $query = "UPDATE GPF_ACCOUNT_CALCULATION SET SUBSCRIPTION_AMT='$subscription', REFUND_AMT='$refund',
                      OTHERS_AMT='$others', WITHDRAWAL_AMT='$withdrawal', ADVANCE_AMT='$advance', DEPOSIT='$deposit',
                      WITHDRAWAL='$debit', CREATE_MODIFY_USER='$loginUser' WHERE CALCULATION_ID='$calculationID'";
            if (sqlCUDData($connection, $query)) {
                $error = 0;
                $message = "Successfully updated";
                $textColor = "success";
            } else {
                $error = 1;
                $message = "Data not saved, try again";
                $textColor = "danger";
            }
        } else {
            $error = 1;
            $message = "Recorrect errors";
            $textColor = "danger";
        }
    }
    $getAmountSQL = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE CALCULATION_ID='$calculationID'";
    $fetchAmount = sqlFetchData($connection, $getAmountSQL);
    foreach ($fetchAmount as $amtList) {
        $regdNo = $amtList['REGD_NO'];
        $fsubscription =  $amtList['SUBSCRIPTION_AMT'];
        $frefund = $amtList['REFUND_AMT'];
        $fothers = $amtList['OTHERS_AMT'];
        $fwithdrawal = $amtList['WITHDRAWAL_AMT'];
        $fadvance = $amtList['ADVANCE_AMT'];
        $pmonth = date("F, Y", strtotime($amtList['PAY_SLIP_DATE']));
        $imonth = date("F, Y", strtotime($amtList['INTEREST_DATE']));
    }
?>
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <?php
                require_once "./includes/breadcumb.php";
                ?>
                <a href="calculation_list.php?regd_no=<?php echo $regdNo; ?>" class="btn btn-info btn-sm">BACK</a>
                <section id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <b class="text-<?php echo $textColor; ?>">
                                        <?php echo $message; ?>
                                    </b>
                                    <center>
                                        <span class="alert alert-info">
                                            Registration number : <?php echo $regdNo; ?>,
                                            Pay slip date : <?php echo $pmonth; ?>,
                                            Interest date : <?php echo $imonth; ?>
                                        </span>
                                    </center><br />

                                    <form action="" method="post">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="subscription">Subscription <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Subscription" name="subscription" id="subscription" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                            echo $subscription;
                                                                                                                                                                        } else {
                                                                                                                                                                            echo $fsubscription;
                                                                                                                                                                        } ?>" />
                                                    </div>
                                                    <b class="text-danger"><?php echo $subscriptionErr; ?></b>
                                                </div>
                                                <div class="form-group">
                                                    <label for="others">Others <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Others" name="others" id="others" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                            echo $others;
                                                                                                                                                        } else {
                                                                                                                                                            echo $fothers;
                                                                                                                                                        } ?>" />
                                                    </div>
                                                    <b class="text-danger"><?php echo $othersErr; ?></b>
                                                </div>
                                                <div class="form-group">
                                                    <label for="withdrawal">Withdrawal <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Withdrawal" name="withdrawal" id="withdrawal" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                        echo $withdrawal;
                                                                                                                                                                    } else {
                                                                                                                                                                        echo $fwithdrawal;
                                                                                                                                                                    } ?>" />
                                                    </div>
                                                    <b class="text-danger"><?php echo $withdrawalErr; ?></b>
                                                </div>


                                                <button type="submit" class="btn btn-primary" name="save_amount" id="save_amount">Save</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="refund">Refund <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Refund" name="refund" id="refund" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                            echo $refund;
                                                                                                                                                        } else {
                                                                                                                                                            echo $frefund;
                                                                                                                                                        } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $refundErr; ?></b>
                                                </div>
                                                <div class="form-group">
                                                    <label for="advance">Advance <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Advance" name="advance" id="advance" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                echo $advance;
                                                                                                                                                            } else {
                                                                                                                                                                echo $fadvance;
                                                                                                                                                            } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $advanceErr; ?></b>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
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
}
require_once "./bottom.php";
?>