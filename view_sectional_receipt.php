   <?php

    $pageName = "Sectional receipt";
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
                                   <table class="table table-bordered table-hover" id="view_inward">
                                       <thead>
                                           <tr>
                                               <th>INWARD NO</th>
                                               <th>REGISTRATION NO</th>
                                               <th>ACCOUNT NO</th>
                                               <th>FULL NAME</th>
                                               <th>PENSION TYPE</th>
                                               <th>CASE TYPE</th>
                                               <th>MARK TO</th>
                                               <th>REGISTERED DATE</th>
                                               <th>OPTION</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $inwardQuery = "SELECT T.SL_NO, T.REGD_NO, T.ACCOUNT_NO, T.SUBSCRIBER_NAME, T.LTA_REGISTERED_DATE, S.FULL_NAME,
                                            T.PENSION_LONG_DESCR, T.CASE_TYPE, T.PENSION_SHORT_DESCR, T.CASE_STATUS, T.REGD_DATE FROM
                                            (SELECT A.SL_NO, A.REGD_NO, 'T/'||E.SERIES_DESCR||'/'||A.ACCOUNT_NO ACCOUNT_NO, B.SUBSCRIBER_NAME, B.LTA_REGISTERED_DATE, 
                                            D.PENSION_LONG_DESCR, A.CASE_TYPE, D.PENSION_SHORT_DESCR, B.CASE_STATUS, A.MARK_TO, B.REGD_DATE FROM GPF_INWARD A INNER JOIN GPF_CASE_STATUS B  
                                            ON A.REGD_NO=B.REGD_NO INNER JOIN MAS_STATUS C ON B.CASE_STATUS=C.STATUS_ID INNER JOIN 
                                            MAS_PENSION_TYPE D ON B.PENSION_TYPE=D.PENSION_ID INNER JOIN VLCS.MM_GPF_SERIES E
                                            ON A.SERIES_ID=E.SERIES_ID WHERE B.CASE_STATUS NOT IN (7,8,10,11,16,17,18,19) GROUP BY A.REGD_NO, E.SERIES_DESCR, 
                                            A.ACCOUNT_NO, B.SUBSCRIBER_NAME, D.PENSION_LONG_DESCR, D.PENSION_SHORT_DESCR, A.MARK_TO, B.CASE_STATUS, 
                                            B.LTA_REGISTERED_DATE, B.REGD_DATE, A.CASE_TYPE, A.SL_NO)T INNER JOIN USER_ACCOUNTS S ON T.MARK_TO=S.USERNAME ";
                                            $fetchinward = sqlFetchData($connection, $inwardQuery);
                                            foreach ($fetchinward as $inwardList) {
                                                switch ($inwardList['CASE_TYPE']) {
                                                    case "F":
                                                        $caseType = "Fresh case";
                                                        break;
                                                    case "C":
                                                        $caseType = "Fresh-corresponding case";
                                                        break;
                                                    case "L":
                                                        $caseType = "Fresh LTA case";
                                                        break;
                                                    case "X":
                                                        $caseType = "Coresponding LTA case";
                                                        break;
                                                    default:
                                                        break;
                                                }
                                            ?>
                                               <tr>
                                                   <td><?php echo $inwardList['SL_NO']; ?></td>
                                                   <td><?php echo $inwardList['REGD_NO']; ?></td>
                                                   <td><?php echo $inwardList['ACCOUNT_NO']; ?></td>
                                                   <td><?php echo $inwardList['SUBSCRIBER_NAME']; ?></td>
                                                   <td><?php echo $inwardList['PENSION_LONG_DESCR']; ?> (<?php echo $inwardList['PENSION_SHORT_DESCR']; ?>)</td>
                                                   <td><?php echo $caseType; ?></td>
                                                   <td><?php echo $inwardList['FULL_NAME']; ?></td>
                                                   <td><?php echo $inwardList['REGD_DATE'] == "" ? date("jS F, Y", strtotime($inwardList['LTA_REGISTERED_DATE'])) : date("jS F, Y", strtotime($inwardList['REGD_DATE'])); ?></td>
                                                   <td>
                                                       <?php
                                                        if (($inwardList['CASE_STATUS'] == 1) || ($inwardList['CASE_STATUS'] == 12)) {
                                                        ?>
                                                           <a href="section_receipt.php?inward_no=<?php echo $inwardList['SL_NO']; ?>">
                                                               <button class="btn btn-warning">CHANGE MARK TO</button>
                                                           </a>
                                                       <?php
                                                        } else {
                                                            echo "Application has been done, you cannot mark";
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
           $('#view_inward').DataTable();
       });
   </script>