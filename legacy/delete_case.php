   <?php
    $mainPage = "Admin";
    $pageName = "Delete cases";
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

                                        if (isset($_REQUEST['case_delete'])) {
                                            $remarksID = date("YmdHis");
                                            $remarks = trim($_REQUEST['remarks']);
                                            $deleteFrom = trim($_REQUEST['delete_from']);

                                            if (empty($deleteFrom)) {
                                                $deleteFromErr = "Required";
                                            }
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
                                            if (($remarksErr == "") && ($deleteFromErr == "")) {
                                                $errors = 0;
                                                if ($deleteFrom == 1) {
                                                    if ($ltaExists > 0) {
                                                        $status = 12;
                                                    } else {
                                                        $status = 1;
                                                    }
                                                    $delAppQuery = "DELETE FROM GPF_APPLICATION WHERE REGD_NO='$registrationNo'";
                                                    $delPreCalQuery = "DELETE FROM GPF_AMOUNT_INFO WHERE REGD_NO='$registrationNo'";
                                                    $delAdjPreCalQuery = "DELETE FROM GPF_ADJ_PRE_CAL WHERE REGD_NO='$registrationNo'";
                                                    $delAppBool = sqlCUDData($connection, $delAppQuery);
                                                    $delPreCalBool = sqlCUDData($connection, $delPreCalQuery);
                                                    $delAdjPreCalBool = sqlCUDData($connection, $delAdjPreCalQuery);
                                                } else {
                                                    if ($ltaExists > 0) {
                                                        $status = 13;
                                                    } else {
                                                        $status = 2;
                                                    }
                                                    $delPreCalQuery = "DELETE FROM GPF_AMOUNT_INFO WHERE REGD_NO='$registrationNo'";
                                                    $delAdjPreCalQuery = "DELETE FROM GPF_ADJ_PRE_CAL WHERE REGD_NO='$registrationNo'";
                                                    $delPreCalBool = sqlCUDData($connection, $delPreCalQuery);
                                                    $delAdjPreCalBool = sqlCUDData($connection, $delAdjPreCalQuery);
                                                }
                                                $delMisCredQuery = "DELETE FROM GPF_MISSING_CREDIT WHERE REGD_NO='$registrationNo'";
                                                $delSubscriptionQuery = "DELETE FROM GPF_SUBSCRIPTION WHERE REGD_NO='$registrationNo'";
                                                $delCalculationQuery = "DELETE FROM GPF_ACCOUNT_CALCULATION WHERE REGD_NO='$registrationNo'";
                                                $delCalculationRemarksQuery = "DELETE FROM GPF_CALCULATION_REMARKS WHERE REGD_NO='$registrationNo'";

                                                $remarkCUDSQL = "INSERT INTO GPF_CASES_REMARKS VALUES('$remarksID', '$registrationNo',
                                                                'DC','$remarks','$loginUser',SYSDATE)";

                                                $statusUpdtSQL = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='$status', ENTERED_DATE='', PRE_CAL_DATE='',
                                                                CALCULATION_DATE='', OBJECTION_DATE='', MINUS_BAL_DATE='', LTA_ENTERED_DATE='', 
                                                                LTA_CHECKED_DATE='', LTA_APPROVED_DATE='' WHERE REGD_NO='$registrationNo'";

                                                $delMisCredBool = sqlCUDData($connection, $delMisCredQuery);
                                                $delSubscriptionBool = sqlCUDData($connection, $delSubscriptionQuery);
                                                $delCalculationBool = sqlCUDData($connection, $delCalculationQuery);
                                                $delCalculationRemarksBool = sqlCUDData($connection, $delCalculationRemarksQuery);
                                                $statusUpdtBool = sqlCUDData($connection, $statusUpdtSQL);
                                                $remarkCUDBool = sqlCUDData($connection, $remarkCUDSQL);

                                                if ($statusUpdtBool && $remarkCUDBool && ($delAppBool || $delPreCalBool || $delAdjPreCalBool || $delMisCredBool || $delSubscriptionBool || $delCalculationBool || $delCalculationRemarksBool)) {
                                                    $remarksSuccess = "Successfully deleted";
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
                                                       <button type="submit" class="btn btn-danger" id="case_delete" name="case_delete">DELETE</button>
                                                   </div>
                                               </div>
                                               <div class="col-lg-6">
                                                   <div class="form-group">
                                                       <label for="remarks"><b>Delete from : <span class="text-danger">* <?php echo $deleteFromErr; ?></b></label>
                                                       <select name="delete_from" id="delete_from" class="form-control" required>
                                                           <option value="">Select delete </option>
                                                           <?php
                                                            $deleteTypeArr = ["1" => "From application", "2" => "From pre-calculation"];
                                                            foreach ($deleteTypeArr as $deleteKey => $deleteList) {
                                                            ?>
                                                               <option value="<?php echo $deleteKey; ?>" <?php if ($errors == 1) {
                                                                                                                if ($deleteKey == $deleteFrom) {
                                                                                                                    echo "selected='selected'";
                                                                                                                }
                                                                                                            } else {
                                                                                                                echo "";
                                                                                                            } ?>><?php echo $deleteList; ?></option>
                                                           <?php
                                                            }
                                                            ?>
                                                       </select>
                                                   </div>
                                               </div>
                                           </div>
                                       </form>
                                   <?php
                                    } else {
                                    ?>
                                       <table class="table table-bordered table-hover" id="delete_cases">
                                           <thead>
                                               <tr>
                                                   <th>REGISTRATION NUMBER</th>
                                                   <th>SUBSCRIBER NAME</th>
                                                   <th>FP REGISTERED DATE</th>
                                                   <th>LTA REGISTERED DATE</th>
                                                   <th>STATUS</th>
                                                   <th>OPTION</th>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php
                                                $usersQuery = "SELECT SUBSCRIBER_NAME, REGD_NO, REGD_DATE, LTA_REGISTERED_DATE, b.STATUS_DESCR 
                                                               FROM GPF_CASE_STATUS a INNER JOIN MAS_STATUS b ON a.CASE_STATUS=b.STATUS_ID
                                                               WHERE a.CASE_STATUS IN(1,2,3,4,9,10,12,13,14) ORDER BY a.REGD_DATE, a.LTA_REGISTERED_DATE";
                                                $fetchUsers = sqlFetchData($connection, $usersQuery);
                                                foreach ($fetchUsers as $usersList) {
                                                ?>
                                                   <tr>
                                                       <td><?php echo $usersList['REGD_NO']; ?></td>
                                                       <td><?php echo $usersList['SUBSCRIBER_NAME']; ?></td>
                                                       <td><?php echo $usersList['REGD_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['REGD_DATE'])); ?></td>
                                                       <td><?php echo $usersList['LTA_REGISTERED_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['LTA_REGISTERED_DATE'])); ?></td>
                                                       <td><?php echo $usersList['STATUS_DESCR']; ?></td>
                                                       <td>
                                                           <a href="delete_case.php?regd_no=<?php echo $usersList['REGD_NO']; ?>">
                                                               <button class="btn btn-danger">DELETE</button>
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
                       window.location.href = "delete_cases.php";
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
           $('#delete_cases').DataTable();
       });
   </script>