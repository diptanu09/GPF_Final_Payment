<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $registrationNo = trim(removeHTMLEntities($dataCred->RegistrationNo));

    $deleteCalculationQuery = "DELETE FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'";
    $deleteAdjCalculationQuery = "DELETE FROM GPF_ADJ_ACC_CALC WHERE REGD_NO='$registrationNo'";
    $deleteAdjPreCalQuery = "DELETE FROM GPF_ADJ_PRE_CAL WHERE REGD_NO='$registrationNo'";

    $caseStatusQuery = "UPDATE GPF_CASE_STATUS SET CASE_STATUS=3, CALCULATION_DATE='' WHERE REGD_NO='$registrationNo'";
    $caseInfoQuery = "UPDATE GPF_AMOUNT_INFO SET CALCULATION_DONE_BY='', OPENING_FIN_YEAR='', OPENING_BAL_AMOUNT='',
                     ACTUAL_DEPOSIT='', EXCESS_DEPOSIT='', WITHDRAWAL='', ACTUAL_INTEREST='', DELAYED_INTEREST='',
                    FINAL_PAYMENT_AMOUNT='', DLIS_AMOUNT='' WHERE REGD_NO='$registrationNo'";


    $caseStatusBool = sqlCUDData($connection, $caseStatusQuery);
    $caseInfoBool = sqlCUDData($connection, $caseInfoQuery);
    $deleteCalculationBool = sqlCUDData($connection, $deleteCalculationQuery);
    $deleteAdjCalculationBool = sqlCUDData($connection, $deleteAdjCalculationQuery);
    $deleteAdjPreCalBool = sqlCUDData($connection, $deleteAdjPreCalQuery);

    if (($caseInfoBool && $deleteCalculationBool && $caseStatusBool) || $deleteAdjCalculationBool || $deleteAdjPreCalBool) {
        $resultArray = array("StatusCode" => 200, "Message" => "Successfully deleted");
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "Try again");
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
