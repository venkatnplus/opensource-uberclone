
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png"  href="{{ settingValue('mini_logo') ? settingValue('mini_logo') : asset('backend/Logo.jpg') }}">   
    <title> {{ settingValue('application_name') ? settingValue('application_name') : config('app.name', 'Taxi Appz') }}</title>

    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend/global_assets/css/icons/icomoon/styles.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend/assets/css/bootstrap_limitless.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend/assets/css/layout.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend/assets/css/components.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend/assets/css/colors.min.css') }}" rel="stylesheet" type="text/css">
    <!-- /global stylesheets -->

    <!-- Core JS files -->
    <script src="{{ asset('backend/global_assets/js/main/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/global_assets/js/main/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/global_assets/js/plugins/loaders/blockui.min.js') }}"></script>
    <script src="{{ asset('backend/global_assets/js/plugins/ui/ripple.min.js') }}"></script>
    <!-- /core JS files -->

    <!-- Theme JS files -->
    <script src="{{ asset('backend/global_assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script src="{{ asset('backend/assets/js/app.js') }}"></script>
    <script src="{{ asset('backend/global_assets/js/demo_pages/login.js') }}"></script>
    <!-- /theme JS files -->
    <style>
       .icon-eye-blocked {
        float: right;
        margin-left: -25px;
        margin-top: -25px;
        position: relative;
        z-index: 2;
        }

        .container{
        padding-top:50px;
        margin: auto;
        }
    </style>

</head>

<body class="bg-slate-800">

    <!-- Page content -->
    <div class="page-content">

        <!-- Main content -->
        <div class="content-wrapper">
            
            <!-- Content area -->
            <div class="content d-flex justify-content-center align-items-center">

				<!-- Password recovery form -->
				<form method="POST" class="login-form" action="{{ route('otp.check') }}">
                @csrf
                @if ($message = Session::get('success'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>    
                    <strong>{{ $message }}</strong>
                </div>
                @endif
					<div class="card mb-0">
						<div class="card-body">
							<div class="text-center mb-3">
								<i class="icon-spinner11 icon-2x text-warning border-warning border-3 rounded-round p-3 mb-3 mt-1"></i>
								<h5 class="mb-0">Password recovery OTP</h5>
								<span class="d-block text-muted">Enter the OTP to Reset the Password</span>
							</div>

							<div class="form-group form-group-feedback form-group-feedback-right">
								<input type="number" id="otp" name="otp" value="{{ old('otp') }}" class="form-control" placeholder="OTP">
                                <input type="hidden" id="slug" name="slug" value="{{ $slug }}">
								<div class="form-control-feedback">
									<i class="icon-mail5 text-muted"></i>
								</div>
                                @if ($errors->has('otp'))
                                    <span class="form-text text-danger">{{ $errors->first('otp') }}</span>
                                @endif
							</div>

							<button type="submit" class="btn bg-blue btn-block"><i class="icon-spinner11 mr-2"></i> Reset password</button>
						</div>
					</div>
				</form>
				<!-- /password recovery form -->

			</div>
            <!-- /content area -->

        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->

</body>


</html>




