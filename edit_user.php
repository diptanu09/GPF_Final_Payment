<?php
$pageName = "Update user";
require_once "./top.php";
if ($_REQUEST['user_name']) {
    $userName = $_REQUEST['user_name'];
    if (isset($_REQUEST['save_user'])) {
        $fullName = trim(removeHTMLEntities($_REQUEST['full_name']));
        $userRole = trim(removeHTMLEntities($_REQUEST['user_role']));

        if (empty($fullName)) {
            $fullNameErr = "Required";
        } else {
            if (!textPatternValidation($fullName, "a-zA-Z- ")) {
                $fullNameErr = "Only letters, hyphen and whitespace are required";
            }
        }
        if (empty($userRole)) {
            $userRoleErr = "Required";
        }

        if (($fullNameErr == "") && ($userRoleErr == "")) {
            $query = "UPDATE USER_ACCOUNTS SET FULL_NAME='$fullName', USER_ROLE='$userRole' WHERE 
                            USERNAME='$userName'";
            if (sqlCUDData($connection, $query)) {
                $error = 0;
                $message = "Successfully updated user";
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
    $userQuery = "SELECT * FROM USER_ACCOUNTS WHERE USERNAME='$userName'";
    $users = sqlFetchData($connection, $userQuery);
    foreach ($users as $userList) {
        $ffullName = $userList['FULL_NAME'];
        $fuserRole = $userList['USER_ROLE'];
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
                                    <b class="text-<?php echo $textColor; ?>">
                                        <?php echo $message; ?>
                                    </b>
                                    <form action="" method="post">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="full_name">Full name <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Full name" name="full_name" id="full_name" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                    echo $fullName;
                                                                                                                                                                } else {
                                                                                                                                                                    echo $ffullName;
                                                                                                                                                                } ?>" />
                                                    </div>
                                                    <b class="text-danger"><?php echo $fullNameErr; ?></b>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="save_user" id="save_user">Save</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="user_role">User role <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="user_role" id="user_role" class="form-control">
                                                            <option value="">Select role</option>
                                                            <?php
                                                            $roleQuery = "SELECT * FROM MAS_ROLES ORDER BY ROLE_ID";
                                                            $roles = sqlFetchData($connection, $roleQuery);
                                                            foreach ($roles as $rolesList) {
                                                            ?>
                                                                <option value="<?php echo $rolesList['ROLE_ID']; ?>" <?php
                                                                                                                        if ($error == 1) {
                                                                                                                            if ($rolesList['ROLE_ID'] == $userRole) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
                                                                                                                        } else {
                                                                                                                            if ($rolesList['ROLE_ID'] == $fuserRole) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
                                                                                                                        }
                                                                                                                        ?>>
                                                                    <?php echo $rolesList['ROLE_NAME']; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <b class="text-danger"><?php echo $userRoleErr; ?></b>
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
    require_once "./bottom.php";
}
?>