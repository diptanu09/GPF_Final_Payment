<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));
    $calculationID = trim(removeHTMLEntities($dataCred->CalculationID));
    $toggle = trim(removeHTMLEntities($dataCred->Toggle));
    $type = trim(removeHTMLEntities($dataCred->Type));

    if ($type == "cutmonth") {
        $field = "CUT_MONTH='$toggle'";
    } else {
        $field = "INTEREST_ON_DEPOSIT='$toggle'";
    }
    $updateQuery = "UPDATE GPF_ACCOUNT_CALCULATION SET $field WHERE CALCULATION_ID='$calculationID'";

    if (sqlCUDData($connection, $updateQuery)) {
        $resultArray = array("StatusCode" => 200, "Message" => "Successfully updated");
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "Try again");
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
