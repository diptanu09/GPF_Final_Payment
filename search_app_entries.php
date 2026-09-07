<?php
$pageName = "All cases upto approved";
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

                                <table class="table table-bordered table-hover" id="app_search">
                                    <thead>
                                        <tr>
                                            <th>REGISTRATION NO.</th>
                                            <th>ACCOUNT NO.</th>
                                            <th>SUBSCRIBER NAME</th>
                                            <th>EMPLOYEE CODE</th>
                                            <th>BENEFICIARY CODE</th>
                                            <th>PENSION TYPE</th>
                                            <th>STATUS</th>
                                            <th>OPTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $applicationQuery = "SELECT A.SL_NO, A.REGD_NO, A.SUBSCRIBER_NAME, 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACC_NO, A.EMPLOYEE_CODE, A.BENEFICIARY_CODE,
                                                             B.PENSION_LONG_DESCR, C.STATUS_DESCR FROM GPF_CASE_STATUS A INNER JOIN MAS_PENSION_TYPE B ON A.PENSION_TYPE=B.PENSION_ID
                                                             INNER JOIN MAS_STATUS C ON A.CASE_STATUS=C.STATUS_ID INNER JOIN VLCS.MM_GPF_SERIES D ON A.SERIES_ID=D.SERIES_ID
                                                             WHERE A.CASE_STATUS NOT IN (7,8,11,16,17,18,19)";
                                        $fetchApplication = sqlFetchData($connection, $applicationQuery);
                                        foreach ($fetchApplication as $applicationList) {

                                        ?>
                                            <tr>
                                                <td><?php echo $applicationList['REGD_NO']; ?></td>
                                                <td><?php echo $applicationList['GPF_ACC_NO']; ?></td>
                                                <td><?php echo $applicationList['SUBSCRIBER_NAME']; ?></td>
                                                <td><?php echo $applicationList['EMPLOYEE_CODE']; ?></td>
                                                <td><?php echo $applicationList['BENEFICIARY_CODE']; ?></td>
                                                <td><?php echo $applicationList['PENSION_LONG_DESCR']; ?></td>
                                                <td><?php echo $applicationList['STATUS_DESCR']; ?></td>
                                                <td><button class="btn btn-warning" onclick=getDetails(<?php echo $applicationList['SL_NO']; ?>)>VIEW</button></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <div id="myModal" class="modal">
                                    <div class="modal-content">
                                        <ul class="nav nav-tabs customtab" role="tablist">
                                            <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#basic_info" role="tab"><span class="hidden-sm-up"><i class="ti-id-badge"></i></span> <span class="hidden-xs-down">Basic info</span></a> </li>
                                            <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#share_holder" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Share holders</span></a> </li>
                                            <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#date_info" role="tab"><span class="hidden-sm-up"><i class="ti-timer"></i></span> <span class="hidden-xs-down">Date information</span></a> </li>
                                            <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#case_remarks" role="tab"><span class="hidden-sm-up"><i class="ti-clipboard"></i></span> <span class="hidden-xs-down">Cases remarks</span></a> </li>
                                            <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#case_log" role="tab"><span class="hidden-sm-up"><i class="ti-trash"></i></span> <span class="hidden-xs-down">Cases logs</span></a> </li>
                                            <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#revert_log" role="tab"><span class="hidden-sm-up"><i class="ti-trash"></i></span> <span class="hidden-xs-down">Revert</span></a> </li>
                                        </ul>
                                        <!-- Tab panes -->
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="basic_info" role="tabpanel">
                                                <div class="p-20">
                                                    <div class="row">
                                                        <div class="col-lg-4">
                                                            <b>Registration Number : </b> <span id="regd_no"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Account number : </b> <span id="account_no"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Subscriber name : </b> <span id="subscriber_name"></span> <span id="designation"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Employee code : </b> <span id="employee_code"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Beneficiary code : </b><span id="beneficiary_code"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Debit during the year : </b>&#8377; <span id="debit_during_year"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Spouse name : </b><span id="spouse_name"></span> (<span id="spouse_relation"></span>)
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Date of <span id="pension_type"></span> : </b><span id="pension_date"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Last fund deduction : </b><span id="last_fund"></span>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <b>Personal address : </b><span id="personal_address"></span>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <b>DDO Address : </b><span id="ddo_code"></span> - <span id="ddo_address"></span>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <b>Treasury : </b><span id="treasury_code"></span> - <span id="treasury_name"></span> <span id="cntrl_treasury_name"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Missing credit / debit : </b><span id="missing_credit"></span> / <span id="missing_debit"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>DLIS Admissible : </b><span id="dlis_admissible"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Interest allowed upto : </b><span id="interest_allowed_upto"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Final Payment amount : </b>&#8377; <span id="final_payment"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>DLIS amount : </b>&#8377; <span id="dlis_amount"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>Date of death after retirement : </b><span id="dod_after_sup"></span>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <b>LTA to whom : </b><span id="lta_whom"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane  p-20" id="share_holder" role="tabpanel">
                                                <div class="table-responsive" style="height: 300px; overflow-y: scroll;">
                                                    <table class="table table bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>SHARE HOLDER NAME</th>
                                                                <th>RELATION WITH SHARE HOLDER</th>
                                                                <th>ADDRESS</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="share_holders"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-pane  p-20" id="date_info" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <b> Date of registration :</b> <span id="date_of_registration"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of application :</b> <span id="date_of_application"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of pre-calculation :</b> <span id="date_of_precalculation"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of calculation :</b> <span id="date_of_calculation"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of checked :</b> <span id="date_of_checked"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of approve :</b> <span id="date_of_approve"></span>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <b> Date of FP / DLIS signed authority :</b> <span id="date_of_fp_sign"></span> / <span id="date_of_dlis_sign"></span>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <b> Date of FP / DLIS authority upload:</b> <span id="date_of_fp_upload"></span> / <span id="date_of_dlis_upload"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of minus balance :</b> <span id="date_of_minus_balance"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of minus balance closed :</b> <span id="date_of_minus_balance_closed"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of objection :</b> <span id="date_of_objection"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of cancellation :</b> <span id="date_of_cancellation"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of LTA registration :</b> <span id="date_of_lta_registration"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of LTA application :</b> <span id="date_of_lta_application"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of LTA checked :</b> <span id="date_of_lta_checked"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of LTA approve :</b> <span id="date_of_lta_approve"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of LTA signed :</b> <span id="date_of_lta_signed"></span>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <b> Date of LTA upload :</b> <span id="date_of_lta_upload"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane  p-20" id="case_log" role="tabpanel">
                                                <div class="table-responsive" style="height: 300px; overflow-y: scroll;">
                                                    <table class="table table bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>STATUS</th>
                                                                <th>ACTION DATE</th>
                                                                <th>ACTION BY</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="case_logs"></tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="tab-pane  p-20" id="case_remarks" role="tabpanel">
                                                <div class="table-responsive" style="height: 300px; overflow-y: scroll;">
                                                    <table class="table table bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>REMARKS TYPE</th>
                                                                <th>REMARKS</th>
                                                                <th>REMARKS BY</th>
                                                                <th>REMARKED ON</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="remarks_logs"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-pane  p-20" id="revert_log" role="tabpanel">
                                                <div class="table-responsive" style="height: 300px; overflow-y: scroll;">
                                                    <table class="table table bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>FROM USER</th>
                                                                <th>TO USER</th>
                                                                <th>MESSAGE</th>
                                                                <th>DATE</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="revert_logs"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
    const getDetails = (slNo) => {
        if (slNo == "" || slNo == null) {
            swal({
                title: "Oops!",
                text: "Please provide serial number",
                icon: "error",
                button: "Close",
            });
        } else {
            const credentials = {
                SerialNo: slNo
            }
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/case_details.php',
                data: JSON.stringify(credentials),
                success: function(returnValue) {
                    const {
                        StatusCode,
                        Message,
                        Case_details,
                        Case_logs,
                        Case_remarks,
                        Share_holder,
                        Reverts
                    } = returnValue;

                    if (StatusCode === 200) {
                        const [{
                            APPROVED_DATE = "",
                            BENEFICIARY_CODE = "",
                            CALCULATION_DATE = "",
                            CANCELED_DATE = "",
                            CHECKED_DATE = "",
                            CNTRL_TRES_NAME = "",
                            DATE_OF_EFFECT = "",
                            DATE_OF_LTA = "",
                            DDO_CODE = "",
                            DDO_DESG = "",
                            DEBIT_DURING_YEAR = "",
                            DESG_TITLE = "",
                            DESIGNATION = "",
                            DLIS_ADMISSIBLE = "",
                            DLIS_AMOUNT = "",
                            DLIS_SIGNED_DATE = "",
                            DLIS_UPLOAD_DATE = "",
                            EMPLOYEE_CODE = "",
                            ENTERED_DATE = "",
                            FINAL_PAYMENT_AMOUNT = "",
                            FP_SIGNED_DATE = "",
                            FP_UPLOAD_DATE = "",
                            GPF_ACCOUNT_NO = "",
                            INTEREST_ALLOWED_UPTO = "",
                            LAST_FUND_DEDUCTION = "",
                            LTA_APPROVED_DATE = "",
                            LTA_CHECKED_DATE = "",
                            LTA_REGISTERED_DATE = "",
                            LTA_ENTERED_DATE = "",
                            LTA_SIGNED_DATE = "---",
                            LTA_TO_WHOM = "",
                            LTA_UPLOAD_DATE = "",
                            MINUS_BAL_DATE = "",
                            MINUS_BAL_CLOSED_DATE = "",
                            MISSING_CREDIT = "",
                            MISSING_DEBIT = "",
                            MOBILE_NO = "",
                            OBJECTION_DATE = "",
                            PENSION_LONG_DESCR = "",
                            PENSION_SHORT_DESCR = "",
                            PERSONAL_ADDRESS = "",
                            PRE_CAL_DATE = "",
                            REGD_DATE = "",
                            REGD_NO = "",
                            RELATION: SPOUSE_RELATION = "",
                            SPOUSE_NAME = "",
                            SUBSCRIBER_NAME = "",
                            TITLE = "",
                            TREASURY_CODE = "",
                            TRES_NAME = ""
                        }] = Case_details;

                        $("#regd_no").text(REGD_NO);
                        $("#account_no").text(GPF_ACCOUNT_NO);
                        $("#subscriber_name").text(SUBSCRIBER_NAME);
                        $("#designation").text(DESIGNATION == null ? " " : ", " + DESG_TITLE == "---" ? " " : DESG_TITLE + " " + DESIGNATION);
                        $("#employee_code").text(EMPLOYEE_CODE == null ? "" : EMPLOYEE_CODE);
                        $("#beneficiary_code").text(BENEFICIARY_CODE == null ? "" : BENEFICIARY_CODE);
                        $("#spouse_name").text(SPOUSE_NAME == null ? "" : SPOUSE_NAME);
                        $("#spouse_relation").text(SPOUSE_RELATION == null ? "" : SPOUSE_RELATION);
                        $("#debit_during_year").text(DEBIT_DURING_YEAR == null ? "---" : DEBIT_DURING_YEAR);
                        $("#pension_type").text(PENSION_LONG_DESCR);
                        $("#pension_date").text(DATE_OF_EFFECT == null ? "" : DATE_OF_EFFECT);
                        $("#last_fund").text(LAST_FUND_DEDUCTION == null ? "" : LAST_FUND_DEDUCTION);
                        $("#personal_address").text(PERSONAL_ADDRESS == null ? "" : PERSONAL_ADDRESS);
                        $("#ddo_code").text(DDO_CODE == null ? "" : DDO_CODE);
                        $("#ddo_address").text(DDO_DESG == null ? "" : DDO_DESG);
                        $("#treasury_code").text(TREASURY_CODE == null ? "" : TREASURY_CODE);
                        $("#cntrl_treasury_name").text(CNTRL_TRES_NAME == null ? "" : " (" + CNTRL_TRES_NAME + ")")
                        $("#treasury_name").text(TRES_NAME == null ? "" : TRES_NAME);
                        $("#missing_credit").text(MISSING_CREDIT == null ? "---" : MISSING_CREDIT == "N" ? "No" : "Yes");
                        $("#missing_debit").text(MISSING_DEBIT == null ? "---" : MISSING_DEBIT == "N" ? "No" : "Yes");
                        $("#dlis_admissible").text(DLIS_ADMISSIBLE == null ? "---" : DLIS_ADMISSIBLE == "N" ? "No" : "Yes");
                        $("#interest_allowed_upto").text(INTEREST_ALLOWED_UPTO == null ? "---" : INTEREST_ALLOWED_UPTO);
                        $("#dod_after_sup").text(DATE_OF_LTA == null ? "------" : DATE_OF_LTA);
                        $("#lta_whom").text(LTA_TO_WHOM == null ? "------" : LTA_TO_WHOM);
                        $("#date_of_registration").text(REGD_DATE == null ? "------" : REGD_DATE);
                        $("#date_of_application").text(ENTERED_DATE == null ? "------" : ENTERED_DATE);
                        $("#date_of_precalculation").text(PRE_CAL_DATE == null ? "------" : PRE_CAL_DATE);
                        $("#date_of_calculation").text(CALCULATION_DATE == null ? "------" : CALCULATION_DATE);
                        $("#date_of_checked").text(CHECKED_DATE == null ? "------" : CHECKED_DATE);
                        $("#date_of_approve").text(APPROVED_DATE == null ? "------" : APPROVED_DATE);
                        $("#date_of_fp_sign").text(FP_SIGNED_DATE == null ? "------" : FP_SIGNED_DATE);
                        $("#date_of_dlis_sign").text(DLIS_SIGNED_DATE == null ? "------" : DLIS_SIGNED_DATE);
                        $("#date_of_fp_upload").text(FP_UPLOAD_DATE == null ? "------" : FP_UPLOAD_DATE);
                        $("#date_of_dlis_upload").text(DLIS_UPLOAD_DATE == null ? "------" : DLIS_UPLOAD_DATE);
                        $("#date_of_minus_balance").text(MINUS_BAL_DATE == null ? "------" : MINUS_BAL_DATE);
                        $("#date_of_minus_balance_closed").text(MINUS_BAL_CLOSED_DATE == null ? "------" : MINUS_BAL_CLOSED_DATE);
                        $("#date_of_objection").text(OBJECTION_DATE == null ? "------" : OBJECTION_DATE);
                        $("#date_of_cancellation").text(CANCELED_DATE == null ? "------" : CANCELED_DATE);
                        $("#date_of_lta_registration").text(LTA_REGISTERED_DATE == null ? "------" : LTA_REGISTERED_DATE);
                        $("#date_of_lta_application").text(LTA_ENTERED_DATE == null ? "------" : LTA_ENTERED_DATE);
                        $("#date_of_lta_checked").text(LTA_CHECKED_DATE == null ? "------" : LTA_CHECKED_DATE);
                        $("#date_of_lta_approve").text(LTA_APPROVED_DATE == null ? "------" : LTA_APPROVED_DATE);
                        $("#date_of_lta_signed").text(LTA_SIGNED_DATE == null ? "------" : LTA_SIGNED_DATE);
                        $("#date_of_lta_upload").text(LTA_UPLOAD_DATE == null ? "------" : LTA_UPLOAD_DATE);
                        $("#final_payment").text(FINAL_PAYMENT_AMOUNT == null ? "" : FINAL_PAYMENT_AMOUNT);
                        $("#dlis_amount").text(DLIS_AMOUNT == null ? "" : DLIS_AMOUNT);

                        let shareHolderTable = "";
                        let caseLogTable = "";
                        let remarkLogsTable = "";
                        let revertLogsTable = "";
                        for (let case_log of Case_logs) {
                            caseLogTable += "<tr>";
                            caseLogTable += "<td>" + case_log.STATUS_DESCR + "</td>";
                            caseLogTable += "<td>" + case_log.ACTION_DATE + "</td>";
                            caseLogTable += "<td>" + case_log.ACTION_USER + "</td>";
                            caseLogTable += "<tr>";
                        }
                        $("#case_logs").html(caseLogTable);

                        for (let revert_log of Reverts) {
                            revertLogsTable += "<tr>";
                            revertLogsTable += "<td>" + revert_log.FROM_USER + "</td>";
                            revertLogsTable += "<td>" + revert_log.TO_USER + "</td>";
                            revertLogsTable += "<td>" + revert_log.MESSAGE + "</td>";
                            revertLogsTable += "<td>" + revert_log.DATE_TIME + "</td>";
                            revertLogsTable += "<tr>";
                        }
                        $("#revert_logs").html(revertLogsTable);



                        if (Case_remarks != "undefined" || Case_remarks != "" || Case_remarks != "null" || Case_remarks != null) {
                            for (let case_remark of Case_remarks) {
                                remarkLogsTable += "<tr>";
                                remarkLogsTable += "<td>" + case_remark.REMARKS_TYPE + "</td>";
                                remarkLogsTable += "<td>" + case_remark.REMARKS + "</td>";
                                remarkLogsTable += "<td>" + case_remark.REMARK_USER + "</td>";
                                remarkLogsTable += "<td>" + case_remark.REMARK_DATE + "</td>";
                                remarkLogsTable += "<tr>";
                            }
                            $("#remarks_logs").html(remarkLogsTable);
                        }
                        $("#share_holders").html(shareHolderTable);
                        if (Share_holder != "undefined" || Share_holder != "" || Share_holder != "null" || Share_holder != null) {
                            for (let share_holder of Share_holder) {
                                shareHolderTable += "<tr>";
                                shareHolderTable += "<td>" + share_holder.SHARE_HOLDER_NAME + "</td>";
                                shareHolderTable += "<td>" + share_holder.SHARE_HOLDER_RELATION + "</td>";
                                shareHolderTable += "<td>" + share_holder.SHARE_HOLDER_ADDRESS + "</td>";
                                shareHolderTable += "<tr>";
                            }
                            $("#share_holders").html(shareHolderTable);
                        }



                        const modal = document.getElementById("myModal");
                        modal.style.display = "block";
                        window.onclick = function(event) {
                            if (event.target == modal) {
                                modal.style.display = "none";
                            }
                        }
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
    $(document).ready(function() {
        $('#app_search').DataTable();
    });
</script>