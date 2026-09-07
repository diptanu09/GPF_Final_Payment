   <?php
    $mainPage = "Admin";
    $pageName = "Users";
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
                           <a href="./add_user.php">
                               <button class="btn btn-primary" style="margin-bottom: 10px;">
                                   Add user
                               </button>
                           </a>
                           <div class="card">
                               <div class="card-body">
                                   <table class="table table-bordered table-hover" id="view_users">
                                       <thead>
                                           <tr>
                                               <th>FULL NAME</th>
                                               <th>USER NAME</th>
                                               <th>PASSWORD</th>
                                               <th>USER STATUS</th>
                                               <th>USER ROLE</th>
                                               <th>OPTION</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php
                                            $usersQuery = "SELECT a.FULL_NAME, a.USERNAME, a.PASSWORD, a.USER_STATUS, b.ROLE_NAME 
                                                           FROM USER_ACCOUNTS a INNER JOIN MAS_ROLES b ON a.USER_ROLE=b.ROLE_ID 
                                                           WHERE a.USERNAME<>'dir' ORDER BY a.FULL_NAME";
                                            $fetchUsers = sqlFetchData($connection, $usersQuery);
                                            foreach ($fetchUsers as $usersList) {
                                            ?>
                                               <tr>
                                                   <td><?php echo $usersList['FULL_NAME']; ?></td>
                                                   <td><?php echo $usersList['USERNAME']; ?></td>
                                                   <td><?php echo $usersList['PASSWORD']; ?></td>
                                                   <td>
                                                       <?php
                                                        if ($usersList['USER_STATUS'] == "Y") {
                                                            $badgeType = "success";
                                                            $userStatus = "Active";
                                                            $btnBadgeType = "danger";
                                                            $btnUserStatus = "N";
                                                        } else {
                                                            $badgeType = "danger";
                                                            $userStatus = "Inactive";
                                                            $btnBadgeType = "success";
                                                            $btnUserStatus = "Y";
                                                        }
                                                        ?>
                                                       <span class="badge badge-<?php echo $badgeType; ?>">
                                                           <?php echo $userStatus; ?>
                                                       </span>
                                                   </td>
                                                   <td><?php echo $usersList['ROLE_NAME']; ?></td>
                                                   <td>
                                                       <a href="edit_user.php?user_name=<?php echo $usersList['USERNAME']; ?>">
                                                           <button class="btn btn-warning">EDIT</button>
                                                       </a>
                                                       <button class="btn btn-<?php echo $btnBadgeType; ?>" value="<?php echo $btnUserStatus; ?>" onclick="statusChange(this.value, '<?php echo $usersList['USERNAME']; ?>')">
                                                           <?php echo $btnUserStatus == "Y" ? "ACTIVE" : "INACTIVE"; ?></button>
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
       function statusChange(statusValue, userName) {
           jQuery.ajax({
               type: 'GET',
               url: 'ajax/user_status_change.php',
               data: 'status_value=' + statusValue + '&&user_name=' + userName,
               success: function(X) {
                   if (X === "Success") {
                       window.location.href = "view_users.php";
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
           $('#view_users').DataTable();
       });
   </script>