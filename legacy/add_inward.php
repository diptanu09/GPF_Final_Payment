<?php
$pageName = "Case registration";
require_once "./top.php";
if (isset($_REQUEST['save_inward'])) {
    $seriesCode = trim(removeHTMLEntities($_REQUEST['series_id']));
    $accountNumber = trim(removeHTMLEntities($_REQUEST['account_number']));
    $subscriberName = trim(removeHTMLEntities($_REQUEST['subscriber_name']));
    $finYear = trim(removeHTMLEntities($_REQUEST['fin_year']));
    $section = trim(removeHTMLEntities($_REQUEST['section']));
    $caseType = trim(removeHTMLEntities($_REQUEST['case_type']));
    $letterNo = trim(removeHTMLEntities($_REQUEST['letter_no']));
    $appliedDate = trim(removeHTMLEntities($_REQUEST['applied_date']));
    $letterType = trim(removeHTMLEntities($_REQUEST['letter_type']));
    $pensionType = trim(removeHTMLEntities($_REQUEST['pension_type']));
    $mobileNumber = trim(removeHTMLEntities($_REQUEST['mobile_number']));
    $employeeCode = trim(removeHTMLEntities($_REQUEST['employee_code']));
    $beneficiaryCode = trim(removeHTMLEntities($_REQUEST['beneficiary_code']));
    $recordNo = trim(removeHTMLEntities($_REQUEST['record_no']));
    $recordDate = trim(removeHTMLEntities($_REQUEST['record_date']));

    if (empty($seriesCode)) {
        $seriesCodeErr = "Required";
    } else {
        if (!textPatternValidation($seriesCode, "0-9")) {
            $seriesCodeErr = "Only numeric are allowed";
        }
    }
    if (empty($accountNumber)) {
        $accountNumberErr = "Required";
    } else {
        if (!textPatternValidation($accountNumber, "0-9")) {
            $accountNumberErr = "Only numberic are allowed";
        }
    }
    if (empty($subscriberName)) {
        $subscriberNameErr = "Required";
    } else {
        if (!textPatternValidation($subscriberName, "a-zA-Z(). ")) {
            $subscriberNameErr = "Only letters, dots and white space are allowed";
        }
    }
    if (empty($finYear)) {
        $finYearErr = "Required";
    } else {
        if (!textPatternValidation($finYear, "0-9")) {
            $finYearErr = "Only numberic are allowed";
        }
    }
    if (empty($section)) {
        $sectionErr = "Required";
    } else {
        if (!textPatternValidation($section, "0-9")) {
            $sectionErr = "Only numeric are allowed";
        }
    }

    $formatSeriesCode = strlen($seriesCode) < 2 ? "0" . $seriesCode : $seriesCode;
    $registrationNo = $finYear . $formatSeriesCode . $accountNumber;

    if (empty($caseType)) {
        $caseTypeErr = "Required";
    } else {
        if (!textPatternValidation($caseType, "a-zA-Z")) {
            $caseTypeErr = "Only letters are allowed";
        } else {
            $dupCaseQuery = "SELECT * FROM GPF_INWARD a INNER JOIN GPF_CASE_STATUS b ON a.REGD_NO=b.REGD_NO
                             WHERE a.REGD_NO = '$registrationNo' AND b.CASE_STATUS != '11'";
            $accountExists = sqlCountData($connection, $dupCaseQuery);
            if ($accountExists == 0) {
                if ($caseType == "C" || $caseType == "R") {
                    $caseTypeErr = "The case has not been registered yet ";
                }
            } else {
                if ($caseType == "F" || $caseType == "L") {
                    $caseTypeErr = "The case has been registered previously as fresh case. Try with corresponding/revised fp case";
                }
            }
        }
    }

    if (empty($letterNo)) {
        $letterNoErr = "";
    } else {
        if (textPatternValidation($letterNo, "'")) {
            $letterNoErr = "Apostophe not allowed";
        }
    }
    if (empty($appliedDate)) {
        $appliedDateErr = "Required";
    } else {
        $appliedDate = date("d-M-Y", strtotime($appliedDate));
    }
    if (empty($letterType)) {
        $letterTypeErr = "Required";
    } else {
        if (!textPatternValidation($letterType, "a-zA-Z")) {
            $letterTypeErr = "Only letters are allowed";
        }
    }
    if (empty($pensionType)) {
        $pensionTypeErr = "Required";
    } else {
        if (!textPatternValidation($pensionType, "0-9")) {
            $pensionTypeErr = "Only numeric are allowed";
        }
    }
    if (empty($recordNo) && $recordNo != 0) {
        $recordNo = "0";
    } else {
        if (!textPatternValidation($recordNo, "0-9")) {
            $recordNoErr = "Only numeric are allowed";
        }
    }
    if (empty($recordDate)) {
        $recordDateErr = "Required";
    } else {
        $recordDate = date("d-M-Y", strtotime($recordDate));
    }
    if (empty($mobileNumber) && $mobileNumber != 0) {
        $mobileNumber = "0";
    } else {
        if (!textPatternValidation($pensionType, "0-9")) {
            $mobileNumberErr = "Only numeric are allowed";
        } else {
            if ($mobileNumber == 0) {
                $mobileNumberErr == "";
            } else {
                if (strlen($mobileNumber) != 10) {
                    $mobileNumberErr = "Must be 10 digits";
                }
            }
        }
    }
    if (empty($employeeCode)) {
        $employeeCode = "0";
    } else {
        if (!textPatternValidation($employeeCode, "0-9")) {
            $employeeCodeErr = "Only numeric are allowed";
        }
    }
    if (empty($beneficiaryCode)) {
        $beneficiaryCode = "0";
    } else {
        if (!textPatternValidation($beneficiaryCode, "0-9")) {
            $beneficiaryCodeErr = "Only numeric are allowed";
        }
    }

    $markTo = $loginUser;
    $slNo = sqlSerialNo($connection, "GPF_INWARD", "SL_NO");
    $caseSLNo = sqlSerialNo($connection, "GPF_CASE_STATUS", "SL_NO");
    $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");

    if (($seriesCodeErr == "") && ($accountNumberErr == "") && ($subscriberNameErr == "") && ($finYearErr == "") && ($sectionErr == "")
        && ($caseTypeErr == "") && ($letterNoErr == "") && ($appliedDateErr == "") && ($letterTypeErr == "") && ($pensionTypeErr == "")
        && ($mobileNumberErr == "") && ($employeeCodeErr == "") && ($beneficiaryCodeErr == "") && ($recordNoErr == "") && ($recordDateErr == "")
    ) {
        $inwardQuery = "INSERT INTO GPF_INWARD VALUES('$slNo','$registrationNo','$seriesCode', '$accountNumber','$letterType','$letterNo', '$appliedDate', 
                   '$finYear', '$caseType', '$section', '$recordNo', '$recordDate', '$markTo', SYSDATE, '$loginUser', 
                    '$loginUser',SYSDATE,'$loginUser',SYSDATE)";

        if ($caseType == "F") {
            $caseStatus = 1;
            $status = 1;
        } elseif ($caseType == "L") {
            $caseStatus = 12;
            $status = 12;
        } elseif ($caseType == "R") {
            $originalStatSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO='$registrationNo'";
            $fetchOrStat = sqlFetchData($connection, $originalStatSQL);
            foreach ($fetchOrStat as $list) {
                $caseStatus = $list['CASE_STATUS'];
                $status = $list['CASE_STATUS'];
            }
        } else {
            $originalStatSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO='$registrationNo' AND CASE_STATUS != '11'";
            $fetchOrStat = sqlFetchData($connection, $originalStatSQL);
            foreach ($fetchOrStat as $list) {
                $caseStatus = $list['CASE_STATUS'];
                $status = $list['CASE_STATUS'];
            }
        }

        if ($caseType == "F") {
            $caseQuery = "INSERT INTO GPF_CASE_STATUS VALUES('$caseSLNo','$registrationNo','$seriesCode','$accountNumber', '$subscriberName', '$employeeCode', '$beneficiaryCode', 
    '$mobileNumber', '$pensionType', '$finYear', '$status',  SYSDATE, '','','','','','','','','','','','','','','','','','','','','')";
        }
        if ($caseType == "L") {
            $caseQuery = "INSERT INTO GPF_CASE_STATUS VALUES('$caseSLNo','$registrationNo','$seriesCode','$accountNumber', '$subscriberName', '$employeeCode', '$beneficiaryCode', 
    '$mobileNumber', '$pensionType', '$finYear', '$status', '', '','','','','','','','','','','','',SYSDATE,'','','','','','','')";
        }

        $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo','$caseStatus',SYSDATE, '$loginUser')";

        if ($mobileNumber > 0 || ($caseType == "F" || $caseType == "L")) {
            if ($pensionType != 6) {
                $seriesDescSQL = "SELECT * FROM VLCS.MM_GPF_SERIES WHERE SERIES_ID='$seriesCode'";
                $fetchSeriesDesc = sqlFetchData($connection, $seriesDescSQL);
                foreach ($fetchSeriesDesc as $fetchSeriesDescs) {
                    $seriesName = $fetchSeriesDescs['SERIES_DESCR'];
                }
                $message = "";
                $message .= "Your GPF Final Payment case T/$seriesName/$accountNumber has been registered in A.G Office, ";
                $message .= "Tripura on " . date("d/m/Y") . ". Your registration no. is : $registrationNo.";
                $smsSLNo = sqlSerialNo($connection, "GPF_SMS", "SL_NO");

                $smsQuery = "INSERT INTO GPF_SMS VALUES('$smsSLNo','$registrationNo','$seriesCode','$accountNumber',
                         '$mobileNumber','$message','N',SYSDATE)";
                sqlCUDData($connection, $smsQuery);
            }
        }
        if (sqlCUDData($connection, $inwardQuery) && sqlCUDData($connection, $caseLogQuery) && sqlCUDData($connection, $caseQuery)) {
            $error = 0;
            $message = "The case has been registered having registration no. " . $registrationNo . ". Inward no.: " . $slNo;
            $textColor = "success";
        } else {
            $error = 1;
            $message = "Data not saved, try again";
            $textColor = "danger";
        }
    } else {
        $error = 1;
        $message = "Recorrect errors";
        $textColor = "danger";
    }
}
?>

<div class="content-wrap">
    <div class="main">
        <div class="container-fluid">
            <?php
            require_once "./includes/breadcumb.php";
            ?>
            <section id="main-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">

                                <b class="text-<?php echo $textColor; ?>">
                                    <?php echo $message; ?>
                                </b>

                                <form action="" method="post">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="series_id">GPF Series <b class="text-danger">* <?php echo $seriesCodeErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="series_id" id="series_id" class="select-search form-control" tabindex="1">
                                                        <option value="">Select series id</option>
                                                        <?php
                                                        $seriesQuery = "SELECT * FROM VLCS.MM_GPF_SERIES ORDER BY SERIES_DESCR";
                                                        $series = sqlFetchData($connection, $seriesQuery);
                                                        foreach ($series as $seriesList) {
                                                        ?>
                                                            <option value="<?php echo $seriesList['SERIES_ID']; ?>" <?php
                                                                                                                    if ($error == 1) {
                                                                                                                        if ($seriesList['SERIES_ID'] == $seriesCode) {
                                                                                                                            echo "selected='selected'";
                                                                                                                        }
                                                                                                                    } else {
                                                                                                                        echo "";
                                                                                                                    }
                                                                                                                    ?>>
                                                                <?php echo $seriesList['SERIES_DESCR']; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="subscriber_name">Subscriber name <b class="text-danger" id="subscriber_msg">* <?php echo $subscriberNameErr; ?></b>
                                                    <button id="get_name" class="btn btn-info btn-sm sweet-wrong" type="button">Get subscriber</button>
                                                </label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Subscriber name" readonly name="subscriber_name" id="subscriber_name" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $subscriberName;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo "";
                                                                                                                                                                                        } ?>">
                                                </div>
                                            </div>

                                            <div class="form-group mb-10">
                                                <label for="section">Section <b class="text-danger">* <?php echo $sectionErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="section" id="section" class="form-control" tabindex="4">
                                                        <option value="">Select section</option>
                                                        <?php
                                                        $sectionQuery = "SELECT * FROM MAS_SECTION ORDER BY SECTION_ID";
                                                        $sections = sqlFetchData($connection, $sectionQuery);
                                                        foreach ($sections as $sectionList) {
                                                        ?>
                                                            <option value="<?php echo $sectionList['SECTION_ID']; ?>" <?php
                                                                                                                        if ($error == 1) {
                                                                                                                            if ($sectionList['SECTION_ID'] == $section) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
                                                                                                                        } else {
                                                                                                                            echo "";
                                                                                                                        }
                                                                                                                        ?>>
                                                                <?php echo $sectionList['SECTION_DESCR']; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="letter_no">Letter no <b class="text-danger"> <?php echo $letterNoErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Letter no" tabindex="6" name="letter_no" id="letter_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                            echo $letterNo;
                                                                                                                                                                        } else {
                                                                                                                                                                            echo "";
                                                                                                                                                                        } ?>">

                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="pension_type">Pension type <b class="text-danger">* <?php echo $pensionTypeErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="pension_type" id="pension_type" class="form-control" tabindex="8">
                                                        <option value="">Select pension type</option>
                                                        <?php
                                                        $pensionQuery = "SELECT * FROM MAS_PENSION_TYPE ORDER BY PENSION_ID";
                                                        $pension = sqlFetchData($connection, $pensionQuery);
                                                        foreach ($pension as $pensionList) {
                                                        ?>
                                                            <option value="<?php echo $pensionList['PENSION_ID']; ?>" <?php
                                                                                                                        if ($error == 1) {
                                                                                                                            if ($pensionList['PENSION_ID'] == $pensionType) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
                                                                                                                        } else {
                                                                                                                            echo "";
                                                                                                                        }
                                                                                                                        ?>>
                                                                <?php echo $pensionList['PENSION_LONG_DESCR']; ?> (<?php echo $pensionList['PENSION_SHORT_DESCR']; ?>)
                                                            </option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="mobile_no">Mobile number <b class="text-danger"> <?php echo $mobileNumberErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Mobile number" name="mobile_number" tabindex="10" id="mobile_number" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $mobileNumber;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo "";
                                                                                                                                                                                        } ?>">

                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="emp_code">Employee code<b class="text-danger"> <?php echo $employeeCodeErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Employee code" name="employee_code" tabindex="12" id="employee_code" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $employeeCode;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo "";
                                                                                                                                                                                        } ?>">

                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="record_date">Record DAK date <b class="text-danger"> * <?php echo $recordDateErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="date" placeholder="Record DAK date" name="record_date" tabindex="14" id="record_date" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                        echo $recordDate == "" ? "" : date("Y-m-d", strtotime($recordDate));
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo $recordDate;
                                                                                                                                                                                    } ?>">

                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary" name="save_inward" id="save_inward" tabindex="15">Save</button>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="account_number">GPF Account number <b class="text-danger">* <?php echo $accountNumberErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Account number" tabindex="2" name="account_number" id="account_number" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $accountNumber;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo "";
                                                                                                                                                                                        } ?>">
                                                </div>
                                                <b class="text-danger"></b>
                                            </div>

                                            <div class="form-group mb-10">
                                                <label for="user_fin_year">Financial year <b class="text-danger">* <?php echo $finYearErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="fin_year" id="fin_year" class="form-control select-search" tabindex="3">
                                                        <option value="">Select financial year</option>
                                                        <?php
                                                        $fin_yearQuery = "SELECT * FROM VLCS.MM_FINANCIAL_YEAR WHERE FIN_YEAR_CODE>3 ORDER BY FIN_YEAR_CODE DESC";
                                                        $fin_years = sqlFetchData($connection, $fin_yearQuery);
                                                        foreach ($fin_years as $fin_yearsList) {
                                                        ?>
                                                            <option value="<?php echo $fin_yearsList['FIN_YEAR_CODE']; ?>" <?php
                                                                                                                            if ($error == 1) {
                                                                                                                                if ($fin_yearsList['FIN_YEAR_CODE'] == $finYear) {
                                                                                                                                    echo "selected='selected'";
                                                                                                                                }
                                                                                                                            } else {
                                                                                                                                echo "";
                                                                                                                            }
                                                                                                                            ?>>
                                                                <?php echo $fin_yearsList['FIN_YEAR']; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="case_type">Case type<b class="text-danger"> * <?php echo $caseTypeErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="case_type" id="case_type" class="form-control" tabindex="5">
                                                        <option value="">Select case type</option>
                                                        <?php
                                                        $caseTypes = array("F" => "Fresh", "C" => "Correspondence", "L" => "Fresh LTA", "R" => "Revised Final Payment");
                                                        foreach ($caseTypes as $caseKey => $caseTypeList) {
                                                        ?>
                                                            <option value="<?php echo $caseKey; ?>" <?php
                                                                                                    if ($error == 1) {
                                                                                                        if ($caseKey == $caseType) {
                                                                                                            echo "selected='selected'";
                                                                                                        }
                                                                                                    } else {
                                                                                                        echo "";
                                                                                                    }
                                                                                                    ?>>
                                                                <?php echo $caseTypeList; ?>
                                                            </option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="applied_date">Applied date <b class="text-danger"> * <?php echo $appliedDateErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="date" placeholder="Applied date" name="applied_date" tabindex="7" id="applied_date" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                        echo $appliedDate == "" ? "" : date("Y-m-d", strtotime($appliedDate));
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo "";
                                                                                                                                                                                    } ?>">

                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="letter_type">Letter type<b class="text-danger"> * <?php echo $letterTypeErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="letter_type" id="letter_type" class="form-control" tabindex="9">
                                                        <option value="">Select letter type</option>
                                                        <?php
                                                        $letterTypes = array("M" => "Manual", "O" => "Online");
                                                        foreach ($letterTypes as $letterKey => $letterTypeList) {
                                                        ?>
                                                            <option value="<?php echo $letterKey; ?>" <?php
                                                                                                        if ($error == 1) {
                                                                                                            if ($letterKey == $letterType) {
                                                                                                                echo "selected='selected'";
                                                                                                            }
                                                                                                        } else {
                                                                                                            echo "";
                                                                                                        }
                                                                                                        ?>>
                                                                <?php echo $letterTypeList; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="ben_code">Beneficiary code<b class="text-danger"> <?php echo $beneficiaryCodeErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Beneficiary code" name="beneficiary_code" tabindex="11" id="beneficiary_code" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                    echo $beneficiaryCode;
                                                                                                                                                                                                } else {
                                                                                                                                                                                                    echo "";
                                                                                                                                                                                                } ?>">

                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="record_no">Record DAK number <b class="text-danger"> <?php echo $recordNoErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Record DAK number" name="record_no" tabindex="13" id="record_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                        echo $recordNo;
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo "";
                                                                                                                                                                                    } ?>">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <?php
        require_once "./includes/copyright.php";
        ?>
        </section>
    </div>
</div>
</div>

<?php
require_once "./bottom.php";
?>
<script>
    jQuery(document).ready(function() {
        jQuery("#get_name").click(function() {
            const seriesID = jQuery("#series_id").val();
            const accountNo = jQuery("#account_number").val();
            if ((seriesID === "") || (accountNo === "") || (seriesID === null) || (accountNo === null)) {
                $("#subscriber_name").val("");
                $("#employee_code").val("");
                $("#mobile_number").val("");
                $("#beneficiary_code").val("");
                swal({
                    title: "Oops!",
                    text: "Please provide series code and account number",
                    icon: "error",
                    button: "Close",
                });
            } else {
                const accountNoCred = {
                    SeriesID: seriesID,
                    AccountNo: accountNo
                }
                jQuery.ajax({
                    type: 'POST',
                    url: 'ajax/subscriber_details_by_account_no.php',
                    data: JSON.stringify(accountNoCred),
                    success: function(returnValue) {
                        const {
                            StatusCode,
                            Message,
                            ...Others
                        } = returnValue;

                        if (StatusCode === 200) {
                            const {
                                SubscriberName,
                                EmployeeCode,
                                MobileNumber,
                                BeneficiaryCode
                            } = Others;
                            $("#subscriber_name").val(SubscriberName);
                            $("#employee_code").val(EmployeeCode);
                            $("#mobile_number").val(MobileNumber);
                            $("#beneficiary_code").val(BeneficiaryCode);
                        } else {
                            if (StatusCode === 205) {
                                swal({
                                    title: "Oops!",
                                    text: Message,
                                    icon: "error",
                                    button: "Close",
                                });
                                $("#subscriber_name").val("");
                                $("#employee_code").val("");
                                $("#mobile_number").val("");
                                $("#beneficiary_code").val("");
                            } else {
                                swal({
                                    title: "Oops!",
                                    text: Message,
                                    icon: "error",
                                    button: "Close",
                                });
                                $("#subscriber_name").val("");
                                $("#employee_code").val("");
                                $("#mobile_number").val("");
                                $("#beneficiary_code").val("");
                            }
                        }
                    },
                    error: function(a, b, c) {
                        console.log(a + " " + b + " " + c)
                    }
                });
            }
        });
    });
</script>