   <?php
    $mainPage = "Admin";
    $pageName = "Unapprove cases";
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
                                        if (isset($_POST['case_unapprove'])) {
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
                                                                 VALUES('$remarksID', '$registrationNo','UA','$remarks','$loginUser',SYSDATE)";

                                                $statusCUDSQL = "UPDATE GPF_CASE_STATUS SET CASE_STATUS='4', CHECKED_DATE='', APPROVED_DATE='',
                                                                LTA_CHECKED_DATE='', LTA_APPROVED_DATE='' WHERE REGD_NO='$registrationNo'";
                                                $caseInfoQuery = "UPDATE GPF_AMOUNT_INFO SET CHECKED_BY='', APPROVED_BY='', LTA_CHECKED_BY='',
                                                                 LTA_APPROVED_BY='' WHERE REGD_NO='$registrationNo'";

                                                if (sqlCUDData($connection, $remarkCUDSQL) && sqlCUDData($connection, $statusCUDSQL) && sqlCUDData($connection, $caseInfoQuery)) {
                                                    $remarksSuccess = "Successfully unapproved";
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
                                                       <button type="submit" name="case_unapprove" class="btn btn-primary">UN-APPROVE</button>
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
                                                   <th>CHECKED DATE</th>
                                                   <th>APPROVED DATE</th>
                                                   <th>LTA CHECKED DATE</th>
                                                   <th>LTA APPROVED DATE</th>
                                                   <th>OPTION</th>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php
                                                $usersQuery = "SELECT SUBSCRIBER_NAME, REGD_NO, CHECKED_DATE, APPROVED_DATE,
                                                               LTA_CHECKED_DATE, LTA_APPROVED_DATE FROM GPF_CASE_STATUS
                                                                WHERE CASE_STATUS IN(5,6,14,15)";
                                                $fetchUsers = sqlFetchData($connection, $usersQuery);
                                                foreach ($fetchUsers as $usersList) {
                                                ?>
                                                   <tr>
                                                       <td><?php echo $usersList['REGD_NO']; ?></td>
                                                       <td><?php echo $usersList['SUBSCRIBER_NAME']; ?></td>
                                                       <td><?php echo $usersList['CHECKED_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['CHECKED_DATE'])); ?></td>
                                                       <td><?php echo $usersList['APPROVED_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['APPROVED_DATE'])); ?></td>
                                                       <td><?php echo $usersList['LTA_CHECKED_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['LTA_CHECKED_DATE'])); ?></td>
                                                       <td><?php echo $usersList['LTA_APPROVED_DATE'] == "" ? "" : date("d/m/Y", strtotime($usersList['LTA_APPROVED_DATE'])); ?></td>
                                                       <td>
                                                           <a href="unapprove.php?regd_no=<?php echo $usersList['REGD_NO']; ?>">
                                                               <button class="btn btn-warning">UNAPPRVOVE</button>
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
       $(document).ready(function() {
           $('#unapprove_cases').DataTable();
       });
   </script>