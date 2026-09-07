<?php
$mainPage = "Admin";
$pageName = "Minus balance list and remarks";
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
                                <?php
                                if (isset($_REQUEST['regd_no'])) {
                                    $registrationNo = trim($_REQUEST['regd_no']);
                                    if (isset($_POST['case_mb'])) {
                                        $remarksID = date("YmdHis");
                                        $remarks = trim($_POST['remarks']);
                                        $amountRecovered = trim($_POST['amount_recovered']);

                                        if (empty($remarks)) {
                                            $remarksErr = "Required";
                                        } else {
                                            if (!textPatternValidation($remarks, "^'")) {
                                                $remarksErr = "Apostophe are not allowed";
                                            } else {
                                                if (strlen($remarks) > 255) {
                                                    $remarksErr = "Maximum length : 255";
                                                }
                                            }
                                        }

                                        if (empty($amountRecovered)) {
                                            $amountRecoveredErr = "Required";
                                        } else {
                                            if (!textPatternValidation($amountRecovered, "0-9")) {
                                                $remarksErr = "Only numeric allowed";
                                            }
                                        }


                                        if (($remarksErr == "") && ($amountRecoveredErr == "")) {
                                            $errors = 0;
                                            $mbRemarkCUDSQL = "INSERT INTO GPF_MINUS_BALANCE_REMARKS 
                                            VALUES('$remarksID', '$registrationNo','$remarks', '$amountRecovered', '$loginUser',SYSDATE)";

                                            $remarkCUDSQL = "INSERT INTO GPF_CASES_REMARKS 
                                                                 VALUES('$remarksID', '$registrationNo','MBC','$remarks','$loginUser',SYSDATE)";

                                            $statusCUDSQL = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='18', MINUS_BAL_CLOSED_DATE = SYSDATE
                                                             WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";

                                            $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");
                                            $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo','18',SYSDATE, '$loginUser')";


                                            $mbRemBool = sqlCUDData($connection, $mbRemarkCUDSQL);
                                            $caseRemBool = sqlCUDData($connection, $remarkCUDSQL);
                                            $statusBool = sqlCUDData($connection, $statusCUDSQL);
                                            $caseLogBool = sqlCUDData($connection, $caseLogQuery);

                                            if ($mbRemBool && $caseRemBool && $statusBool && $caseLogBool) {
                                                $remarksSuccess = "Successfully closed minus balance";
                                            } else {
                                                $remarksErr = "Data not saved. Try again later";
                                            }
                                        } else {
                                            $errors = 1;
                                        }
                                    }
                                ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            Registration number : <?php echo $registrationNo; ?> <br>
                                            <b><span class="text-success"><?php echo $remarksSuccess; ?></span></b> <br>

                                            <form action="" method="post">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label for="remarks"><b>Remarks : <span class="text-danger">* <?php echo $remarksErr; ?></span></b> </label>
                                                            <input type="text" name="remarks" tabindex="1" maxlength="255" class="form-control" value="<?php if ($errors == 1) {
                                                                                                                                                            echo $remarks;
                                                                                                                                                        } else {
                                                                                                                                                            echo "";
                                                                                                                                                        } ?>" required />
                                                        </div>
                                                        <div class="form-group">
                                                            <button type="submit" name="case_mb" tabindex="3" class="btn btn-primary">CLOSE MINUS BALANCE</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label for="amount_recovered"><b>Amount recovered : <span class="text-danger">* <?php echo $amountRecoveredErr; ?></span> </b></label>
                                                            <input type="text" name="amount_recovered" tabindex="2" maxlength="255" class="form-control" value="<?php if ($errors == 1) {
                                                                                                                                                                    echo $amountRecovered;
                                                                                                                                                                } else {
                                                                                                                                                                    echo "";
                                                                                                                                                                } ?>" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                <?php
                                } else {
                                ?>
                                    <table class="table table-bordered table-hover" id="minus_bal_cases">
                                        <thead>
                                            <tr>
                                                <th>REGISTRATION NUMBER</th>
                                                <th>SUBSCRIBER NAME</th>
                                                <th>OPTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $usersQuery = "SELECT SUBSCRIBER_NAME, REGD_NO FROM GPF_CASE_STATUS
                                                                WHERE CASE_STATUS=10";
                                            $fetchUsers = sqlFetchData($connection, $usersQuery);
                                            foreach ($fetchUsers as $usersList) {
                                            ?>
                                                <tr>
                                                    <td><?php echo $usersList['REGD_NO']; ?></td>
                                                    <td><?php echo $usersList['SUBSCRIBER_NAME']; ?></td>
                                                    <td>
                                                        <a href="minus_balance_remarks.php?regd_no=<?php echo $usersList['REGD_NO']; ?>">
                                                            <button class="btn btn-warning">CLOSE MINUS BALANCE </button>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                <?php

                                }
                                ?>
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
        $('#minus_bal_cases').DataTable();
    });
</script>