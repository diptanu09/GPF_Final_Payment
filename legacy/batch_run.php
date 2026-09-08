<?php
$pageName = "Batch run for monthly report";
require_once "./top.php";

?>

<div class="content-wrap">
    <div class="main">
        <div class="container-fluid">
            <?php
            require_once "./includes/breadcumb.php";
            ?>
            <section id="main-content">
                <?php
                if (isset($_REQUEST['run_batch'])) {
                    $batchMonth = trim($_REQUEST['batch_month']);

                    if (empty($batchMonth)) {
                        $batchMonthErr = "Required";
                    } else {
                        $batchMonth = date("01-M-Y", strtotime($batchMonth));
                    }

                    if ($batchMonthErr == "") {
                    } else {
                        $message = "";
                        $textColor = "danger";
                    }

                ?>

                <?php
                }
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">

                                <b class="text-<?php echo $textColor; ?>">
                                    <?php echo $message; ?>
                                </b>
                                <br>
                                <form action="" method="post">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="batch_month">Batch month <b class="text-danger">* <?php echo $batchMonthErr; ?> </b></label>
                                                <input type="month" name="batch_month" id="batch_month" class="form-control" required>
                                            </div>

                                            <button type="submit" class="btn btn-primary" name="run_batch" id="run_batch" tabindex="15">
                                                Run batch
                                            </button>
                                        </div>

                                    </div>
                                </form>


                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <?php
        require_once "./includes/copyright.php";
        ?>
        </desgTitle>
    </div>
</div>
</div>

<?php
require_once "./bottom.php";
?>