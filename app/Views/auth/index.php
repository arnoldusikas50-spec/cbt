<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Login Siswa - Asesmen Berbasis Komputer</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

	<?php $logo_app = ($setting->logo ?? null) == null ? base_url() . 'assets/img/favicon.png' : base_url() . $setting->logo; ?>
	<link rel="shortcut icon" href="<?= $logo_app ?>" type="image/x-icon">
	<meta name="description"
		content="Aplikasi Asesmen Berbasis Komputer membantu sekolah dan siswa dalam pelaksanaan penilaian di sekolah.">
	<meta name="keyword"
		content="ANBK, AKM, Ujian, Ujian Nasional, Ulangan Harian, Ulangan Semester, Mid Semester, Asesmen Nasional, Ujian Sekolah">
	<link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/bootstrap-icon/bootstrap-icons.css">
	<link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/sweetalert2/sweetalert2.min.css">

	<script src="<?= base_url() ?>/assets/plugins/jquery/jquery.min.js"></script>
</head>

<body class="hold-transition m-0 p-0" style="background-color: #f4f6f9;">
	<div class="container-fluid text-white p-0"
		style="background:#00238f;background-size: contain;background-image: url('<?= base_url() ?>/assets/front_assets/images/header-bg.png');background-repeat: no-repeat;background-position: left; height:185px; position: fixed; top: 0; left: 0; width: 100%; z-index: 0;">
		<div class="row m-0">
			<div class="col-md-12 pt-5">
				<img class="img-fluid" style="position:absolute;left:50%;transform: translateX(-50%);"
					src="<?= base_url() ?>/assets/front_assets/images/logo.png" alt="DISDIK NTT">
			</div>
		</div>
	</div>

	<div class="wrapper" id="login-container" style="position: relative; z-index: 1;">
		<div id="formContent" class="shadow-lg anim-fade-up">
			<div class="text-start p-5">
				<div class="mb-2">
					<h3 class="fw-bold text-dark">Selamat Datang</h3>
				</div>
				<div class="mb-4 text-muted"><small class="text-muted">Silakan login menggunakan username dan password
						yang kamu
						miliki</small>
				</div>


				<!-- Username Login Form -->
				<div id="login-username">
					<form action="<?= site_url('auth/login') ?>" id="login" class="login-form" method="POST">
						<?= csrf_field() ?>
						<input type="hidden" name="login_variant" value="siswa">

						<!-- Underlined Input Username -->
						<div class="underlined-input-group mb-4">
							<i class="bi bi-person-circle me-3"></i>
							<input type="text" class="form-control" placeholder="Username" name="username" id="identity" autocomplete="off" required>
						</div>

						<!-- Underlined Input Password -->
						<div class="underlined-input-group mb-4">
							<i class="bi bi-lock-fill me-3"></i>
							<input type="password" class="form-control" placeholder="Password" name="password" id="password" autocomplete="off" required>
							<span class="ms-2" onclick="showPassword()" id="btn-eye" style="cursor: pointer;">
								<i class="btn-eye bi bi-eye-fill"></i>
							</span>
						</div>

						<div class="row mt-5">
							<div class="col-12 text-center">
								<button type="submit" id="submit" class="btn btn-login-pill w-100">Login</button>
							</div>
						</div>
					</form>
				</div>

			</div>

		</div>
		<div id="infoMessage" class="mt-3" style="width: 90%; max-width: 450px;">
			<?php if ($message != "") { ?>
				<div class="shadow p-3 bg-white rounded-3 text-danger fw-bold text-center"><?= $message; ?></div>
			<?php } ?>
		</div>

	</div>


	<style>
		:root {
			--primary: #3b82f6;
			--primary-dark: #2563eb;
			--gray-100: #f8fafc;
			--gray-200: #e2e8f0;
			--gray-500: #64748b;
			--gray-800: #1e293b;
		}

		html,
		body {
			margin: 0 !important;
			padding: 0 !important;
			background-color: #f4f6f9;
			height: 100%;
			overflow: hidden;
			font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
		}

		/* Local Animations */
		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(20px); }
			to { opacity: 1; transform: translateY(0); }
		}
		@keyframes shake {
			0%, 100% { transform: translateX(0); }
			25% { transform: translateX(-5px); }
			75% { transform: translateX(5px); }
		}
		.anim-fade-up { animation: fadeIn 0.6s ease-out forwards; }
		.anim-shake { animation: shake 0.4s ease-in-out; }

		.wrapper {
			display: flex;
			align-items: center;
			flex-direction: column;
			justify-content: flex-start;
			width: 100%;
			height: 100vh;
			padding: 20px;
			padding-top: 130px;
		}

		#formContent {
			border-radius: 1.25rem;
			background: #fff;
			width: 100%;
			max-width: 390px;
			position: relative;
			padding: 0;
			background-size: 230px;
			background-image: url('<?= base_url() ?>/assets/front_assets/images/logo-w.png');
			background-repeat: no-repeat;
			background-position: top right;
			border: 1px solid rgba(0,0,0,0.05);
			margin-top: 14px;
		}

		.underlined-input-group {
			display: flex;
			align-items: center;
			background-color: transparent;
			border-bottom: 2px solid var(--gray-200);
			padding: 0.58rem 0.2rem;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			position: relative;
		}

		.underlined-input-group:focus-within {
			border-bottom-color: var(--primary);
		}

		.underlined-input-group i {
			color: var(--gray-500);
			font-size: 1.2rem;
			transition: color 0.3s;
		}

		.underlined-input-group:focus-within i {
			color: var(--primary);
		}

		.underlined-input-group .form-control {
			border: none !important;
			background: transparent !important;
			box-shadow: none !important;
			padding: 0.2rem 0.4rem;
			font-size: 0.95rem;
			color: var(--gray-800);
			font-weight: 500;
		}

		.underlined-input-group .form-control::placeholder {
			color: var(--gray-500);
			font-weight: 400;
			opacity: 0.7;
		}

		.btn-login-pill {
			background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
			color: white;
			border: none;
			border-radius: 50px;
			padding: 0.75rem;
			font-size: 0.95rem;
			font-weight: 600;
			transition: all 0.3s;
			box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
		}

		.btn-login-pill:hover {
			transform: translateY(-2px);
			box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.4);
			color: white;
		}

		.btn-login-pill:active {
			transform: translateY(0);
		}

		.alert-modern {
			border-radius: 12px;
			padding: 0.85rem;
			font-size: 0.9rem;
			font-weight: 500;
			border: none;
		}

		.alert-modern.alert-error { background: #fee2e2; color: #991b1b; }
		.alert-modern.alert-info { background: #e0f2fe; color: #0369a1; }
		.alert-modern.alert-success { background: #dcfce7; color: #166534; }


	</style>

	<script src="<?= base_url() ?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="<?= base_url() ?>/assets/plugins/sweetalert2/sweetalert2.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function () {
			let base_url = '<?= base_url(); ?>';
			console.log('Base URL:', base_url);

			console.log('Form Action:', $('form#login').attr('action'));

			$('form#login').on('submit', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				var btnsubmit = $('#submit');
				var originalBtnHtml = btnsubmit.html();
				btnsubmit.attr('disabled', 'disabled').html('<span class="spinner-border spinner-border-sm me-2"></span> Mohon menunggu...');

				$.ajax({
					url: $(this).attr('action'),
					type: 'POST',
					data: $(this).serialize(),
					dataType: 'json',
					success: function (data) {
						if (data.status) {
							let go = data.data?.redirect ? data.data.redirect : base_url + (data.data?.url || 'dashboard');
							setTimeout(() => window.location.href = go, 800);
						} else {
							btnsubmit.removeAttr('disabled').html(originalBtnHtml);
							if (data.errors) {
								$.each(data.errors, (key, val) => {
									if (val) $('[name="' + key + '"]').closest('.underlined-input-group').css('border-bottom-color', '#ef4444');
								});
							}
							$('#infoMessage').html('<div class="alert-modern alert-error anim-shake"><i class="bi bi-exclamation-triangle-fill me-2"></i> ' + (data.message || 'Ups! Ada yang salah.') + '</div>');
						}
					},
					error: function (xhr) {
						btnsubmit.removeAttr('disabled').html(originalBtnHtml);
						let msg = xhr.status === 403 ? 'Sesi habis, yuk refresh.' : 'Gagal terhubung ke server.';
						$('#infoMessage').html('<div class="alert-modern alert-error anim-shake"><i class="bi bi-wifi-off me-2"></i> ' + msg + '</div>');
					}
				});
			});

			$('input').on('focus input', function () {
				$(this).closest('.underlined-input-group').css('border-bottom-color', 'var(--gray-200)');
			});
		});

		function showPassword() {
			var input = $('#password');
			var icon = $('#btn-eye i');
			if (input.attr('type') === 'password') {
				icon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
				input.attr('type', 'text');
			} else {
				icon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
				input.attr('type', 'password');
			}
		}
	</script>
</body>

</html>
