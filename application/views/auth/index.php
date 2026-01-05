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

	<div class="wrapper" style="position: relative; z-index: 1;">
		<div id="formContent" class="shadow-lg animate__animated animate__fadeInUp">
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
					<?= form_open("auth/login", array('id' => 'login', 'class' => 'login-form')); ?>
					<input type="hidden" name="login_variant" value="siswa">
					<input type="hidden" id="cbt-only" name="cbt-only" value="0">

					<!-- Modern Input Username -->
					<div
						class="input-container mb-3 d-flex align-items-center bg-white border rounded-pill px-3 py-2 shadow-sm">
						<i class="bi bi-person-circle text-secondary me-3" style="font-size: 1.25rem;"></i>
						<input type="text" class="form-control border-0 shadow-none bg-transparent p-0 text-dark"
							placeholder="Username" name="username" id="identity" autocomplete="off" required value=""
							oninvalid="this.setCustomValidity('Silakan masukan username Anda')"
							oninput="setCustomValidity('')">
						<div class="invalid-feedback"></div>
					</div>

					<!-- Modern Input Password -->
					<div
						class="input-container mb-3 d-flex align-items-center bg-white border rounded-pill px-3 py-2 shadow-sm">
						<i class="bi bi-lock-fill text-secondary me-3" style="font-size: 1.25rem;"></i>
						<input type="password" class="form-control border-0 shadow-none bg-transparent p-0 text-dark"
							placeholder="Password" name="password" id="password" autocomplete="off" required value=""
							oninvalid="this.setCustomValidity('Silakan masukan password Anda')"
							oninput="setCustomValidity('')">
						<span class="cursor-pointer text-muted ms-2" onclick="showPassword()" id="btn-eye"
							style="cursor: pointer;">
							<i class="btn-eye bi bi-eye" style="font-size: 1.25rem;"></i>
						</span>
						<div class="invalid-feedback"></div>
					</div>

					<div class="icheck-cyan" hidden>
						<input type='checkbox' checked="checked" />
						<label for="cbt-only">Login CBT</label>
					</div>
					<textarea class="d-none" id="data_uri" name="data_uri"></textarea>

					<div class="row mt-4">
						<div class="col-12">
							<?= form_submit('submit', 'Login', array('id' => 'submit', 'class' => 'btn btn-primary w-100 fw-bold py-2 rounded-pill', 'style' => 'font-size: 1rem; background-color: #00238f; border: none; box-shadow: 0 4px 6px rgba(0, 35, 143, 0.3);')); ?>
						</div>
					</div>

					<?= form_close(); ?>
				</div>

				<div id="loader">
					<img src='<?= base_url() ?>/assets/img/ajax-loader.gif' style="max-width: 100px;" />
				</div>
			</div>

		</div>
		<div id="infoMessage" style="width: 90%; max-width: 450px;">
			<?php if ($message != "") { ?>
				<div class="shadow p-3 bg-white rounded-3 text-danger mt-3 fw-bold text-center"><?= $message; ?></div>
			<?php } ?>
		</div>

	</div>
	<footer class="container text-center py-3 fixed-bottom" style="z-index: 2;">
		<small class="text-muted">COPYRIGHT &copy; <?php echo date('Y'); ?> | IKASMEDIA</small>
	</footer>

	<style>
		/* STRUCTURE */
		html,
		body {
			margin: 0 !important;
			padding: 0 !important;
			background-color: #f4f6f9;
			height: 100%;
			overflow: hidden;
			/* Prevent scrolling */
			font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
		}

		#loader {
			display: none;
			margin: 0;
			position: fixed;
			top: 50%;
			left: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
			z-index: 9999;
		}

		.wrapper {
			display: flex;
			align-items: center;
			flex-direction: column;
			justify-content: flex-start;
			width: 100%;
			height: 100vh;
			/* Fixed height match screen */
			padding: 20px;
			padding-top: 130px;
		}

		#formContent {
			border-radius: 15px;
			background: #fff;
			width: 90%;
			max-width: 450px;
			position: relative;
			padding: 0px;
			background-size: 300px;
			background-image: url('<?= base_url() ?>/assets/front_assets/images/logo-w.png');
			background-repeat: no-repeat;
			background-position: top right;
		}

		/* Modern Input Container Focus State */
		.input-container {
			transition: all 0.3s;
			background-color: #fff;
			/* Ensure white background */
			border: 1px solid #e9ecef !important;
		}

		.input-container:focus-within {
			border-color: #007bff !important;
			box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
		}

		input.form-control {
			height: auto;
			/* Auto height for flex center */
			font-size: 1rem;
		}

		::placeholder {
			color: #adb5bd;
			opacity: 1;
		}

		.btn-primary:hover {
			background-color: #001861 !important;
			transform: translateY(-1px);
		}

		/* ANIMATIONS */
		#infoMessage {
			transition: all 0.3s ease-in-out;
			font-weight: 500;
			overflow: hidden;
		}
	</style>

	<script src="<?= base_url() ?>/assets/app/js/jquery.backstretch.js"></script>

	<script type="text/javascript">
		let base_url = <?= json_encode(base_url()) ?>;
		let csrf_token_name = <?= json_encode($this->config->item('csrf_token_name')) ?>;
		let csrf_cookie_name = <?= json_encode($this->config->item('csrf_cookie_name')) ?>;

		// Helper function to get CSRF cookie
		function getCookie(name) {
			let cookieArray = document.cookie.split(';');
			for (let cookie of cookieArray) {
				let [key, value] = cookie.trim().split('=');
				if (key === name) {
					return decodeURIComponent(value);
				}
			}
			return null;
		}

		function ajaxcsrf() {
			var csrfname = csrf_token_name;
			var csrfhash = getCookie(csrf_cookie_name);
			var csrf = {};
			csrf[csrfname] = csrfhash;
			$.ajaxSetup({
				"data": csrf
			});
		}

		$(document).ready(function () {
			$('form#login input').on('change keycup', function () {
				$(this).removeClass('is-invalid');
				$(this).closest('.input-group').find('.invalid-feedback').text('');
			});

			$('form#login').on('submit', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				var infobox = $('#infoMessage');
				infobox.html('<div class="shadow p-3 bg-white rounded-3 text-info mt-3 fw-bold text-center">Checking...</div>');

				var btnsubmit = $('#submit');
				btnsubmit.attr('disabled', 'disabled').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...');

				$('#loader').show();

				ajaxcsrf();

				$.ajax({
					url: $(this).attr('action'),
					type: 'POST',
					data: $(this).serialize(),
					success: function (data) {
						$('#loader').fadeOut();
						btnsubmit.removeAttr('disabled').text('Login');

						//console.log('login', data);
						if (data.status) {
							infobox.html('<div class="shadow p-3 bg-white rounded-3 text-success mt-3 fw-bold text-center">' + (data.message || "Login Sukses. Anda akan dialihkan...") + '</div>');
							const respData = data.data || {};
							let go = respData.redirect ? respData.redirect : base_url + (respData.url || 'dashboard');
							// Check role explicitly if needed
							if (!respData.redirect && respData.role === 'siswa') {
								go = base_url + 'dashboard';
							}
							setTimeout(function () {
								window.location.href = go;
							}, 1000);
						} else {
							infobox.removeClass('alert-info').text(''); // Clear processing msg

							// Handle Field Errors
							if (data.errors) {
								$.each(data.errors, function (key, val) {
									let input = $('[name="' + key + '"]');
									if (val) {
										input.addClass('is-invalid');
										input.closest('.input-group').find('.invalid-feedback').text(val);
									} else {
										input.removeClass('is-invalid');
									}
								});
							}

							// Handle General Message
							if (data.message) {
								infobox.html('<div class="shadow p-3 bg-white rounded-3 text-danger mt-3 fw-bold text-center">' + data.message + '</div>');
							}
						}
					},
					error: function () {
						$('#loader').fadeOut();
						btnsubmit.removeAttr('disabled').text('Login');
						infobox.html('<div class="shadow p-3 bg-white rounded-3 text-danger mt-3 fw-bold text-center">Terjadi kesalahan server.</div>');
					}
				});
			});

		});

		function showPassword() {
			var input = $('#password');
			var icon = $('.btn-eye');
			if (input.attr('type') === 'password') {
				icon.removeClass('bi-eye').addClass('bi-eye-slash');
				input.attr('type', 'text');
			} else {
				icon.removeClass('bi-eye-slash').addClass('bi-eye');
				input.attr('type', 'password');
			}
		}
	</script>