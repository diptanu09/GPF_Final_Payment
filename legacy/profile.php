   <?php
    $pageName = "Profile";
    require_once "./top.php";
    if (isset($_REQUEST['change_password'])) {
        $opass = trim($_REQUEST["opass"]);
        $npass = trim($_REQUEST["npass"]);
        if (empty($opass)) {
            $opassErr = "Required";
        } else {
            $oldPassSQL = "SELECT * FROM USER_ACCOUNTS WHERE USERNAME='$loginUser' AND PASSWORD='$opass'";
            $passExist = sqlCountData($connection, $oldPassSQL);
            if ($passExist == 0) {
                $opassErr = "Old password didn't matched";
            }
        }
        if (empty($npass)) {
            $npassErr = "Required";
        }

        if (($opassErr == "") && ($npassErr == "")) {
            $changepassSQL = "UPDATE USER_ACCOUNTS SET PASSWORD='$npass' WHERE USERNAME='$loginUser'";
            if (sqlCUDData($connection, $changepassSQL)) {
                $success = "You have changed your password";
            } else {
                $error = "Something wrong. Try Again";
            }
        } else {
            $error = "Recorrect errors";
        }
    }

    ?>

   <div class="content-wrap">
       <div class="main">
           <div class="container-fluid">
               <div class="row">
                   <div class="col-lg-8 p-r-0 title-margin-right">

                   </div>
                   <!-- /# column -->
                   <div class="col-lg-4 p-l-0 title-margin-left">
                       <div class="page-header">
                           <div class="page-title">
                               <ol class="breadcrumb">
                                   <li class="breadcrumb-item">
                                       <a href="./home.php">Dashboard</a>
                                   </li>
                                   <li class="breadcrumb-item active"><?php echo $pageName; ?></li>
                               </ol>
                           </div>
                       </div>
                   </div>
                   <!-- /# column -->
               </div>
               <!-- /# row -->
               <section id="main-content">
                   <div class="row">
                       <div class="col-lg-12">
                           <div class="card">
                               <div class="card-body">
                                   <div class="user-profile">
                                       <div class="row">
                                           <div class="col-lg-4">
                                               <div class="user-skill">
                                                   <h4>Change password</h4>
                                                   <b class="text-success"><?php echo $success; ?></b>
                                                   <b class="text-danger"><?php echo $error; ?></b>
                                                   <form action="" method="post">
                                                       <div class="form-group">
                                                           <label for="old_password"><b>Old password <span class="text-danger">* <?php echo $opassErr; ?></span></b></label>
                                                           <input type="password" name="opass" id="opass" class="form-control">
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="new_password"><b>New password <span class="text-danger">* <?php echo $npassErr; ?></span></b></label>
                                                           <input type="password" name="npass" id="npass" class="form-control">
                                                       </div>
                                                       <div class="form-group">
                                                           <button type="submit" class="btn btn-primary" name="change_password" id="change_password">
                                                               CHANGE PASSWORD
                                                           </button>
                                                       </div>
                                                   </form>
                                               </div>
                                           </div>
                                           <div class="col-lg-8">
                                               <div class="user-profile-name"><?php echo $loginName; ?></div>
                                               <div class="user-Location">
                                                   <i class="ti-location-pin"></i> FP Section
                                               </div>
                                               <div class="user-job-title"><?php echo $loginRoleName; ?></div>
                                               <!-- <div class="ratings">
                                                   <h4>Ratings</h4>
                                                   <div class="rating-star">
                                                       <span><?php //echo $ratings; 
                                                                ?></span>
                                                       <i class="ti-star color-danger"></i>
                                                       <i class="ti-star color-danger"></i>
                                                       <i class="ti-star color-danger"></i>
                                                       <i class="ti-star color-danger"></i>
                                                       <i class="ti-star"></i>
                                                   </div>
                                               </div> -->
                                               <!--<div class="user-send-message">
                                                   <button class="btn btn-primary btn-addon" type="button">
                                                       <i class="ti-email"></i>Send Message</button>
                                               </div>-->
                                           </div>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
                   <!-- /# row -->
                   <?php
                    require_once "./bottom.php";
                    ?>
               </section>
           </div>
       </div>
   </div>

   <?php
    require_once "./bottom.php";
    ?>