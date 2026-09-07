<?php

require_once "./includes/constants.php";
require_once "./includes/connection.php";
require_once "./includes/global_functions.php";

if (isset($_REQUEST['login_button'])) {
    $userName = trim(removeHTMLEntities($_REQUEST['username']));
    $password = trim(removeHTMLEntities($_REQUEST['password']));
    if (empty($userName) || empty($password)) {
        $loginErr = "Please provide all the fields";
    } else {
        $query = "SELECT * FROM USER_ACCOUNTS WHERE USERNAME='$userName' AND PASSWORD='$password'";
        $count = sqlCountData($connection, $query);

        if ($count > 0) {
            $query = "";
            $count = 0;
            $query = "SELECT * FROM USER_ACCOUNTS WHERE USERNAME='$userName' AND USER_STATUS='Y'";
            $count = sqlCountData($connection, $query);
            if ($count > 0) {
                $query = "";
                $query = "SELECT USER_ROLE FROM USER_ACCOUNTS WHERE USERNAME='$userName'";
                $fetchData = sqlFetchData($connection, $query);
                foreach ($fetchData as $list) {
                    $_SESSION['GPF_USER_ROLE'] = $list['USER_ROLE'];
                }
                $_SESSION['GPF_USERNAME'] = $userName;
                redirectPage("home.php");
            } else {
                $loginErr = "You are in-active. Please contact admin";
            }
        } else {
            $loginErr = "Wrong username or password";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>GPF Final Payment || Login</title>
    <link href="./assets/css/font-awesome.min.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/bootstrap.min.css?<?php echo time(); ?>" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/login_style.css?<?php echo time(); ?>" />
</head>

<body>

    <div class="signinform">
        <h1>GPF FINAL PAYMENT SYSTEM</h1>
        <!-- container -->
        <div class="container">
            <!-- main content -->
            <div class="w3l-form-info">
                <div class="w3_info">
                    <form action="" method="post">
                        <div class="input-group">
                            <span><i class="fa fa-user" aria-hidden="true"></i></span>
                            <input type="text" placeholder="Username" name="username" required>
                        </div>
                        <div class="input-group">
                            <span><i class="fa fa-key" aria-hidden="true"></i></span>
                            <input type="password" placeholder="Password" name="password" required>
                        </div>
                        <button class="btn btn-primary btn-block" name="login_button" type="submit">Login</button>
                    </form>
                    <p class="text-danger font-weight-bold" style="margin-top: 15px;"><?php echo $loginErr; ?></p>
                </div>
            </div>
            <!-- //main content -->
        </div>
        <!-- //container -->
        <!-- footer -->
        <?php
        require_once "./includes/copyright.php";
        ?>
        <!-- footer -->
    </div>

</body>

</html>