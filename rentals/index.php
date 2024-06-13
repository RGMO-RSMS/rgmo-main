<?php

    include_once __DIR__ . '/../objects/session.php';

    $SES = Session::getInstance();

    if($SES->id == null) {
        header("location: ../login/");
    }
    else if(isset($SES->id)) {

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rentals</title>
    <?php require_once __DIR__ . '/../components/link.html'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">

    <div class="wrapper">

        <?php
            require_once __DIR__ . '/../components/navbar.php';
            require_once __DIR__ . '/../components/sidebar.php';
            require_once __DIR__ . '/../components/modals.html';
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"></h1>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <?php
                        switch($_GET['link']) {
                            case 'new': require_once 'new.html'; break;
                        }
                    ?>
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

    <script>

        $(document).ready(function() {

            // Assign PHP variable to js variable
            let civil_selection = [
                {"id": 'Single', "text": 'Single'}, {"id": 'Married', "text": 'Married'},
                {"id": 'Divorced', "text": 'Divorced'}, {"id": 'Widowed', "text": 'Widowed'}
            ];
            let sex_selection = [{"id": 'Male', "text": 'Male'}, {"id": 'Female', "text": 'Female'}];
            let role = "<?php echo $SES->role_name;  ?>";
            let civil_select = $($('#new-form-id select')[0]);
            let sex_select = $($('#new-form-id select')[1]);
            let services_row = $('.services-row');
            let selected_service = 0;
            displaySidebar(role, 'Rentals');

            civil_select.select2({
                width: '100%',
                theme: 'bootstrap4',
                placeholder: 'Select Civil Status',
                allowClear: true,
                data: civil_selection
            }).on('change', function() {($(this).val() == "") ? $(this).addClass('is-invalid') : $(this).removeClass('is-invalid') });

            sex_select.select2({
                width: '100%',
                theme: 'bootstrap4',
                placeholder: 'Select Sex',
                allowClear: true,
                data: sex_selection
            }).on('change', function() {($(this).val() == "") ? $(this).addClass('is-invalid') : $(this).removeClass('is-invalid') });

            $.ajax({
                url: '../controller/ServicesController.php',
                type: 'POST',
                data: { case: 'all types' },
                success: function(response) {
                    let div_h = response.height + 80;
                    let img_h = response.height;
                    response.data.forEach(value => {
                        let div = $("<div class='col-4 mb-2 border rounded' style='height:"+div_h+"px;' id="+value.type_id+"></div>");
                        let img = $("<img src='../includes/images/"+value.service_image+"' style='height:"+img_h+"px; width: 100%;'>");
                        let label_name = $("<div><b>Service Name:</b> "+value.type_name+"</div>");
                        let label_price = $("<div><b>Price:</b> "+value.f_price+"</div>");
                        let label_av = $("<div><b>Available:</b> "+value.availability_status+"</div>");
                        div.append(img).append(label_name).append(label_price).append(label_av);
                        services_row.append(div);
                    });
                    let cards = $(".services-row .rounded");
                    cards.on('click', function(e) {
                        e.preventDefault();
                        cards.removeClass('border-danger');
                        $(this).addClass('border-danger');
                        selected_service = $(this).attr('id');
                    });
                },
                async: false
            });//ajax
            
            $('#new-form-id').validate({
                rules: {
                    lname: {required: true},
                    fname: {required: true},
                    mname: {required: true},
                    address: {required: true},
                    civil_status: {required: true},
                    sex: {required: true},
                    email: {required: true},
                    contact: {required: true, maxlength: 11, minlength: 11}
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.input-group').append(error);
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) { $(element).addClass('is-invalid'); },
                unhighlight: function(element, errorClass, validClass) { $(element).removeClass('is-invalid'); },
                submitHandler: function(form) {

                    Swal.fire({
                        position: 'top',
                        title: 'Are you sure!',
                        text: 'You want to Submit Request?',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes',
                    }).then((result) => {

                        if(result.isConfirmed) {

                            let formData = new FormData(form);
                            formData.append('case', 'request');
                            formData.append('type_id', selected_service);

                            $.ajax({
                                url: '../controller/RegisterController.php',
                                type: 'POST',
                                processData: false,
                                contentType: false,
                                data:formData,
                                success: function(response) {

                                    if(response.status == true) {

                                        Swal.fire({
                                            position: 'top',
                                            icon: 'success',
                                            title: 'Request Successfully Submitted!',
                                            showConfirmButton: false,
                                            timer: 1000
                                        }).then(function() {
                                            window.location.href = '../';
                                        });

                                    }
                                    else {
                                        Swal.fire({
                                            position: 'top',
                                            icon: 'warning',
                                            title: response.message,
                                            showConfirmButton: true
                                        });
                                    }

                                }
                            });

                        }

                    });

                }
            });

        });// document ready

    </script>

</body>
</html>

<?php
    }else {
        header("location: ../objects/logout.php");
    }
?>