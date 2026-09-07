<?php
$pageName = "Calculation";
require_once "./top.php";
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
                                <?php
                                if (isset($_REQUEST['regd_no'])) {
                                    $registrationNo = $_REQUEST['regd_no'];
                                    $infoSQL = "SELECT c.PENSION_LONG_DESCR, c.PENSION_SHORT_DESCR, b.DATE_OF_EFFECT FROM GPF_CASE_STATUS
                                                a INNER JOIN GPF_APPLICATION b ON a.REGD_NO=b.REGD_NO INNER JOIN MAS_PENSION_TYPE c ON
                                                a.PENSION_TYPE=c.PENSION_ID WHERE a.CASE_STATUS!='11' AND a.REGD_NO='$registrationNo'";
                                    $fetchInfo = sqlFetchData($connection, $infoSQL);
                                    foreach ($fetchInfo as $fetchInfoList) {
                                        $pensionType = $fetchInfoList['PENSION_LONG_DESCR'];
                                        $dateOfEffect = date("d/m/Y", strtotime($fetchInfoList['DATE_OF_EFFECT']));
                                    }
                                ?>
                                    <?php
                                    $cutMonthQuery = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo' AND CUT_MONTH='Y'";
                                    $cutMonthExist = sqlCountData($connection, $cutMonthQuery);
                                    if ($cutMonthExist > 0) {
                                    ?>
                                        <button class="btn btn-primary" onclick="doCalculation(<?php echo $registrationNo; ?>)">
                                            DO CALCULATION
                                        </button>
                                    <?php
                                    } else {
                                    ?>
                                        <b class="text-danger">Please select cut month</b>
                                    <?php
                                    }
                                    ?>

                                    <b class="alert alert-info">Date of <?php echo $pensionType; ?> : <?php echo $dateOfEffect; ?></b>
                                    <br><br>
                                    <div class="fixTableHead">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>FIN YEAR</th>
                                                    <th>PAY SLIP DATE</th>
                                                    <th>INTEREST DATE</th>
                                                    <th>SUBS</th>
                                                    <th>REF</th>
                                                    <th>OTH</th>
                                                    <th>WITHD</th>
                                                    <th>ADV</th>
                                                    <th>DEP</th>
                                                    <th>DEB</th>
                                                    <th>ROI</th>
                                                    <th>SET CUT MONTH</th>
                                                    <th>CUT MONTH</th>
                                                    <th>INT ON DEPOSIT</th>
                                                    <th>SET INT ON DEPOSIT</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $calQuery = "SELECT * FROM GPF_ACCOUNT_CALCULATION a INNER JOIN VLCS.MM_FINANCIAL_YEAR b ON a.FIN_YEAR_CODE=b.FIN_YEAR_CODE
                                                         WHERE a.REGD_NO='$registrationNo' ORDER BY a.FIN_YEAR_CODE, a.PAY_SLIP_DATE ";
                                                $fetchCalculation = sqlFetchData($connection, $calQuery);
                                                foreach ($fetchCalculation as $calculationList) {
                                                    if ($calculationList['ADJUSTMENT'] == 'Y') {
                                                        $adjustmentTogg = "<b class='text-danger'>*</b>";
                                                    } else {
                                                        $adjustmentTogg = "";
                                                    }
                                                ?>
                                                    <?php
                                                    if ($calculationList['CUT_MONTH'] == 'Y') {
                                                    ?>
                                                        <tr style="background-color: #FA651A;">
                                                            <?php
                                                        } else {
                                                            if ($calculationList['INTEREST_ON_DEPOSIT'] == 'N') {
                                                            ?>
                                                        <tr style="background-color: #B9FAF3;">
                                                    <?php
                                                            }
                                                        }
                                                    ?>
                                                    <td><?php echo $adjustmentTogg . " " . $calculationList['FIN_YEAR']; ?></td>
                                                    <td><?php echo date("F, Y", strtotime($calculationList['PAY_SLIP_DATE'])); ?></td>
                                                    <td><?php echo date("F, Y", strtotime($calculationList['INTEREST_DATE'])); ?></td>
                                                    <td><?php echo $calculationList['SUBSCRIPTION_AMT']; ?></td>
                                                    <td><?php echo $calculationList['REFUND_AMT']; ?></td>
                                                    <td><?php echo $calculationList['OTHERS_AMT']; ?></td>
                                                    <td><?php echo $calculationList['WITHDRAWAL_AMT']; ?></td>
                                                    <td><?php echo $calculationList['ADVANCE_AMT']; ?></td>
                                                    <td><?php echo $calculationList['DEPOSIT']; ?></td>
                                                    <td><?php echo $calculationList['WITHDRAWAL']; ?></td>
                                                    <td><?php echo $calculationList['RATE_OF_INTEREST']; ?></td>
                                                    <td>
                                                        <?php
                                                        if ($calculationList['CUT_MONTH'] === 'Y') {
                                                        ?>
                                                            <button class="btn btn-sm btn-primary" value="N" onclick="changeStat('<?php echo $calculationList['CALCULATION_ID']; ?>', 'cutmonth', this.value)">NO</button>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <button class="btn btn-sm btn-primary" value="Y" onclick="changeStat('<?php echo $calculationList['CALCULATION_ID']; ?>', 'cutmonth', this.value)">YES</button>
                                                        <?php
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($calculationList['CUT_MONTH'] === 'Y') {
                                                        ?>
                                                            <span style="font-weight: bolder;" class="float-right"><?php echo $calculationList['CUT_MONTH'] == "Y" ? "Yes" : "No"; ?></span>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <span style="font-weight: bolder;" class="float-right"><?php echo $calculationList['CUT_MONTH'] == "Y" ? "Yes" : "No"; ?></span>
                                                        <?php
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($calculationList['INTEREST_ON_DEPOSIT'] === 'Y') {
                                                        ?>
                                                            <button class="btn btn-sm btn-primary" value="N" onclick="changeStat('<?php echo $calculationList['CALCULATION_ID']; ?>', 'int_dep', this.value)">NO</button>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <button class="btn btn-sm btn-primary" value="Y" onclick="changeStat('<?php echo $calculationList['CALCULATION_ID']; ?>', 'int_dep', this.value)">YES</button>
                                                        <?php
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($calculationList['INTEREST_ON_DEPOSIT'] === 'Y') {
                                                        ?>
                                                            <span style="font-weight: bolder;" class="float-right"><?php echo $calculationList['INTEREST_ON_DEPOSIT'] == "Y" ? "Yes" : "No"; ?></span>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <span style="font-weight: bolder;" class="float-right"><?php echo $calculationList['INTEREST_ON_DEPOSIT'] == "Y" ? "Yes" : "No"; ?></span>
                                                        <?php
                                                        }
                                                        ?>
                                                    </td>
                                                        </tr>
                                                    <?php
                                                }
                                                    ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <form action="" method="post">
                                        <div class="form-inline">
                                            <label for="regd_no"><b>REGISTRATION NO.: </b> </label>&nbsp;&nbsp;&nbsp;
                                            <input type="text" pattern="[0-9]+" title="Only numbers" required name="registration_no" id="registration_no" class="form-control">
                                            &nbsp;&nbsp;&nbsp;
                                            <button type="submit" name="get_reg_no" class="btn btn-info">SEARCH</button>
                                        </div>
                                    </form><br><br>
                                    <?php
                                    if (isset($_REQUEST['get_reg_no'])) {
                                        $registrationNo = trim($_REQUEST['registration_no']);
                                    ?>
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>REGISTRATION NO</th>
                                                    <th>ACCOUNT NO</th>
                                                    <th>FULL NAME</th>
                                                    <th>PENSION TYPE</th>
                                                    <th>DATE OF EFFECT</th>
                                                    <th>OPTION</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $inwardQuery = "SELECT a.REGD_NO, 'T/'||e.SERIES_DESCR||'/'||a.ACCOUNT_NO ACCOUNT_NO, b.SUBSCRIBER_NAME,
                                                        d.PENSION_LONG_DESCR, b.CASE_STATUS, f.DATE_OF_EFFECT, d.PENSION_SHORT_DESCR FROM GPF_INWARD a INNER JOIN
                                                        GPF_CASE_STATUS b ON a.REGD_NO=b.REGD_NO INNER JOIN MAS_PENSION_TYPE d ON b.PENSION_TYPE=d.PENSION_ID 
                                                        INNER JOIN VLCS.MM_GPF_SERIES e ON a.SERIES_ID=e.SERIES_ID INNER JOIN GPF_APPLICATION f ON
                                                        a.REGD_NO=f.REGD_NO  WHERE a.REGD_NO='$registrationNo' AND b.CASE_STATUS!=11 GROUP BY a.REGD_NO, e.SERIES_DESCR,
                                                         a.ACCOUNT_NO, b.SUBSCRIBER_NAME, d.PENSION_LONG_DESCR, d.PENSION_SHORT_DESCR, f.DATE_OF_EFFECT, b.CASE_STATUS";
                                                $fetchinward = sqlFetchData($connection, $inwardQuery);
                                                foreach ($fetchinward as $inwardList) {
                                                    $appQuery = "SELECT * FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='" . $inwardList['REGD_NO'] . "'";
                                                    $countCalculation = sqlCountData($connection, $appQuery);
                                                    $caseStatus = $inwardList['CASE_STATUS'];
                                                ?>
                                                    <tr>
                                                        <td><?php echo $inwardList['REGD_NO']; ?></td>
                                                        <td><?php echo $inwardList['ACCOUNT_NO']; ?></td>
                                                        <td><?php echo $inwardList['SUBSCRIBER_NAME']; ?></td>
                                                        <td> <?php echo $inwardList['PENSION_LONG_DESCR']; ?> (<?php echo $inwardList['PENSION_SHORT_DESCR']; ?>)</td>
                                                        <td><?php echo date("d-m-Y", strtotime($inwardList['DATE_OF_EFFECT'])); ?></td>
                                                        <td>
                                                            <?php
                                                            if ($countCalculation > 0) {
                                                                if (($caseStatus > 4 && $caseStatus < 9) || $caseStatus > 13) {
                                                            ?>
                                                                    <span class="badge badge-warning">Case has been approved/signed</span>
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <button class="btn btn-danger btn-sm" onclick="deleteCalculation(<?php echo $inwardList['REGD_NO']; ?>)">DELETE</button>
                                                                <?php
                                                                }
                                                                ?>
                                                                <a href="calculation_sheet.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                                    <button class="btn btn-info btn-sm"> SHOW</button>
                                                                </a>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <button class="btn btn-primary btn-sm" onclick="setCalculation(<?php echo $inwardList['REGD_NO']; ?>)">SET FOR CALCULATION</button>
                                                            <?php
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                <?php
                                    }
                                } ?>
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
    function setCalculation(registrationNo) {
        if (registrationNo === "") {
            swal({
                title: "Oops!",
                text: "Please provide registration number",
                icon: "error",
                button: "Close",
            });
        } else {
            const regdNoCred = {
                RegistrationNo: registrationNo
            }
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/set_calculation.php',
                data: JSON.stringify(regdNoCred),
                success: function(returnValue) {
                    const {
                        StatusCode,
                        Message
                    } = returnValue;

                    if (StatusCode === 200) {
                        window.location.href = "calculation_list.php?regd_no=" + registrationNo
                    } else {
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
    }


    function deleteCalculation(registrationNo) {

        swal({
                title: "Are you sure?",
                text: "You are about to delete the calculation, be safe",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    if (registrationNo === "") {
                        swal({
                            title: "Oops!",
                            text: "Please provide registration number",
                            icon: "error",
                            button: "Close",
                        });
                    } else {
                        const regdNoCred = {
                            RegistrationNo: registrationNo
                        }
                        jQuery.ajax({
                            type: 'POST',
                            url: 'ajax/delete_calculation.php',
                            data: JSON.stringify(regdNoCred),
                            success: function(returnValue) {
                                const {
                                    StatusCode,
                                    Message
                                } = returnValue;

                                if (StatusCode === 200) {
                                    window.location.href = "calculation_list.php"
                                } else {
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
                } else {
                    swal("Your calculation has not been deleted");
                }
            });
    }

    function doCalculation(registrationNo) {
        if (registrationNo === "") {
            swal({
                title: "Oops!",
                text: "Please provide registration number",
                icon: "error",
                button: "Close",
            });
        } else {
            const regdNoCred = {
                LoginUser: "<?php echo $loginUser; ?>",
                RegistrationNo: registrationNo
            }
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/calculate.php',
                data: JSON.stringify(regdNoCred),
                success: function(returnValue) {
                    const {
                        StatusCode,
                        Message
                    } = returnValue;

                    if (StatusCode === 200) {
                        window.location.href = "calculation_sheet.php?regd_no=" + registrationNo
                    } else {
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
    }

    function changeStat(calculationID, type, typeVal) {
        const registrationNo = "<?php echo $registrationNo; ?>";
        if (calculationID === "") {
            swal({
                title: "Oops!",
                text: "Please provide calculation id",
                icon: "error",
                button: "Close",
            });
        } else {
            const toggleCred = {
                CalculationID: calculationID,
                Type: type,
                Toggle: typeVal
            }
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/cutmonth_intdep.php',
                data: JSON.stringify(toggleCred),
                success: function(returnValue) {
                    const {
                        StatusCode,
                        Message
                    } = returnValue;

                    if (StatusCode === 200) {
                        window.location.href = "calculation_list.php?regd_no=" + registrationNo
                    } else {
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
    }
</script>