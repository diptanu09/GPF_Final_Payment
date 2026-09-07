<?php
$pageName = "Minus balance report";
require_once "./top.php";
if (isset($_REQUEST['regd_no'])) {
    $registrationNo = trim($_REQUEST['regd_no']);
    $applicationQuery = "SELECT 'T/'||D.SERIES_DESCR||'/'||A.ACCOUNT_NO GPF_ACCOUNT_NO, A.SUBSCRIBER_NAME, H.SECTION_DESCR, 
                         C.PENSION_SHORT_DESCR, I.FINAL_PAYMENT_AMOUNT, F.DDO_CODE, B.PERSONAL_ADDRESS, E.FIN_YEAR, B.DESG_TITLE,
                         B.DESIGNATION, B.TITLE, B.SPOUSE_NAME, B.RELATION FROM GPF_CASE_STATUS A INNER JOIN GPF_APPLICATION B ON A.REGD_NO=B.REGD_NO
                         INNER JOIN MAS_PENSION_TYPE C ON A.PENSION_TYPE=C.PENSION_ID INNER JOIN VLCS.MM_GPF_SERIES D 
                         ON A.SERIES_ID=D.SERIES_ID INNER JOIN VLCS.MM_FINANCIAL_YEAR E ON A.FIN_YEAR_CODE=E.FIN_YEAR_CODE 
                         INNER JOIN VLCS.STATE_DDO F ON B.DDO_CODE=F.DDO_CODE INNER JOIN GPF_INWARD G ON A.REGD_NO=G.REGD_NO
                         INNER JOIN MAS_SECTION H ON G.SECTION=H.SECTION_ID LEFT JOIN GPF_AMOUNT_INFO I ON A.REGD_NO=I.REGD_NO
                         WHERE a.REGD_NO='$registrationNo'";

    $fetchApplicationData = sqlFetchData($connection, $applicationQuery);
    foreach ($fetchApplicationData as $fetchApplicationList) {
        $sectionName = $fetchApplicationList['SECTION_DESCR'];
        $fpensionType = $fetchApplicationList['PENSION_SHORT_DESCR'];
        $fgpfSeries = $fetchApplicationList['GPF_ACCOUNT_NO'];
        $fsubscriberName = $fetchApplicationList['SUBSCRIBER_NAME'];
        $ffinYear = $fetchApplicationList['FIN_YEAR'];
        $fnameTitle = $fetchApplicationList['TITLE'];
        $fdesignationTitle = $fetchApplicationList['DESG_TITLE'];
        $fdesignation = $fetchApplicationList['DESIGNATION'];
        $fspouseName = $fetchApplicationList['SPOUSE_NAME'];
        $fspouseRelation = $fetchApplicationList['RELATION'];
        $fpersonalAddress = $fetchApplicationList['PERSONAL_ADDRESS'];
        $fddoCode = $fetchApplicationList['DDO_CODE'];
        $finalPayment = $fetchApplicationList['FINAL_PAYMENT_AMOUNT'];
    }

    $fmatter = "I am to invite a reference to your letter no. F.1(6-79)/ESSTT/PR/03/13181-82 dated 11-08-2022 on the subject cited above I am state that his GPF Final Payment balance arises to Minus Balance of ₹ $finalPayment due to GPF Withdrawals of ₹ (y) in (Date) and ₹ (z) in (Date).

    As per sub-rule(7) of Rule 11 of the GPF (CS) Rules, 1960, in case a subscriber is found to have been drawn from the Fund an amount in excess of the amount standing to his / her credit on the date of the drawal, the overdrawn amount shall be repaid by him / her with interest thereon alongwith panel rate of interest under sub-rule (1). The Minus Balance/Overdrawn amount shall be credited to Govt. account under the Major Head '8009 - State Provident Funds' through a separate challan. The interest realized on the overdrawn amount shall be credited to Govt. account under 'Interest on overdrawls from Provident Fund' under the Head '0049-Interest Receipts' through a separate challan.
    
    It is a serious matter as it involves irregularities on the part of DDO / Department and is likely to cause loss to Government exchequer. I would request you to kindly look into the matter personally so as to avoid such overdrawn.
    
    You are therefore requested to verify the overdrawal amount from your record and make arrangement for recovery of overdrawal amount / remaining part thereof, if partial recovery is made, and interest thereon as per GPF Rules with intimation to this office for necessary action at this end.";

    $fheadOfOffice = "";

    $fcfi = "1. It is recommended for appropriate action against DDO for his negligence for sanctioning of Rs() in (date) and Rs () in (date) as withdrawal / advance more than the balance at credit for the subscriber.
   
    2. $fnameTitle $fsubscriberName, $fpersonalAddress.    
   
    3. The Accounts Officer / Sr. Accounts Offcer, is requested to held up the Gratuity / DCRG untill further information";
?>

    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <?php
                require_once "./includes/breadcumb.php";
                ?>
                <section id="main-content">
                    <?php
                    if (isset($_REQUEST['generate_report'])) {
                        $accountNumber = trim($_REQUEST['account_no']);
                        $pensionType = trim($_REQUEST['pension_type']);
                        $nameTitle = trim($_REQUEST['name_title']);
                        $designationTitle = trim($_REQUEST['designation_title']);
                        $spouseName = trim($_REQUEST['spouse_name']);
                        $personalAddress = trim($_REQUEST['personal_address']);
                        $matter = trim($_REQUEST['matter']);
                        $cfi = trim($_REQUEST['cfi']);
                        $finYear = trim($_REQUEST['finyear']);
                        $subscriberName = trim($_REQUEST['subscriber_name']);
                        $designation = trim($_REQUEST['designation']);
                        $spouseRelation = trim($_REQUEST['spouse_relation']);
                        $ddoCode = trim($_REQUEST['ddo_code']);
                        $signature = trim($_REQUEST['signature']);
                        $headOfOffice = trim($_REQUEST['head_of_office']);

                        $ddoNameSQL = "SELECT * FROM VLCS.STATE_DDO WHERE DDO_CODE='$ddoCode'";
                        $fetchDDO = sqlFetchData($connection, $ddoNameSQL);
                        foreach ($fetchDDO as $ddoList) {
                            $ddoDesignation = $ddoList['DDO_DESG'];
                        }
                    ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <button id="upload_btn" onclick="download()" class="btn btn-primary">
                                            DOWNLOAD</button>
                                        <div class="row">
                                            <div class="col-lg-2"></div>
                                            <div class="col-lg-8" id="printArea">
                                                <table width="100%" cellpadding="5px" cellspacing="0" border="0">
                                                    <tr>
                                                        <td width="8%" align="right" valign="top"><img src="<?php echo getHostLink('GPF_Final_Payment'); ?>/assets/images/cag_logo.png" width="64" height="64" /></td>
                                                        <td width="92%">
                                                            <center>
                                                                <span style="font-size: 20px; font-weight: bold;">महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला</span><br />
                                                                <span style="font-weight: bold;font-size: 14px;">OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA</span>
                                                            </center>
                                                        </td>
                                                        <td width="8%" align="left" valign="top"><img src="<?php echo getHostLink('GPF_Final_Payment'); ?>/assets/images/ashok_stambh.png" width="44" height="64" /></td>

                                                    </tr>
                                                </table>
                                                <hr>

                                                <table style="width:100%;">
                                                    <tr>
                                                        <td style="text-align: left; width:70%;">No. <?php echo $sectionName; ?> / FP / Minus Balance / <?php echo $pensionType; ?> / <?php echo $finYear; ?> / <?php echo $registrationNo; ?> /</td>
                                                        <td style="text-align: right; width:30%;">Date : <?php echo date("d / m / Y"); ?></td>
                                                    </tr>
                                                </table> <br>
                                                <div style="text-align:justify; line-height:1.8em; padding:10px;">
                                                    <!-- <h1 id="watermark" class="watermark">MINUS BALANCE AUTHORITY</h1> -->
                                                    To<br />
                                                    <?php echo str_replace(",", "<br/>", $ddoDesignation); ?><br /><br />
                                                    <span style="font-weight: bold;">Subject :</span> Regarding GPF Final Payment in respect of <?php echo $subscriberName; ?>, <?php echo $designationTitle . " " . $designation; ?> holder of GPF A/c No. <?php echo $accountNumber; ?><br><br>

                                                    Sir, <br>
                                                    <div style="padding-left: 20px;;">
                                                        <?php echo nl2br($matter); ?>
                                                    </div><br>
                                                    <div style="text-align: right;">Yours faithfully<br /><br />
                                                        <div style="font-weight:bold;"> <?php echo $signature; ?></div>
                                                    </div>
                                                    <table style="margin-top: 200px; margin-bottom:10px; width:100%;">
                                                        <tr>
                                                            <td style="text-align: left; width:70%;">No. <?php echo $sectionName; ?> / Minus Balance / <?php echo $pensionType; ?> / <?php echo $finYear; ?> / <?php echo $registrationNo; ?> / </td>
                                                            <td style="text-align: right; width:30%;">Date : <?php echo date("d / m / Y"); ?></td>
                                                        </tr>
                                                    </table>
                                                    <table style="width:100%;">
                                                        <tr>
                                                            <td style="width:70%;">
                                                                <div style="font-weight: bold;">
                                                                    Copy for information to:-
                                                                </div><br />
                                                                <?php echo nl2br($cfi);
                                                                ?>
                                                            </td>
                                                            <td style="font-weight: bold; vertical-align:bottom; width:30%; text-align: right;">
                                                                <?php echo $signature; ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    } else {
                    ?>
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
                                                        <div class=" input-group input-group-default">
                                                            <input required type="text" placeholder="GPF Account number" name="account_no" readonly class="form-control" value="<?php echo $fgpfSeries; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="pension_type">Pension type<b class="text-danger"> *</b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Pension type" name="pension_type" readonly class="form-control" value="<?php echo $fpensionType; ?>">
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
                                                                $desgTitles = ["Retd.", "Ex."];
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
                                                        <label for="spouse_name">Spouse name <b class="text-danger"> <?php echo $spouseNameErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input type="text" placeholder="Spouse name" tabindex="5" name="spouse_name" id="spouse_name" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                            echo $spouseName;
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $fspouseName == "" ? "NA" : $fspouseName;
                                                                                                                                                                                        } ?>">

                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="personal_address">Personal address <b class="text-danger"> * <?php echo $personalAddressErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Personal address" name="personal_address" tabindex="11" id="personal_address" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                                    echo $personalAddress;
                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                    echo $fpersonalAddress;
                                                                                                                                                                                                                } ?>">

                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="matter">Matter <b class="text-danger"> * <?php echo $matterErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <textarea required name="matter" id="matter" cols="10" rows="30" class="form-control" style="height: 150px;">
                                                        <?php if ($error === 1) {
                                                            echo $matter;
                                                        } else {
                                                            echo trim($fmatter);
                                                        } ?>
                                                        </textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="cfi">Copy for information <b class="text-danger"> * <?php echo $cfiErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <textarea required name="cfi" id="cfi" cols="10" rows="30" class="form-control" style="height: 150px;">
                                                        <?php if ($error === 1) {
                                                            echo $cfi;
                                                        } else {
                                                            echo trim($fcfi);
                                                        } ?>
                                                        </textarea>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary" name="generate_report" id="generate_report" tabindex="15">Generate report</button>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="regd_no">Registration number<b class="text-danger"> *</b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Registration number" name="reg_no" readonly class="form-control" value="<?php echo $registrationNo; ?>">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="financial_year">Financial year<b class="text-danger"> *</b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Financial year" name="finyear" readonly class="form-control" value="<?php echo $ffinYear; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="subscriber_name">Subscriber name <b class="text-danger"> * <?php echo $subscriberNameErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Subscriber name" tabindex="2" name="subscriber_name" id="subscriber_name" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                                echo $subscriberName;
                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                echo $fsubscriberName;
                                                                                                                                                                                                            } ?>">
                                                        </div>
                                                    </div>


                                                    <div class="form-group">
                                                        <label for="designation">Designation <b class="text-danger">* <?php echo $designationErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Designation" tabindex="4" name="designation" id="designation" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                    echo $designation;
                                                                                                                                                                                                } else {
                                                                                                                                                                                                    echo $fdesignation;
                                                                                                                                                                                                } ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="spouse_relation">Relation <b class="text-danger"> <?php echo $spouseRelationErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input type="text" placeholder="Relation" tabindex="6" name="spouse_relation" id="spouse_relation" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                echo $spouseRelation;
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo $fspouseRelation == "" ? "NA" : $fspouseRelation;
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
                                                        <label for="signature">Signature <b class="text-danger"> * <?php echo $sectionErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <input required type="text" placeholder="Signature" name="signature" tabindex="11" id="signature" class="form-control" value="<?php if ($error === 1) {
                                                                                                                                                                                                echo $section;
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo "Accounts Officer";
                                                                                                                                                                                            } ?>">

                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="head_of_office">Head of office <b class="text-danger"> <?php echo $matterErr; ?></b></label>
                                                        <div class="input-group input-group-default">
                                                            <textarea required name="head_of_office" id="head_of_office" cols="10" rows="30" class="form-control" style="height: 150px;">
                                                        <?php if ($error === 1) {
                                                            echo $headOfOffice;
                                                        } else {
                                                            echo trim($fheadOfOffice);
                                                        } ?>
                                                        </textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>


                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
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
}
?>
<script type="text/javascript">
    const download = () => {
        let registrationNo = "<?php echo $registrationNo; ?>";
        let authority = $("#printArea").html();

        let authorityData = {
            RegistrationNo: registrationNo,
            Authority: authority,
            AuthorityType: "minus_balance"
        }
        jQuery.ajax({
            type: 'POST',
            url: 'ajax/other_reports.php',
            data: JSON.stringify(authorityData),
            success: function(returnValue) {

                const {
                    StatusCode,
                    Message,
                    Link
                } = returnValue;

                if (StatusCode === 200) {
                    window.open(Link, '_blank');
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
</script>