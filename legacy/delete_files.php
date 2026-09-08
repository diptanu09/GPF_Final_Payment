<?php
$pageName = "Delete unused files";
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
                if (isset($_REQUEST['dlt_files'])) {

                    array_map('unlink', glob("pdf_files/extra_pdf/authority_cancel/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/calculated_cases/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/case_cancel/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/corrigendum/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/objection/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/ddo_application/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/input_sheet/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/intimation_letter/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/minus_balance/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/objection/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/pending_cases/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/revalidation/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/settled_cases/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/signed_cases/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/user_report/*.*"));
                    array_map('unlink', glob("pdf_files/extra_pdf/user_signed_cases/*.*"));
                    array_map('unlink', glob("pdf_files/unsigned_pdf/*.*"));
                    array_map('unlink', glob("assets/images/qr/*.*"));
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
                                            <button type="submit" class="btn btn-primary" name="dlt_files" id="dlt_files" tabindex="15">
                                                Delete unsued files
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