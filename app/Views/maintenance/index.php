<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Maintenance - <?= esc($app_name ?? 'Aplikasi CBT') ?></title>
	<meta content="width=device-width, initial-scale=1.0, user-scalable=no" name="viewport">

	<?php
	$logo_app = ($logo_kiri ?? null) == null ? base_url() . 'assets/img/favicon.png' : base_url() . $logo_kiri;
	?>
	<link rel="shortcut icon" href="<?= $logo_app ?>" type="image/x-icon">
	<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/bootstrap-icon/bootstrap-icons.css">
	<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/bootstrap/css/bootstrap.min.css">

	<style>
		:root {
			--mtc-primary: #0f172a;
			--mtc-primary-soft: #1e293b;
			--mtc-accent: #3b82f6;
			--mtc-accent-soft: #dbeafe;
			--mtc-bg: #f1f5f9;
			--mtc-card: #ffffff;
			--mtc-text: #0f172a;
			--mtc-text-muted: #64748b;
			--mtc-border: #e2e8f0;
			--mtc-success: #10b981;
			--mtc-warning: #f59e0b;
			--mtc-font: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
		}

		* {
			box-sizing: border-box;
		}

		html,
		body {
			margin: 0;
			padding: 0;
			background: var(--mtc-bg);
			min-height: 100vh;
			font-family: var(--mtc-font);
			color: var(--mtc-text);
			overflow-x: hidden;
		}

		body {
			display: flex;
			flex-direction: column;
		}

		/* ===== Header ===== */
		.mtc-header {
			background: linear-gradient(135deg, var(--mtc-primary) 0%, var(--mtc-primary-soft) 100%);
			color: #fff;
			padding: 2rem 1rem 3rem;
			position: relative;
			overflow: hidden;
		}

		.mtc-header::before {
			content: "";
			position: absolute;
			top: -50%;
			right: -10%;
			width: 400px;
			height: 400px;
			background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
			border-radius: 50%;
		}

		.mtc-header::after {
			content: "";
			position: absolute;
			bottom: -30%;
			left: -10%;
			width: 300px;
			height: 300px;
			background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
			border-radius: 50%;
		}

		.mtc-header .container {
			position: relative;
			z-index: 2;
		}

		.mtc-logo {
			width: 48px;
			height: 48px;
			object-fit: contain;
			filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
		}

		.mtc-brand {
			font-size: 1.25rem;
			font-weight: 700;
			letter-spacing: 0.02em;
		}

		.mtc-brand span {
			display: block;
			font-size: 0.8rem;
			font-weight: 400;
			opacity: 0.85;
			letter-spacing: 0.04em;
			text-transform: uppercase;
		}

		/* ===== Main ===== */
		.mtc-main {
			flex: 1;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 2rem 1rem;
		}

		.mtc-card {
			background: var(--mtc-card);
			border-radius: 1.5rem;
			border: 1px solid var(--mtc-border);
			box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
			width: 100%;
			max-width: 420px;
			overflow: hidden;
			animation: mtcFadeIn 0.7s ease-out;
		}

		@keyframes mtcFadeIn {
			from {
				opacity: 0;
				transform: translateY(16px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.mtc-card-body {
			padding: 2.5rem 2rem;
			text-align: center;
		}

		/* Icon */
		.mtc-icon {
			width: 80px;
			height: 80px;
			border-radius: 1rem;
			background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1.5rem;
			position: relative;
			box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3);
			animation: mtcPulse 2.5s infinite;
		}

		@keyframes mtcPulse {
			0%,
			100% {
				box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
			}

			50% {
				box-shadow: 0 0 0 12px rgba(245, 158, 11, 0);
			}
		}

		.mtc-icon i {
			color: #fff;
			font-size: 2.25rem;
		}

		/* Status badge */
		.mtc-status {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.4rem 1rem;
			border-radius: 2rem;
			background: #fef3c7;
			color: #92400e;
			font-size: 0.8rem;
			font-weight: 600;
			margin-bottom: 1.25rem;
			text-transform: uppercase;
			letter-spacing: 0.05em;
		}

		.mtc-status .dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: #f59e0b;
			animation: mtcBlink 1.5s infinite;
		}

		@keyframes mtcBlink {
			0%,
			100% {
				opacity: 1;
			}

			50% {
				opacity: 0.4;
			}
		}

		/* Heading */
		.mtc-title {
			font-size: 1.5rem;
			font-weight: 700;
			margin: 0 0 0.5rem;
			letter-spacing: -0.01em;
		}

		.mtc-subtitle {
			color: var(--mtc-text-muted);
			font-size: 0.95rem;
			margin: 0 0 1.75rem;
			line-height: 1.5;
		}

		/* Message box */
		.mtc-message {
			background: #fef3c7;
			border: 1px solid #fcd34d;
			color: #92400e;
			border-radius: 0.75rem;
			padding: 1rem 1.25rem;
			font-size: 0.9rem;
			text-align: left;
			margin-bottom: 1.75rem;
			line-height: 1.55;
		}

		.mtc-message i {
			margin-right: 0.5rem;
		}

		/* Countdown */
		.mtc-countdown-label {
			font-size: 0.75rem;
			text-transform: uppercase;
			letter-spacing: 0.1em;
			color: var(--mtc-text-muted);
			font-weight: 600;
			margin-bottom: 0.75rem;
			display: block;
		}

		.mtc-countdown {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 0.5rem;
			margin-bottom: 1.75rem;
		}

		.mtc-count-box {
			background: var(--mtc-bg);
			border: 1px solid var(--mtc-border);
			border-radius: 0.75rem;
			padding: 0.6rem 0.4rem;
			min-width: 56px;
		}

		.mtc-count-box .num {
			display: block;
			font-size: 1.5rem;
			font-weight: 800;
			color: var(--mtc-primary);
			line-height: 1;
			font-variant-numeric: tabular-nums;
		}

		.mtc-count-box .unit {
			display: block;
			font-size: 0.6rem;
			color: var(--mtc-text-muted);
			font-weight: 500;
			margin-top: 0.2rem;
		}

		.mtc-count-sep {
			font-size: 1.25rem;
			font-weight: 700;
			color: var(--mtc-text-muted);
			line-height: 1;
		}

		.mtc-done {
			display: none;
			margin-top: 1rem;
			font-size: 0.9rem;
			color: var(--mtc-success);
			font-weight: 600;
			animation: mtcFadeIn 0.5s ease-out;
		}

		/* Footer */
		.mtc-footer {
			background: #fff;
			border-top: 1px solid var(--mtc-border);
			padding: 1.25rem 1rem;
			text-align: center;
		}

		.mtc-footer-text {
			font-size: 0.8rem;
			color: var(--mtc-text-muted);
			margin: 0 0 0.25rem;
		}

		.mtc-footer-text small {
			font-size: 0.7rem;
			opacity: 0.7;
		}

		/* Utility */
		.mtc-visually-hidden {
			display: none;
		}

		/* Responsive */
		@media (max-width: 576px) {
			.mtc-header {
				padding: 1.5rem 1rem 2rem;
			}

			.mtc-card-body {
				padding: 2rem 1.5rem;
			}

			.mtc-count-box {
				min-width: 48px;
				padding: 0.5rem 0.3rem;
			}

			.mtc-count-box .num {
				font-size: 1.25rem;
			}

			.mtc-count-sep {
				font-size: 1rem;
			}
		}
	</style>
</head>

<body class="m-0 p-0">

	<!-- ===== Header ===== -->
	<header class="mtc-header">
		<div class="container">
			<div class="d-flex align-items-center gap-2 mb-3">
				<img src="<?= $logo_app ?>" alt="Logo" class="mtc-logo">
				<div>
					<div class="mtc-brand"><?= esc($app_name ?? 'Aplikasi CBT') ?></div>
					<?php if (!empty($sekolah)): ?>
						<span><?= esc($sekolah) ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<!-- ===== Main ===== -->
	<main class="mtc-main">
		<div class="mtc-card">
			<div class="mtc-card-body">
				<div class="mtc-icon">
					<i class="bi bi-cone-striped"></i>
				</div>

				<span class="mtc-status">
					<span class="dot"></span>
					Sedang Dalam Maintenance
				</span>

				<h2 class="mtc-title">Aplikasi Sedang Dalam Pemeliharaan</h2>
				<p class="mtc-subtitle">Mohon maaf atas ketidaknyamanannya. Tim kami sedang melakukan perbaikan dan pemeliharaan sistem.</p>

				<div class="mtc-message">
					<i class="bi bi-info-circle-fill"></i>
					<?= nl2br(esc($message ?? 'Aplikasi sedang dalam pemeliharaan. Mohon tunggu hingga selesai.')) ?>
				</div>

				<?php if (!empty($maintenance_until)): ?>
					<span class="mtc-countdown-label">Perkiraan selesai dalam</span>
					<div class="mtc-countdown">
						<div class="mtc-count-box">
							<span class="num" id="mtnHours">00</span>
							<span class="unit">Jam</span>
						</div>
						<span class="mtc-count-sep">:</span>
						<div class="mtc-count-box">
							<span class="num" id="mtnMinutes">00</span>
							<span class="unit">Menit</span>
						</div>
						<span class="mtc-count-sep">:</span>
						<div class="mtc-count-box">
							<span class="num" id="mtnSeconds">00</span>
							<span class="unit">Detik</span>
						</div>
					</div>
					<div id="mtnDone" class="mtc-done">
						<i class="bi bi-check-circle-fill me-1"></i>
						Maintenance telah selesai. Halaman dimuat ulang otomatis…
					</div>
				<?php endif; ?>
			</div>
		</div>
	</main>

	<!-- ===== Footer ===== -->
	<footer class="mtc-footer">
		<p class="mtc-footer-text">
			<?= esc($app_name ?? 'Aplikasi CBT') ?>
			<?php if (!empty($sekolah)): ?>
				<br>
				<small><?= esc($sekolah) ?></small>
			<?php endif; ?>
		</p>
	</footer>

	<script>
		(function () {
			var untilStr = '<?= esc(str_replace(' ', 'T', $maintenance_until ?? '')) ?>';
			if (!untilStr) {
				return;
			}

			var until = new Date(untilStr).getTime();
			if (isNaN(until)) {
				return;
			}

			var hEl = document.getElementById('mtnHours');
			var mEl = document.getElementById('mtnMinutes');
			var sEl = document.getElementById('mtnSeconds');
			var doneEl = document.getElementById('mtnDone');
			var reloaded = false;

			function pad(n) {
				return n < 10 ? '0' + n : '' + n;
			}

			function tick() {
				var diff = Math.floor((until - Date.now()) / 1000);

				if (diff <= 0) {
					hEl.textContent = '00';
					mEl.textContent = '00';
					sEl.textContent = '00';
					if (!reloaded) {
						reloaded = true;
						doneEl.style.display = 'block';
						setTimeout(function () {
							window.location.reload();
						}, 2000);
					}
					return;
				}

				var h = Math.floor(diff / 3600);
				var m = Math.floor((diff % 3600) / 60);
				var s = diff % 60;

				hEl.textContent = pad(h);
				mEl.textContent = pad(m);
				sEl.textContent = pad(s);
				setTimeout(tick, 1000);
			}

			tick();
		})();
	</script>
</body>

</html>
