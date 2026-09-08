<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $ddoCode = trim(removeHTMLEntities($dataCred->DDOCode));

    $query = "SELECT * FROM VLCS.STATE_DDO WHERE DDO_CODE='$ddoCode'";
    $records = sqlCountData($connection, $query);

    if ($records == 0) {
        $resultArray = array("StatusCode" => 205, "Message" => "No DDO found");
    } else {
        $fetchDDO = sqlFetchData($connection, $query);
        foreach ($fetchDDO as $fetchDDOList) {
            $treasuryCode = $fetchDDOList['DDO_TREASURY_CODE'];
        }
        if (($treasuryCode == "") || ($treasuryCode == null)) {
            $resultArray = array(
                "StatusCode" => 205,
                "Message" => "No treasury found"
            );
        } else {
            $resultArray = array(
                "StatusCode" => 200,
                "Message" => "Treasury found",
                "TreasuryCode" => $treasuryCode
            );
        }
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
