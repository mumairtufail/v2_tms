<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title>Reset Password</title>
	<meta name="description" content="Dashboard" />
	
	<!-- Favicon -->
	<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
	<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
	
	<!-- Toggles CSS -->
	<link href="{{ asset('vendors/jquery-toggles/css/toggles.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('vendors/jquery-toggles/css/themes/toggles-light.css') }}" rel="stylesheet" type="text/css">
	
	<!-- Custom CSS -->
	<link href="{{ asset('dist/css/style.css') }}" rel="stylesheet" type="text/css">
</head>
<body>
	<!-- Preloader -->
	<div class="preloader-it">
		<div class="loader-pendulums"></div>
	</div>
	<!-- /Preloader -->
	
	<!-- HK Wrapper -->
	<div class="hk-wrapper">
		
		<!-- Main Content -->
		<div class="hk-pg-wrapper hk-auth-wrapper">
			<header class="d-flex justify-content-end align-items-center">
			</header>
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12 pa-0">
						<div class="auth-form-wrap pt-xl-0 pt-70">
							<div class="auth-form w-xl-30 w-lg-55 w-sm-75 w-100">
								<a class="auth-brand text-center d-block mb-20" href="#">
									<!-- <h2 class="brand-text">TMS</h2> -->
								</a>
								<form method="POST" action="{{ route('password.update') }}">
									@csrf
									<input type="hidden" name="token" value="{{ $token }}">
									<h1 class="display-4 text-center mb-10">Reset Password</h1>
									<p class="text-center mb-30">Enter your new password below.</p>
									<div class="form-group">
										<input class="form-control" type="email" name="email" value="{{ old('email', $email ?? '') }}" required placeholder="Email">
									</div>
									<div class="form-group">
										<input class="form-control" type="password" name="password" required placeholder="New Password">
									</div>
									<div class="form-group">
										<input class="form-control" type="password" name="password_confirmation" required placeholder="Confirm Password">
									</div>
									<button class="btn btn-primary btn-block" type="submit">Reset Password</button>
									@if ($errors->any())
										<div class="alert alert-danger mt-20">
											@foreach ($errors->all() as $error)
												<div>{{ $error }}</div>
											@endforeach
										</div>
									@endif
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Main Content -->
	
	</div>
	<!-- /HK Wrapper -->
	
	<!-- JavaScript -->
	
	<!-- jQuery -->
	<script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
	
	<!-- Bootstrap Core JavaScript -->
	<script src="{{ asset('vendors/popper.js/dist/umd/popper.min.js') }}"></script>
	<script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
	
	<!-- Slimscroll JavaScript -->
	<script src="{{ asset('dist/js/jquery.slimscroll.js') }}"></script>

	<!-- Fancy Dropdown JS -->
	<script src="{{ asset('dist/js/dropdown-bootstrap-extended.js') }}"></script>
	
	<!-- FeatherIcons JavaScript -->
	<script src="{{ asset('dist/js/feather.min.js') }}"></script>
	
	<!-- Init JavaScript -->
	<script src="{{ asset('dist/js/init.js') }}"></script>
</body>
</html>
