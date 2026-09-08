<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $noOfCopies = trim(removeHTMLEntities($dataCred->NoOfCopies));
    $registrationNo = strtoupper(trim(removeHTMLEntities($dataCred->RegistrationNo)));

    $sql = "SELECT b.DDO_DESG, c.TRES_NAME, a.PERSONAL_ADDRESS FROM GPF_APPLICATION a INNER JOIN VLCS.STATE_DDO b
            ON a.DDO_CODE=b.DDO_CODE INNER JOIN VLCS.STATE_TREASURY c ON a.TREASURY_CODE=c.TRES_CODE WHERE 
            a.REGD_NO = '$registrationNo'";
    $exists = sqlCountData($connection, $sql);
    if ($exists > 0) {
        $fetchDetail = sqlFetchData($connection, $sql);
        foreach ($fetchDetail as $fetchDetails) {
            for ($i = 0; $i < $noOfCopies; $i++) {
                if ($i == 0) {
                    $data[$i]['Copy'] = $fetchDetails['DDO_DESG'];
                    $data[$i]['CopyTo'] = "D";
                } else if ($i == 1) {
                    $data[$i]['Copy'] = $fetchDetails['TRES_NAME'];
                    $data[$i]['CopyTo'] = "T";
                } else if ($i == 2) {
                    $data[$i]['Copy'] = $fetchDetails['PERSONAL_ADDRESS'];
                    $data[$i]['CopyTo'] = "P";
                } else {
                    $data[$i]['CopyTo'] = "O";
                }
            }
        }
        $resultArray = array("StatusCode" => 200, "Message" => "No records found", "Data" => $data);
    } else {
        $resultArray = array("StatusCode" => 205, "Message" => "No records found");
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
