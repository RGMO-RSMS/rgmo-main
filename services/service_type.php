<?php

    include_once __DIR__ . '/../objects/session.php';

    $SES = Session::getInstance();

    if($SES->id == null) {
        header("location: ../login/");
    }
    else if(isset($SES->id) && $SES->role_name == 'client') {

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Type</title>
    <?php require_once __DIR__ . '/../components/link.html'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">

    <div class="wrapper">

        <?php
            require_once __DIR__ . '/../components/navbar.php';
            require_once __DIR__ . '/../components/sidebar.php';
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-1"> 
                            <a href="#" onclick="history.back()"><i class="fas fa-arrow-left" style="color: primary;"></i></a> 
                        </div>
                        <div class="col-sm-10"> <h4 class="m-0"></h4> </div>
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="card border border-primary rounded p-4"></div>
                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->

        </div>
        <!-- /.content-wrapper -->

        <?php require_once __DIR__ . '/../components/footer.html'; ?>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->

    </div>
    <!-- ./wrapper -->

    <?php require_once __DIR__ . '/../components/script.html'; ?>

</body>
</html>

<script>

    $(document).ready(function() {

        // Assign Role to js variable
        let user_id = "<?php echo $SES->id; ?>";
        let role = "<?php echo $SES->role_name; ?>";
        let service_name = GetURLParameter('s');

        displaySidebar(role, 'Services');
        $('h4').text('Services/' + service_name);

        // List of Services Available
        $.ajax({
            url: '../controller/ServicesController.php',
            type: 'POST',
            data: {case: 'fetch type', service_name: service_name},
            success: function(data) {

                let row = $("<div class='row justify-content-center'></div>");
                
                data.forEach(element => {

                    let div_col = $("<div class='col-md-3 m-2 border border-primary'></div>");
                    let div_card = $("<div class='card'></div>");
                    let img = $("<img class='card-img-top' src='../includes/images/"+element.service_image+"' height='200px' width='100%'>")
                    .css('cursor', 'pointer')
                        .on('click', () => { 

                            // Check if service is available
                            if(element.availability_status == 'yes') {
                                window.location.href = 'service_info.php?s=' + service_name + '&type_id=' + element.type_id;
                            }
                            else {
                                Swal.fire({
                                    position: 'top',
                                    icon: 'warning',
                                    title: 'This Service is not Available!',
                                    text: 'Please Pick Other Service.'
                                });
                            }

                    });
                    let div_body = $("<div class='card-body'></div>");
                    let card_title = $("<div class='card-title'><b>Available: "+element.availability_status+"</b></div>");
                    let card_text = $("<p class='card-text'>"+element.type_name+"</p>");
                    
                    div_body.append(card_title).append(card_text);
                    div_col.append(div_card).append(img).append(div_body);
                    row.append(div_col);

                });// foreach

                // Display Images to card
                $('.card').append(row);

            }
        });

    });// document

</script>

<?php
    }else {
        header("location: ../objects/logout.php");
    }
?>