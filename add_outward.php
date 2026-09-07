<?php
$pageName = "Add outward";
require_once "./top.php";
if (isset($_REQUEST['save_outward'])) {
    $outwardType = trim(removeHTMLEntities($_REQUEST['outward_type']));
    $subject = trim(removeHTMLEntities($_REQUEST['subject']));
    $noCopies = trim(removeHTMLEntities($_REQUEST['no_copies']));
    $letterNo = trim(removeHTMLEntities($_REQUEST['letter_no']));
    $registrationNo = trim(removeHTMLEntities($_REQUEST['registration_no']));

    if (empty($outwardType)) {
        $outwardTypeErr = "Required";
    }

    if (empty($subject)) {
        $subjectErr = "Required";
    } else {
        if (!textPatternValidation($subject, "a-zA-Z0-9\/. ")) {
            $subjectErr = "Only alphanumeric, white-space, slash and dot(.) are allowed";
        }
    }
    if (empty($letterNo)) {
        $letterNo = "";
    } else {
        if (!textPatternValidation($letterNo, "^'")) {
            $letterNoErr = "Apostophe are not allowed";
        }
    }
    if ($outwardType == "O" || $outwardType == "M") {
        if (empty($registrationNo)) {
            $registrationNoErr = "Required";
        } else {
            if (!textPatternValidation($registrationNo, "0-9")) {
                $registrationNoErr = "Only numeric are allowed";
            } else {
                switch ($outwardType) {
                    case "O":
                        $regSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO='$registrationNo'  
                        AND CASE_STATUS NOT IN (5,6,7,8,11,14,15,16,17,18)";
                        $objExist = sqlCountData($connection, $regSQL);
                        if ($objExist == 0) {
                            $registrationNoErr = "Registration number is approved or signed or not exists";
                        }
                        break;
                    case "M":
                        $regSQL = "SELECT * FROM GPF_CASE_STATUS a INNER JOIN GPF_AMOUNT_INFO b ON a.REGD_NO=b.REGD_NO
                                   WHERE a.REGD_NO='$registrationNo' AND a.CASE_STATUS IN (6, 16) AND b.FINAL_PAYMENT_AMOUNT<0";
                        $objExist = sqlCountData($connection, $regSQL);
                        if ($objExist == 0) {
                            $registrationNoErr = "Registration number is not minus balance or not approved as minus balance or not exists";
                        }
                        break;
                    default:
                        $registrationNoErr = "";
                        break;
                }
            }
        }
    } else {
        if (empty($registrationNo)) {
            $registrationNo = "";
        } else {
            if (!textPatternValidation($registrationNo, "0-9")) {
                $registrationNoErr = "Only numeric are allowed";
            }
        }
    }

    if (empty($noCopies)) {
        $noCopiesErr = "Required";
    } else {
        if (!textPatternValidation($noCopies, "0-9")) {
            $noCopiesErr = "Only numeric are allowed";
        }
    }


    if (($outwardTypeErr == "") && ($subjectErr == "") && ($letterNoErr == "") && ($registrationNoErr == "") && ($noCopiesErr == "")) {
        for ($i = 0; $i < $noCopies; $i++) {
            $slNo = sqlSerialNo($connection, "GPF_OUTWARD", "SL_NO");
            $query = "INSERT INTO GPF_OUTWARD VALUES('$slNo', '$outwardType','$subject','$letterNo','$registrationNo',
                   '" . trim(removeHTMLEntities($_REQUEST['copies'][$i])) . "', '" . trim(removeHTMLEntities($_REQUEST['copy_type'][$i])) . "',
                   '" . trim(removeHTMLEntities($_REQUEST['sent_by'][$i])) . "', '" . trim(removeHTMLEntities($_REQUEST['bar_code'][$i])) . "', 
                   '$loginUser',SYSDATE,'$loginUser',SYSDATE)";
            $outwardNos[] = $slNo;
            $outwardBool[] = sqlCUDData($connection, $query);
        }
        if ($registrationNo) {
            switch ($outwardType) {
                case "B":
                    $caseStatus = 19;
                    $caseStatusQuery = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='$caseStatus', BALANCE_TRANSFER_CLOSED_DATE=SYSDATE 
                                    WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
                    $caseStatusBool = sqlCUDData($connection, $caseStatusQuery);
                    break;
                case "M":
                    $caseStatus = 10;
                    $caseStatusQuery = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='$caseStatus', MINUS_BAL_DATE=SYSDATE 
                                    WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
                    $caseStatusBool = sqlCUDData($connection, $caseStatusQuery);
                    break;
                case "O":
                    $caseStatus = 9;
                    $caseStatusQuery = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='$caseStatus', OBJECTION_DATE=SYSDATE 
                                    WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
                    $caseStatusBool = sqlCUDData($connection, $caseStatusQuery);

                case "R":
                    $statusSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO='$registrationNo' ";
                    $fetchStatus = sqlFetchData($connection, $statusSQL);
                    foreach ($fetchStatus as $cStatus) {
                        $caseStatus = $cStatus['CASE_STATUS'];
                    }

                    $caseStatusQuery = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='$caseStatus', REVALIDATION_DATE=SYSDATE 
                                        WHERE REGD_NO='$registrationNo' AND CASE_STATUS!='11'";
                    $caseStatusBool = sqlCUDData($connection, $caseStatusQuery);
                    break;
                default:
                    break;
            }
            $caseLogSLNo = sqlSerialNo($connection, "GPF_CASES_LOG", "SL_NO");
            $caseLogQuery = "INSERT INTO GPF_CASES_LOG VALUES('$caseLogSLNo','$registrationNo','$caseStatus',SYSDATE, '$loginUser')";
            $caseLogBool = sqlCUDData($connection, $caseLogQuery);
        }

        if (!in_array(false, $outwardBool) ||  $caseStatusBool ||  $caseLogBool) {
            $error = 0;
            $message = "Successfully saved. The outward number(s) : " . implode(", ", $outwardNos);
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
                                                <label for="outward_type">Outward type <b class="text-danger">*</b></label>
                                                <div class="input-group input-group-default">
                                                    <select name="outward_type" id="outward_type" class="form-control" required>
                                                        <option value="">Select outward type</option>
                                                        <?php
                                                        $outwardTypeSQL = "SELECT * FROM MAS_OUTWARD_TYPE ORDER BY TYPE_SHORT_NAME";
                                                        $fetchType = sqlFetchData($connection, $outwardTypeSQL);
                                                        foreach ($fetchType as $fetchTypes) {
                                                        ?>
                                                            <option value="<?php echo $fetchTypes['TYPE_SHORT_NAME']; ?>" <?php
                                                                                                                            if ($error == 1) {
                                                                                                                                if ($fetchTypes['TYPE_SHORT_NAME'] == $outwardType) {
                                                                                                                                    echo "selected='selected'";
                                                                                                                                }
                                                                                                                            } else {
                                                                                                                                echo "";
                                                                                                                            }
                                                                                                                            ?>>
                                                                <?php echo $fetchTypes['TYPE_DESCR']; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <b class="text-danger"><?php echo $outwardtypeErr; ?></b>
                                            </div>
                                            <div class="form-group">
                                                <label for="subject">Subject<b class="text-danger"> *</b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Subject" name="subject" id="subject" required class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                    echo $subject;
                                                                                                                                                                } else {
                                                                                                                                                                    echo "";
                                                                                                                                                                } ?>">
                                                </div>
                                                <b class="text-danger"><?php echo $subjectErr; ?></b>
                                            </div>

                                            <div class="form-group">
                                                <label for="no_copies">No of copies<b class="text-danger"> *</b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Copy no" name="no_copies" id="no_copies" required class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                        echo $noCopies;
                                                                                                                                                                    } else {
                                                                                                                                                                        echo "";
                                                                                                                                                                    } ?>">
                                                </div>
                                                <b class="text-danger"><?php echo $noCopiesErr; ?></b>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="letter_no">Letter no <b class="text-danger"></b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Letter no" name="letter_no" id="letter_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                echo $letterNo;
                                                                                                                                                            } else {
                                                                                                                                                                echo "";
                                                                                                                                                            } ?>">
                                                </div>
                                                <b class="text-danger"><?php echo $letterNoErr; ?></b>
                                            </div>
                                            <div class="form-group">
                                                <label for="registration_no">Registration no<b class="text-danger"> </b></label>
                                                <div class="input-group input-group-default">
                                                    <input type="text" placeholder="Registration no" name="registration_no" id="registration_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                    echo $registrationNo;
                                                                                                                                                                                } else {
                                                                                                                                                                                    echo "";
                                                                                                                                                                                } ?>">
                                                </div>
                                                <b class="text-danger"><?php echo $registrationNoErr; ?></b>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="copy_details"> </div>
                                    <button type="submit" class="btn btn-primary" name="save_outward" id="save_outward">Save</button>
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
    $(document).ready(function() {
        $("#no_copies").keyup(function() {
            let html = "";
            let noCopies = $(this).val();
            let registrationNo = $("#registration_no").val();

            if (noCopies == null || noCopies == "") {
                html = "";
                $("#copy_details").html(html);
            } else {
                const copiesCred = {
                    NoOfCopies: noCopies,
                    RegistrationNo: registrationNo
                }
                jQuery.ajax({
                    type: 'POST',
                    url: 'ajax/outward_copies.php',
                    data: JSON.stringify(copiesCred),
                    success: function(returnValue) {
                        const {
                            StatusCode,
                            Message,
                            Data
                        } = returnValue;
                        if (StatusCode === 200) {
                            let i = 0;
                            for (Datas of Data) {
                                i++;
                                html += `<div class="col-lg-3">
                                <div class="form-group">
                                    <label for="registration_no">Copy ${i}<b class="text-danger"> *</b></label>
                                    <div class="input-group input-group-default">
                                        <input type="text" placeholder="Copy to" maxlength="255" required name="copies[]" id="copies" class="form-control"
                                        value="${Datas.Copy}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="registration_no">Sent by ${i}<b class="text-danger"> *</b></label>
                                    <div class="input-group input-group-default">
                                    <select class="form-control" name="sent_by[]" id="sent_by">
                                    <option value="By Hand">By Hand</option>
                                    <option value="Email">Email</option>
                                    <option value="Registered">Registered</option>
                                    <option value="Speed Post">Speed Post</option>
                                    <option value="Other">Other</option>
                                    </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="registration_no">Bar code ${i}<b class="text-danger"> </b></label>
                                    <div class="input-group input-group-default">
                                        <input type="text" placeholder="Bar code" maxlength="50" name="bar_code[]" id="bar_code" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="copy_type">Copy type ${i}<b class="text-danger"> *</b></label>
                                    <div class="input-group input-group-default">
                                    <select class="form-control" name="copy_type[]" id="copy_type" required>
                                    <option value="D" ${Datas.CopyTo=='D'?'selected':''}>DDO</option>
                                    <option value="T" ${Datas.CopyTo=='T'?'selected':''}>Treasury</option>
                                    <option value="P" ${Datas.CopyTo=='P'?'selected':''}>Personal</option>
                                    <option value="O" ${Datas.CopyTo=='O'?'selected':''}>Other</option>
                                    </select>
                                    </div>
                                </div>
                            </div>`;
                            }
                        } else {
                            for (let i = 1; i <= noCopies; i++) {
                                html += `<div class="col-lg-3">
                                <div class="form-group">
                                    <label for="registration_no">Copy ${i}<b class="text-danger"> *</b></label>
                                    <div class="input-group input-group-default">
                                        <input type="text" placeholder="Copy to" maxlength="255" required name="copies[]" id="copies" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="registration_no">Sent by ${i}<b class="text-danger"> *</b></label>
                                    <div class="input-group input-group-default">
                                    <select class="form-control" name="sent_by[]" id="sent_by">
                                    <option value="By Hand">By Hand</option>
                                    <option value="Email">Email</option>
                                    <option value="Registered">Registered</option>
                                    <option value="Speed Post">Speed Post</option>
                                    <option value="Other">Other</option>
                                    </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="registration_no">Bar code ${i}<b class="text-danger"> </b></label>
                                    <div class="input-group input-group-default">
                                        <input type="text" placeholder="Bar code" maxlength="50" name="bar_code[]" id="bar_code" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="copy_type">Copy type ${i}<b class="text-danger"> *</b></label>
                                    <div class="input-group input-group-default">
                                    <select class="form-control" name="copy_type[]" id="copy_type" required>
                                    <option value="D">DDO</option>
                                    <option value="T">Treasury</option>
                                    <option value="P">Personal</option>
                                    <option value="O">Other</option>
                                    </select>
                                    </div>
                                </div>
                            </div>`;
                            }
                        }
                        $("#copy_details").html(html);
                    },
                    error: function(a, b, c) {
                        console.log(a + " " + b + " " + c)
                    }
                });

            }
        });
    });
</script>