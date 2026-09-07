   <?php

    $pageName = "Inward List";
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
                           <a href="./add_inward.php">
                               <button class="btn btn-primary" style="margin-bottom: 10px;">
                                   Fresh entry / Fresh LTA entry
                               </button>
                           </a>
                           <a href="./add_lta_inward.php">
                               <button class="btn btn-danger" style="margin-bottom: 10px;">
                                   Entry LTA (corresponding)
                               </button>
                           </a>
                           <a href="./online_application_list.php">
                               <button class="btn btn-info" style="margin-bottom: 10px;">
                                   Online application
                               </button>
                           </a>
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
                                               <th>REGISTERED DATE</th>
                                               <th>OPTION</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $inwardQuery = "SELECT a.SL_NO, a.REGD_NO, 'T/'||e.SERIES_DESCR||'/'||a.ACCOUNT_NO ACCOUNT_NO, b.SUBSCRIBER_NAME, b.LTA_REGISTERED_DATE, 
                                                            d.PENSION_LONG_DESCR, a.CASE_TYPE, d.PENSION_SHORT_DESCR, b.CASE_STATUS, b.REGD_DATE FROM GPF_INWARD a INNER JOIN GPF_CASE_STATUS b  
                                                            ON a.REGD_NO=b.REGD_NO INNER JOIN MAS_STATUS c ON b.CASE_STATUS=c.STATUS_ID INNER JOIN 
                                                            MAS_PENSION_TYPE d ON b.PENSION_TYPE=d.PENSION_ID INNER JOIN VLCS.MM_GPF_SERIES e
                                                            ON a.SERIES_ID=e.SERIES_ID WHERE b.CASE_STATUS NOT IN (7,8,10,11,16,17,18,19) ORDER BY a.CREATE_DATE DESC";
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
                                                    case "R":
                                                        $caseType = "Revised Final Payment";
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
                                                   <td><?php echo $inwardList['REGD_DATE'] == "" ? date("jS F, Y", strtotime($inwardList['LTA_REGISTERED_DATE'])) : date("jS F, Y", strtotime($inwardList['REGD_DATE'])); ?></td>
                                                   <td>
                                                       <a href="case_delay_remarks.php?regd_no=<?php echo $inwardList['REGD_NO']; ?>">
                                                           <button class="btn btn-success">DELAY REMARKS</button>
                                                       </a><br />
                                                       <?php
                                                        if (($inwardList['CASE_STATUS'] == 1) || ($inwardList['CASE_STATUS'] == 12)) {
                                                        ?>
                                                           <a href="edit_inward.php?inward_no=<?php echo $inwardList['SL_NO']; ?>">
                                                               <button class="btn btn-warning">EDIT</button>
                                                           </a>
                                                       <?php
                                                        } else {
                                                        ?>
                                                           <span class="badg badge-info">Cannot edit as application has been done</span>
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