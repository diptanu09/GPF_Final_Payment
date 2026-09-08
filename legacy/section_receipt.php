<?php
$pageName = "Transfer case";
require_once "./top.php";
if (isset($_REQUEST['inward_no'])) {
    $inwardNo = trim($_REQUEST['inward_no']);
    if (isset($_REQUEST['change_mark_to'])) {
        $fromUser = trim($_REQUEST['from_user']);
        $remarks = trim($_REQUEST['remarks']);
        $toUser = trim($_REQUEST['to_user']);

        if (empty($toUser)) {
            $toUserErr = "Required";
        } else {
            if (!textPatternValidation($toUser, "a-zA-Z0-9")) {
                $toUserErr = "Only alphanumric and white space are allowed";
            }
        }
        if (empty($fromUser)) {
            $fromUserErr = "Required";
        } else {
            if (!textPatternValidation($fromUser, "a-zA-Z0-9")) {
                $fromUserErr = "Only alphanumric and white space are allowed";
            }
        }
        if (empty($remarks)) {
            $remarksErr = "Required";
        } else {
            if (!textPatternValidation($designation, "^'")) {
                $remarksErr = "Apostophe are not allowed";
            }
        }

        if ($fromUserErr == "" && $toUserErr == "" && $remarksErr == "") {

            $changeQuery = "UPDATE GPF_INWARD SET MARK_TO='$toUser', MARK_DATE=SYSDATE,
                                  MARK_BY='$loginUser' WHERE SL_NO='$inwardNo'";

            $changeBool = sqlCUDData($connection, $changeQuery);

            $trnSLNo = sqlSerialNo($connection, "GPF_TRANSFER_REMARKS", "SL_NO");

            $transferQuery = "INSERT INTO GPF_TRANSFER_REMARKS VALUES('$trnSLNo', '$inwardNo', '$fromUser',
                                '$toUser','$remarks','$loginUser', SYSDATE)";
            $transferBool = sqlCUDData($connection, $transferQuery);

            if ($transferBool && $changeBool) {
                $error = 0;
                $message = "Successfully transferred";
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
    $fromQuery = "SELECT * FROM GPF_INWARD WHERE SL_NO='$inwardNo'";
    $fetchFrom = sqlFetchData($connection, $fromQuery);
    foreach ($fetchFrom as $userList) {
        $fFromUser = $userList['MARK_TO'];
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
                                                    <label for="from_user">From user <b class="text-danger"> * <?php echo $fromUserErr; ?></b></label>
                                                    <input type="hidden" name="from_user" id="from_user" value="<?php echo $fFromUser; ?>">
                                                    <div class="input-group input-group-default">
                                                        <select class="form-control" tabindex="1" disabled>
                                                            <option value="">Select user</option>
                                                            <?php
                                                            $fuserQuery = "SELECT * FROM USER_ACCOUNTS WHERE USER_STATUS='Y'
                                                                           ORDER BY FULL_NAME";
                                                            $fuser = sqlFetchData($connection, $fuserQuery);
                                                            foreach ($fuser as $fusers) {
                                                            ?>
                                                                <option value="<?php echo $fusers['USERNAME']; ?>" <?php
                                                                                                                    if ($error == 1) {
                                                                                                                        if ($fusers['USERNAME'] == $fromUser) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    } else {
                                                                                                                        if ($fusers['USERNAME'] == $fFromUser) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    }
                                                                                                                    ?>>
                                                                    <?php echo $fusers['FULL_NAME']; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <b class="text-danger"><?php echo $fromUserErr; ?></b>
                                                </div>
                                                <div class="form-group">
                                                    <label for="remarks">Remarks<b class="text-danger"> * <?php echo $remarksErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" class="form-control" name="remarks" id="remarks" value=" <?php
                                                                                                                                    if ($error == 1) {
                                                                                                                                        echo $remarks;
                                                                                                                                    } else {
                                                                                                                                    }
                                                                                                                                    ?>">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="change_mark_to" id="change_mark_to" tabindex="3">Save</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group mb-10">
                                                    <label for="to_user">To user<b class="text-danger"> * <?php echo $toUserErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="to_user" id="to_user" class="form-control" tabindex="2">
                                                            <option value="">Select user</option>
                                                            <?php
                                                            $role = $_SESSION['GPF_USER_ROLE'] + 1;
                                                            $userQuery = "SELECT * FROM USER_ACCOUNTS WHERE USER_STATUS='Y'
                                                                           ORDER BY FULL_NAME";
                                                            $user = sqlFetchData($connection, $userQuery);
                                                            foreach ($user as $users) {
                                                            ?>
                                                                <option value="<?php echo $users['USERNAME']; ?>" <?php
                                                                                                                    if ($error == 1) {
                                                                                                                        if ($users['USERNAME'] == $toUser) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    }
                                                                                                                    ?>>
                                                                    <?php echo $users['FULL_NAME']; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
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