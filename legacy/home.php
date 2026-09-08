   <?php
    $pageName = "Dashboard";
    require_once "./top.php";
    ?>
   <?php
    $rmbSQL = "SELECT SUM(NVL(AMOUNT_RECOVERED,0)) AMOUNT FROM GPF_MINUS_BALANCE_REMARKS";
    foreach (sqlFetchData($connection, $rmbSQL) as $rminusBalanceList) {
        $rminusBalanceAmount = $rminusBalanceList['AMOUNT'];
    }
    ?>
   <?php
    $mbSQL = "SELECT SUM(NVL(T.FINAL_PAYMENT_AMOUNT,0))AMOUNT FROM
             (SELECT b.FINAL_PAYMENT_AMOUNT FROM GPF_CASE_STATUS a INNER JOIN GPF_AMOUNT_INFO b ON 
             a.REGD_NO=b.REGD_NO WHERE a.APPROVED_DATE IS NOT NULL AND b.FINAL_PAYMENT_AMOUNT<0
             UNION ALL SELECT b.FINAL_PAYMENT_AMOUNT FROM GPF_CASE_STATUS a INNER JOIN GPF_AMOUNT_INFO b ON 
             a.REGD_NO=b.REGD_NO WHERE a.LTA_APPROVED_DATE IS NOT NULL AND b.FINAL_PAYMENT_AMOUNT<0)T";
    foreach (sqlFetchData($connection, $mbSQL) as $minusBalanceList) {
        $minusBalanceAmount = $minusBalanceList['AMOUNT'];
    }
    $minusBalanceAmount = $rminusBalanceAmount + $minusBalanceAmount;
    ?>
   <div class="content-wrap">
       <div class="main">
           <div class="container-fluid">
               <div class="row">
                   <div class="col-lg-8 p-r-0 title-margin-right">
                       <div class="page-header">
                           <div class="page-title">
                               <h1><span id="date"></span>, <span id="time"></span></h1>
                           </div>
                       </div>
                   </div>
                   <!-- /# column -->
                   <div class="col-lg-4 p-l-0 title-margin-left">
                       <div class="page-header">
                           <div class="page-title">
                               <ol class="breadcrumb">
                                   <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                                   <li class="breadcrumb-item active">Home</li>
                               </ol>
                           </div>
                       </div>
                   </div>
                   <!-- /# column -->
               </div>
               <!-- /# row -->
               <section id="main-content">
                   <center>
                       <div class="alert alert-info" style="font-weight: bolder;">
                           WITH EFFECT FROM 1<sup>st</sup> April, 2023
                       </div>
                   </center>

                   <div class="row">
                       <div class="col-lg-4">
                           <div class="card">
                               <div class="stat-widget-one">
                                   <div class="stat-icon dib"><i class="ti-arrow-right color-info border-info"></i>
                                   </div>
                                   <div class="stat-content dib">
                                       <div class="stat-text">Registered</div>
                                       <?php
                                        $regSQL = "SELECT S.PENSION_LONG_DESCR PENSION_DESC, NVL(T.FRESH_CASE,0)FRESH_CASE, NVL(T.LTA_CASE,0)LTA_CASE FROM 
                                                (SELECT PENSION_TYPE, COUNT(CASE WHEN REGD_DATE IS NOT NULL AND LTA_REGISTERED_DATE IS NULL THEN 1 END) FRESH_CASE, 
                                                COUNT(CASE WHEN REGD_DATE IS NULL AND LTA_REGISTERED_DATE IS NOT NULL THEN 1 END) LTA_CASE FROM GPF_CASE_STATUS
                                                WHERE CASE_STATUS!=11 AND TO_DATE(REGD_DATE)>'31-MAR-2023' GROUP BY PENSION_TYPE)T RIGHT JOIN MAS_PENSION_TYPE S ON T.PENSION_TYPE=S.PENSION_ID ORDER BY S.PENSION_ID";
                                        $fetchTotalReg = sqlFetchData($connection, $regSQL);
                                        $totalReg = 0;
                                        foreach ($fetchTotalReg as $fetchTotalRegs) {
                                            $totalReg += $fetchTotalRegs['FRESH_CASE'] + $fetchTotalRegs['LTA_CASE'];
                                        }
                                        ?>
                                       <div class="stat-digit"><?php echo $totalReg; ?></div>
                                   </div>
                                   <table class="table table-striped table-bordered mt-10">
                                       <thead>
                                           <tr>
                                               <th>Case type</th>
                                               <th>Only FP Cases</th>
                                               <th>Only LTA Cases</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $fetchRegList = sqlFetchData($connection, $regSQL);
                                            foreach ($fetchRegList as $fetchRegLists) {
                                            ?>
                                               <tr>
                                                   <td><?php echo $fetchRegLists['PENSION_DESC']; ?></td>
                                                   <td style="text-align: right;"><?php echo $fetchRegLists['FRESH_CASE']; ?></td>
                                                   <td style="text-align: right;"><?php echo $fetchRegLists['LTA_CASE']; ?></td>
                                               </tr>
                                           <?php
                                            }
                                            ?>
                                       </tbody>
                                   </table>
                               </div>
                           </div>
                       </div>
                       <div class="col-lg-4">
                           <div class="card">
                               <div class="stat-widget-one">
                                   <div class="stat-icon dib"><i class="ti-check color-success border-success"></i>
                                   </div>
                                   <div class="stat-content dib">
                                       <div class="stat-text">Settled </div>
                                       <?php
                                        $closedSQL = "SELECT S.PENSION_LONG_DESCR PENSION_DESC, NVL(T.FRESH_CASE,0)FRESH_CASE, NVL(T.LTA_CASE,0)LTA_CASE FROM 
                                        (SELECT PENSION_TYPE, COUNT(CASE WHEN REGD_DATE IS NOT NULL AND LTA_REGISTERED_DATE IS NULL AND CASE_STATUS IN (7,8,18,19) THEN 1 END) FRESH_CASE, 
                                        COUNT(CASE WHEN REGD_DATE IS NULL AND LTA_REGISTERED_DATE IS NOT NULL AND CASE_STATUS IN (16,17,18,19) THEN 1 END) LTA_CASE FROM GPF_CASE_STATUS
                                        WHERE CASE_STATUS!=11 AND TO_DATE(REGD_DATE)>'31-MAR-2023' GROUP BY PENSION_TYPE)T RIGHT JOIN MAS_PENSION_TYPE S ON T.PENSION_TYPE=S.PENSION_ID ORDER BY S.PENSION_ID";
                                        $fetchTotalSettle = sqlFetchData($connection, $closedSQL);
                                        $totalSettled = 0;
                                        foreach ($fetchTotalSettle as $fetchTotalSettled) {
                                            $totalSettled += $fetchTotalSettled['FRESH_CASE'] + $fetchTotalSettled['LTA_CASE'];
                                        }
                                        ?>
                                       <div class="stat-digit"><?php echo $totalSettled; ?></div>
                                   </div>
                                   <table class="table table-striped table-bordered mt-10">
                                       <thead>
                                           <tr>
                                               <th>Case type</th>
                                               <th>Only FP Cases</th>
                                               <th>Only LTA Cases</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $fetchSettledList = sqlFetchData($connection, $closedSQL);
                                            foreach ($fetchSettledList as $fetchSettledLists) {
                                            ?>
                                               <tr>
                                                   <td><?php echo $fetchSettledLists['PENSION_DESC']; ?></td>
                                                   <td style="text-align: right;"><?php echo $fetchSettledLists['FRESH_CASE']; ?></td>
                                                   <td style="text-align: right;"><?php echo $fetchSettledLists['LTA_CASE']; ?></td>
                                               </tr>
                                           <?php
                                            }
                                            ?>
                                       </tbody>
                                   </table>
                               </div>
                           </div>
                       </div>
                       <div class="col-lg-4">
                           <div class="card">
                               <div class="stat-widget-one">
                                   <div class="stat-icon dib"><i class="ti-alarm-clock color-danger border-danger"></i>
                                   </div>
                                   <div class="stat-content dib">
                                       <div class="stat-text">Pending </div>
                                       <?php
                                        $pendingSQL = "SELECT S.PENSION_LONG_DESCR PENSION_DESC, NVL(T.FRESH_CASE,0)FRESH_CASE, NVL(T.LTA_CASE,0)LTA_CASE FROM 
                                        (SELECT PENSION_TYPE, COUNT(CASE WHEN REGD_DATE IS NOT NULL AND LTA_REGISTERED_DATE IS NULL AND CASE_STATUS NOT IN (7,8,16,17,18,19) THEN 1 END) FRESH_CASE, 
                                        COUNT(CASE WHEN REGD_DATE IS  NULL AND LTA_REGISTERED_DATE IS NOT NULL AND CASE_STATUS NOT IN (7,8,16,17,18,19) THEN 1 END) LTA_CASE FROM GPF_CASE_STATUS
                                        WHERE CASE_STATUS!=11 AND TO_DATE(REGD_DATE)>'31-MAR-2023' GROUP BY PENSION_TYPE)T RIGHT JOIN MAS_PENSION_TYPE S ON T.PENSION_TYPE=S.PENSION_ID ORDER BY S.PENSION_ID";
                                        $fetchTotalPending = sqlFetchData($connection, $pendingSQL);
                                        $totalPending = 0;
                                        foreach ($fetchTotalPending as $fetchTotalPendings) {
                                            $totalPending += $fetchTotalPendings['FRESH_CASE'] + $fetchTotalPendings['LTA_CASE'] + $fetchTotalPendings['LTA_CORR_CASE'];
                                        }
                                        ?>
                                       <div class="stat-digit"><?php echo $totalPending; ?></div>
                                   </div>
                                   <table class="table table-striped table-bordered mt-10">
                                       <thead>
                                           <tr>
                                               <th>Case type</th>
                                               <th>Only FP Cases</th>
                                               <th>Only LTA Cases</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $fetchPendingList = sqlFetchData($connection, $pendingSQL);
                                            foreach ($fetchPendingList as $fetchPendingLists) {
                                            ?>
                                               <tr>
                                                   <td><?php echo $fetchPendingLists['PENSION_DESC']; ?></td>
                                                   <td style="text-align: right;"><?php echo $fetchPendingLists['FRESH_CASE']; ?></td>
                                                   <td style="text-align: right;"><?php echo $fetchPendingLists['LTA_CASE']; ?></td>
                                               </tr>
                                           <?php
                                            }
                                            ?>
                                       </tbody>
                                   </table>
                               </div>
                           </div>
                       </div>
                   </div>
                   <div class="row">
                       <div class="col-lg-4">
                           <div class="card bg-success">
                               <h5 class="stat-text" style="color: #FFFFFF;">
                                   Final Payment Amount Signed
                               </h5>
                               <?php
                                $fpSQL = "SELECT SUM(NVL(b.FINAL_PAYMENT_AMOUNT,0)) AMOUNT FROM GPF_CASE_STATUS a INNER JOIN GPF_AMOUNT_INFO b ON 
                                          a.REGD_NO=b.REGD_NO WHERE (a.FP_SIGNED_DATE IS NOT NULL AND TO_DATE(REGD_DATE)>'31-MAR-2023') OR
                                          (a.LTA_SIGNED_DATE IS NOT NULL AND TO_DATE(LTA_SIGNED_DATE)>'31-MAR-2023')";
                                foreach (sqlFetchData($connection, $fpSQL) as $finalPaymentList) {
                                    $finalPaymentAmount = $finalPaymentList['AMOUNT'];
                                }
                                ?>
                               <div style="color: #FFFFFF; font-weight:bolder; font-size:35px;">
                                   &#8377; <?php echo round($finalPaymentAmount); ?></div>
                           </div>
                       </div>
                       <div class="col-lg-4">
                           <div class="card bg-info">
                               <h5 class="stat-text" style="color: #FFFFFF;">
                                   Minus Balance Amount Approved
                               </h5>
                               <div style="color: #FFFFFF; font-weight:bolder; font-size:35px;">
                                   &#8377; <?php echo $minusBalanceAmount; ?></div>
                           </div>
                       </div>
                       <div class="col-lg-4">
                           <div class="card bg-primary">
                               <h5 class="stat-text" style="color: #FFFFFF;">
                                   Uploaded in HRMS
                               </h5>
                               <?php
                                $hrmsSQL = "SELECT COUNT(*) UPLOAD_COUNT FROM GPF_CASE_STATUS WHERE
                                            (FP_UPLOAD_DATE IS NOT NULL AND TO_DATE(FP_UPLOAD_DATE)>'31-MAR-2023') OR
                                            (LTA_UPLOAD_DATE IS NOT NULL AND TO_DATE(LTA_UPLOAD_DATE)>'31-MAR-2023')";
                                foreach (sqlFetchData($connection, $hrmsSQL) as $hrmsList) {
                                    $hrmsCount = $hrmsList['UPLOAD_COUNT'];
                                }
                                ?>
                               <div style="color: #FFFFFF; font-weight:bolder; font-size:35px;">
                                   <?php echo $hrmsCount; ?></div>
                           </div>
                       </div>
                   </div>
                   <div class="row">
                       <div class="col-lg-4">
                           <div class="card bg-danger">
                               <div class="card-title pr">
                                   <h5 class="stat-text" style="color: #FFFFFF;">
                                       Pending cases
                                   </h5>
                               </div>
                               <div class="card-body">
                                   <div class="table-responsive">
                                       <table class="table student-data-table m-t-20">
                                           <thead>
                                               <tr>
                                                   <th style="color: #FFFFFF;">Particulars</th>
                                                   <th style="color: #FFFFFF;">No of cases</th>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php
                                                $pendingDaysSQL = "SELECT M.PEN_DAYS PENDING_TYPE, SUM(M.C) PENDING_RECORDS FROM
                                                (SELECT CASE WHEN T.PEN_DAYS BETWEEN 0 AND 14 THEN 'Less than 15 days' END PEN_DAYS, COUNT(*) C FROM (SELECT CASE WHEN REGD_DATE IS NOT NULL THEN TO_DATE(SYSDATE)-TO_DATE(REGD_DATE)
                                                WHEN REGD_DATE IS NULL THEN TO_DATE(SYSDATE)-TO_DATE(LTA_REGISTERED_DATE) END AS PEN_DAYS
                                                FROM GPF_CASE_STATUS WHERE CASE_STATUS NOT IN (7,8,10,11,18,19))T GROUP BY T.PEN_DAYS
                                                UNION ALL SELECT CASE WHEN T.PEN_DAYS BETWEEN 15 AND 30 THEN '15-30 days' END PEN_DAYS, COUNT(*) C FROM (SELECT CASE WHEN REGD_DATE IS NOT NULL THEN TO_DATE(SYSDATE)-TO_DATE(REGD_DATE)
                                                WHEN REGD_DATE IS NULL THEN TO_DATE(SYSDATE)-TO_DATE(LTA_REGISTERED_DATE) END AS PEN_DAYS
                                                FROM GPF_CASE_STATUS WHERE CASE_STATUS NOT IN (7,8,10,11,18,19))T GROUP BY T.PEN_DAYS
                                                UNION ALL SELECT CASE WHEN T.PEN_DAYS BETWEEN 31 AND 45 THEN '31-45 days' END PEN_DAYS, COUNT(*) C FROM (SELECT CASE WHEN REGD_DATE IS NOT NULL THEN TO_DATE(SYSDATE)-TO_DATE(REGD_DATE)
                                                WHEN REGD_DATE IS NULL THEN TO_DATE(SYSDATE)-TO_DATE(LTA_REGISTERED_DATE) END AS PEN_DAYS
                                                FROM GPF_CASE_STATUS WHERE CASE_STATUS NOT IN (7,8,10,11,18,19))T GROUP BY T.PEN_DAYS
                                                UNION ALL SELECT CASE WHEN T.PEN_DAYS BETWEEN 46 AND 60 THEN '46-60 days' END PEN_DAYS, COUNT(*) C FROM (SELECT CASE WHEN REGD_DATE IS NOT NULL THEN TO_DATE(SYSDATE)-TO_DATE(REGD_DATE)
                                                WHEN REGD_DATE IS NULL THEN TO_DATE(SYSDATE)-TO_DATE(LTA_REGISTERED_DATE) END AS PEN_DAYS
                                                FROM GPF_CASE_STATUS WHERE CASE_STATUS NOT IN (7,8,10,11,18,19))T GROUP BY T.PEN_DAYS
                                                UNION ALL SELECT CASE WHEN T.PEN_DAYS >60 THEN 'More than 60 days' END PEN_DAYS, COUNT(*) C FROM (SELECT CASE WHEN REGD_DATE IS NOT NULL THEN TO_DATE(SYSDATE)-TO_DATE(REGD_DATE)
                                                WHEN REGD_DATE IS NULL THEN TO_DATE(SYSDATE)-TO_DATE(LTA_REGISTERED_DATE) END AS PEN_DAYS
                                                FROM GPF_CASE_STATUS WHERE CASE_STATUS NOT IN (7,8,10,11,18,19))T GROUP BY T.PEN_DAYS)M WHERE M.PEN_DAYS IS NOT NULL
                                                GROUP BY M.PEN_DAYS";
                                                foreach (sqlFetchData($connection, $pendingDaysSQL) as $pendingList) {
                                                ?>
                                                   <tr>
                                                       <td style="color: #FFFFFF;"><?php echo $pendingList['PENDING_TYPE']; ?></td>
                                                       <td style="color: #FFFFFF;"><?php echo $pendingList['PENDING_RECORDS']; ?></td>
                                                   </tr>
                                               <?php
                                                }
                                                ?>
                                           </tbody>
                                       </table>
                                   </div>
                               </div>
                           </div>
                       </div>


                       <div class="col-lg-4">
                           <div class="card bg-warning">
                               <h5 class="stat-text" style="color: #000000;">
                                   Minus Balance Amount Retrieved
                               </h5>

                               <div style="color: #000000; font-weight:bolder; font-size:35px;">
                                   &#8377; <?php echo abs($rminusBalanceAmount); ?></div>
                           </div>
                       </div>
                       <!-- /# column -->
                   </div>
                   <!-- /# row -->
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
       let startTime = function() {
           let today = new Date();
           let h = today.getHours();
           let m = today.getMinutes();
           let s = today.getSeconds();
           h = check(h);
           m = check(m);
           s = check(s);
           let dd = today.getDate();
           let mm = today.getMonth() + 1;
           let yyyy = today.getFullYear();
           mm = check(mm);
           dd = check(dd);

           let suffix = "AM";
           if (h >= 12) {
               suffix = "PM";
               h = h - 12;
           }
           if (h == 0) {
               h = 12;
           }
           $('#time').text(h + ":" + m + ":" + s + " " + suffix);
           $('#date').text(dd + " / " + mm + " / " + yyyy);
           let t = setTimeout(function() {
               startTime()
           }, 500);
       }

       function check(i) {
           if (i < 10) {
               i = "0" + i;
           } // add zero in front of numbers < 10
           return i;
       }
       window.onload = startTime()
   </script>