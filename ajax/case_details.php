<?php
require_once "../includes/global_functions.php";
require_once "../includes/constants.php";
require_once "../includes/connection.php";
require_once "../includes/header_config.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $dataCred = json_decode(file_get_contents("php://input"));

    $serialNo = trim(removeHTMLEntities($dataCred->SerialNo));

    $regNoSQL = "SELECT * FROM GPF_CASE_STATUS WHERE SL_NO='$serialNo'";
    $fetchReg = sqlFetchData($connection, $regNoSQL);
    foreach ($fetchReg as $regNo) {
        $registrationNo = $regNo['REGD_NO'];
    }


    $caseDetailsSQL = "SELECT A.REGD_NO, 'T/'||E.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, A.SUBSCRIBER_NAME, F.PENSION_LONG_DESCR, F.PENSION_SHORT_DESCR,
              A.EMPLOYEE_CODE, A.BENEFICIARY_CODE,A.MOBILE_NO, D.STATUS_DESCR, B.DEBIT_DURING_YEAR, H.DDO_DESG, B.DDO_CODE, I.TRES_NAME, B.TREASURY_CODE,
              B.TITLE, CASE WHEN A.PENSION_TYPE=6 THEN '---' ELSE B.DESG_TITLE END DESG_TITLE, B.DESIGNATION, C.FINAL_PAYMENT_AMOUNT, C.DLIS_AMOUNT, C.DLIS_ADMISSIBLE, C.MISSING_CREDIT, C.MISSING_DEBIT,
              B.PERSONAL_ADDRESS, B.SPOUSE_NAME, B.RELATION, B.LTA_TO_WHOM, B.LAST_FUND_DEDUCTION, C.INTEREST_ALLOWED_UPTO, B.DATE_OF_EFFECT,
              B.DATE_OF_LTA,A.REGD_DATE, A.ENTERED_DATE, A.PRE_CAL_DATE, A.CALCULATION_DATE, A.CHECKED_DATE, A.APPROVED_DATE, A.FP_SIGNED_DATE, A.MINUS_BAL_CLOSED_DATE,
              A.FP_UPLOAD_DATE, A.MINUS_BAL_DATE, A.OBJECTION_DATE, A.CANCELED_DATE, A.DLIS_SIGNED_DATE, A.DLIS_UPLOAD_DATE, J.TRES_NAME CNTRL_TRES_NAME,
              A.LTA_REGISTERED_DATE, A.LTA_CHECKED_DATE, A.LTA_ENTERED_DATE, A.LTA_APPROVED_DATE, A.LTA_SIGNED_DATE, A.LTA_UPLOAD_DATE 
              FROM GPF_CASE_STATUS A LEFT JOIN GPF_APPLICATION B ON A.REGD_NO=B.REGD_NO LEFT JOIN GPF_AMOUNT_INFO C ON A.REGD_NO=C.REGD_NO 
              LEFT JOIN MAS_STATUS D ON A.CASE_STATUS=D.STATUS_ID LEFT JOIN VLCS.MM_GPF_SERIES E ON A.SERIES_ID=E.SERIES_ID LEFT
              JOIN MAS_PENSION_TYPE F ON A.PENSION_TYPE=F.PENSION_ID INNER JOIN VLCS.MM_FINANCIAL_YEAR G ON A.FIN_YEAR_CODE=G.FIN_YEAR_CODE
              LEFT JOIN VLCS.STATE_DDO H ON B.DDO_CODE=H.DDO_CODE LEFT JOIN VLCS.STATE_TREASURY I ON B.TREASURY_CODE=I.TRES_CODE
              LEFT JOIN VLCS.STATE_TREASURY J ON J.TRES_CODE=I.CNTR_TRES WHERE a.REGD_NO='$registrationNo' AND a.SL_NO='$serialNo' ORDER BY A.REGD_DATE DESC";
    $records = sqlCountData($connection, $caseDetailsSQL);

    if ($records == 0) {
        $resultArray = array("StatusCode" => 205, "Message" => "No case found");
    } else {
        $caseDetails = [];
        $caseRemarkLogs = [];
        $fetchCaseDetail = sqlFetchData($connection, $caseDetailsSQL);
        foreach ($fetchCaseDetail as $fetchCaseDetails) {
            $caseDetails[] = $fetchCaseDetails;
        }
        $caseLogSQL = "SELECT TO_CHAR(TO_DATE(a.ACTION_DATE,'DD-MON-YY'),'DD-MON-YYYY') ACTION_DATE, b.STATUS_DESCR,
                       c.FULL_NAME ACTION_USER FROM GPF_CASES_LOG a INNER JOIN MAS_STATUS b ON a.CASE_STATUS=b.STATUS_ID
                       INNER JOIN USER_ACCOUNTS c ON c.USERNAME=a.ACTION_BY WHERE a.REGD_NO='$registrationNo' 
                       ORDER BY a.SL_NO";
        $fetchCaseLog = sqlFetchData($connection, $caseLogSQL);
        foreach ($fetchCaseLog as $fetchCaseLogs) {
            $caseLogs[] = $fetchCaseLogs;
        }
        $caseRemarksSQL = "SELECT c.REMARK_DESCR REMARKS_TYPE, a.REMARKS, TO_CHAR(TO_DATE(a.CREATE_MODIFY_DATE,'DD-MON-YY'),'DD-MON-YYYY') REMARK_DATE,
                           b.FULL_NAME REMARK_USER FROM GPF_CASES_REMARKS a INNER JOIN USER_ACCOUNTS b ON b.USERNAME=a.CREATE_MODIFY_USER
                           INNER JOIN MAS_CASE_REMARKS c ON a.REMARKS_TYPE=c.CASE_REMARK WHERE a.REGD_NO='$registrationNo' ORDER BY a.REMARKS_ID";
        $fetchRemarksLog = sqlFetchData($connection, $caseRemarksSQL);
        foreach ($fetchRemarksLog as $fetchRemarksLogs) {
            $caseRemarkLogs[] = $fetchRemarksLogs;
        }

        $shareHolderSQL = "SELECT SHARE_HOLDER_NAME, SHARE_HOLDER_RELATION, SHARE_HOLDER_ADDRESS
                           FROM GPF_ACCOUNT_SHARE_HOLDERS WHERE REGD_NO='$registrationNo'";
        $fetchShareHolder = sqlFetchData($connection, $shareHolderSQL);
        foreach ($fetchShareHolder as $fetchShareHolders) {
            $shareHolders[] = $fetchShareHolders;
        }

        $revertSQL = "SELECT b.FULL_NAME FROM_USER, c.FULL_NAME TO_USER, TO_CHAR(TO_DATE(a.DATE_TIME,'DD-MON-YY'),'DD-MON-YYYY') DATE_TIME,
                      a.MESSAGE FROM GPF_REVERT_MESSAGES a INNER JOIN USER_ACCOUNTS b ON a.FROM_USER=b.USERNAME 
                      INNER JOIN USER_ACCOUNTS c ON a.TO_USER=c.USERNAME WHERE a.REGD_NO='$registrationNo'";
        $fetchRevert = sqlFetchData($connection, $revertSQL);
        foreach ($fetchRevert as $fetchReverts) {
            $reverts[] = $fetchReverts;
        }


        $resultArray = array(
            "StatusCode" => 200,
            "Message" => "Subscriber found",
            "Case_details" => $caseDetails,
            "Case_logs" => $caseLogs,
            "Case_remarks" => $caseRemarkLogs,
            "Share_holder" => sizeof($shareHolders) > 0 ? $shareHolders : "",
            "Reverts" => sizeof($reverts) > 0 ? $reverts : ""
        );
    }
}
echo json_encode($resultArray, JSON_PRETTY_PRINT);
