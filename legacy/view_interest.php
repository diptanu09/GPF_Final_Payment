   <?php

    $pageName = "Interest";
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
                           <a href="./add_interest.php">
                               <button class="btn btn-primary" style="margin-bottom: 10px;">
                                   Add interest
                               </button>
                           </a>
                           <div class="card">
                               <div class="card-body">
                                   <table class="table table-bordered table-hover" id="view_interest">
                                       <thead>
                                           <tr>
                                               <th>FINANCIAL YEAR</th>
                                               <th>MONTH</th>
                                               <th>RATE OF INTEREST</th>
                                               <th>OPTION</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $interestQuery = "SELECT A.SL_NO, B.FIN_YEAR,A.YEAR_DESC, A.RATE_OF_INTEREST FROM MAS_INTEREST A
                                                              INNER JOIN VLCS.MM_FINANCIAL_YEAR B ON A.FIN_YEAR_CODE=B.FIN_YEAR_CODE
                                                              ORDER BY A.FIN_YEAR_CODE DESC, A.YEAR_DESC DESC";
                                            $fetchinterest = sqlFetchData($connection, $interestQuery);
                                            foreach ($fetchinterest as $interestList) {
                                            ?>
                                               <tr>
                                                   <td><?php echo $interestList['FIN_YEAR']; ?></td>
                                                   <td><?php echo date("F, Y", strtotime($interestList['YEAR_DESC'])); ?></td>
                                                   <td><?php echo $interestList['RATE_OF_INTEREST']; ?></td>
                                                   <td>
                                                       <a href="edit_interest.php?slno=<?php echo $interestList['SL_NO']; ?>">
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
           $('#view_interest').DataTable({});
       });
   </script>