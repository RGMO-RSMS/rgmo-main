<!--services selection
admin_index.html line 25

                        <div class="col-6">
                            <div class="col-4 float-right">
                                <select name="availability_select" id="availability-select">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-4 float-right">
                                <select name="location_select" id="location-select">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div> 


page 409

// $('#availability-select').select2({
                //     width: '100%',
                //     theme: 'bootstrap4',
                //     placeholder: 'Filter Availability',
                //     allowClear: true,
                //     data: [
                //         {id: 'yes', text: 'yes'},
                //         {id: 'no', text: 'no'},
                //     ]
                // }).on('change', function() {
                //     filter_var = $(this).val();
                //     filter_true = (filter_var == "yes" || filter_var == "no") ? 1 : 0;
                //     services_table.ajax.reload();
                // });
  
                // $('#location-select').select2({
                //     width: '100%',
                //     theme: 'bootstrap4',
                //     placeholder: 'filter location',
                //     allowClear: true,
                //     data: [
                //         {id: 'Isabela', text: 'Isabela'},
                //         {id: 'Cagayan', text: 'Cagayan'},
                //         {id: 'Ilocos Norte', text: 'Ilocos Norte'},
                //         {id: 'Cavite', text: 'Cavite'},
                //         {id: 'Teresa', text: 'Teresa'},
                //         {id: 'Antipolo', text: 'Antipolo'},
                //     ]
                // }).on('change', function() {
                //     filter_var = $(this).val();
                //     filter_true = (filter_var == "Isabela" || filter_var == "Cagayan" || filter_var == "Ilocos Norte" || filter_var == "Cavite" || filter_var == "Teresa" || filter_var == "Antipolo") ? 1 : 0;
                //     services_table.ajax.reload();
                // });

                // // Add Service Name Select
                // selectServiceName.select2({
                //     width: '100%', theme: 'bootstrap4',
                //     placeholder: 'Select Service',
                //     allowClear: true,
                //     ajax: {
                //         url: '../controller/ServicesController.php',
                //         type: 'POST',
                //         data: {case: 'services selection'},
                //         processResults: function(data, params) {
                //             return {results: data};
                //         }
                //     }
                // });

---------------------Back-end------------------

        // if($_POST['availability'] == $value->availability_status) {
        //     $new_data[] = $value;
        // }elseif($_POST['location'] == $value->location) {
        //     $new_data[] = $value;
        // }

-->