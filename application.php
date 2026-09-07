<?php
$pageName = "Application entry/edit";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);
    if (isset($_REQUEST['save_application'])) {
        $seriesCode = trim(removeHTMLEntities($_REQUEST['series_code']));
        $accountNumber = trim($_REQUEST['account_number']);
        $nameTitle = trim(removeHTMLEntities($_REQUEST['name_title']));
        $designationTitle = trim(removeHTMLEntities($_REQUEST['designation_title']));
        $spouseName = trim(removeHTMLEntities($_REQUEST['spouse_name']));
        $employeeCode = trim(removeHTMLEntities($_REQUEST['employee_code']));
        $effectDate = trim(removeHTMLEntities($_REQUEST['effect_date']));
        $personalAddress = trim($_REQUEST['personal_address']);
        $ddoCode = trim(removeHTMLEntities($_REQUEST['ddo_code']));
        $subscriberName = trim(removeHTMLEntities($_REQUEST['subscriber_name']));
        $designation = trim(removeHTMLEntities($_REQUEST['designation']));
        $spouseRelation = trim(removeHTMLEntities($_REQUEST['spouse_relation']));
        $beneficiaryCode = trim(removeHTMLEntities($_REQUEST['beneficiary_code']));
        $lastFundDeduction = trim(removeHTMLEntities($_REQUEST['last_fund_deduction']));
        $debitDuringYear = trim(removeHTMLEntities($_REQUEST['debit_during_year']));
        $treasuryCode = trim(removeHTMLEntities($_REQUEST['treasury_code']));
        $ltaWhom = trim(removeHTMLEntities($_REQUEST['lta_whom']));
        $dodrDate = trim(removeHTMLEntities($_REQUEST['date_of_death_retirement']));

        if (empty($nameTitle)) {
            $nameTitleErr = "Required";
        } else {
            if (!textPatternValidation($nameTitle, "a-zA-Z.")) {
                $seriesCodeErr = "Only letters and dot are allowed";
            }
        }
        if (empty($designationTitle)) {
            $designationTitleErr = "Required";
        } else {
            if (!textPatternValidation($designationTitle, "a-zA-Z.")) {
                $designationTitleErr = "Only letters are allowed";
            }
        }
        if (empty($subscriberName)) {
            $subscriberNameErr = "Required";
        } else {
            if (!textPatternValidation($subscriberName, "a-zA-Z(). ")) {
                $subscriberNameErr = "Only letters, dots, parenthesis and white space are allowed";
            } else {
                if (strlen($subscriberName) > 255) {
                    $subscriberNameErr = "Maximum length : 255 is allowed";
                }
            }
        }
        if (empty($spouseName)) {
            $spouseNameErr = "Required";
        } else {
            if (!textPatternValidation($spouseName, "a-zA-Z. ")) {
                $spouseNameErr = "Only letters, dots and white space are allowed";
            } else {
                if (strlen($spouseName) > 255) {
                    $spouseNameErr = "Maximum length : 255 is allowed";
                }
            }
        }
        if (empty($designation)) {
            $designationErr = "Required";
        } else {
            if (!textPatternValidation($designation, "^'")) {
                $designationErr = "Apostophe are not allowed";
            }
        }
        if (empty($ltaWhom)) {
            $ltaWhom = "";
        } else {
            if (!textPatternValidation($ltaWhom, "^'")) {
                $ltaWhomErr = "Apostophe are not allowed";
            }
        }
        if (empty($employeeCode)) {
            $employeeCode = "---";
        } else {
            if (!textPatternValidation($employeeCode, "0-9")) {
                $employeeCodeErr = "Only numeric are allowed";
            }
        }
        if (empty($beneficiaryCode)) {
            $beneficiaryCode = "---";
        } else {
            if (!textPatternValidation($beneficiaryCode, "0-9")) {
                $beneficiaryCodeErr = "Only numeric are allowed";
            }
        }

        if (empty($personalAddress)) {
            $personalAddressErr = "Required";
        } else {
            if (strlen($personalAddress) > 255) {
                $personalAddressErr = "Maximum length : 255 is allowed";
            }
        }
        if (empty($effectDate)) {
            $effectDateErr = "Required";
        } else {
            $effectDate = date("d-M-Y", strtotime($effectDate));
        }

        if (empty($spouseRelation)) {
            $spouseRelationErr = "Required";
        } else {
            if (!textPatternValidation($spouseRelation, "^'")) {
                $spouseRelationErr = "Apostophe are not allowed";
            }
        }
        if (empty($ddoCode)) {
            $ddoCodeErr = "Required";
        } else {
            if (!textPatternValidation($ddoCode, "0-9")) {
                $ddoCodeErr = "Only numeric are allowed";
            }
        }
        if (empty($treasuryCode)) {
            $treasuryCodeErr = "Required";
        } else {
            if (!textPatternValidation($treasuryCode, "a-zA-Z0-9")) {
                $treasuryCodeErr = "Only alphanumeric are allowed";
            }
        }
        if (empty($lastFundDeduction)) {
            $lastFundDeductionErr = "Required";
        } else {
            $lastFundDeduction = date("01-M-Y", strtotime($lastFundDeduction));
        }
        if (empty($dodrDate)) {
            $dodrDate = "";
        } else {
            $dodrDate = date("d-M-Y", strtotime($dodrDate));
        }
        if (empty($debitDuringYear)) {
            $debitDuringYear = "0";
        } else {
            if (!textPatternValidation($debitDuringYear, "0-9.")) {
                $debitDuringYearErr = "Only numeric and dot(.) are allowed";
            }
        }

        if (($nameTitleErr == "") && ($designationTitleErr == "") && ($spouseNameErr == "") && ($effectDateErr == "") && ($personalAddressErr == "")
            && ($ddoCodeErr == "") && ($ltaWhomErr == "") && ($subscriberNameErr == "") && ($designationErr == "") && ($spouseRelationErr == "") && ($lastFundDeductionErr == "")
            && ($debitDuringYearErr == "") && ($employeeCodeErr == "") && ($beneficiaryCodeErr == "") && ($treasuryCodeErr == "")
        ) {
            $ltaSearchQuery = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO='$registrationNo' AND LTA_REGISTERED_DATE IS NOT NULL";
            $countLTARows = sqlCountData($connection, $ltaSearchQuery);
            if ($countLTARows > 0) {
                $caseStatus = 13;
            } else {
                $caseStatus = 2;
            }
            $appSearchQuery = "SELECT * FROM GPF_APPLICATION WHERE REGD_NO='$registrationNo'";
            $countRows = sqlCountData($connection, $appSearchQuery);
            if ($countRows > 0) {
                $applicationCudQuery = "UPDATE GPF_APPLICATION SET TITLE='$nameTitle', DESG_TITLE='$designationTitle', DESIGNATION='$designation', SPOUSE_NAME='$spouseName', RELATION='$spouseRelation',
                PERSONAL_ADDRESS='$personalAddress', DDO_CODE='$ddoCode', TREASURY_CODE='$treasuryCode', DATE_OF_EFFECT='$effectDate', LAST_FUND_DEDUCTION='$lastFundDeduction',
                DEBIT_DURING_YEAR='$debitDuringYear', DATE_OF_LTA='$dodrDate', LTA_TO_WHOM='$ltaWhom', MODIFY_USER='$loginUser', 
                MODIFY_DATE=SYSDATE  WHERE REGD_NO='$registrationNo'";

                if ($countLTARows > 0) {
                    $caseQuery = "UPDATE GPF_CASE_STATUS SET SUBSCRIBER_NAME='$subscriberName', EMPLOYEE_CODE='$employeeCode', 
                  BENEFICIARY_CODE='$beneficiaryCode', LTA_ENTERED_DATE=SYSDATE, CASE_STATUS='$caseStatus'
                  WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
                } else {
                    $caseQuery = "UPDATE GPF_CASE_STATUS SET SUBSCRIBER_NAME='$subscriberName', EMPLOYEE_CODE='$employeeCode', 
                    BENEFICIARY_CODE='$beneficiaryCode' WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
                }
            } else {
                $slNo = sqlSerialNo($connection, "GPF_APPLICATION", "SL_NO");
                $applicationCudQuery = "INSERT INTO GPF_APPLICATION VALUES('$slNo','$registrationNo','$seriesCode', '$accountNumber','$nameTitle','$designationTitle', '$designation', 
                '$spouseName', '$spouseRelation', '$personalAddress', '$ddoCode', '$treasuryCode', '$effectDate', '$dodrDate', '$ltaWhom', '$lastFundDeduction', 
               '$debitDuringYear', '$loginUser',SYSDATE,'$loginUser',SYSDATE)";
                if ($countLTARows > 0) {
                    $caseQuery = "UPDATE GPF_CASE_STATUS SET SUBSCRIBER_NAME='$subscriberName', EMPLOYEE_CODE='$employeeCode', 
                                  BENEFICIARY_CODE='$beneficiaryCode', LTA_ENTERED_DATE=SYSDATE, CASE_STATUS='$caseStatus'
                                  WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
                } else {
                    $caseQuery = "UPDATE GPF_CASE_STATUS SET SUBSCRIBER_NAME='$subscriberName', EMPLOYEE_CODE='$employeeCode', 
                                  BENEFICIARY_CODE='$beneficiaryCode', ENTERED_DATE=SYSDATE, CASE_STATUS='$caseStatus'
                                  WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
                }
            }
            $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");
            $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo','$caseStatus',SYSDATE, '$loginUser')";
            if (sqlCUDData($connection, $applicationCudQuery) && sqlCUDData($connection, $caseQuery) && sqlCUDData($connection, $caseLogQuery)) {
                $error = 0;
                $message = "Successfully saved";
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

    $applicationQuery = "SELECT * FROM GPF_INWARD a LEFT JOIN GPF_APPLICATION b ON a.REGD_NO=b.REGD_NO
                         INNER JOIN VLCS.MM_GPF_SERIES c ON a.SERIES_ID=c.SERIES_ID INNER JOIN GPF_CASE_STATUS
                          d ON a.REGD_NO=d.REGD_NO INNER JOIN MAS_PENSION_TYPE e ON d.PENSION_TYPE=e.PENSION_ID
                          INNER JOIN VLCS.MM_FINANCIAL_YEAR f ON d.FIN_YEAR_CODE=f.FIN_YEAR_CODE WHERE a.REGD_NO='$registrationNo'";

    $fetchApplicationData = sqlFetchData($connection, $applicationQuery);
    foreach ($fetchApplicationData as $fetchApplicationList) {
        $fpensionID = $fetchApplicationList['PENSION_TYPE'];
        $fseriesCode = $fetchApplicationList['SERIES_ID'];
        $faccountNumber = $fetchApplicationList['ACCOUNT_NO'];
        $femployeeCode = $fetchApplicationList['EMPLOYEE_CODE'] == "---" ? "" : $fetchApplicationList['EMPLOYEE_CODE'];
        $fbeneficiaryCode = $fetchApplicationList['BENEFICIARY_CODE'] == "---" ? "" : $fetchApplicationList['BENEFICIARY_CODE'];
        $fpensionType = $fetchApplicationList['PENSION_LONG_DESCR'] . " (" . $fetchApplicationList['PENSION_SHORT_DESCR'] . ")";
        $fgpfSeries = "T/" . $fetchApplicationList['SERIES_DESCR'] . '/' . $fetchApplicationList['ACCOUNT_NO'];
        $fsubscriberName = $fetchApplicationList['SUBSCRIBER_NAME'];
        $ffinYear = $fetchApplicationList['FIN_YEAR'];
        $fnameTitle = $fetchApplicationList['TITLE'];
        $fdesignationTitle = $fetchApplicationList['DESG_TITLE'];
        $fdesignation = $fetchApplicationList['DESIGNATION'];
        $fspouseName = $fetchApplicationList['SPOUSE_NAME'];
        $fspouseRelation = $fetchApplicationList['RELATION'];
        $feffectDate = $fetchApplicationList['DATE_OF_EFFECT'];
        $flastFundDeduction = $fetchApplicationList['LAST_FUND_DEDUCTION'];
        $fpersonalAddress = $fetchApplicationList['PERSONAL_ADDRESS'];
        $fdebitDuringYear = $fetchApplicationList['DEBIT_DURING_YEAR'];
        $fddoCode = $fetchApplicationList['DDO_CODE'];
        $ftreasuryCode = $fetchApplicationList['TREASURY_CODE'];
        $fdodrDate = $fetchApplicationList['DATE_OF_LTA'];
        $fltaWhom = $fetchApplicationList['LTA_TO_WHOM'];
    }
?>

    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <?php
                require_once "./includes/breadcumb.php";
                ?>
                <desgTitle id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">

                                    <b class="text-<?php echo $textColor; ?>">
                                        <?php echo $message; ?>
                                    </b>
                                    <br>
                                    <form action="" method="post">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="account_no">GPF Account number<b class="text-danger"> *</b></label>
                                                    <input type="hidden" name="series_code" id="series_code" value="<?php if ($error === 1) {
                                                                                                                        echo $seriesCode;
                                                                                                                    } else {
                                                                                                                        echo $fseriesCode;
                                                                                                                    } ?>" />
                                                    <input type="hidden" name="account_number" id="account_number" value="<?php if ($error === 1) {
                                                                                                                                echo $accountNumber;
                                                                                                                            } else {
                                                                                                                                echo $faccountNumber;
                                                                                                                            } ?>" />
                                                    <div class=" input-group input-group-default">
                                                        <input type="text" placeholder="GPF Account number" readonly class="form-control" value="<?php echo $fgpfSeries; ?>" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="pension_type">Pension type<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Pension type" readonly class="form-control" value="<?php echo $fpensionType; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mb-10">
                                                    <label for="name_title">Name title <b class="text-danger"> * <?php echo $nameTitleErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="name_title" id="name_title" class="form-control select-search" tabindex="1">
                                                            <option value="">Select name title</option>
                                                            <?php
                                                            $titleArr = ["Shri.", "Smt.", "Lt."];
                                                            foreach ($titleArr as $titleVals) {
                                                            ?>
                                                                <option value="<?php echo $titleVals; ?>" <?php
                                                                                                            if ($error == 1) {
                                                                                                                if ($titleVals == $nameTitle) {
                                                                                                                    echo "selected='selected'";
                                                                                                                }
                                                                                                            } else {
                                                                                                                if ($titleVals == $fnameTitle) {
                                                                                                                    echo "selected='selected'";
                                                                                                                }
                                                                                                            }
                                                                                                            ?>>
                                                                    <?php echo $titleVals; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-10">
                                                    <label for="desg_title">Designation title <b class="text-danger"> * <?php echo $designationTitleErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="designation_title" id="designation_title" class="select-search form-control" tabindex="3">
                                                            <option value="">Select designation title</option>
                                                            <?php
                                                            $desgTitles = ["Retd.", "Ex.", "None"];
                                                            foreach ($desgTitles as $desgTitleList) {
                                                            ?>
                                                                <option value="<?php echo $desgTitleList; ?>" <?php
                                                                                                                if ($error == 1) {
                                                                                                                    if ($desgTitleList == $designationTitle) {
                                                                                                                        echo "selected='selected'";
                                                                                                                    }
                                                                                                                } else {
                                                                                                                    if ($desgTitleList == $fdesignationTitle) {
                                                                                                                        echo "selected='selected'";
                                                                                                                    }
                                                                                                                }
                                                                                                                ?>>
                                                                    <?php echo $desgTitleList; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="spouse_name">Spouse name <b class="text-danger"> * <?php echo $spouseNameErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Spouse name" tabindex="5" name="spouse_name" id="spouse_name" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                        echo $spouseName;
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo $fspouseName == "" ? "NA" : $fspouseName;
                                                                                                                                                                                    } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="emp_code">Employee code <b class="text-danger"> <?php echo $employeeCodeErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Employee code" name="employee_code" tabindex="7" id="employee_code" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $employeeCode;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $femployeeCode;
                                                                                                                                                                                        } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="effect_date">Date of <?php if ($fpensionID == 7) {
                                                                                            echo "superranuation (revised)";
                                                                                        } else {
                                                                                            echo $fpensionType;
                                                                                        } ?> <b class="text-danger"> * <?php echo $effectDateErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="date" placeholder="Record DAK date" name="effect_date" tabindex="9" id="effect_date" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $effectDate == "" ? "" : date("Y-m-d", strtotime($effectDate));
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $feffectDate == "" ? "" : date("Y-m-d", strtotime($feffectDate));
                                                                                                                                                                                        } ?>">

                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="personal_address">Personal address <b class="text-danger"> * <?php echo $personalAddressErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Personal address" name="personal_address" tabindex="11" id="personal_address" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                        echo $personalAddress;
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        echo $fpersonalAddress;
                                                                                                                                                                                                    } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group mb-10">
                                                    <label for="ddo_code">Drawing and Disbursment Officer (DDO) <b class="text-danger"> * <?php echo $ddoCodeErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="ddo_code" id="ddo_code" class="select-search form-control" tabindex="13">
                                                            <option value="">Select DDO</option>
                                                            <?php
                                                            $ddoQuery = "SELECT * FROM VLCS.STATE_DDO ORDER BY DDO_CODE";
                                                            $ddos = sqlFetchData($connection, $ddoQuery);
                                                            foreach ($ddos as $ddoLists) {
                                                            ?>
                                                                <option value="<?php echo $ddoLists['DDO_CODE']; ?>" <?php
                                                                                                                        if ($error == 1) {
                                                                                                                            if ($ddoLists['DDO_CODE'] == $ddoCode) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
                                                                                                                        } else {
                                                                                                                            if ($ddoLists['DDO_CODE'] == $fddoCode) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
                                                                                                                        }
                                                                                                                        ?>>
                                                                    <?php echo $ddoLists['DDO_CODE']; ?> - <?php echo $ddoLists['DDO_DESG']; ?>,
                                                                    Pin: <?php echo $ddoLists['PIN']; ?>
                                                                </option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>




                                                <div class="form-group">
                                                    <label for="effect_date">Date of death after retirement <b class="text-danger"> <?php echo $dodrDateErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="date" placeholder="DODR" name="date_of_death_retirement" tabindex="15" id="date_of_death_retirement" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                            echo $dodrDate == "" ? "" : date("Y-m-d", strtotime($dodrDate));
                                                                                                                                                                                                        } else {
                                                                                                                                                                                                            echo $fdodrDate == "" ? "" : date("Y-m-d", strtotime($fdodrDate));
                                                                                                                                                                                                        } ?>">

                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="save_application" id="save_application" tabindex="17">Save</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="regd_no">Registration number<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Registration number" readonly class="form-control" value="<?php echo $registrationNo; ?>">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="financial_year">Financial year<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Financial year" readonly class="form-control" value="<?php echo $ffinYear; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="subscriber_name">Subscriber name <b class="text-danger"> * <?php echo $subscriberNameErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Subscriber name" tabindex="2" name="subscriber_name" id="subscriber_name" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                    echo $subscriberName;
                                                                                                                                                                                                } else {
                                                                                                                                                                                                    echo $fsubscriberName;
                                                                                                                                                                                                } ?>">
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label for="designation">Designation <b class="text-danger">* <?php echo $designationErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Designation" tabindex="4" name="designation" id="designation" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                        echo $designation;
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo $fdesignation;
                                                                                                                                                                                    } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="spouse_relation">Relation <b class="text-danger"> * <?php echo $spouseRelationErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Relation" tabindex="6" name="spouse_relation" id="spouse_relation" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $spouseRelation;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $fspouseRelation == "" ? "NA" : $fspouseRelation;
                                                                                                                                                                                        } ?>">
                                                    </div>

                                                </div>
                                                <div class="form-group">
                                                    <label for="ben_code">Beneficiary code<b class="text-danger"> <?php echo $beneficiaryCodeErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Beneficiary code" name="beneficiary_code" tabindex="8" id="beneficiary_code" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                        echo $beneficiaryCode;
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        echo $fbeneficiaryCode;
                                                                                                                                                                                                    } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="lfd">Last fund deduction <b class="text-danger"> * <?php echo $lastFundDeductionErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="month" placeholder="Last fund deduction" name="last_fund_deduction" tabindex="10" id="last_fund_deduction" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                                echo $lastFundDeduction == "" ? "" : date("Y-m", strtotime($lastFundDeduction));
                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                echo $flastFundDeduction == "" ? "" : date("Y-m", strtotime($flastFundDeduction));
                                                                                                                                                                                                            } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ddy">Debit during the year<b class="text-danger"> <?php echo $debitDuringYearErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Debit during the year" name="debit_during_year" tabindex="12" id="debit_during_year" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                                echo $debitDuringYear;
                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                echo $fdebitDuringYear;
                                                                                                                                                                                                            } ?>">

                                                    </div>
                                                </div>
                                                <div class="form-group mb-10">
                                                    <label for="treasury_code">Treasury / Sub-treasury<b class="text-danger"> * <?php echo $treasuryCodeErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="treasury_code" id="treasury_code" class="form-control" tabindex="14">
                                                            <option value="">Select treasury / sub-treasury</option>
                                                            <?php
                                                            $tryQuery = "SELECT * FROM VLCS.STATE_TREASURY ORDER BY TRES_NAME";
                                                            $trys = sqlFetchData($connection, $tryQuery);
                                                            foreach ($trys as $tryLists) {
                                                            ?>
                                                                <option value="<?php echo $tryLists['TRES_CODE']; ?>" <?php
                                                                                                                        if ($error == 1) {
                                                                                                                            if ($tryLists['TRES_CODE'] == $treasuryCode) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
                                                                                                                        } else {
                                                                                                                            if ($tryLists['TRES_CODE'] == $ftreasuryCode) {
                                                                                                                                echo "selected='selected'";
                                                                                                                            }
                                                                                                                        }
                                                                                                                        ?>>
                                                                    <?php echo $tryLists['TRES_CODE']; ?> - <?php echo $tryLists['TRES_NAME']; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="lta_whom">LTA Whom<b class="text-danger"> <?php echo $ltaWhomErr; ?></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" name="lta_whom" id="lta_whom" placeholder="LTA to whom" tabindex="16" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                    echo $ltaWhom;
                                                                                                                                                                                } else {
                                                                                                                                                                                    echo $fltaWhom;
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
            </desgTitle>
        </div>
    </div>
    </div>

    <?php
    require_once "./bottom.php";
    ?>
    <script>
        jQuery(document).ready(function() {
            jQuery("#ddo_code").change(function() {
                const ddoCode = jQuery(this).val();

                if ((ddoCode == "") || (ddoCode == null)) {
                    $("#treasury_code").val("");
                    swal({
                        title: "Oops!",
                        text: "Please provide DDO code",
                        icon: "error",
                        button: "Close",
                    });

                } else {
                    const ddoCodeCred = {
                        DDOCode: ddoCode
                    }
                    jQuery.ajax({
                        type: 'POST',
                        url: 'ajax/ddo_treasury.php',
                        data: JSON.stringify(ddoCodeCred),
                        success: function(returnValue) {
                            const {
                                StatusCode,
                                Message,
                                ...Others
                            } = returnValue;

                            if (StatusCode === 200) {
                                const {
                                    TreasuryCode
                                } = Others;
                                jQuery("#treasury_code").val(TreasuryCode);
                            } else {
                                jQuery("#treasury_code").val("");
                                swal({
                                    title: "Oops!",
                                    text: Message,
                                    icon: "error",
                                    button: "Close",
                                });
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
<?php
}
?>