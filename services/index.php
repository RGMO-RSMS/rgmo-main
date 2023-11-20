<?php

    include_once __DIR__ . '/../objects/session.php';

    $SES = Session::getInstance();

    if(isset($SES->id)) {

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services</title>
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

            <?php 
            
                switch($SES->role_name) {

                    case 'client':
                        require_once __DIR__ . '/client_index.html';
                    break;

                    case 'admin':
                        require_once __DIR__ . '/admin_index.html';
                    break;

                }// switch
            
            ?>

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
        displaySidebar(role, 'Services');

        switch(role) {

            case 'client':

                // List of Services Available
                $.ajax({
                    url: '../controller/ServicesController.php',
                    type: 'POST',
                    data: {case: 'services'},
                    success: function(data) {
                        
                        data.forEach(element => {

                            let div_col = $("<div class='col-lg-3 col-6'></div>");
                            let small_box = $("<div class='small-box bg-primary'></div>");
                            let inner = $("<div class='inner'></div>");
                            inner.append("<p>"+element.service_name+"</p>");
                            small_box.append(inner);

                            // All Services in specific id is Available
                            if(checkServices(element.service_id, '../controller/ServicesController.php')) {
                                small_box.css('cursor', 'pointer').on('click', () => {
                                    window.location.href = 'service_type.php?s=' + element.service_name;
                                });
                            }
                            else {
                                small_box.removeClass('bg-primary')
                                .addClass('bg-secondary')
                                .attr("title", "This Service is Closed");
                            }

                            
                            div_col.append(small_box);
                            $('#services-row-id').append(div_col);
                            
                        });

                    }
                });

            break;

            case 'admin':

                let selectServiceName = $($('#add-service-id select')[0]);
                let type_id = '';
                let filter_var = 0;
                let filter_true = 0;

                let services_table = $('#service-table-id').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "lengthChange": false,
                    ajax: {
                        url: '../controller/ServicesController.php',
                        type: 'POST',
                        data: (d) => {
                            d.case = 'all types table',
                            d.availability = filter_var,
                            d.isTrue = filter_true
                        }
                    },
                    columns: [
                        {title: 'Service Name', 'data': 'service_id', targets: [0]},
                        {title: 'Type Name', 'data': 'type_name', targets: [1]},
                        {title: 'Location', 'data': 'location', targets: [2]},
                        {title: 'Price', 'data': 'decimal_price', targets: [3]},
                        {title: 'Availability', 'data': 'availability_status', targets: [4]},
                        {title: 'Action', 'data': 'type_id', targets: [5]}
                    ],
                    createdRow: function(row, data, index) {

                        // Action Buttons
                        let btn_update = $("<button type='button' class='btn btn-success mr-2'> Update </button>");
                        let btn_delete = $("<button type='button' class='btn btn-danger'> Delete </button>");
                        $('td', row).eq(5).text('').append(btn_update).append(btn_delete);

                        // Update Button
                        btn_update.on('click', function(e) {

                            e.preventDefault();
                            
                            // Show Modal
                            $('#modal-update-service').modal('toggle');

                            // Display For Update Service
                            $.ajax({
                                url: '../controller/ServicesController.php',
                                type: 'POST',
                                data: {
                                    case: 'display update service', 
                                    type_id: data.type_id,
                                    service_id: data.service_id
                                },
                                success: function(response) {

                                    let type = $($('#update-service-id input')[0]);
                                    let location = $($('#update-service-id input')[1]);
                                    let price = $($('#update-service-id input')[2]);
                                    let description = $($('#update-service-id textarea'));
                                    let display_image = $($('#update-service-image div')[0]);
                                    type.val(response.type_name);
                                    location.val(response.location);
                                    price.val(response.true_price);
                                    description.val(response.description);
                                    type_id = response.type_id;

                                    // Services Select
                                    $('#update-service-id').val(null).trigger('change');

                                    $('#update-service-id select').select2({
                                        width: '100%',
                                        theme: 'bootstrap4',
                                        data: response.selected_service
                                    });

                                    $('#update-service-id select').trigger('change');

                                    // Remove Previous Appended Images before Adding New
                                    $($('#update-service-image div > img')).remove();

                                    // Remove Previous Upload Value from Choose Image
                                    $($("#update-service-image input[type='file']")).val("");

                                    // Service Image Display
                                    let image_element = $("<img src='../includes/images/"+response.service_image+"' alt='Service Image' height='100' width='200'>");
                                    display_image.append(image_element);

                                },
                                async: false
                            });// ajax display update


                        });// update on click

                        // Update Service
                        $('#update-service-id').validate({
                            rules: {
                                service_name: {required: true},
                                type_name: {required: true},
                                location: {required: true},
                                price: {required: true},
                                description: {required: true}
                            },
                            errorElement: 'span',
                            errorPlacement: function(error, element) {
                                error.addClass('invalid-feedback');
                                element.closest('.form-group').append(error);
                            },
                            highlight: function(element, errorClass, validClass) { $(element).addClass('is-invalid'); },
                            unhighlight: function(element, errorClass, validClass) { $(element).removeClass('is-invalid'); },
                            submitHandler: function(form) { 

                                Swal.fire({
                                    position: 'top',
                                    title: 'Are you sure!',
                                    text: 'You want to Update this Service?',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Update'
                                }).then((result) => {

                                    if(result.isConfirmed) {
                                        
                                        let formData = new FormData(form);
                                        formData.append('case', 'update service');
                                        formData.append('type_id', type_id);

                                        $.ajax({
                                            url: '../controller/ServicesController.php',
                                            type: 'POST',
                                            processData: false,
                                            contentType: false,
                                            data:formData,
                                            success: function(response) {

                                                if(response.status == true) {

                                                    Swal.fire({
                                                        position: 'top',
                                                        icon: 'success',
                                                        title: 'Service Updated!',
                                                        showConfirmButton: false,
                                                        timer: 1500
                                                    }).then(function() {
                                                        location.reload();
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
                                    
                                });// swal

                            }// submit handler
                        });// validate

                        // Update Service Image
                        $('#update-service-image').validate({
                            rules: { service_image: {required: true} },
                            errorElement: 'span',
                            errorPlacement: function(error, element) {
                                error.addClass('invalid-feedback');
                                element.closest('.form-group').append(error);
                            },
                            highlight: function(element, errorClass, validClass) { $(element).addClass('is-invalid'); },
                            unhighlight: function(element, errorClass, validClass) { $(element).removeClass('is-invalid'); },
                            submitHandler: function(form) {

                                Swal.fire({
                                    position: 'top',
                                    icon: 'warning',
                                    title: 'Are you sure?',
                                    text: 'You want Update this Image?',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Yes'
                                }).then((result) => {

                                    if(result.isConfirmed) {

                                        let formData = new FormData(form);
                                        formData.append('case', 'update service image');
                                        formData.append('type_id', type_id);

                                        $.ajax({
                                            url: '../controller/ServicesController.php',
                                            type: 'POST',
                                            contentType: false,
                                            processData: false,
                                            data: formData,
                                            success: function(response) {
                                                if(response.status == true) {

                                                    Swal.fire({
                                                        position: 'top',
                                                        icon: 'success',
                                                        title: 'Service Image Updated!',
                                                        showConfirmButton: false,
                                                        timer: 1500
                                                    }).then(function() {
                                                        location.reload();
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

                                    }// if

                                });

                            }
                        });// update service image validate

                        // Delete Button
                        btn_delete.on('click', function(e) {
                            e.preventDefault();

                            Swal.fire({
                                position: 'top',
                                icon: 'warning',
                                title: 'Are you sure?',
                                text: 'You want to delete this service?',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes'
                            }).then((result) => {
                                if(result.isConfirmed) {
                                    
                                    $.ajax({
                                        url: '../controller/ServicesController.php',
                                        type: 'POST',
                                        data: {case: 'delete service', type_id: data.type_id},
                                        success: function(response) {

                                            if(response.status) {
                                                Swal.fire({
                                                    position: 'top',
                                                    icon: 'success',
                                                    title: 'Service Deleted!',
                                                    showConfirmButton: false,
                                                    timer: 1500
                                                }).then(function() {
                                                    window.location.reload();
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

                        });// delete on click

                    }
                });//DataTable

                $('#availability-select').select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    placeholder: 'Filter Availability',
                    allowClear: true,
                    data: [
                        {id: 'yes', text: 'yes'},
                        {id: 'no', text: 'no'},
                    ]
                }).on('change', function() {
                    filter_var = $(this).val();
                    filter_true = (filter_var == "yes" || filter_var == "no") ? 1 : 0;
                    services_table.ajax.reload();
                });

                // Add Service Name Select
                selectServiceName.select2({
                    width: '100%', theme: 'bootstrap4',
                    placeholder: 'Select Service',
                    allowClear: true,
                    ajax: {
                        url: '../controller/ServicesController.php',
                        type: 'POST',
                        data: {case: 'services selection'},
                        processResults: function(data, params) {
                            return {results: data};
                        }
                    }
                });

                // Service Image Upload Event
                invalidImageType($('#service-image'));
                invalidImageType($('#update-image-file'));

                // Add Service
                $('#add-service-id').validate({
                    rules: {
                        service_name: {required: true},
                        type_name: {required: true},
                        location: {required: true},
                        price: {required: true},
                        description: {required: true},
                        service_image: {required: true}
                    },
                    errorElement: 'span',
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('.form-group').append(error);
                    },
                    highlight: function(element, errorClass, validClass) { $(element).addClass('is-invalid'); },
                    unhighlight: function(element, errorClass, validClass) { $(element).removeClass('is-invalid'); },
                    submitHandler: function(form) { 

                        Swal.fire({
                            position: 'top',
                            title: 'Are you sure!',
                            text: 'You want to Add this Service?',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Add'
                        }).then((result) => {

                            if(result.isConfirmed) {
                                
                                let formData = new FormData(form);
                                formData.append('case', 'add service');

                                $.ajax({
                                    url: '../controller/ServicesController.php',
                                    type: 'POST',
                                    processData: false,
                                    contentType: false,
                                    data:formData,
                                    success: function(response) {

                                        if(response.status == true) {

                                            Swal.fire({
                                                position: 'top',
                                                icon: 'success',
                                                title: 'Service Added!',
                                                showConfirmButton: true
                                            }).then(function() {
                                                location.reload();
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
                            
                        });// swal

                    }// submit handler
                });// validate
            
            break;

        }// switch

    });// document

</script>

<?php
    }else if(isset($_GET['direct_service'])){
        require_once __DIR__ . '/direct_service.php';
    }
    else {
        header("location: ../objects/logout.php");
    }
?>