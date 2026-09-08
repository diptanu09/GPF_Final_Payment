<?php
$pageName = "Add user";
require_once "./top.php";
if (isset($_REQUEST['save_user'])) {
    $fullName = trim(removeHTMLEntities($_REQUEST['full_name']));
    $userName = trim(removeHTMLEntities($_REQUEST['user_name']));
    $userRole = trim(removeHTMLEntities($_REQUEST['user_role']));
    $password = trim(removeHTMLEntities($_REQUEST['password']));
    if (empty($fullName)) {
        $fullNameErr = "Required";
    } else {
        if (!textPatternValidation($fullName, "a-zA-Z- ")) {
            $fullNameErr = "Only letters, hyphen and whitespace are required";
        }
    }
    if (empty($userName)) {
        $userNameErr = "Required";
    } else {
        if (!textPatternValidation($userName, "a-zA-Z0-9")) {
            $userNameErr = "Only alphanumeric are required";
        } else {
            $dupUserNameQuery = "SELECT * FROM USER_ACCOUNTS WHERE USERNAME = '$userName'";
            $count = sqlCountData($connection, $dupUserNameQuery);
            if ($count > 0) {
                $userNameErr = "Username already taken. Try with another";
            }
        }
    }
    if (empty($userRole)) {
        $userRoleErr = "Required";
    }
    if (empty($password)) {
        $passwordErr = "Required";
    }
    if (($fullNameErr == "") && ($userRoleErr == "") && ($userNameErr == "") && ($passwordErr == "")) {
        $query = "INSERT INTO USER_ACCOUNTS VALUES('$fullName','$userName','$password','Y',
                   '$userRole','$loginUser',SYSDATE,'$loginUser',SYSDATE)";
        if (sqlCUDData($connection, $query)) {
            $error = 0;
            $message = "Successfully created user";
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
                                                                                                                                                                echo "";
                                                                                                                                                            } ?>" />
                                                </div>
                                                <b class="text-danger"><?php echo $fullNameErr; ?></b>
                                            </div>
                                            <div class="form-group">
                                                <label for="user_name">User name <b class="text-danger">*</b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="User name" name="user_name" id="user_name" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                echo $userName;
                                                                                                                                                            } else {
                                                                                                                                                                echo "";
                                                                                                                                                            } ?>">
                                                </div>
                                                <b class="text-danger"><?php echo $userNameErr; ?></b>
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
                                                                                                                        echo "";
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
                                            <div class="form-group">
                                                <label for="password">Password <b class="text-danger">*</b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="password" placeholder="Password" name="password" id="password" class="form-control">
                                                </div>
                                                <b class="text-danger"><?php echo $passwordErr; ?></b>
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
?>