<?php
$pageName = "Digital sign";
require_once "./top.php";
if (isset($_REQUEST['regd_no']) && isset($_REQUEST['type'])) {
    $registrationNo = $_REQUEST['regd_no'];
    $type = $_REQUEST['type'];
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
                                    <center>
                                        <b>
                                            Digital Signature for registration no. <?php echo $registrationNo; ?>,
                                            Type : <?php echo ucfirst($type); ?>
                                        </b>
                                    </center>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="token">Select token</label>
                                                <select id="tokenddl" class="form-control"></select>
                                            </div>
                                            <button id="btnDSignPDF" onClick="getPdfB64()" class="btn btn-primary">
                                                Sign PDF
                                            </button>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="pin">Enter pin</label>
                                                <input id="txtPin" type="password" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
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
    <script src="./assets/js/digital_signature/dsign.min.js"></script>
    <script>
        $(document).ready(function() {
            fillTokenDDL("tokenddl");
        });


        function getPdfB64() {
            $("#dscDetail").css("color", "black");
            let token = $("#tokenddl").val();
            let pin = $("#txtPin").val();
            let pdfUrl = "<?php echo getHostLink(SITE_NAME . '/pdf_files/unsigned_pdf/' . $registrationNo . '_' . $type . '.pdf') ?>";
            let dsc = signPDF(token, pin, pdfUrl, "dscDetail");

            if (dsc.getError() == "false") {
                let registrationNo = "<?php echo $registrationNo; ?>";
                let type = "<?php echo $type; ?>";
                let user = "<?php echo $loginUser; ?>";
                let authorityData = {
                    UserName: user,
                    RegistrationNo: registrationNo,
                    Authority: dsc.getSignedPDF(),
                    AuthorityType: type
                }
                console.log(dsc.getSignedPDF());
                jQuery.ajax({
                    type: 'POST',
                    url: 'ajax/digital_signature_decrypt.php',
                    data: JSON.stringify(authorityData),
                    success: function(returnValue) {
                        const {
                            StatusCode,
                            Message,
                            Link
                        } = returnValue;

                        if (StatusCode === 200) {
                            window.open(Link, "_blank")
                        } else {
                            swal({
                                title: "Oops!",
                                text: Message,
                                icon: "error",
                                button: "Close",
                            });
                        }
                    },
                    error: function(a, b, c) {
                        console.log(a + " " + b + " " + c)
                    }
                });
            } else {
                swal({
                    title: "Digitally not signed!",
                    text: dsc.getError(),
                    icon: "error",
                    button: "Close",
                });
            }
        }
    </script>
<?php
}
?>