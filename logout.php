<?php
session_start();
unset($_SESSION['GPF_USERNAME']);
unset($_SESSION['GPF_USER_ROLE']);
if (isset($_SERVER['HTTP_REFERER'])) {
?>
    <script type="text/javascript">
        window.location.href = "index.html";
    </script>
<?php
} else {
?>
    <script type="text/javascript">
        window.location.href = "index.html";
    </script>
<?php
}
exit;
?>