<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $registrationNo = trim(removeHTMLEntities($dataCred->RegistrationNo));
    $closingFinYear = trim(removeHTMLEntities($dataCred->ClosingFinYear));

    $query = "SELECT * FROM VLCS.GP_YEARLY_BALANCES a INNER JOIN GPF_APPLICATION b ON a.SERIES_ID=b.SERIES_ID
              AND a.ACCOUNT_NO=b.ACCOUNT_NO WHERE b.REGD_NO='$registrationNo' AND a.FIN_YEAR_CODE='$closingFinYear'";
    $records = sqlCountData($connection, $query);

    if ($records == 0) {
        $resultArray = array("StatusCode" => 205, "Message" => "No balance found");
    } else {
        $fetchBal = sqlFetchData($connection, $query);
        foreach ($fetchBal as $fetchBalList) {
            $closingBalance = $fetchBalList['CL_BAL_WITHDRAWL'];
        }
        if (($closingBalance == "") || ($closingBalance == null)) {
            $resultArray = array(
                "StatusCode" => 205,
                "Message" => "Unable to fetch balance"
            );
        } else {
            $resultArray = array(
                "StatusCode" => 200,
                "Message" => "Treasury found",
                "ClosingBalanceAmount" => $closingBalance
            );
        }
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
