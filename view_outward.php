   <?php

    $pageName = "Outward";
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
                           <a href="./add_outward.php">
                               <button class="btn btn-primary" style="margin-bottom: 10px;">
                                   Entry case
                               </button>
                           </a>
                           <div class="card">
                               <div class="card-body">
                                   <table class="table table-bordered table-hover" id="view_outward">
                                       <thead>
                                           <tr>
                                               <th>OUTWARD NO</th>
                                               <th>OUTWARD TYPE</th>
                                               <th>REGISTRATION NO</th>
                                               <th>SUBJECT</th>
                                               <th>LETTER NO</th>
                                               <th>COPY TO</th>
                                               <th>OUTWARD DATE</th>
                                               <th>OPTION</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $outwardQuery = "SELECT a.SL_NO, b.TYPE_DESCR, a.REGD_NO, a.SUBJECT, a.LETTER_NO, a.COPY_TO,
                                                             a.CREATE_DATE FROM GPF_OUTWARD a INNER JOIN MAS_OUTWARD_TYPE b 
                                                             ON a.OUTWARD_TYPE=b.TYPE_SHORT_NAME";
                                            $fetchoutward = sqlFetchData($connection, $outwardQuery);
                                            foreach ($fetchoutward as $outwardList) {
                                            ?>
                                               <tr>
                                                   <td><?php echo $outwardList['SL_NO']; ?></td>
                                                   <td><?php echo $outwardList['TYPE_DESCR']; ?></td>
                                                   <td><?php echo $outwardList['REGD_NO']; ?></td>
                                                   <td><?php echo $outwardList['SUBJECT']; ?></td>
                                                   <td><?php echo $outwardList['LETTER_NO']; ?></td>
                                                   <td> <?php echo $outwardList['COPY_TO']; ?></td>
                                                   <td><?php echo $outwardList['CREATE_DATE'] == "" ? "" : date("d-m-Y", strtotime($outwardList['CREATE_DATE'])); ?></td>
                                                   <td>
                                                       <a href="edit_outward.php?outward_no=<?php echo $outwardList['SL_NO']; ?>">
                                                           <button class="btn btn-warning">EDIT</button>
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
           $('#view_outward').DataTable();
       });
   </script>