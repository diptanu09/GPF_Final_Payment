<?php
$pageName = "LTA Case registration";
require_once "./top.php";
if (isset($_REQUEST['save_inward'])) {
    $section = trim(removeHTMLEntities($_REQUEST['section']));
    $letterNo = trim(removeHTMLEntities($_REQUEST['letter_no']));
    $appliedDate = trim(removeHTMLEntities($_REQUEST['applied_date']));
    $letterType = trim(removeHTMLEntities($_REQUEST['letter_type']));
    $registrationNo = trim(removeHTMLEntities($_REQUEST['registration_number']));
    $recordNo = trim(removeHTMLEntities($_REQUEST['record_no']));
    $recordDate = trim(removeHTMLEntities($_REQUEST['record_date']));


    if (empty($section)) {
        $sectionErr = "Required";
    } else {
        if (!textPatternValidation($section, "0-9")) {
            $sectionErr = "Only numeric are allowed";
        }
    }
    if (empty($registrationNo)) {
        $registrationNoErr = "Required";
    } else {
        if (!textPatternValidation($registrationNo, "0-9")) {
            $registrationNoErr =  "Only numeric are allowed";
        } else {
            $regSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO ='$registrationNo'";
            $regExist = sqlCountData($connection, $regSQL);
            if ($regExist == 0) {
                $registrationNoErr =  "No registration number found";
            } else {
                $regAppSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO ='$registrationNo' 
                              AND APPROVED_DATE IS NOT NULL";
                $regAppExist = sqlCountData($connection, $regAppSQL);
                if ($regAppExist == 0) {
                    $registrationNoErr =  "Registration number not approved for LTA";
                }
            }
        }
    }

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

    if (empty($recordNo)) {
        $recordNoErr = "";
    } else {
        if (!textPatternValidation($recordNo, "0-9")) {
            $recordNoErr = "Only numeric are allowed";
        }
    }
    if (empty($recordDate)) {
        $recordDateErr = "";
    } else {
        $recordDate = date("d-M-Y", strtotime($recordDate));
    }

    $markTo = "sudha";
    $slNo = sqlSerialNo($connection, "GPF_INWARD", "SL_NO");
    $caseSLNo = sqlSerialNo($connection, "GPF_CASE_STATUS", "SL_NO");
    $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");

    if (($sectionErr == "") && ($letterNoErr == "") && ($appliedDateErr == "") && ($letterTypeErr == "") && ($recordNoErr == "") && ($recordDateErr == "") && ($registrationNoErr == "")) {
        $getRegSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO ='$registrationNo'";
        $fetchregDet = sqlFetchData($connection, $getRegSQL);
        foreach ($fetchregDet as $regList) {
            $seriesCode = $regList['SERIES_ID'];
            $accountNumber = $regList['ACCOUNT_NO'];
            $finYear = $regList['FIN_YEAR_CODE'];
        }

        $inwardQuery = "INSERT INTO GPF_INWARD VALUES('$slNo','$registrationNo','$seriesCode', '$accountNumber','$letterType','$letterNo', '$appliedDate', 
                   '$finYear', 'X', '$section', '$recordNo', '$recordDate', '$markTo', SYSDATE, '$loginUser', 
                    '$loginUser',SYSDATE,'$loginUser',SYSDATE)";

        $caseStatus = 12;
        $status = 12;
        $caseQuery = "UPDATE GPF_CASE_STATUS SET LTA_REGISTERED_DATE=SYSDATE, CASE_STATUS='$status' WHERE REGD_NO='$registrationNo'";

        $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo','$caseStatus',SYSDATE, '$loginUser')";


        if (sqlCUDData($connection, $inwardQuery) && sqlCUDData($connection, $caseLogQuery) && sqlCUDData($connection, $caseQuery)) {
            $error = 0;
            $message = "The lta case has been registered for the registration no. " . $registrationNo . ". Inward no.: " . $slNo;
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
                                            <div class="form-group mb-10">
                                                <label for="section">Section <b class="text-danger">* <?php echo $sectionErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="section" id="section" class="form-control" tabindex="1">
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
                                                    <input type="text" placeholder="Letter no" tabindex="3" name="letter_no" id="letter_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                            echo $letterNo;
                                                                                                                                                                        } else {
                                                                                                                                                                            echo "";
                                                                                                                                                                        } ?>">

                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="record_date">Record DAK date <b class="text-danger"> <?php echo $recordDateErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="date" placeholder="Record DAK date" name="record_date" tabindex="5" id="record_date" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                        echo $recordDate == "" ? "" : date("Y-m-d", strtotime($recordDate));
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo $recordDate;
                                                                                                                                                                                    } ?>">

                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="record_no">Record DAK number <b class="text-danger"> <?php echo $recordNoErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Record DAK number" name="record_no" tabindex="7" id="record_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                    echo $recordNo;
                                                                                                                                                                                } else {
                                                                                                                                                                                    echo "";
                                                                                                                                                                                } ?>">

                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary" name="save_inward" id="save_inward" tabindex="8">Save</button>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="registration">Registration number <b class="text-danger">* <?php echo $registrationNoErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Registration number" tabindex="2" name="registration_number" id="registration_number" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                            echo $registrationNo;
                                                                                                                                                                                                        } else {
                                                                                                                                                                                                            echo "";
                                                                                                                                                                                                        } ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="applied_date">Applied date <b class="text-danger"> * <?php echo $appliedDateErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="date" placeholder="Applied date" name="applied_date" tabindex="4" id="applied_date" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                        echo $appliedDate == "" ? "" : date("Y-m-d", strtotime($appliedDate));
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo "";
                                                                                                                                                                                    } ?>">

                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="letter_type">Letter type<b class="text-danger"> * <?php echo $letterTypeErr; ?></b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="letter_type" id="letter_type" class="form-control" tabindex="6">
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