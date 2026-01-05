<!-- Add Signature Pad -->
<script src="<?= base_url('assets/plugins/signature/signature_pad.min.js') ?>"></script>

<main class="py-4 main-content-padding-fix">
	<?php
	if (isset($jadwal)) {
		$bank = $jadwal->bank;
		$mapel = $bank->mapel;
		$bank_kelas = $bank->bank_kelas;
	}
	?>

	<div class="container-fluid container-p-x" style="position: relative; z-index: 1000; margin-top: 1rem;">
		<div class="row justify-content-center">
			<div class="col-md-10 col-lg-7">

				<!-- Section 1: Konfirmasi Data Peserta -->
				<?php if (isset($jadwal)): ?>
					<div id="konfirm_data" style="display:block;">
						<div class="card card-modern overflow-hidden border-0">
							<div class="p-4 bg-white border-bottom">
								<div class="d-flex align-items-center justify-content-center text-center">
									<div>
										<div class="icon-box bg-light-primary text-primary rounded-circle mx-auto mb-3">
											<i class="bi bi-person-vcard fs-3"></i>
										</div>
										<h4 class="fw-bold mb-1 col-12">Konfirmasi Data</h4>
										<p class="text-muted mb-0">Pastikan data di bawah ini adalah data Anda.</p>
									</div>
								</div>
							</div>
							<div class="card-body p-4">
								<div class="row g-4">
									<div class="col-12">
										<div class="bg-light p-4 rounded-4 border border-dashed text-center">
											<div class="row g-4">
												<div class="col-md-6 border-end-md">
													<small class="text-uppercase text-muted fw-bold ls-1">Mata
														Pelajaran</small>
													<h5 class="fw-bold text-dark mt-1 mb-0">
														<?= htmlspecialchars($mapel->nama_mapel) ?>
													</h5>
												</div>
												<div class="col-md-6">
													<small class="text-uppercase text-muted fw-bold ls-1">Kelas</small>
													<h5 class="fw-bold text-dark mt-1 mb-0">
														<?= htmlspecialchars($siswa->kode_kelas) ?>
													</h5>
												</div>
												<div class="col-12">
													<hr class="my-0 dashed">
												</div>
												<div class="col-md-6 border-end-md">
													<small class="text-uppercase text-muted fw-bold ls-1">Nama
														Peserta</small>
													<h5 class="fw-bold text-dark mt-1 mb-0">
														<?= htmlspecialchars($siswa->nama) ?>
													</h5>
												</div>
												<div class="col-md-6">
													<small class="text-uppercase text-muted fw-bold ls-1">NISN</small>
													<h5 class="fw-bold text-dark mt-1 mb-0">
														<?= htmlspecialchars($siswa->nisn) ?>
													</h5>
												</div>
											</div>
										</div>
									</div>

									<div class="col-12 mt-4">
										<div class="d-flex gap-3">
											<a href="<?= base_url() ?>cbtsiswa/cbt"
												class="btn btn-primary btn-lg rounded-pill w-100 fw-bold border hover-scale"
												style="color: #64748b;">
												<i class="bi bi-arrow-left me-2"></i> Batal
											</a>
											<button type="button"
												class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-lg hover-scale btn-gradient"
												onclick="konfirmasiData()">
												Lanjutkan <i class="bi bi-arrow-right ms-2"></i>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<!-- Section 2: Form Verifikasi & TTD -->
				<?php if (isset($jadwal)): ?>
					<div id="konfirm_test" style="display:none;">
						<div class="row g-4">
							<!-- Form Column -->
							<div class="col-lg-7">
								<div class="card card-modern h-100 border-0">
									<div class="card-header bg-white border-bottom-0 pt-4 px-4">
										<h5 class="fw-bold text-dark mb-0"><i
												class="bi bi-shield-check text-primary me-2"></i>Verifikasi Diri</h5>
									</div>
									<div class="card-body p-4">
										<?php if ($support && $valid): ?>
											<?= form_open('', array('id' => 'konfir')) ?>
											<input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?>">
											<input type="hidden" name="jadwal" value="<?= $jadwal->id_jadwal ?>">
											<input type="hidden" name="bank" value="<?= $jadwal->bank->id_bank ?>">

											<div class="mb-3">
												<label class="form-label small fw-bold text-muted text-uppercase">Ketik Nama
													Lengkap</label>
												<div class="input-group input-group-fixed">
													<span
														class="input-group-text input-group-text-modern bg-white input-group-icon-fixed">
														<i class="bi bi-person text-muted"></i>
													</span>
													<input type="text"
														class="form-control form-control-modern ps-2 input-control-fixed"
														placeholder="Sesuai kartu peserta..." required name="nama_peserta"
														id="nama_peserta" oninput="this.setCustomValidity('')"
														oninvalid="this.setCustomValidity('Wajib diisi')">
												</div>
												<div id="nama_peserta_error" class="text-danger small mt-1 fw-bold"></div>
											</div>

											<div class="mb-3">
												<label class="form-label small fw-bold text-muted text-uppercase">Jenis
													Kelamin</label>
												<div class="input-group input-group-fixed">
													<span
														class="input-group-text input-group-text-modern bg-white input-group-icon-fixed">
														<i class="bi bi-gender-ambiguous text-muted"></i>
													</span>
													<select id="jenis_kelamin" name="jenis_kelamin"
														class="form-select form-control-modern ps-2 input-control-fixed"
														required>
														<option value="">Pilih...</option>
														<option value="L">Laki-Laki</option>
														<option value="P">Perempuan</option>
													</select>
												</div>
											</div>

											<div class="mb-4">
												<label class="form-label small fw-bold text-muted text-uppercase">Tanggal
													Lahir</label>
												<div class="d-flex gap-2">
													<select class="form-select form-control-modern text-center" name="tgl"
														required>
														<option value="">Tgl</option>
														<?php for ($i = 1; $i <= 31; $i++)
															printf('<option value="%02d">%02d</option>', $i, $i); ?>
													</select>
													<select class="form-select form-control-modern text-center" name="bulan"
														required>
														<option value="">Bln</option>
														<?php
														$months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
														foreach ($months as $k => $m)
															printf('<option value="%02d">%s</option>', $k + 1, $m);
														?>
													</select>
													<select class="form-select form-control-modern text-center" name="tahun"
														required>
														<option value="">Thn</option>
														<?php for ($i = 1990; $i <= date('Y'); $i++)
															printf('<option value="%d">%d</option>', $i, $i); ?>
													</select>
												</div>
											</div>

											<?php if ($jadwal->token === '1'): ?>
												<div class="mb-4 p-3 bg-light rounded-3 border border-dashed">
													<label
														class="form-label small fw-bold text-dark text-uppercase mb-2 d-block text-center">Token
														Ujian</label>
													<input type="text"
														class="form-control form-control-modern text-center fs-4 fw-bold letter-spacing-lg text-uppercase input-token-fixed"
														placeholder="TOKEN" name="token" required>
												</div>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								</div>
							</div>

							<!-- Signature Column -->
							<div class="col-lg-5">
								<div class="card card-modern h-100 border-0">
									<div class="card-header bg-white border-bottom-0 pt-4 px-4">
										<h5 class="fw-bold text-dark mb-0"><i class="bi bi-pen text-primary me-2"></i>Tanda
											Tangan</h5>
									</div>
									<div class="card-body p-4 d-flex flex-column">
										<div class="alert alert-warning border-0 small py-2 px-3 rounded-3 mb-3"
											style="background-color: #fffbeb; color: #b45309;">
											<i class="bi bi-info-circle me-1"></i> Saya mengerjakan dengan jujur.
										</div>

										<div class="signature-pad-wrapper flex-grow-1" id="signature-pad">
											<canvas class="signature-pad w-100 rounded-3 bg-white"
												style="height: 180px;"></canvas>
											<textarea style="display: none;" id="ttdku" name="ttdku"></textarea>

											<div class="d-flex justify-content-between mt-3">
												<button type="button" data-action="clear"
													class="btn btn-primary btn-sm rounded-pill px-3 text-muted fw-bold border">
													<i class="bi bi-eraser me-1"></i> Hapus
												</button>
												<button type="button" data-action="simpan"
													class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
													<i class="bi bi-check2 me-1"></i> Simpan
												</button>
											</div>
										</div>

										<hr class="my-4 border-light">

										<button type="submit" form="konfir" id="btnMulai"
											class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-lg hover-scale mb-3"
											disabled>
											<i class="bi bi-play-circle-fill me-2"></i> MULAI UJIAN
										</button>
										<a href="<?= base_url('cbtsiswa/cbt') ?>"
											class="btn btn-outline-secondary btn-sm w-100 rounded-pill fw-bold border-0">
											Batal
										</a>
									</div>
								</div>
							</div>
							<?= form_close(); ?>
						</div>

					</div>
				<?php else: ?>
					<!-- Error State -->
					<div class="text-center py-5 fade-in-up">
						<div class="d-inline-block p-4 rounded-circle bg-white shadow-sm mb-4">
							<i class="bi bi-exclamation-triangle text-danger display-4"></i>
						</div>
						<h3 class="fw-bold text-dark">Akses Ditolak</h3>
						<p class="text-muted mb-4"><?= isset($error) ? $error : 'Ujian tidak dapat diakses saat ini.' ?></p>
						<a href="<?= base_url('cbtsiswa/cbt') ?>"
							class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
							Kembali ke Dashboard
						</a>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</div>

	<!-- Toast Container -->
	<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
		<div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive"
			aria-atomic="true">
			<div class="d-flex">
				<div class="toast-body fw-bold" id="toast-body">
					<!-- Message here -->
				</div>
				<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
			</div>
		</div>
	</div>

	<!-- Confirmation Modal -->
	<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header border-bottom-0 pb-0">
					<h5 class="modal-title fw-bold">Data Sudah Benar?</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pt-2 text-muted">
					Pastikan data diri Anda sudah sesuai.
				</div>
				<div class="modal-footer border-top-0 pt-0">
					<button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Cek
						Lagi</button>
					<button type="button" class="btn btn-primary rounded-pill px-4" id="confirmBtnModal">Ya,
						Lanjutkan</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Scripts (Functionality Preserved) -->
	<script src="<?= base_url() ?>/assets/app/js/redirect.js"></script>
	<script>
		// Form Submission Logic
		// Toast Helper
		function showToast(message, type = 'info') {
			var toastEl = document.getElementById('liveToast');
			var toastBody = document.getElementById('toast-body');

			toastEl.className = 'toast align-items-center border-0';
			if (type === 'success') {
				toastEl.classList.add('text-white', 'bg-success');
			} else if (type === 'error') {
				toastEl.classList.add('text-white', 'bg-danger');
			} else if (type === 'warning') {
				toastEl.classList.add('text-dark', 'bg-warning');
			} else {
				toastEl.classList.add('bg-white');
			}

			toastBody.textContent = message;
			var toast = new bootstrap.Toast(toastEl);
			toast.show();
		}

		// Form Submission Logic
		$('#konfir').submit(function (e) {
			e.preventDefault();
			e.stopPropagation();
			ajaxcsrf();

			var namaPesertaInput = $('#nama_peserta').val().trim();
			var namaSiswa = "<?= addslashes($siswa->nama) ?>".trim();
			var btnMulai = $('#btnMulai');

			if (namaPesertaInput !== namaSiswa) {
				showToast("Nama tidak sesuai. Mohon ketik nama Anda: " + namaSiswa, 'warning');
				return;
			}

			if (!window.isSignatureSaved) {
				showToast("Silakan tanda tangan dan simpan terlebih dahulu.", 'warning');
				return;
			}

			// Show loading state
			var originalBtnHtml = btnMulai.html();
			btnMulai.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan Ujian...');

			$.ajax({
				type: 'POST',
				url: base_url + 'cbtsiswa/validasi_siswa',
				data: $(this).serialize(),
				success: function (data) {
					if (data.token === true) {
						if (signaturePad.isEmpty()) {
							showToast("Tanda tangan kosong.", 'error');
							btnMulai.prop('disabled', false).html(originalBtnHtml);
						} else {
							if (data.izinkan === true) {
								if (data.ada_waktu === true) {
									var jmlSoal = Array.isArray(data.jml_soal) ? data.jml_soal.length : (data.jml_soal ? Object.keys(data.jml_soal).length : 0);
									if (jmlSoal > 0) {
										var sigData = signaturePad.toDataURL('image/png');
										$('#ttdku').html(sigData);
										window.location.href = base_url + 'cbtsiswa/penilaian/' + $('input[name="jadwal"]').val();
										// No need to reset button as we are redirecting
									} else {
										showToast("Soal belum tersedia. Hubungi proktor. (Error 004)", 'error');
										btnMulai.prop('disabled', false).html(originalBtnHtml);
									}
								} else {
									showToast((data.warn && data.warn.msg) || "Waktu ujian telah berakhir.", 'error');
									btnMulai.prop('disabled', false).html(originalBtnHtml);
								}
							} else {
								showToast("Akses ditolak. Anda terdeteksi login di perangkat lain.", 'error');
								btnMulai.prop('disabled', false).html(originalBtnHtml);
							}
						}
					} else {
						showToast("Token salah. Silakan periksa kembali token ujian.", 'error');
						btnMulai.prop('disabled', false).html(originalBtnHtml);
					}
				},
				error: function () {
					showToast("Gagal menghubungkan ke server.", 'error');
					btnMulai.prop('disabled', false).html(originalBtnHtml);
				}
			});
		});

		// Signature Pad Init
		var wrapper = document.getElementById("signature-pad");
		var signaturePad;
		if (wrapper) {
			var canvas = wrapper.querySelector("canvas");
			var saveButton = wrapper.querySelector("[data-action=simpan]");
			var clearButton = wrapper.querySelector("[data-action=clear]");

			function resizeCanvas() {
				var ratio = Math.max(window.devicePixelRatio || 1, 1);
				canvas.width = canvas.offsetWidth * ratio;
				canvas.height = canvas.offsetHeight * ratio;
				canvas.getContext("2d").scale(ratio, ratio);
				if (signaturePad) signaturePad.clear(); // Simply clear on resize for simplicity in this context or restore data if needed
			}
			window.addEventListener("resize", resizeCanvas);

			signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
			resizeCanvas();

			clearButton.addEventListener("click", function () {
				signaturePad.clear();
				window.isSignatureSaved = false;
				$('#btnMulai').prop('disabled', true);
				saveButton.innerHTML = '<i class="bi bi-check2 me-1"></i> Simpan';
				saveButton.disabled = false;
			});

			saveButton.addEventListener("click", function () {
				if (signaturePad.isEmpty()) {
					showToast("Silakan tanda tangan terlebih dahulu.", 'warning');
				} else {
					var btn = $(this);
					btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

					$.ajax({
						url: base_url + "cbtsiswa/save_signature",
						data: {
							signature_data: signaturePad.toDataURL('image/png'),
							siswa: $('input[name="siswa"]').val(),
							jadwal: $('input[name="jadwal"]').val(),
							bank: $('input[name="bank"]').val()
						},
						type: "post",
						dataType: "json",
						success: function (response) {
							if (response.status) {
								window.isSignatureSaved = true;
								$('#btnMulai').prop('disabled', false);
								btn.html('<i class="bi bi-check-lg"></i> Tersimpan');
								showToast("Tanda tangan tersimpan.", 'success');
							} else {
								btn.prop('disabled', false).html('<i class="bi bi-check2"></i> Simpan');
								showToast(response.message || "Gagal menyimpan tanda tangan.", 'error');
							}
						},
						error: function () {
							btn.prop('disabled', false).html('<i class="bi bi-check2"></i> Simpan');
							showToast("Gagal menyimpan tanda tangan.", 'error');
						}
					});
				}
			});
		}

		// UI Helpers
		function konfirmasiData() {
			var myModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
			myModal.show();

			// Handle Button Click
			$('#confirmBtnModal').off('click').on('click', function () {
				myModal.hide();
				$('#konfirm_data').slideUp(300);
				$('#konfirm_test').delay(300).slideDown(300, function () {
					resizeCanvas();
					signaturePad.clear();
				});
			});
		}
	</script>
</main>