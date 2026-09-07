   <?php
    $pageName = "Corrigendum / Revalidation";
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
                                   <form action="" method="post">
                                       <div class="form-inline">
                                           <label for="regd_no">REGISTRATION NO. :</label>
                                           <input type="text" required placeholder="REGISTRATION NO" name="regd_no" id="regd_no" class="form-control" />
                                           &nbsp;&nbsp;&nbsp;
                                           <button class="btn btn-primary" name="search_btn" id="search_btn">SEARCH</button>
                                       </div>
                                   </form><br><br>
                                   <?php
                                    if (isset($_REQUEST['search_btn'])) {
                                        $registrationNo = trim($_REQUEST['regd_no']);
                                    ?>
                                       <table class="table table-bordered table-hover" id="record_list">
                                           <thead>
                                               <tr>
                                                   <th>REGISTRATION NO</th>
                                                   <th>SUBSCRIBER NAME</th>
                                                   <th>OPTION</th>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php
                                                $searchSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO='$registrationNo'";
                                                $fetchList = sqlFetchData($connection, $searchSQL);
                                                foreach ($fetchList as $fetchLists) {
                                                ?>
                                                   <tr>
                                                       <td><?php echo $fetchLists['REGD_NO']; ?></td>
                                                       <td><?php echo $fetchLists['SUBSCRIBER_NAME']; ?></td>
                                                       <td>
                                                           <a href="corrigendum_report.php?regd_no=<?php echo $fetchLists['REGD_NO']; ?>">
                                                               <button class="btn btn-primary">CORRIGENDUM</button>
                                                           </a>
                                                           <a href="revalidation_report.php?regd_no=<?php echo $fetchLists['REGD_NO']; ?>">
                                                               <button class="btn btn-info">REVALIDATION</button>
                                                           </a>
                                                       </td>
                                                   </tr>
                                               <?php
                                                }
                                                ?>
                                           </tbody>
                                       </table>
                                   <?php
                                    }
                                    ?>
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