<?php
$pageName = "Update interest";
require_once "./top.php";
if ($_REQUEST['slno']) {
    $slNo = $_REQUEST['slno'];
    if (isset($_REQUEST['save_roi'])) {
        $finYear = trim(removeHTMLEntities($_REQUEST['fin_year']));
        $hfinYear = trim(removeHTMLEntities($_REQUEST['hfin_year']));
        $rateOfInterest = trim(removeHTMLEntities($_REQUEST['rate_of_interest']));
        $accountingMonth = trim(removeHTMLEntities($_REQUEST['accounting_month']));
        $haccountingMonth = trim(removeHTMLEntities($_REQUEST['haccounting_month']));
        $year = trim(removeHTMLEntities($_REQUEST['year']));

        if (empty($finYear)) {
            $finYearErr = "Required";
        } else {
            if (!textPatternValidation($finYear, "0-9")) {
                $finYearErr = "Only numeric are allowed";
            }
        }
        if (empty($rateOfInterest)) {
            $rateOfInterestErr = "Required";
        } else {
            if (!textPatternValidation($rateOfInterest, "0-9.")) {
                $rateOfInterestErr = "Only number and dot(.) are allowed";
            }
        }
        if (empty($accountingMonth)) {
            $accountingMonthErr = "Required";
        } else {
            if (!textPatternValidation($accountingMonth, "0-9")) {
                $accountingMonthErr = "Only numeric are allowed";
            }
        }
        if (empty($year)) {
            $yearErr = "Required";
        } else {
            $year = date("01-M-Y", strtotime($year));
        }

        if (($finYearErr == "") && ($accountingMonthErr == "") && ($rateOfInterestErr == "") && ($yearErr == "")) {
            $query = "UPDATE MAS_INTEREST SET FIN_YEAR_CODE='$finYear', ACCOUNTING_MONTH='$accountingMonth', YEAR_DESC='$year', 
            RATE_OF_INTEREST='$rateOfInterest', MODIFY_USER='$loginUser', MODIFY_DATE=SYSDATE WHERE SL_NO='$slNo'";
            if (sqlCUDData($connection, $query)) {
                $error = 0;
                $message = "Successfully updated rate of interest";
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
    $roiQuery = "SELECT * FROM MAS_INTEREST WHERE SL_NO='$slNo'";
    $rois = sqlFetchData($connection, $roiQuery);
    foreach ($rois as $roisList) {
        $froi = $roisList['RATE_OF_INTEREST'];
        $fyear = $roisList['YEAR_DESC'];
        $faccountingMonth = $roisList['ACCOUNTING_MONTH'];
        $ffinYear = $roisList['FIN_YEAR_CODE'];
        $fhaccountingMonth = $roisList['ACCOUNTING_MONTH'];
        $fhfinYear = $roisList['FIN_YEAR_CODE'];
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
                                                    <label for="user_fin_year">Financial year <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="fin_year" id="fin_year" class="form-control">
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
                                                    <b class="text-danger"><?php echo $finYearErr; ?></b>
                                                </div>
                                                <div class="form-group">
                                                    <label for="user_name">Rate of interest <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="text" placeholder="Rate of interest" name="rate_of_interest" id="rate_of_interest" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                        echo $rateOfInterest;
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo $froi;
                                                                                                                                                                                    } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $rateOfInterestErr; ?></b>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="save_roi" id="save_roi">Save</button>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="accounting_month">Accounting month <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <select name="accounting_month" id="accounting_month" class="form-control">
                                                            <option value="">Select accounting month</option>
                                                            <?php
                                                            $accmonthQuery = "SELECT * FROM VLCS.MM_ACCOUNTING_MONTH WHERE ACCOUNTING_MONTH_CODE BETWEEN 1 AND 12 ORDER BY ACCOUNTING_MONTH_CODE ";
                                                            $accmonth = sqlFetchData($connection, $accmonthQuery);
                                                            foreach ($accmonth as $accmonthList) {
                                                            ?>
                                                                <option value="<?php echo $accmonthList['ACCOUNTING_MONTH_CODE']; ?>" <?php
                                                                                                                                        if ($error == 1) {
                                                                                                                                            if ($accmonthList['ACCOUNTING_MONTH_CODE'] == $accountingMonth) {
                                                                                                                                                echo "selected='selected'";
                                                                                                                                            }
                                                                                                                                        } else {
                                                                                                                                            if ($accmonthList['ACCOUNTING_MONTH_CODE'] == $faccountingMonth) {
                                                                                                                                                echo "selected='selected'";
                                                                                                                                            }
                                                                                                                                        }
                                                                                                                                        ?>>
                                                                    <?php echo ucfirst(strtolower($accmonthList['ACC_MONTH_LONG_NAME'])); ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <b class="text-danger"><?php echo $accountingMonthErr; ?></b>
                                                </div>
                                                <div class="form-group">
                                                    <label for="year">Month <b class="text-danger">*</b></label>
                                                    <div class="input-group input-group-default">
                                                        <input type="month" placeholder="Year" name="year" id="year" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                        echo date("Y-m", strtotime($year));
                                                                                                                                                    } else {
                                                                                                                                                        echo date("Y-m", strtotime($fyear));
                                                                                                                                                    } ?>">
                                                    </div>
                                                    <b class="text-danger"><?php echo $yearErr; ?></b>
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