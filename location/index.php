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
    <title>Location</title>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <?php require_once __DIR__ . '/../components/link.html'; ?>

    <style>

        /* 
        * Always set the map height explicitly to define the size of the div element
        * that contains the map. 
        */

        #map {
            height: 700px;
        }

        /* 
        * Optional: Makes the sample page fill the window. 
        */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

    </style>


</head>

<body class="hold-transition sidebar-mini layout-fixed">

    <div class="wrapper">

        <?php
            require_once __DIR__ . '/../components/navbar.php';
            require_once __DIR__ . '/../components/sidebar.php';
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div id="map"></div>
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
        displaySidebar(role, 'Location');

    });// document

    function callTheMap() {
        var map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 16.72064737228411, lng: 121.68951205356905 },
        zoom: 15,
        });
    }

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA92BUiQ713-42oouRif-PL27ub7siuYTc&libraries=visualization&callback=callTheMap" async defer></script>

<?php
    }else {
        header("location: ../objects/logout.php");
    }
?>