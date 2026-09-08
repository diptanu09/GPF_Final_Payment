<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $seriesCode = trim(removeHTMLEntities($dataCred->SeriesID));
    $accountNo = trim(removeHTMLEntities($dataCred->AccountNo));

    $query = "SELECT * FROM VLCS.GP_ACCOUNTS WHERE SERIES_ID='$seriesCode' AND ACCOUNT_NO='$accountNo'";
    $records = sqlCountData($connection, $query);

    if ($records == 0) {
        $resultArray = array("StatusCode" => 205, "Message" => "No subscriber found");
    } else {
        $fetchUsers = sqlFetchData($connection, $query);
        foreach ($fetchUsers as $fetchUsersList) {
            $accountClosedTag = ucwords(strtolower($fetchUsersList['ACCOUNT_CLOSED_TAG']));
            $subscriberName = ucwords(strtolower($fetchUsersList['ACC_HOLDER_NAME']));
            $employeeCode = ucwords(strtolower($fetchUsersList['EMP_CODE']));
            $beneficiaryCode = ucwords(strtolower($fetchUsersList['BENE_CODE']));
            $mobileNo = ucwords(strtolower($fetchUsersList['MOBILE']));
            $closingDate = ucwords(strtolower($fetchUsersList['DATE_OF_CLOSURE']));
        }
        if (($accountClosedTag === "") || ($accountClosedTag === null) || ($accountClosedTag === "N")) {
            $resultArray = array(
                "StatusCode" => 200,
                "Message" => "Subscriber found",
                "SubscriberName" => $subscriberName,
                "EmployeeCode" => $employeeCode,
                "BeneficiaryCode" => $beneficiaryCode,
                "MobileNumber" => $mobileNo
            );
        } else {
            $resultArray = array(
                "StatusCode" => 202,
                "Message" => "The GPF account has closed on " . date("d-m-Y", strtotime($closingDate))
            );
        }
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
