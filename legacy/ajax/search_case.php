<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));
    $whereClause = "";
    $codeNum = trim(removeHTMLEntities($dataCred->Code_num));
    $accName = strtoupper(trim(removeHTMLEntities($dataCred->Acc_Name)));
    $seriesID = trim(removeHTMLEntities($dataCred->Series));
    $accountNo = strtoupper(trim(removeHTMLEntities($dataCred->Acc_No)));

    $whereClause = "";
    if (!empty($codeNum)) {
        $whereClause = "a.REGD_NO='$codeNum' OR a.EMPLOYEE_CODE='$codeNum' OR a.BENEFICIARY_CODE='$codeNum'";
    } elseif (!empty($accName)) {
        $whereClause = "UPPER(a.SUBSCRIBER_NAME) LIKE '%$accName%'";
    } elseif (!empty($seriesID) && !empty($accountNo)) {
        $whereClause = "a.SERIES_ID='$seriesID' AND a.ACCOUNT_NO='$accountNo'";
    } else {
    }
    $subscribersQuery = "SELECT a.SL_NO, a.REGD_NO, a.SUBSCRIBER_NAME, 'T/'||d.SERIES_DESCR||'/'||a.ACCOUNT_NO GPF_ACC_NO,
    a.EMPLOYEE_CODE, a.BENEFICIARY_CODE, c.STATUS_DESCR FROM GPF_CASE_STATUS a LEFT JOIN
    GPF_APPLICATION b ON a.REGD_NO=b.REGD_NO LEFT JOIN MAS_STATUS c ON a.CASE_STATUS=c.STATUS_ID 
   INNER JOIN VLCS.MM_GPF_SERIES d ON a.SERIES_ID=d.SERIES_ID WHERE $whereClause";
    $records = sqlCountData($connection, $subscribersQuery);
    $data = [];


    if ($records == 0) {
        $resultArray = array("StatusCode" => 205, "Message" => "No subscribers found");
    } else {
        $fetchsubscribers = sqlFetchData($connection, $subscribersQuery);
        foreach ($fetchsubscribers as $subscribersList) {
            $data[] = $subscribersList;
        }
        $resultArray = array(
            "StatusCode" => 200,
            "Message" => "Treasury found",
            "Details" => $data
        );
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
