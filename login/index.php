<?php

	include_once __DIR__ . '/../objects/session.php';

	$SES = SESSION::getInstance();

	if($SES->id == null) {
		$SES->destroy();
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

  <style>
	#caps-pass {
		display: none;
		color: red;
		font-weight: bold;
	}
  </style>
</head>

<body class="hold-transition login-page">

	<div class="row w-100">

	<!-- Left Column -->
	<div class="col-6 d-flex align-items-center justify-content-center">

		<div class="login-box">
			<div class="login-logo">
				<a href="#"><b>Resource Generation Management Office </b><i>(RGMO)</i></a>
				<div class="row"><a href="#"><b>Rental Services Monitoring System</b></a></div>
			</div>
			<!-- /.login-logo -->
			<div class="card">
				<div class="card-body login-card-body">
					<p class="login-box-msg"><b>Log in to your account</b></p>

					<form id="login-user">
						<div class="input-group mb-3">
							<input type="email" name="_email" class="form-control" placeholder="Email">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-envelope"></span>
								</div>
							</div>
						</div>
						<div class="input-group mb-3">
							<input type="password" id="_pass" name="_pass" class="form-control" placeholder="Password">
							<div class="input-group-append">
								<div class="input-group-text">
								<i class="fas fa-eye" id="togglePass" style="cursor: pointer;"></i>
								</div>
							</div>
						</div>
						<p id="caps-pass">Caps Lock is ON</p>

						<div class="row">

							<div class="col-8">
								<p class="mb-2"></p>
								<p class="mb-0">
									<a href="../register" class="text-center">Not a Client? Register here</a>
								</p>
							</div>

							<div class="col-4">
								<button type="submit" class="btn btn-primary btn-block">Sign In</button>
							</div>

						</div>
						<!-- /.row -->
					</form>

					<!-- <p class="mb-1">
						<a href="forgot-password.html">I forgot my password</a>
					</p> -->
					

				</div>
				<!-- /.login-card-body -->

			</div>
		</div>
		<!-- /.login-box -->

	</div>
	<!-- left column end -->

	<!-- Right Column -->
	<div class="col-6">
		<img src="../includes/images/4.jpg" alt="Sample Pic">
	</div>
	<!-- right column end -->

	</div>
	<!-- /.row -->


<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.min.js"></script>
<!-- Sweet Alert-->
<script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- Password Show or Hide -->
<script src="../components/pass-show-hide.js"></script>

<script>

	$(document).ready(function() {

		$('#login-user').on('submit', function(e) {

			let formData = new FormData(this);
			e.preventDefault();

			$.ajax({
				url: '../controller/LoginController.php',
				type: 'POST',
				processData: false,
				contentType: false,
				data: formData,
				success: function(response) {

					// If Login is Successful
					if(response.status) {

						Swal.mixin({
							toast: true, position: 'top-end', showConfirmButton: false
						}).fire({ 
							icon: 'success', title: 'Login Successful'
						});
		
						setTimeout(function() {
							window.location.href = '../dashboard/';
						},1000);

					}
					// If not
					else {

						let error = response.message.split("-");

						Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 })
							.fire({ icon: error[1], title: error[0] });

					}
				}
			});// ajax

		});// submit

	});// document ready

</script>

</body>
</html>
