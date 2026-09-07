<?php
require_once "./includes/global_functions.php";
require_once "./includes/constants.php";
require_once "./includes/connection.php";
require_once "./includes/convert_figure_words.php";
require_once './assets/lib/pdflib/autoload.php';
require_once './assets/lib/phpqrcode/qrlib.php';
if ((!$_SESSION['GPF_USERNAME']) && (!$_SESSION['GPF_USER_ROLE'])) {
    redirectPage("index.html");
}
$loginUser = $_SESSION['GPF_USERNAME'];
$loginRole = $_SESSION['GPF_USER_ROLE'];
$loginDetailQuery = "";
$loginDetailQuery .= "SELECT a.FULL_NAME, b.ROLE_NAME FROM USER_ACCOUNTS a INNER JOIN MAS_ROLES b ";
$loginDetailQuery .= "ON a.USER_ROLE = b.ROLE_ID WHERE a.USERNAME='$loginUser'";
$fetchLoginData = sqlFetchData($connection, $loginDetailQuery);
foreach ($fetchLoginData as $fetchLoginDataList) {
    $loginName = $fetchLoginDataList['FULL_NAME'];
    $loginRoleName = $fetchLoginDataList['ROLE_NAME'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>GPF Final Payment || <?php echo $pageName; ?></title>

    <link href="./assets/css/font-awesome.min.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/themify-icons.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/sidebar.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/bootstrap.min.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/dataTables.bootstrap4.min.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/helper.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/select2.min.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/style.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/modals.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/report_format.css?<?php echo time(); ?>" rel="stylesheet">
    <link href="./assets/css/demo_style.css?<?php echo time(); ?>" rel="stylesheet">
</head>

<body>
    <div class="sidebar sidebar-hide-to-small sidebar-shrink sidebar-gestures">
        <div class="nano">
            <div class="nano-content">
                <ul>
                    <div class="logo"><a href="./home.php">
                            <!-- <img src="images/logo.png" alt="" /> --><span>GPF Final Payment</span>
                        </a></div>
                    <li class="label">Main</li>
                    <li><a href="http://192.168.100.9/GPF_Residual_Payment/home.php" target="_blank">
                            <i class="ti-link"></i> Residual Payment</a></li>
                    <!-- <li><a href="http://localhost:81/GPF_Residual_Payment/home.php" target="_blank">
                            <i class="ti-link"></i> Residual Payment</a></li> -->
                    <li><a href="./home.php">
                            <i class="ti-dashboard"></i> Dashboard</a></li>

                    <?php
                    if ($loginRole == 1) {
                    ?>
                        <!-- Admin settings-->
                        <li class="label">Admin settings</li>
                        <li>
                            <a class="sidebar-sub-toggle">
                                <i class="ti-info-alt"></i> Master
                                <span class="sidebar-collapse-icon ti-angle-down"></span>
                            </a>
                            <ul>
                                <li><a href="./view_users.php"> <i class="ti-user"></i> Users</a></li>
                                <li><a href="./view_interest.php"> <i class="ti-info"></i> Interest</a></li>
                                <li><a href="./delete_files.php"> <i class="ti-trash"></i> Delete unused files</a></li>
                            </ul>
                        </li>
                        <li>
                            <a class="sidebar-sub-toggle">
                                <i class="ti-harddrive"></i> Batch run
                                <span class="sidebar-collapse-icon ti-angle-down"></span>
                            </a>
                            <ul>
                                <li><a href="./batch_run.php"> <i class="ti-widget"></i> Report monthly batch run</a></li>
                            </ul>
                        </li>
                        <li><a class="sidebar-sub-toggle">
                                <i class="ti-file"></i> Case
                                <span class="sidebar-collapse-icon ti-angle-down"></span>
                            </a>
                            <ul>
                                <li><a href="./unapprove.php"> <i class="ti-na"></i> Un-approve</a></li>
                                <li><a href="./delete_digital_signature.php"> <i class="ti-close"></i> Delete digital signature</a></li>
                                <li><a href="./delete_case.php"> <i class="ti-close"></i> Delete cases</a></li>
                                <li><a href="./cancel_case.php"> <i class="ti-trash"></i> Cancel cases</a></li>
                            </ul>
                        </li>
                        <!-- Admin settings-->
                    <?php
                    }
                    ?>
                    <!-- Diary -->
                    <li class="label">Diary</li>
                    <li><a href="./view_inward.php"> <i class="ti-arrow-right"></i> Inward</a></li>
                    <li><a class="sidebar-sub-toggle">
                            <i class="ti-notepad"></i> Sectional receipt
                            <span class="sidebar-collapse-icon ti-angle-down"></span>
                        </a>
                        <ul>
                            <li><a href="./view_sectional_receipt.php"> <i class="ti-notepad"></i> Mark cases</a></li>
                            <li><a href="./marked_cases_report.php"> <i class="ti-notepad"></i> Report</a></li>
                        </ul>
                    </li>
                    <li><a class="sidebar-sub-toggle">
                            <i class="ti-pencil-alt"></i> Application
                            <span class="sidebar-collapse-icon ti-angle-down"></span>
                        </a>
                        <ul>
                            <li><a href="./view_application.php"> <i class="ti-pencil-alt"></i> View</a></li>
                            <li><a href="./application_edit_list.php"> <i class="ti-pencil-alt"></i> Edit list</a></li>
                        </ul>
                    </li>

                    <li><a href="./view_pre_calculation.php"> <i class="ti-star"></i> Pre-calculation/Broad sheet</a></li>
                    <li><a href="./calculation_list.php"> <i class="ti-money"></i> Calculation</a></li>
                    <li><a href="./input_sheet.php"> <i class="ti-receipt"></i> Input sheet</a></li>

                    <?php
                    if ($loginRole == 1 || $loginRole == 2 || $loginRole == 3) {
                    ?>
                        <!-- Check and Approval -->
                        <li class="label">Check and Approve case</li>
                        <li><a href="./approval_list.php"> <i class="ti-check"></i>Check and Approve case</a></li>
                        <!-- Check and Approval -->

                        <!-- Authority and digital signature -->
                        <li class="label">Authority and digital signature</li>
                        <li><a href="authority.php"><i class="ti-write"></i>Authority reports</a></li>
                        <li><a href="./digital_signature_list.php"><i class="ti-marker"></i> Signature list</a></li>
                        <!-- Authority and digital signature -->
                    <?php
                    }
                    ?>

                    <li><a href="./view_outward.php"> <i class="ti-arrow-left"></i> Outward</a></li>
                    <!-- Upload list in HRMS -->
                    <li class="label">Upload list in HRMS</li>
                    <li><a href="upload_list.php"><i class="ti-upload"></i>Authority reports upload</a></li>
                    <!-- Upload list in HRMS -->

                    <!-- Minus balance remarks -->
                    <li class="label">Minus balance remarks</li>
                    <li><a href="minus_balance_remarks.php"><i class="ti-minus"></i>Minus balance</a></li>
                    <!-- Minus balance remarks -->

                    <!-- Monthly report -->
                    <!-- <li class="label">Monthly report</li>
                    <li><a href="./monthly_report.php"> <i class="ti-info"></i> Monthly report</a></li>
                    <li><a href="tmc_report.php"><i class="ti-notepad"></i>TMC report</a></li> -->
                    <!-- Monthly report -->

                    <!-- Letters -->
                    <li class="label">Letters</li>
                    <li><a href="corrigendum_revalidation.php"><i class="ti-files"></i>Corrigendum/Revalidation</a></li>
                    <li><a href="objection_minus_balance.php"><i class="ti-files"></i>Objection/Minus balance</a></li>
                    <li><a href="intimation_letter.php"><i class="ti-files"></i>Intimation letter</a></li>
                    <!-- Letters -->

                    <li class="label">Search</li>
                    <li><a href="search.php"><i class="ti-search"></i>Search</a></li>
                    <li><a href="search_app_entries.php"><i class="ti-search"></i>Application entries</a></li>

                    <li class="label">MIS reports</li>
                    <li> <a href="closed_report.php"><i class="ti-notepad"></i>Closed report (list)</a></li>
                    <li><a href="mis_user_report.php"><i class="ti-notepad"></i>User report (count)</a></li>
                    <li><a href="mis_calculated_cases.php"><i class="ti-notepad"></i>Calculated cases (list)</a></li>
                    <li><a href="pending_cases.php"><i class="ti-notepad"></i>Pending cases (list)</a></li>
                    <li><a href="mis_settled_cases.php"><i class="ti-notepad"></i>Settled cases (list)</a></li>
                    <li><a class="sidebar-sub-toggle">
                            <i class="ti-notepad"></i> Digital signature
                            <span class="sidebar-collapse-icon ti-angle-down"></span>
                        </a>
                        <ul>
                            <li><a href="./mis_digital_signature.php"> <i class="ti-notepad"></i> List</a></li>
                            <li><a href="./mis_user_digital_signature.php"> <i class="ti-notepad"></i> User wise</a></li>
                        </ul>
                    </li>
                    <li><a href="mis_authority_cancel_cases.php"><i class="ti-notepad"></i>Authority cancel cases (list)</a></li>
                    <li><a href="mis_cancel_cases.php"><i class="ti-notepad"></i>Cancel cases (list)</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /# sidebar -->

    <div class="header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="float-left">
                        <div class="hamburger sidebar-toggle">
                            <span class="line"></span>
                            <span class="line"></span>
                            <span class="line"></span>
                        </div>
                    </div>
                    <div class="float-right">
                        <div class="dib">
                            <div class="header-icon">
                                <a href="logout.php" title="Logout">
                                    <i class="ti-power-off"></i>
                                </a>
                            </div>
                        </div>
                        <div class="dib">
                            <div class="header-icon">
                                <a href="./profile.php">
                                    <span class="user-avatar"><?php echo $loginName; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>