<?php
$pageName = "Application share holder edit";
require_once "./top.php";
if (isset($_REQUEST['sl_no'])) {
    $slNo = trim($_REQUEST['sl_no']);
    if (isset($_REQUEST['save_app_share_holder'])) {
        $seriesCode = trim(removeHTMLEntities($_REQUEST['series_code']));
        $accountNumber = trim($_REQUEST['account_number']);
        $shareHolderName = trim(removeHTMLEntities($_REQUEST['share_holder_name']));
        $personalAddress = trim(removeHTMLEntities($_REQUEST['personal_address']));
        $shareHolderRelation = trim(removeHTMLEntities($_REQUEST['share_holder_relation']));

        if (empty($shareHolderName)) {
            $shareHolderNameErr = "Required";
        } else {
            if (!textPatternValidation($shareHolderName, "a-zA-Z ")) {
                $shareHolderNameErr = "Only letters and white space are allowed";
            } else {
                if (strlen($shareHolderName) > 255) {
                    $shareHolderNameErr = "Maximum length : 255 is allowed";
                }
            }
        }

        if (empty($personalAddress)) {
            $personalAddressErr = "Required";
        } else {
            if (!textPatternValidation($personalAddress, "a-zA-Z0-9. ")) {
                $personalAddressErr = "Only alphanumeric, dot, white space and special chars are allowed";
            } else {
                if (strlen($personalAddress) > 255) {
                    $personalAddressErr = "Maximum length : 255 is allowed";
                }
            }
        }

        if (empty($shareHolderRelation)) {
            $shareHolderRelationErr = "Required";
        } else {
            if (!textPatternValidation($shareHolderRelation, "a-zA-Z ")) {
                $shareHolderRelationErr = "Only letters and white space are allowed";
            }
        }

        if (($shareHolderNameErr == "") && ($personalAddressErr == "") && ($shareHolderRelationErr == "")) {

            $appShareHolderCudQuery = "UPDATE GPF_ACCOUNT_SHARE_HOLDERS SET SHARE_HOLDER_NAME='$shareHolderName', 
            SHARE_HOLDER_RELATION='$shareHolderRelation', SHARE_HOLDER_ADDRESS='$personalAddress', 
            MODIFY_USER='$loginUser', MODIFY_DATE=SYSDATE WHERE SL_NO='$slNo'";

            if (sqlCUDData($connection, $appShareHolderCudQuery)) {
                $error = 0;
                $message = "Successfully saved";
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

    $applicationQuery = "SELECT * FROM GPF_INWARD a LEFT JOIN GPF_APPLICATION b ON a.REGD_NO=b.REGD_NO
    INNER JOIN VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID INNER JOIN GPF_CASE_STATUS
     d ON a.REGD_NO=d.REGD_NO INNER JOIN MAS_PENSION_TYPE e ON d.PENSION_TYPE=e.PENSION_ID
     INNER JOIN VLCS.MM_FINANCIAL_YEAR f ON d.FIN_YEAR_CODE=f.FIN_YEAR_CODE INNER JOIN GPF_ACCOUNT_SHARE_HOLDERS g 
     ON a.REGD_NO=g.REGD_NO WHERE g.SL_NO='$slNo'";

    $fetchApplicationData = sqlFetchData($connection, $applicationQuery);
    foreach ($fetchApplicationData as $fetchApplicationList) {
        $fregistrationNo = $fetchApplicationList['REGD_NO'];
        $fseriesCode = $fetchApplicationList['SERIES_ID'];
        $faccountNumber = $fetchApplicationList['ACCOUNT_NO'];
        $fgpfSeries = "T/" . $fetchApplicationList['SERIES_DESCR'] . '/' . $fetchApplicationList['ACCOUNT_NO'];
        $fsubscriberName = $fetchApplicationList['SUBSCRIBER_NAME'];
        $fpensionType = $fetchApplicationList['PENSION_LONG_DESCR'] . " (" . $fetchApplicationList['PENSION_SHORT_DESCR'] . ")";
        $fshareHolderName = $fetchApplicationList['SHARE_HOLDER_NAME'];
        $fshareHolderRelation = $fetchApplicationList['SHARE_HOLDER_RELATION'];
        $fpersonalAddress = $fetchApplicationList['SHARE_HOLDER_ADDRESS'];
    }
?>

    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <?php
                require_once "./includes/breadcumb.php";
                ?>
                <desgTitle id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <a href="view_application_share_holders.php?regd_no=<?php echo $fregistrationNo; ?>">
                                <button class="btn btn-info">BACK</button>
                            </a>
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
                                                    <label for="account_no">GPF Account number<b class="text-danger"> *</b></label>
                                                    <input type="hidden" name="series_code" id="series_code" value="<?php if ($error === 1) {
                                                                                                                        echo $seriesCode;
                                                                                                                    } else {
                                                                                                                        echo $fseriesCode;
                                                                                                                    } ?>" />
                                                    <input type="hidden" name="account_number" id="account_number" value="<?php if ($error === 1) {
                                                                                                                                echo $accountNumber;
                                                                                                                            } else {
                                                                                                                                echo $faccountNumber;
                                                                                                                            } ?>" />
                                                    <div class=" input-group input-group-default">
                                                        <input type="text" placeholder="GPF Account number" readonly class="form-control" value="<?php echo $fgpfSeries; ?>" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="pension_type">Pension type<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Pension type" readonly class="form-control" value="<?php echo $fpensionType; ?>">
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label for="share_holder_name">Share holder name <b class="text-danger"> * <?php echo $shareHolderNameErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Share holder name" tabindex="1" name="share_holder_name" id="share_holder_name" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                        echo $shareHolderName;
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        echo $fshareHolderName;
                                                                                                                                                                                                    } ?>">

                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="personal_address">Personal address <b class="text-danger"> * <?php echo $personalAddressErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Personal address" name="personal_address" tabindex="3" id="personal_address" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                        echo $personalAddress;
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        echo $fpersonalAddress;
                                                                                                                                                                                                    } ?>">

                                                    </div>
                                                </div>


                                                <button type="submit" class="btn btn-primary" name="save_app_share_holder" id="save_app_share_holder" tabindex="4">Save</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="regd_no">Registration number<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Registration number" readonly class="form-control" value="<?php echo $fregistrationNo; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="subscriber_name">Subscriber name <b class="text-danger"> * </b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Subscriber name" readonly name="subscriber_name" id="subscriber_name" class="form-control" value="<?php echo $fsubscriberName; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="share_holder_relation">Relation <b class="text-danger"> * <?php echo $shareHolderRelationErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Relation" tabindex="2" name="share_holder_relation" id="share_holder_relation" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                        echo $shareHolderRelation;
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        echo $fshareHolderRelation;
                                                                                                                                                                                                    } ?>">
                                                    </div>
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
            </desgTitle>
        </div>
    </div>
    </div>

    <?php
    require_once "./bottom.php";
    ?>

<?php
}
?>