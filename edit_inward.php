<?php
$pageName = "Case registration edit";
require_once "./top.php";
if (isset($_REQUEST['inward_no'])) {
    $inwardNo = $_REQUEST['inward_no'];
    if (isset($_REQUEST['save_inward'])) {
        $seriesCode = trim(removeHTMLEntities($_REQUEST['series_id']));
        $accountNumber = trim(removeHTMLEntities($_REQUEST['account_number']));
        $subscriberName = trim(removeHTMLEntities($_REQUEST['subscriber_name']));
        $finYear = trim(removeHTMLEntities($_REQUEST['fin_year']));
        $section = trim(removeHTMLEntities($_REQUEST['section']));
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
            if (!textPatternValidation($subscriberName, "a-zA-Z. ")) {
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
                $sectionErr = "Only letters and white space are allowed";
            }
        }

        $formatSeriesCode = strlen($seriesCode) < 2 ? "0" . $seriesCode : $seriesCode;
        $registrationNo = $finYear . $formatSeriesCode . $accountNumber;

        if (empty($letterNo)) {
            $letterNoErr = "";
        } else {
            if (textPatternValidation($letterNo, "'")) {
                $letterNoErr = "Only letters are allowed";
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
        if (empty($mobileNumber)) {
            $mobileNumber = "0";
        } else {
            if (!textPatternValidation($pensionType, "0-9")) {
                $mobileNumberErr = "Only numeric are allowed";
            } else {
                if (strlen($mobileNumber) != 10) {
                    $mobileNumberErr = "Must be 10 digits";
                }
            }
        }
        if (empty($employeeCode)) {
            $employeeCode = "0";
        } else {
            if (!textPatternValidation($pensionType, "0-9")) {
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

        $regNoSQL = "SELECT * FROM GPF_INWARD WHERE SL_NO='$serialNo'";
        $fetchReg = sqlFetchData($connection, $regNoSQL);
        foreach ($fetchReg as $regNo) {
            $registrationNumber = $regNo['REGD_NO'];
        }

        if (($seriesCodeErr == "") && ($accountNumberErr == "") && ($subscriberNameErr == "") && ($finYearErr == "") && ($sectionErr == "")
            && ($letterNoErr == "") && ($appliedDateErr == "") && ($letterTypeErr == "") && ($pensionTypeErr == "")
            && ($mobileNumberErr == "") && ($employeeCodeErr == "") && ($beneficiaryCodeErr == "") && ($recordNoErr == "") && ($recordDateErr == "")
        ) {
            $inwardQuery = "UPDATE GPF_INWARD SET REGD_NO='$registrationNo', SERIES_ID='$seriesCode', ACCOUNT_NO='$accountNumber',
                            LETTER_TYPE='$letterType', LETTER_NO='$letterNo', APPLIED_DATE='$appliedDate', FIN_YEAR_CODE='$finYear', 
                            SECTION='$section', RECORD_DAK_NO='$recordNo', RECORD_DAK_DATE='$recordDate', MODIFY_USER='$loginUser',
                            MODIFY_DATE=SYSDATE WHERE SL_NO='$inwardNo'";

            $caseQuery = "UPDATE GPF_CASE_STATUS SET REGD_NO='$registrationNo', SERIES_ID='$seriesCode', ACCOUNT_NO='$accountNumber',
                          SUBSCRIBER_NAME='$subscriberName', EMPLOYEE_CODE='$employeeCode', BENEFICIARY_CODE='$beneficiaryCode', 
                          MOBILE_NO='$mobileNumber', PENSION_TYPE='$pensionType', FIN_YEAR_CODE='$finYear'
                          WHERE REGD_NO='$registrationNumber' AND CASE_STATUS!=11";

            $inwardBool = sqlCUDData($connection, $inwardQuery);
            $caseBool = sqlCUDData($connection, $caseQuery);
            if ($inwardBool && $caseBool) {
                $error = 0;
                $message = "Successfully updated the inward record";
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

    $getInwardQuery = "SELECT * FROM GPF_INWARD a INNER JOIN GPF_CASE_STATUS b ON a.REGD_NO=b.REGD_NO 
                      WHERE a.SL_NO='$inwardNo'";
    $fetchInward = sqlFetchData($connection, $getInwardQuery);
    foreach ($fetchInward as $fetchInwardList) {
        $fseriesCode = $fetchInwardList['SERIES_ID'];
        $faccountNumber = $fetchInwardList['ACCOUNT_NO'];
        $fsubscriberName = $fetchInwardList['SUBSCRIBER_NAME'];
        $ffinYear = $fetchInwardList['FIN_YEAR_CODE'];
        $fsection = $fetchInwardList['SECTION'];
        $fletterNo = $fetchInwardList['LETTER_NO'];
        $fappliedDate = $fetchInwardList['APPLIED_DATE'];
        $fletterType = $fetchInwardList['LETTER_TYPE'];
        $fpensionType = $fetchInwardList['PENSION_TYPE'];
        $fmobileNumber = $fetchInwardList['MOBILE_NO'];
        $femployeeCode = $fetchInwardList['EMPLOYEE_CODE'];
        $fbeneficiaryCode = $fetchInwardList['BENEFICIARY_CODE'];
        $frecordNo = $fetchInwardList['RECORD_DAK_NO'];
        $frecordDate = $fetchInwardList['RECORD_DAK_DATE'];
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
                                                                                                                            if ($seriesList['SERIES_ID'] == $fseriesCode) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
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
                                                                                                                                                                                                echo $fsubscriberName;
                                                                                                                                                                                            } ?>">
                                                    </div>
                                                </div>

                                                <div class="form-group mb-10">
                                                    <label for="section">Section <b class="text-danger">* <?php echo $sectionErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="section" id="section" class="select-search form-control" tabindex="4">
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
                                                                                                                                if ($sectionList['SECTION_ID'] == $fsection) {
                                                                                                                                    echo "selected='selected'";
                                                                                                                                }
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
                                                                                                                                                                                echo $fletterNo;
                                                                                                                                                                            } ?>">

                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="pension_type">Pension type <b class="text-danger">* <?php echo $pensionTypeErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="pension_type" id="pension_type" class="select-search form-control" tabindex="8">
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
                                                                                                                                if ($pensionList['PENSION_ID'] == $fpensionType) {
                                                                                                                                    echo "selected='selected'";
                                                                                                                                }
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
                                                                                                                                                                                                echo $fmobileNumber;
                                                                                                                                                                                            } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="emp_code">Employee code<b class="text-danger"> <?php echo $employeeCodeErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Employee code" name="employee_code" tabindex="12" id="employee_code" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                echo $employeeCode;
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo $femployeeCode;
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
                                                                                                                                                                                                echo $faccountNumber;
                                                                                                                                                                                            } ?>">
                                                    </div>
                                                    <b class="text-danger"></b>
                                                </div>
                                                <div class="form-group mb-10">
                                                    <label for="user_fin_year">Financial year <b class="text-danger">* <?php echo $finYearErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="fin_year" id="fin_year" class="form-control select-search" tabindex="5">
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
                                                                                                                                    if ($fin_yearsList['FIN_YEAR_CODE'] == $ffinYear) {
                                                                                                                                        echo "selected='selected'";
                                                                                                                                    }
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
                                                    <label for="applied_date">Applied date <b class="text-danger"> * <?php echo $appliedDateErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="date" placeholder="Applied date" name="applied_date" tabindex="7" id="applied_date" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $appliedDate == "" ? "" : date("Y-m-d", strtotime($appliedDate));
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $fappliedDate == "" ? "" : date("Y-m-d", strtotime($fappliedDate));
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
                                                                                                                if ($letterKey == $fletterType) {
                                                                                                                    echo "selected='selected'";
                                                                                                                }
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
                                                                                                                                                                                                        echo $fbeneficiaryCode;
                                                                                                                                                                                                    } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="record_no">Record DAK number <b class="text-danger"> <?php echo $recordNoErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Record DAK number" name="record_no" tabindex="13" id="record_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $recordNo;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $frecordNo;
                                                                                                                                                                                        } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="record_date">Record DAK date <b class="text-danger"> * <?php echo $recordDateErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="date" placeholder="Record DAK date" name="record_date" tabindex="14" id="record_date" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $recordDate == "" ? "" : date("Y-m-d", strtotime($recordDate));
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $frecordDate == "" ? "" : date("Y-m-d", strtotime($frecordDate));
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
}
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