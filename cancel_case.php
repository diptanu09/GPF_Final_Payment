   <?php
    $mainPage = "Admin";
    $pageName = "Cancel cases";
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
                                        $ltaCheckSQL = "SELECT * FROM GPF_INWARD WHERE REGD_NO='$registrationNo' 
                                                        AND CASE_TYPE IN ('L', 'X')";
                                        $ltaExists = sqlCountData($connection, $ltaCheckSQL);

                                        if (isset($_REQUEST['case_cancel'])) {
                                            $remarksID = date("YmdHis");
                                            $remarks = trim($_REQUEST['remarks']);

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
                                                $remarkCUDSQL = "INSERT INTO GPF_CASES_REMARKS VALUES('$remarksID', '$registrationNo',
                                                                'CA','$remarks','$loginUser',SYSDATE)";

                                                $statusUpdtSQL = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='11', CANCELED_DATE=SYSDATE
                                                                  WHERE REGD_NO='$registrationNo'";

                                                $statusUpdtBool = sqlCUDData($connection, $statusUpdtSQL);
                                                $remarkCUDBool = sqlCUDData($connection, $remarkCUDSQL);

                                                if ($statusUpdtBool && $remarkCUDBool) {
                                                    $remarksSuccess = "Successfully cancelled";
                                                } else {
                                                    $remarksErr = "Data not saved. Try again later";
                                                }
                                            } else {
                                                $errors = 1;
                                            }
                                        }
                                    ?>
                                       <b>Registration number : </b> <?php echo $registrationNo; ?>
                                       <span class="text-success"><b><?php echo $remarksSuccess; ?></b></span>
                                       <form action="" method="post">
                                           <div class="row">
                                               <div class="col-lg-6">
                                                   <div class="form-group">
                                                       <label for="remarks"><b>Remarks :
                                                               <span class="text-danger">* <?php echo $remarksErr; ?></span></b></label>
                                                       <input type="text" name="remarks" maxlength="255" class="form-control" value="<?php if ($errors == 1) {
                                                                                                                                            echo $remarks;
                                                                                                                                        } else {
                                                                                                                                            echo "";
                                                                                                                                        } ?>" required />
                                                   </div>
                                                   <div class="form-group">
                                                       <button type="submit" class="btn btn-danger" id="case_cancel" name="case_cancel">CANCEL</button>
                                                   </div>
                                               </div>
                                           </div>
                                       </form>
                                   <?php
                                    } else {
                                    ?>
                                       <table class="table table-bordered table-hover" id="cancel_cases">
                                           <thead>
                                               <tr>
                                                   <th>REGISTRATION NUMBER</th>
                                                   <th>SUBSCRIBER NAME</th>
                                                   <th>FP REGISTERED DATE</th>
                                                   <th>LTA REGISTERED DATE</th>
                                                   <th>OPTION</th>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php
                                                $usersQuery = "SELECT SUBSCRIBER_NAME, REGD_NO, REGD_DATE, LTA_REGISTERED_DATE 
                                                            FROM GPF_CASE_STATUS WHERE CASE_STATUS IN (1, 9, 12)";
                                                $fetchUsers = sqlFetchData($connection, $usersQuery);
                                                foreach ($fetchUsers as $usersList) {
                                                ?>
                                                   <tr>
                                                       <td><?php echo $usersList['REGD_NO']; ?></td>
                                                       <td><?php echo $usersList['SUBSCRIBER_NAME']; ?></td>
                                                       <td><?php echo $usersList['REGD_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['REGD_DATE'])); ?></td>
                                                       <td><?php echo $usersList['LTA_REGISTERED_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['LTA_REGISTERED_DATE'])); ?></td>
                                                       <td>
                                                           <a href="cancel_case.php?regd_no=<?php echo $usersList['REGD_NO']; ?>">
                                                               <button class="btn btn-danger">CANCEL</button>
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
       function statusChange(statusValue, userName) {
           jQuery.ajax({
               type: 'GET',
               url: 'ajax/user_status_change.php',
               data: 'status_value=' + statusValue + '&&user_name=' + userName,
               success: function(X) {
                   if (X === "Success") {
                       window.location.href = "cancel_cases.php";
                   } else {
                       alert("Something wrong");
                   }
               },
               error: function(a, b, c) {
                   console.log(a + " " + b + " " + c)
               }
           });
       }
       $(document).ready(function() {
           $('#cancel_cases').DataTable();
       });
   </script>