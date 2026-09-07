   <?php
    $pageName = "Delay case remarks";
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
                                   <?php
                                    if (isset($_REQUEST['regd_no'])) {
                                        $registrationNo = trim($_REQUEST['regd_no']);
                                        if (isset($_POST['case_delay'])) {
                                            $remarksID = date("YmdHis");
                                            $remarks = trim($_POST['remarks']);
                                            if (empty($remarks)) {
                                                $remarksErr = "Required";
                                            } else {
                                                if (!textPatternValidation($remarks, "^'")) {
                                                    $remarksErr = "Apostophe are not allowed";
                                                } else {
                                                    if (strlen($remarks) > 255) {
                                                        $remarksErr = "Maximum length : 255";
                                                    }
                                                }
                                            }
                                            if ($remarksErr == "") {
                                                $errors = 0;
                                                $remarksExistsSQL = "SELECT * FROM GPF_CASES_REMARKS 
                                                                     WHERE REGD_NO='$registrationNo' AND REMARKS_TYPE='DL'";
                                                if (sqlCountData($connection, $remarksExistsSQL) > 0) {
                                                    $remarkCUDSQL = "UPDATE GPF_CASES_REMARKS SET REMARKS='$remarks', CREATE_MODIFY_USER='$loginUser',  
                                                                     CREATE_MODIFY_DATE=SYSDATE WHERE REGD_NO='$registrationNo' AND REMARKS_TYPE='DL'";
                                                } else {
                                                    $remarkCUDSQL = "INSERT INTO GPF_CASES_REMARKS 
                                                                 VALUES('$remarksID', '$registrationNo','DL','$remarks','$loginUser',SYSDATE)";
                                                }

                                                if (sqlCUDData($connection, $remarkCUDSQL)) {
                                                    $remarksSuccess = "Successfully saved";
                                                } else {
                                                    $remarksErr = "Data not saved. Try again later";
                                                }
                                            } else {
                                                $errors = 1;
                                            }
                                        }
                                    ?>
                                       <div class="row">
                                           <div class="col-lg-6">
                                               Registration number : <?php echo $registrationNo; ?> <br><br>
                                               <form action="" method="post">
                                                   <div class="form-group">
                                                       <label for="remarks"><b>Remarks :
                                                               <span class="text-danger">* <?php echo $remarksErr; ?></span>
                                                               <span class="text-success"><?php echo $remarksSuccess; ?></span>
                                                           </b>
                                                       </label>
                                                       <input type="text" name="remarks" maxlength="255" class="form-control" value="<?php if ($errors == 1) {
                                                                                                                                            echo $remarks;
                                                                                                                                        } else {
                                                                                                                                            echo "";
                                                                                                                                        } ?>" required />
                                                   </div>
                                                   <div>
                                                       <button type="submit" name="case_delay" class="btn btn-primary">
                                                           SAVE</button>
                                                   </div>
                                               </form>
                                           </div>
                                       </div>
                                       <div class="row">
                                           <div class="col-lg-12">
                                               <table class="table table-bordered table-hover">
                                                   <thead>
                                                       <tr>
                                                           <th>REMARKS</th>
                                                           <th>CREATED BY</th>
                                                           <th>CREATED ON</th>
                                                       </tr>
                                                   </thead>
                                                   <tbody>
                                                       <?php
                                                        $delayRemarksSQL = "SELECT a.REMARKS, b.FULL_NAME, a.CREATE_MODIFY_DATE FROM GPF_CASES_REMARKS a 
                                                                            INNER JOIN USER_ACCOUNTS b ON a.CREATE_MODIFY_USER=b.USERNAME WHERE 
                                                                            a.REGD_NO='$registrationNo' AND a.REMARKS_TYPE='DL'";
                                                        $fetchDelay = sqlFetchData($connection, $delayRemarksSQL);
                                                        foreach ($fetchDelay as $delay) {
                                                        ?>
                                                           <tr>
                                                               <td><?php echo $delay['REMARKS'] ?></td>
                                                               <td><?php echo $delay['FULL_NAME'] ?></td>
                                                               <td><?php echo $delay['CREATE_MODIFY_DATE'] ?></td>
                                                           </tr>
                                                       <?php
                                                        }
                                                        ?>
                                                   </tbody>
                                               </table>
                                           </div>
                                       </div>
                                   <?php
                                    }
                                    ?>
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
           $('#unapprove_cases').DataTable();
       });
   </script>