<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";

if ($_REQUEST['status_value'] && $_REQUEST['user_name']) {
    $statusValue = trim(removeHTMLEntities($_REQUEST['status_value']));
    $userName = trim(removeHTMLEntities($_REQUEST['user_name']));

    $query = "UPDATE USER_ACCOUNTS SET USER_STATUS='$statusValue' WHERE USERNAME='$userName'";
    if (sqlCUDData($connection, $query)) {
        $message = "Success";
    } else {
        $message = "Failed";
    }
    echo $message;
}
