   <?php
    $mainPage = "Admin";
    $pageName = "Delete signature";
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
                                        if (isset($_POST['del_ds'])) {
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
                                                $remarkCUDSQL = "INSERT INTO GPF_CASES_REMARKS 
                                                                 VALUES('$remarksID', '$registrationNo','DS','$remarks','$loginUser',SYSDATE)";

                                                $caseSQL = "SELECT * FROM GPF_CASE_STATUS WHERE REGD_NO='$registrationNo'";
                                                $fetchCaseStatus = sqlFetchData($connection, $caseSQL);
                                                foreach ($fetchCaseStatus as $fetchCaseStatusList) {
                                                    $status = $fetchCaseStatusList['CASE_STATUS'];
                                                    $pensionType = $fetchCaseStatusList['PENSION_TYPE'];
                                                }
                                                if ($status == 16) {
                                                    $caseStatus = 14;
                                                    $statusCUDSQL = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='$caseStatus', LTA_SIGNED_DATE=''
                                                                     WHERE REGD_NO='$registrationNo'";
                                                } else {
                                                    $caseStatus = 6;
                                                    $statusCUDSQL = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='$caseStatus', FP_SIGNED_DATE='', DLIS_SIGNED_DATE=''
                                                                     WHERE REGD_NO='$registrationNo'";
                                                }

                                                if (sqlCUDData($connection, $remarkCUDSQL) && sqlCUDData($connection, $statusCUDSQL)) {
                                                    if ($pensionType == 2) {
                                                        deleteFile("pdf_files/signed_pdf/dlis/" . $registrationNo . ".pdf");
                                                    } else {
                                                        if ($status == 16) {
                                                            deleteFile("pdf_files/signed_pdf/lta/" . $registrationNo . ".pdf");
                                                        } else {
                                                            deleteFile("pdf_files/signed_pdf/authority/" . $registrationNo . ".pdf");
                                                        }
                                                    }

                                                    $remarksSuccess = "Successfully deleted digital signature";
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
                                                       <button type="submit" name="del_ds" class="btn btn-primary">DELETE DIGITAL SIGNATURE</button>
                                                   </div>
                                               </form>
                                           </div>
                                       </div>

                                   <?php
                                    } else {
                                    ?>
                                       <table class="table table-bordered table-hover" id="unapprove_cases">
                                           <thead>
                                               <tr>
                                                   <th>REGISTRATION NUMBER</th>
                                                   <th>SUBSCRIBER NAME</th>
                                                   <th>FP SIGNED DATE</th>
                                                   <th>DLIS SIGNED DATE</th>
                                                   <th>OPTION</th>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php
                                                $usersQuery = "SELECT SUBSCRIBER_NAME, REGD_NO, FP_SIGNED_DATE, DLIS_SIGNED_DATE
                                                           FROM GPF_CASE_STATUS WHERE CASE_STATUS=7 ORDER BY CHECKED_DATE";
                                                $fetchUsers = sqlFetchData($connection, $usersQuery);
                                                foreach ($fetchUsers as $usersList) {
                                                ?>
                                                   <tr>
                                                       <td><?php echo $usersList['REGD_NO']; ?></td>
                                                       <td><?php echo $usersList['SUBSCRIBER_NAME']; ?></td>
                                                       <td><?php echo $usersList['FP_SIGNED_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['FP_SIGNED_DATE'])); ?></td>
                                                       <td><?php echo $usersList['DLIS_SIGNED_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['DLIS_SIGNED_DATE'])); ?></td>
                                                       <td>
                                                           <a href="delete_digital_signature.php?regd_no=<?php echo $usersList['REGD_NO']; ?>">
                                                               <button class="btn btn-warning">DELETE SIGNATURE</button>
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
                       window.location.href = "unapprove_cases.php";
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
           $('#unapprove_cases').DataTable();
       });
   </script>