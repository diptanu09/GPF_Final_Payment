<?php
$pageName = "Revert messages";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);
    $fromUser = $_SESSION['GPF_USERNAME'];
    if (isset($_REQUEST['save_message'])) {
        $toUser = trim($_REQUEST['to_user']);
        $remarkMessage = trim(removeHTMLEntities($_REQUEST['remark_message']));

        if (empty($toUser)) {
            $toUserErr = "Required";
        } else {
            if (!textPatternValidation($toUser, "a-zA-Z0-9")) {
                $toUserErr = "Only alphanumric and white space are allowed";
            }
        }
        if (empty($remarkMessage)) {
            $remarkMessageErr = "Required";
        } else {
            if (!textPatternValidation($remarkMessage, "^'")) {
                $remarkMessageErr = "Apostophe are not allowed";
            } else {
                if (strlen($remarkMessage) > 255) {
                    $remarkMessageErr = "Maximum length : 255 is allowed";
                }
            }
        }

        if (($toUserErr == "") && ($remarkMessageErr == "")) {

            $revertMsgSLNo = sqlSerialNo($connection, "GPF_REVERT_MESSAGES", "SL_NO");
            $revertInsertQuery = "INSERT INTO GPF_REVERT_MESSAGES VALUES('$revertMsgSLNo','$registrationNo',
                             '$fromUser', '$toUser' ,SYSDATE, '$remarkMessage')";
            if (sqlCUDData($connection, $revertInsertQuery)) {
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
?>

    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <?php
                require_once "./includes/breadcumb.php";
                ?>
                <div id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <a href="approval_details.php?regd_no=<?php echo $registrationNo; ?>" class="btn btn-primary">
                                BACK
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
                                                <div class="form-group mb-10">
                                                    <label for="to_user">To user<b class="text-danger"> * <?php echo $toUserErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="to_user" id="to_user" class="form-control" tabindex="1">
                                                            <option value="">Select user</option>
                                                            <?php
                                                            $role = $_SESSION['GPF_USER_ROLE'] + 1;
                                                            $userQuery = "SELECT * FROM USER_ACCOUNTS WHERE USER_STATUS='Y' AND USER_ROLE='$role'
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

                                                <button type="submit" class="btn btn-primary" name="save_message" id="save_message" tabindex="3">Save</button>
                                            </div>
                                            <div class="col-lg-6">

                                                <div class="form-group">
                                                    <label for="remark_message">Remark message <b class="text-danger"> * <?php echo $remarkMessageErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Remark message" tabindex="2" name="remark_message" id="remark_message" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                echo $remarkMessage;
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo "";
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