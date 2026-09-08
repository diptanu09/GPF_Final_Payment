   <?php

    $pageName = "Application share holders";
    require_once "./top.php";
    if (isset($_REQUEST['regd_no'])) {
        $registrationNo = trim($_REQUEST['regd_no']);
        $getNameQuery = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO='$registrationNo'";
        $fetchName = sqlFetchData($connection, $getNameQuery);
        foreach ($fetchName as $nameList) {
            $subscriberName = $nameList['SUBSCRIBER_NAME'];
        }
    ?>

       <div class="content-wrap">
           <div class="main">
               <div class="container-fluid">
                   <?php
                    require_once "./includes/breadcumb.php";                    ?>

                   <section id="main-content">
                       <div class="row">
                           <div class="col-lg-12">
                               <a href="view_application.php">
                                   <button class="btn btn-info">BACK TO APPLICATION</button>
                               </a>
                               <a href="application_share_holders.php?regd_no=<?php echo $registrationNo; ?>">
                                   <button class="btn btn-primary">ENTRY</button>
                               </a>
                               <div class="card">
                                   <div class="card-body">
                                       <b>
                                           <center><?php
                                                    echo $subscriberName . " (Registration no.: " . $registrationNo . ")";
                                                    ?></center>
                                       </b>
                                       <table class="table table-bordered table-hover" id="view_shareHolder">
                                           <thead>
                                               <tr>
                                                   <th>FULL NAME</th>
                                                   <th>RELATION</th>
                                                   <th>ADDRESS</th>
                                                   <th>OPTION</th>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php
                                                $shareHolderQuery = "SELECT * FROM GPF_CASE_STATUS a INNER JOIN GPF_APPLICATION b ON a.REGD_NO=b.REGD_NO 
                                                                  INNER JOIN GPF_ACCOUNT_SHARE_HOLDERS c ON a.REGD_NO=c.REGD_NO WHERE c.REGD_NO='$registrationNo'";
                                                $fetchshareHolder = sqlFetchData($connection, $shareHolderQuery);
                                                foreach ($fetchshareHolder as $shareHolderList) {

                                                ?>
                                                   <tr>
                                                       <td><?php echo $shareHolderList['SHARE_HOLDER_NAME']; ?></td>
                                                       <td><?php echo $shareHolderList['SHARE_HOLDER_RELATION']; ?></td>
                                                       <td><?php echo $shareHolderList['SHARE_HOLDER_ADDRESS']; ?></td>
                                                       <td>
                                                           <a href="edit_application_share_holders.php?sl_no=<?php echo $shareHolderList['SL_NO']; ?>">
                                                               <button class="btn btn-warning"> EDIT</button>
                                                           </a>
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
               $('#view_shareHolder').DataTable();
           });
       </script>
   <?php
    }
    ?>