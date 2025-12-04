
<div class="wrapper wrapper-admin">
	<div class="admin-shell container">
		<div class="row justify-content-center">
			<div class="col-lg-10 col-xl-8">
				<div class="admin-login-card">
					<div class="row no-gutters">
						<div class="col-lg-6 admin-image-side">
							<div class="admin-image-content">

								<h3 class="admin-image-title">CBT DISDIK NTT</h3>
								<p class="admin-image-text">"Ujian lebih mudah dan efisien dengan sistem CBT yang dapat diakses melalui PC dan Android. Dirancang untuk memberikan pengalaman tes yang cepat, aman, dan akurat."</p>

								<div class="admin-image-support">
									<div class="admin-support__icon mr-3"><i class="fa fa-life-ring"></i></div>
									<div>
										<small class="text-uppercase text-white">Butuh bantuan?</small>
										<div class="font-weight-semibold text-white">Hubungi operator sekolah</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 admin-form-side">
							<div id="formContent" class="admin-form-card">
								<div class="admin-form-header">
									<div class="text-center">
										<div class="admin-login-logo mb-2">
											<img src="<?= base_url('uploads/settings/logo_kiri.png'); ?>" alt="Logo Sekolah" class="admin-login-logo-img">
										</div>
										<h4 class="mb-1">L O G I N</h4>
										<p class="mb-0 text-muted">Silahkan masukkan Username dan Password Anda.</p>
									</div>
								</div>
								<div id="infoMessage" class="admin-info mb-1"> <?php echo $message; ?> </div>

								<?= form_open("auth/login", array('id' => 'login', 'class' => 'login-form')); ?>
								<input type="hidden" name="login_variant" value="<?= $login_variant ?? 'staff'; ?>">
								<input type="hidden" id="cbt-only" name="cbt-only" value="<?= $cbt_only_default ?? '0'; ?>">

								<div class="form-group mb-1">
									<label for="identity" class="admin-label">Username</label>
									<div class="admin-input">
										<i class="admin-input__icon fa fa-user-circle"></i>
										<input type="text" class="form-control" placeholder="Username" name="username" id="identity" autocomplete="off" required="true" value="" oninvalid="this.setCustomValidity('Silakan masukan username Anda')" oninput="setCustomValidity('')">
									</div>
								</div>
								<div class="form-group mb-1">
									<label for="password" class="admin-label">Password</label>
									<div class="admin-input">
										<i class="admin-input__icon fa fa-lock"></i>
										<input type="password" class="form-control" placeholder="Password" name="password" id="password" autocomplete="false" required="true" value="" oninvalid="this.setCustomValidity('Silakan masukan password Anda')" oninput="setCustomValidity('')">
										<button type="button" class="admin-input__action" onClick="showPassword()" id="btn-eye"><i class="btn-eye fa fa-eye"></i></button>
									</div>
								</div>

								<?php if (!empty($show_mode_selector)): ?>
								<div class="custom-control custom-switch admin-switch">
									<input type='checkbox' class="custom-control-input" id="toggle-cbt" <?= ($cbt_only_default ?? '0') === '1' ? 'checked' : ''; ?> />
									<label class="custom-control-label d-none" for="toggle-cbt">Mode CBT saja</label>
								</div>
								<?php endif; ?>
								<textarea class="d-none" id="data_uri" name="data_uri"></textarea>

								<div class="d-flex justify-content-between align-items-center mt-2 flex-column flex-md-row gap-3">
									<div class="w-100 text-md-right text-center mt-3 mt-md-0">
										<?= form_submit('submit', 'Masuk', array('id' => 'submit', 'class' => 'btn btn-info px-4 btn-block btn-md-inline', 'style' => 'font-size: 1rem')) ; ?>
									</div>
								</div>
								<div class="text-center mt-3">
									<small class="text-muted">COPYRIGHT &copy; <?php $tgl=date('Y'); echo $tgl; ?> | IKASMEDIA</small>
								</div>
								<?= form_close(); ?>
						</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	:root {
		--admin-primary: #0f75ff;
		--admin-dark: #031b4e;
		--admin-gradient: radial-gradient(circle at top left, #5dd2ff 0%, rgba(93, 210, 255, 0) 55%), linear-gradient(135deg, #001b86 0%, #0037c4 60%, #0055ff 100%);
	}

	.loader {
		margin: 0;
		position: absolute;
		top: 50%;
		left: 50%;
		-ms-transform: translate(-50%, -50%);
		transform: translate(-50%, -50%);
	}

	.wrapper-admin {
		background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 30%, #e9ecef 60%, #f8f9fa 100%);
		background-attachment: fixed;
		padding: 60px 0 80px;
		position: relative;
		overflow: hidden;
		min-height: 100vh;
		width: 100vw;
		margin: 0;
		left: 0;
		top: 0;
	}

	
	.admin-login-card {
		border-radius: 24px;
		overflow: hidden;
		box-shadow: 0 25px 50px rgba(3, 27, 78, 0.15);
		backdrop-filter: blur(20px);
		background: rgba(255, 255, 255, 0.1);
		border: 1px solid rgba(255, 255, 255, 0.2);
		min-height: 500px;
	}

	.admin-image-side {
		background-image: linear-gradient(135deg, rgba(3, 27, 78, 0.6), rgba(15, 117, 255, 0.4)), url('<?= base_url() ?>/assets/img/side-bg.png');
		background-size: cover;
		background-position: center;
		background-blend-mode: multiply;
		position: relative;
		display: flex;
		align-items: center;
		box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.2);
	}

	.admin-image-content {
		padding: 40px;
		color: #fff;
		position: relative;
		z-index: 2;
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.admin-image-content::before {
		content: '';
		position: absolute;
		inset: 0;
		background: radial-gradient(circle at top left, rgba(93, 210, 255, 0.35), transparent 55%);
		z-index: -1;
	}

	.admin-logo-section {
		text-align: center;
		margin-bottom: 24px;
	}

	.admin-logo {
		max-width: 120px;
		max-height: 120px;
		border-radius: 16px;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
		background: rgba(255, 255, 255, 0.1);
		padding: 8px;
		backdrop-filter: blur(10px);
		border: 2px solid rgba(255, 255, 255, 0.2);
		transition: transform 0.3s ease, box-shadow 0.3s ease;
	}

	.admin-logo:hover {
		transform: scale(1.05);
		box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
	}

	.admin-logo-placeholder {
		width: 120px;
		height: 120px;
		border-radius: 16px;
		background: rgba(255, 255, 255, 0.1);
		backdrop-filter: blur(10px);
		border: 2px solid rgba(255, 255, 255, 0.2);
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 0 auto;
		font-size: 3rem;
		color: rgba(255, 255, 255, 0.8);
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
	}

	.admin-image-title {
		font-size: 1.8rem;
		font-weight: 700;
		margin-bottom: 12px;
	}

	.admin-image-text {
		color: rgba(255, 255, 255, 0.85);
		margin-bottom: 24px;
	}

	.admin-image-stats {
		display: flex;
		justify-content: space-between;
		gap: 16px;
		flex-wrap: wrap;
		margin: 24px 0;
	}

	.admin-image-support {
		display: flex;
		align-items: center;
		margin-top: auto;
	}

	.admin-form-side {
		background: rgba(255, 255, 255, 0.3);
		display: flex;
		align-items: center;
		padding: 20px;
		backdrop-filter: blur(8px);
		border: 1px solid rgba(255, 255, 255, 0.3);
	}

	.admin-form-card {
		border-radius: 16px;
		background: rgba(255, 255, 255, 0.4);
		border: 1px solid rgba(255, 255, 255, 0.5);
		box-shadow: 0 2px 8px rgba(31, 38, 135, 0.1);
		padding: 32px;
		width: 100%;
		position: relative;
		backdrop-filter: blur(10px);
	}

	.admin-form-header {
		display: flex;
		justify-content: center;
		align-items: center;
		margin-bottom: 32px;
	}

	.admin-form-header h4 {
		color: #031b4e;
		text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
		font-weight: 700;
	}

	.admin-form-header p {
		color: #4f5d75;
		text-shadow: 0 1px 2px rgba(255, 255, 255, 0.6);
	}

	.admin-login-logo {
		text-align: center;
		margin-bottom: 20px;
	}

	.admin-login-logo-img {
		width: 80px;
		height: 80px;
		border-radius: 12px;
		box-shadow: 0 6px 20px rgba(15, 117, 255, 0.2);
		background: rgba(255, 255, 255, 0.9);
		padding: 6px;
		backdrop-filter: blur(8px);
		border: 2px solid rgba(15, 117, 255, 0.15);
		transition: transform 0.3s ease, box-shadow 0.3s ease;
	}

	.admin-login-logo-img:hover {
		transform: scale(1.05);
		box-shadow: 0 8px 25px rgba(15, 117, 255, 0.3);
	}

	.admin-form-avatar {
		width: 54px;
		height: 54px;
		border-radius: 50%;
		background: linear-gradient(135deg, var(--admin-primary), #00d4ff);
		color: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 1.3rem;
		box-shadow: 0 8px 16px rgba(15, 117, 255, 0.3);
		animation: pulse 2s infinite;
	}

	.admin-metrics__label {
		display: block;
		font-size: 0.8rem;
		letter-spacing: 0.8px;
		color: rgba(255, 255, 255, 0.7);
	}

	.admin-support__icon {
		width: 42px;
		height: 42px;
		background: rgba(255, 255, 255, 0.15);
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #fff;
		backdrop-filter: blur(10px);
		border: 1px solid rgba(255, 255, 255, 0.2);
		animation: float 3s ease-in-out infinite;
	}

	@keyframes float {
		0%, 100% {
			transform: translateY(0px);
		}
		50% {
			transform: translateY(-10px);
		}
	}

	@keyframes pulse {
		0%, 100% {
			transform: scale(1);
			box-shadow: 0 8px 16px rgba(15, 117, 255, 0.3);
		}
		50% {
			transform: scale(1.05);
			box-shadow: 0 12px 24px rgba(15, 117, 255, 0.4);
		}
	}

	.admin-label {
		font-weight: 600;
		color: #031b4e;
		font-size: 0.9rem;
		text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
	}

	.admin-input {
		position: relative;
		display: flex;
		align-items: center;
	}

	.admin-input__icon {
		position: absolute;
		left: 16px;
		color: var(--admin-primary);
		font-size: 1.1rem;
		background: rgba(15, 117, 255, 0.1);
		padding: 8px;
		border-radius: 8px;
		z-index: 10;
	}

	.admin-input .form-control {
		padding-left: 60px;
		border-radius: 16px;
		border: 1px solid rgba(3, 27, 78, 0.15);
		padding-top: 0.9rem;
		padding-bottom: 0.9rem;
		transition: all 0.3s ease;
		background: rgba(255, 255, 255, 0.9);
		backdrop-filter: blur(5px);
		color: #031b4e;
		font-weight: 500;
	}

	.admin-input .form-control:focus {
		box-shadow: 0 0 0 0.2rem rgba(15, 117, 255, 0.12);
		border-color: var(--admin-primary);
		transform: translateY(-2px);
		box-shadow: 0 8px 25px rgba(15, 117, 255, 0.15);
	}

	.admin-input__action {
		position: absolute;
		right: 12px;
		border: none;
		background: none;
		color: #6c7d99;
		font-size: 1rem;
		padding: 8px;
		cursor: pointer;
	}

	.admin-highlight {
		background: rgba(15, 117, 255, 0.12);
		border-radius: 16px;
		border: 1px solid rgba(15, 117, 255, 0.2);
		backdrop-filter: blur(15px);
		background: rgba(15, 117, 255, 0.08);
		padding: 12px 16px;
	}

	.badge-soft-primary {
		background: rgba(255, 255, 255, 0.15);
		color: #fff;
		font-weight: 600;
		padding: 6px 16px;
		border-radius: 999px;
		letter-spacing: 1px;
		text-transform: uppercase;
		font-size: 0.75rem;
	}

	.btn-info {
		background: linear-gradient(135deg, var(--admin-primary), #00d4ff);
		border: none;
		border-radius: 16px;
		box-shadow: 0 10px 20px rgba(15, 117, 255, 0.3);
		transition: all 0.3s ease;
		position: relative;
		overflow: hidden;
	}

	.btn-info::before {
		content: '';
		position: absolute;
		top: 0;
		left: -100%;
		width: 100%;
		height: 100%;
		background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
		transition: left 0.5s;
	}

	.btn-info:hover::before {
		left: 100%;
	}

	.btn-info:hover {
		transform: translateY(-2px);
		box-shadow: 0 15px 30px rgba(15, 117, 255, 0.4);
	}

	.admin-switch label {
		color: #4f5d75;
		font-weight: 500;
	}

	.auth-footer {
		padding: 12px 0;
		margin-top: 24px;
		font-size: 0.85rem;
		color: #6c7d99;
	}

	@media (max-width: 991px) {
		.admin-image-side {
			display: none;
		}
		.admin-form-side {
			padding: 10px;
		}
		.admin-login-card {
			margin: 0 15px;
		}
	}

	@media (max-width: 576px) {
		.admin-visual-card__content,
		.admin-card {
			padding: 24px;
		}
	}
</style>

<script src="<?= base_url() ?>/assets/app/js/jquery.backstretch.js"></script>

<script type="text/javascript">
	let base_url = '<?=base_url();?>';

	$(document).ready(function(){
		$('#myCarousel').carousel({
			interval: 1000 * 2,
			pause: 'none'
		});

		const $cbtToggle = $('#toggle-cbt');
		const $cbtField = $('#cbt-only');

		if ($cbtToggle.length) {
			$cbtToggle.on('change', function(){
				$cbtField.val(this.checked ? '1' : '0');
			});
		}

		const $infoMessage = $('#infoMessage');
		const baseAlertClasses = 'alert mt-3 text-center';

		$('form#login input').on('change', function(){
			$(this).closest('.form-group').removeClass('has-error');
		});

		$('form#login').on('submit', function(e){
			e.preventDefault();
			e.stopImmediatePropagation();

			$infoMessage.removeAttr('class').addClass(baseAlertClasses + ' alert-info').text('Checking...');

			var btnsubmit = $('#submit');
			btnsubmit.attr('disabled', 'disabled').val('Wait...');

			const arrForm = $(this).serializeArray()
			const cbtOnly = arrForm.find(function (obj) {
				return obj.name === 'cbt-only'
			})
			localStorage.setItem('garudaCBT.login', cbtOnly !== undefined ? '1' : '0')

			$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			data: $(this).serialize(),
			success: function(data){
				$infoMessage.removeAttr('class').text('');
				btnsubmit.removeAttr('disabled').val('Masuk');
				if(data.status){
					$infoMessage.addClass(baseAlertClasses + ' alert-success').text(data.message || 'Login Sukses');

					const isLogin = localStorage.getItem('garudaCBT.login')
					const isCbtMode = isLogin ? isLogin === '1' : false
					const respData = data.data || {};
					let go = respData.redirect ? respData.redirect : base_url + (respData.url || 'dashboard');
					if (!respData.redirect && isCbtMode && data.role === 'siswa') {
						go = 'siswa/cbt'
					}
					window.location.href = go;
				}else{
					if(data.errors){
						$.each(data.errors, function(key, val){
						const $input = $('[name="'+key+'"');
						$input.closest('.form-group').toggleClass('has-error', !!val);
						});
					}
					if(data.message){
						$infoMessage.addClass(baseAlertClasses + ' alert-danger').text(data.message);
					}
				}
			}
			});
		});

		const today = new Date();
		const formatted = today.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
		$('#admin-current-date').text(formatted);
	});
</script>


<script>
	function showPassword() {
		var type = $('#password').attr('type');
		if (type ==='password') {
			$('.btn-eye').removeClass('fa fa-eye');
			$('.btn-eye').addClass('fa fa-eye-slash');	
			$('#password').attr('type','text');
		}
		else {
			$('.btn-eye').removeClass('fa fa-eye-slash');
			$('.btn-eye').addClass('fa fa-eye');	
			$('#password').attr('type','password');
		}
	}
</script>
  

</body>
</html>
