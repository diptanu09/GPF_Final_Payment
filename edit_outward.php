<?php
$pageName = "Edit outward";
require_once "./top.php";
if (isset($_REQUEST['outward_no'])) {
    $outwardNo = $_REQUEST['outward_no'];
    if (isset($_REQUEST['save_outward'])) {
        $outwardType = trim(removeHTMLEntities($_REQUEST['outward_type']));
        $subject = trim(removeHTMLEntities($_REQUEST['subject']));
        $registrationNo = trim(removeHTMLEntities($_REQUEST['registration_no']));
        $noCopies = trim(removeHTMLEntities($_REQUEST['no_copies']));
        $letterNo = trim(removeHTMLEntities($_REQUEST['letter_no']));
        $copyTo = trim(removeHTMLEntities($_REQUEST['copy_to']));
        $copyType = trim(removeHTMLEntities($_REQUEST['copy_type']));
        $sentBy = trim(removeHTMLEntities($_REQUEST['sent_by']));
        $barCode = trim(removeHTMLEntities($_REQUEST['bar_code']));

        if (empty($outwardType)) {
            $outwardTypeErr = "Required";
        }
        if (empty($copyTo)) {
            $copyToErr = "Required";
        }
        if (empty($barCode)) {
            $barCodeErr = "";
        } else {
            if (strlen($barCode > 50)) {
                $barCodeErr = "Maximum 50 characters";
            } else {
                if (!textPatternValidation($barCode, "a-zA-Z0-9 ")) {
                    $barCodeErr = "Only alphanumeric, white-space are allowed";
                }
            }
        }
        if (empty($registrationNo)) {
            $registrationNo = "";
        } else {
            if (!textPatternValidation($registrationNo, "0-9")) {
                $registrationNoErr = "Only numeric are allowed";
            }
        }
        if (empty($subject)) {
            $subjectErr = "Required";
        } else {
            if (!textPatternValidation($subject, "a-zA-Z0-9. ")) {
                $subjectErr = "Only alphanumeric, white-space and dot(.) are allowed";
            }
        }
        if (empty($letterNo)) {
            $letterNo = "";
        } else {
            if (!textPatternValidation($letterNo, "a-zA-Z0-9. ")) {
                $letterNoErr = "Only alphanumeric, white-space and dot(.) are allowed";
            }
        }
        if (empty($registrationNo)) {
            $registrationNo = "";
        } else {
            if (!textPatternValidation($registrationNo, "0-9")) {
                $registrationNoErr = "Only numeric are allowed";
            }
        }
        if (empty($noCopies)) {
            $noCopiesErr = "Required";
        } else {
            if (!textPatternValidation($noCopies, "0-9")) {
                $noCopiesErr = "Only numeric are allowed";
            }
        }


        if (($outwardTypeErr == "") && ($subjectErr == "") && ($registrationNoErr == "") && ($letterNoErr == "") && ($barCodeErr == "") && ($copyToErr == "")) {
            $outwardQuery = "UPDATE GPF_OUTWARD SET OUTWARD_TYPE='$outwardType', SUBJECT='$subject', LETTER_NO='$letterNo',
                     REGD_NO='$registrationNo', COPY_TO='$copyTo', COPY_TYPE='$copyType', SENT_BY='$sentBy', BAR_CODE='$barCode'
                      WHERE SL_NO='$outwardNo'";
            if (sqlCUDData($connection, $outwardQuery)) {
                $error = 0;
                $message = "Successfully updated";
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
    $outwardSQL = "SELECT * FROM GPF_OUTWARD WHERE SL_NO='$outwardNo'";
    $fetchoutward = sqlFetchData($connection, $outwardSQL);
    foreach ($fetchoutward as $list) {
        $foutwardType = $list['OUTWARD_TYPE'];
        $fsubject = $list['SUBJECT'];
        $fletterNo = $list['LETTER_NO'];
        $fregistrationNo = $list['REGD_NO'];
        $fcopyTo = $list['COPY_TO'];
        $fcopyType = $list['COPY_TYPE'];
        $fsentBy = $list['SENT_BY'];
        $fbarCode = $list['BAR_CODE'];
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
                                                                                                                                    if ($fetchTypes['TYPE_SHORT_NAME'] == $foutwardType) {
                                                                                                                                        echo "selected='selected'";
                                                                                                                                    }
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
                                                                                                                                                                        echo $fsubject;
                                                                                                                                                                    } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $subjectErr; ?></b>
                                                </div>

                                                <div class="form-group">
                                                    <label for="copy_to">Copy to<b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Copy to" name="copy_to" id="copy_to" required class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                        echo $copyTo;
                                                                                                                                                                    } else {
                                                                                                                                                                        echo $fcopyTo;
                                                                                                                                                                    } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $copyToErr; ?></b>
                                                </div>
                                                <div class="form-group">
                                                    <label for="bar_code">Bar code<b class="text-danger"> </b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Bar Code" name="bar_code" id="bar_code" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                echo $barCode;
                                                                                                                                                            } else {
                                                                                                                                                                echo $fbarCode;
                                                                                                                                                            } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $barCodeErr; ?></b>
                                                </div>

                                                <button type="submit" class="btn btn-primary" name="save_outward" id="save_outward">Save</button>


                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="letter_no">Letter no <b class="text-danger"></b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Letter no" name="letter_no" id="letter_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                    echo $letterNo;
                                                                                                                                                                } else {
                                                                                                                                                                    echo $fletterNo;
                                                                                                                                                                } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $letterNoErr; ?></b>
                                                </div>
                                                <div class="form-group">
                                                    <label for="registration_no">Registration no<b class="text-danger"> </b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Registration no" name="registration_no" readonly id="registration_no" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                echo $registrationNo;
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo $fregistrationNo;
                                                                                                                                                                                            } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $registrationNoErr; ?></b>
                                                </div>

                                                <div class="form-group">
                                                    <label for="sent_by">Sent by <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="sent_by" id="sent_by" class="form-control" required>
                                                            <?php
                                                            $sentByList = ["Registered", "Email", "Other"];
                                                            foreach ($sentByList as $sentByLists) {
                                                            ?>
                                                                <option value="<?php echo $sentByLists; ?>" <?php
                                                                                                            if ($error == 1) {
                                                                                                                if ($sentByLists == $sentBy) {
                                                                                                                    echo "selected='selected'";
                                                                                                                }
                                                                                                            } else {
                                                                                                                if ($sentByLists == $fsentBy) {
                                                                                                                    echo "selected='selected'";
                                                                                                                }
                                                                                                            }
                                                                                                            ?>>
                                                                    <?php echo $sentByLists; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="copy_type">Copy type <b class="text-danger"> *</b></label>
                                                    <div class="input-group input-group-default">
                                                        <select class="form-control" name="copy_type" id="copy_type" required>
                                                            <?php
                                                            $copyTypeList = ["D" => "DDO", "T" => "Treasury", "P" => "Personal", "O" => "Other"];
                                                            foreach ($copyTypeList as $copyTypekey => $copyTypeLists) {
                                                            ?>
                                                                <option value="<?php echo $copyTypekey; ?>" <?php
                                                                                                            if ($error == 1) {
                                                                                                                if ($copyTypekey == $copyType) {
                                                                                                                    echo "selected='selected'";
                                                                                                                }
                                                                                                            } else {
                                                                                                                if ($copyTypekey == $fcopyType) {
                                                                                                                    echo "selected='selected'";
                                                                                                                }
                                                                                                            }
                                                                                                            ?>>
                                                                    <?php echo $copyTypeLists; ?></option>
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
}
?>