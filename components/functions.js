
// Initializations

function displaySidebar(role, page) {

    let sidebar_id = (role == 'client') ? [1, 4, 5, 7] : [1, 2, 8, 5, 3, 6];
        
    $.ajax({
        url: '../controller/SidebarController.php',
        type: 'POST',
        data: {
            id: sidebar_id,
            case: 'sidebar_list'
        },
        success: function(data) {

            // Append Sidebar
            data.forEach(element => {
                
                let li = $("<li class='nav-item'></li>");
                let i = $("<i></i>");
                let p = $("<p></p>");
                
                // Admin Reports
                if(element.element_text == "Reports") {

                    // Variables
                    let a = $("<a href='#' class='nav-link'>");
                    let angle_i = $("<i class='fas fa-angle-left right'></i>");
                    let tree_ul = $("<ul class='nav nav-treeview'></ul>");

                    // Append Reports Sidebar
                    p.text(" " + element.element_text).append(angle_i);
                    i.addClass(element.element_class);
                    a.append(i).append(p);

                    // Treeview
                    const tree_arr = [
                        [" Payment Report", "payment", "fas fa-money-bill-wave"], 
                        [" Financial Report", "financial", "fas fa-piggy-bank"], 
                        [" Graphical Chart", "chart", "fas fa-chart-bar"]
                    ];

                    // Treeview Data
                    tree_arr.forEach(element => {
                        
                        let tree_li = $("<li class='nav-item'></li>");
                        let tree_a = $("<a href='../reports/?link="+element[1]+"' class='nav-link'></a>");
                        let tree_i = $("<i class='"+element[2]+"'></i>");
                        let tree_p = $("<p>"+element[0]+"</p>");

                        // Active Class
                        if(GetURLParameter('link') == element[1]) {  
                            tree_a.addClass('active');
                            isReports = true;
                        }

                        tree_a.append(tree_i).append(tree_p);
                        tree_li.append(tree_a);
                        tree_ul.append(tree_li);

                    });// foreach

                    // Append List to a tag Reports
                    li.append(a).append(tree_ul);

                    // Check if what sidebar will be active
                    if(element.element_text == page) { a.addClass('active') }

                }// reports
                // Rentals
                else if(element.element_text == "Rentals") {

                    // Variables
                    let a = $("<a href='#' class='nav-link'>");
                    let angle_i = $("<i class='fas fa-angle-left right'></i>");
                    let tree_ul = $("<ul class='nav nav-treeview'></ul>");

                    // Append Reports Sidebar
                    p.text(" " + element.element_text).append(angle_i);
                    i.addClass(element.element_class);
                    a.append(i).append(p);

                    // Treeview
                    const tree_arr = [
                        [" New", "new", "fas fa-file"]
                    ];

                    // Treeview Data
                    tree_arr.forEach(element => {
                        
                        let tree_li = $("<li class='nav-item'></li>");
                        let tree_a = $("<a href='../rentals/?link="+element[1]+"' class='nav-link'></a>");
                        let tree_i = $("<i class='"+element[2]+"'></i>");
                        let tree_p = $("<p>"+element[0]+"</p>");

                        // Active Class
                        if(GetURLParameter('link') == element[1]) {  
                            tree_a.addClass('active');
                            // isReports = true;
                        }

                        tree_a.append(tree_i).append(tree_p);
                        tree_li.append(tree_a);
                        tree_ul.append(tree_li);

                    });// foreach

                    // Append List to a tag Reports
                    li.append(a).append(tree_ul);

                    // Check if what sidebar will be active
                    if(element.element_text == page) { a.addClass('active') }

                }// rentals
                else {

                    let a = $("<a href='" + element.element_uri + "' class='nav-link'></a>");
                    p.text(" " + element.element_text);
                    i.addClass(element.element_class);
                    a.append(i).append(p);
                    li.append(a);

                    // Check if what sidebar will be active
                    if(element.element_text == page) { a.addClass('active') }

                }

                // Append List to ul
                $('#sidebar-ul-id').append(li);

            });

        },
        async: false
    });

}// display sidebar

// Get Variable from URL
function GetURLParameter(sParam) {

    let sPageURL = window.location.search.substring(1);
    let sURLVariables = sPageURL.split('&');

    for(var i = 0; i < sURLVariables.length; i++) {

        let sParameterName = sURLVariables[i].split('=');

        if(sParameterName[0] == sParam) {
            return decodeURIComponent(sParameterName[1]);
        }

    }

}// GetURLParameter

// Check if All Type of Service is Available
function checkServices(id, uri) {

    let result = '';

    $.ajax({
        url: uri,
        type: 'POST',
        data: { case: 'service availability', id: id},
        success: function(response) {
            result = response;
        },
        async: false
    });

    return result;

}// function

function invalidImageType(element_id) {

    element_id.on('change', function() {

        let file = element_id.val().split(".");

        // Check file extension if image
        if(file[file.length - 1] == "jpg" || file[file.length - 1] == "jpeg" || file[file.length - 1] == "png") {}
        else {

            Swal.fire({
                position: 'top',
                icon: 'warning',
                title: 'Invalid File!',
                text: 'JPEG, JPG and PNG image file only.',
                showConfirmButton: true
            });

            $(this).val('');

        }// else 

    });// on change

}// invalid image type

function numberFormat(value) {
    var parts = value.toFixed(2).split(".");
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
}
