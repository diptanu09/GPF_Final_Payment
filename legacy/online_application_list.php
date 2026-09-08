   <?php

    $pageName = "Online application";
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
                                   <table class="table table-bordered table-hover" id="view_onlineApp">
                                       <thead>
                                           <tr>
                                               <th>DDO</th>
                                               <th>GPF ACCOUNT NO</th>
                                               <th>SUBSCRIBER NAME</th>
                                               <th>TREASURY</th>
                                               <th>PENSION TYPE</th>
                                               <th>APPLY DATE</th>
                                               <th>OPTION</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $onlineAppQuery = "select ddo_code, series_code, gpf_account_no, subscriber_name, treasury_code, apply_date,
                                                               pension_type from gpf_final_payment.online_application where pension_type!='DISM'
                                                               group by ddo_code, pension_type, series_code, gpf_account_no, subscriber_name, treasury_code, apply_date
                                                               order by apply_date desc";
                                            $fetchonlineApp = sqlPGFetchData($conn_pgsql, $onlineAppQuery);
                                            foreach ($fetchonlineApp as $onlineAppList) {
                                                $ddoCode = $onlineAppList[0];
                                                $seriesName = $onlineAppList[1];
                                                $accountNo = $onlineAppList[2];
                                                $subscriberName = $onlineAppList[3];
                                                $treasury = $onlineAppList[4];
                                                $applyDate = $onlineAppList[5];
                                                $pensionType = $onlineAppList[6];
                                                $accountClosedSQL = "SELECT a.SERIES_ID, a.ACCOUNT_NO FROM VLCS.GP_ACCOUNTS a INNER JOIN VLCS.MM_GPF_SERIES b ON 
                                                                     a.SERIES_ID=b.SERIES_ID WHERE b.SERIES_DESCR='$seriesName' AND 
                                                                     a.ACCOUNT_NO='$accountNo' AND NVL(a.ACCOUNT_CLOSED_TAG,'N')='Y' ";
                                                $accountExists = sqlCountData($connection, $accountClosedSQL);
                                                if ($accountExists == 0) {
                                                    $ddoSQL = "SELECT * FROM VLCS.STATE_DDO WHERE DDO_CODE='$ddoCode'";
                                                    $fetchDDO = sqlFetchData($connection, $ddoSQL);
                                                    foreach ($fetchDDO as $ddoList) {
                                                        $ddoDesg = $ddoList['DDO_DESG'];
                                                    }
                                            ?>
                                                   <tr>
                                                       <td><?php echo  $ddoDesg . " (" . $ddoCode . ")"; ?></td>
                                                       <td>T / <?php echo  $seriesName; ?> / <?php echo $accountNo; ?></td>
                                                       <td><?php echo $subscriberName; ?></td>
                                                       <td><?php echo $treasury; ?></td>
                                                       <td><?php echo $pensionType; ?></td>
                                                       <td><?php echo $applyDate; ?></td>
                                                       <td>
                                                           <a href="online_application_details.php?series=<?php echo $seriesName; ?>&&account_no=<?php echo $accountNo; ?>">
                                                               <button class="btn btn-primary">VIEW</button>
                                                           </a>
                                                       </td>
                                                   </tr>
                                           <?php
                                                }
                                            }
                                            ?>
                                       </tbody>
                                   </table>
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
           $('#view_onlineApp').DataTable({
               order: [
                   [4, 'desc']
               ],
           });
       });
   </script>