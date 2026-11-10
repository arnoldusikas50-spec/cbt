<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<!-- Content Header -->
<div class="app-content-header fade-in-up">
	<div class="container-fluid">
		<div class="row align-items-center">
			<div class="col-sm-6">
				<h3 class="mb-0 fw-bold text-dark">
					<i class="bi-[icon] text-primary me-2"></i>
					<?= esc($judul ?? 'Halaman') ?>
				</h3>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-end mb-0 bg-transparent">
					<li class="breadcrumb-item">
						<a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
							<i class="bi bi-house-door me-1"></i>Dashboard
						</a>
					</li>
					<li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
				</ol>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<section class="content">
			<div class="container-fluid">
				<div class="card shadow-sm">
					<div class="card-header d-flex align-items-center justify-content-between">
						<h3 class="card-title mb-0">Pakta Integritas</h3>
						<div class="d-flex align-items-center ms-auto">
							<button class="btn btn-success me-2" id="btn-print">
								<i class="bi bi-printer me-1"></i> Cetak
							</button>
							<a href="<?= base_url('cbtcetak') ?>" class="btn btn-danger">
								<i class="bi bi-arrow-left-circle me-1"></i> Kembali
							</a>
						</div>
					</div>
					<div class="card-body">
						<div class="alert alert-info d-flex align-items-center mb-4" role="alert">
							<i class="bi bi-info-circle-fill me-2 fs-5"></i>
							<div>
								Pengawas <strong>WAJIB</strong> mengisi dan melengkapi Profile agar semua data pakta
								integritas terisi dengan benar.
							</div>
						</div>

						<div class="p-4 bg-light border rounded">
							<div class="d-flex justify-content-center align-items-center">
								<div id="print-preview" style="width: 21cm; min-height: 29cm;"
									class="bg-white shadow-sm p-4">


									<div
										style="line-height: 1.2; font-family: 'Verdana'; font-size: 12pt; text-align: center;">
										<b>PAKTA INTEGRITAS</b><br>
										<b>PELAKSANAAN <a class="editable bg-white">
												<?= isset($setting->nama_aplikasi) ? strtoupper($setting->nama_aplikasi) : 'ASESMEN BERBASIS KOMPUTER' ?></a></b><br>
										<b>TAHUN PELAJARAN <a class="editable bg-white">
												<?= ($tp_active->tahun ?? (date('Y') . '/' . (date('Y') + 1))) ?></a></b>
									</div>
									<br>
									<div style="text-align: justify; font-family: 'Verdana'">
										Saya yang bertanda tanggan di bawah ini :
									</div>
									<table style="width: 100%;font-family: 'Verdana';">
										<tr>
											<td style="width: 30px;"></td>
											<td style="width: 30%;">
												Nama
											</td>
											<td>:</td>
											<td class="editable bg-white">
												<?= ($profile->nama_lengkap ?? null) == null ? '.................................................................' : $profile->nama_lengkap ?>
											</td>
										</tr>
										<tr>
											<td></td>
											<td id="title-ruang">
												NIP/NIK
											</td>
											<td>:</td>
											<td class="editable bg-white" id="edit-ruang">
												<?= !isset($profile->nip) || $profile->nip == null ? '.................................................................' : $profile->nip ?>
											</td>
										</tr>
										<tr>
											<td></td>
											<td>Tugas/Jabatan</td>
											<td>:</td>
											<td class="editable bg-white" id="edit-sesi">
												<?= isset($profile->jabatan) ? $profile->jabatan : 'Pengawas Ruang' ?>
											</td>
										</tr>

										<tr>
											<td></td>
											<td>Asal Instansi</td>
											<td>:</td>
											<td class="editable bg-white" id="edit-instansi">
												<?= isset($profile->instansi) ? $profile->instansi : 'SMA Negeri 2 Amfoang Timur' ?>
											</td>
										</tr>

										<tr>
											<td></td>
											<td>No. Handphone</td>
											<td>:</td>
											<td class="editable bg-white" id="edit-instansi">
												<?= isset($profile->no_hp) ? $profile->no_hp : '085339035155' ?>
											</td>
										</tr>

										<tr>
											<td></td>
											<td>Email</td>
											<td>:</td>
											<td class="editable bg-white" id="edit-instansi">
												<?= isset($profile->email) ? $profile->email : 'randyikas@gmail.com' ?>
											</td>
										</tr>

										<tr>
											<td></td>
											<td><b>Bertugas di </b></td>
											<td>:</td>
											<td></td>
										</tr>
										<tr>
											<td></td>
											<td>
												Provinsi
											</td>
											<td>:</td>
											<td class="editable bg-white">Nusa Tenggara Timur</td>
										</tr>
										<tr>
											<td></td>
											<td>
												Kabupaten/ Kota
											</td>
											<td>:</td>
											<td class="editable bg-white">Kabupaten Kupang</td>
										</tr>
										<tr>
											<td></td>
											<td>
												Tempat Pelaksanaan
											</td>
											<td>:</td>
											<td class="editable bg-white" id="edit-nama_sekolah">
												<?= isset($kop->sekolah) ? $kop->sekolah : '' ?>
											</td>
										</tr>
										<tr>
											<td></td>
											<td>Tanggal Pelaksanaan</td>
											<td>:</td>
											<td class="editable bg-white">10 s/d 14 <?= singkat_tanggal(date('M')) . ' ' . date('Y') ?>
											</td>
										</tr>
									</table>
									<table border='0' width='90%' align='center'>
										<tr height='50'>
											<td height='30' colspan='2'
												style="text-align: justify; font-family: 'Verdana'">
												Dengan ini menyatakan bahwa :
											</td>
										</tr>
									</table>
									<table border='0' width='90%'
										style="text-align: justify; font-family: 'Verdana' ;margin-left:50px">
										<tr>
											<td width='5%'>1. </td>
											<td width='95%'>Sanggup meningkatkan kualitas, kredibilitas, dan
												akuntabilitas
												pelaksanaan Tes demi meningkatkan mutu pendidikan nasional.</td>
										</tr>
										<tr>
											<td width='5%'>2. </td>
											<td width='95%'>Sanggup melaksanakan tugas sesuai Prosedur Operasi Standar
												penyelenggaraan Ujian.</td>
										</tr>
										<tr>
											<td width='5%'>3. </td>
											<td width='95%'>Sanggup menjaga keamanan dan kerahasiaan bahan
												<?= isset($setting->nama_aplikasi) ? ($setting->nama_aplikasi) : 'Asesmen Berbasis Komputer' ?>
												Tahun <?= date('Y') ?>
											</td>
										</tr>
										<tr>
											<td width='5%'>4. </td>
											<td width='95%'>Sanggup melaksanakan
												<?= isset($setting->nama_aplikasi) ? ($setting->nama_aplikasi) : 'Asesmen Berbasis Komputer' ?>
												<?= date('Y') ?> secara jujur dan penuh tanggung jawab.
											</td>
										</tr>
									</table>
									<br>
									<table border='0' width='90%' align='center'>
										<tr height='50'>
											<td height='30' colspan='2'
												style="text-align: justify; font-family: 'Verdana'">
												Demikian pakta integritas ini saya buat dengan sebenar-benarnya tanpa
												ada
												unsur paksaan dari pihak manapun.
												<br><br>
												Apabila saya melanggar hal-hal yang telah dinyatakan dalam pakta
												integritas ini, saya bersedia dikenakan sanksi sesuai dengan hukum dan
												ketentuan peraturan perundang-undangan yang berlaku.
											</td>
										</tr>
									</table>
									<br>
									<table width='100%' height='20' style="text-align: justify; font-family: 'Verdana'">
										<tr>
											<td width='60%'></td>
											<td width='50%' class="editable bg-white" style="position: relative;">
												Nunuanah,&nbsp; <?= date('d M Y') ?>
												<br><?= ($profile->jabatan ?? null) == null ? 'Pengawas Ruang' : $profile->jabatan ?>,
												<div
													style="position: relative; height: 80px; width: 100%; display: flex; align-items: flex-end;">
													<img src="<?= !empty($profile->ttd ?? '') ? base_url($profile->ttd) : base_url('/assets/img/signature.png') ?>"
														style="height: 80px; position:absolute; bottom: 0; left: 0;"
														alt="ttd">
												</div>
												(<u>&nbsp;&nbsp;
													<?= ($profile->nama_lengkap ?? null) == null ? '...................................' : $profile->nama_lengkap ?>&nbsp;&nbsp;</u>)
												<br>NIP.
												<?= !isset($profile->nip) || ($profile->nip ?? null) == null ? '...................................' : $profile->nip ?>
												</nip>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
	</div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
<script>
	var oldVal1 = '<?= isset($kop->header_1) ? $kop->header_1 : "" ?>';
	var oldVal2 = '<?= isset($kop->header_2) ? $kop->header_2 : "" ?>';
	var oldVal3 = '<?= isset($kop->header_3) ? $kop->header_3 : "" ?>';
	var oldVal4 = '<?= isset($kop->header_4) ? $kop->header_4 : "" ?>';

	var kepsek = '<?= isset($kop->kepsek) ? $kop->kepsek : "" ?>';
	var logoKanan = '<?= isset($kop->logo_kanan) ? base_url() . $kop->logo_kanan : "" ?>';
	var logoKiri = '<?= isset($kop->logo_kiri) ? base_url() . $kop->logo_kiri : "" ?>';
	var tandatangan = '<?= isset($kop->tanda_tangan) ? base_url() . $kop->tanda_tangan : "" ?>';

	var printBy = 1;
	var infoData = {};
	var infoSiswa = [];
	var allInfo = '';
	var oldInfo = '';

	var hari = ['Minngu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
	var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

	var d = new Date();
	var curr_day = d.getDay();
	var curr_date = d.getDate();

	var curr_month = d.getMonth();
	var curr_year = d.getFullYear();

	function buatTanggal() {
		return hari[curr_day] + ", " + curr_date + "  " + bulan[curr_month] + " " + curr_year;
	}

	function submitKop() {
		$('#set-kop').submit();
	}

	$(document).ready(function () {
		ajaxcsrf();
		var opsiJadwal = $("#jadwal");
		var opsiRuang = $("#ruang");
		var opsiSesi = $("#sesi");
		var opsiKelas = $("#kelas");

		$('.editable').attr('contentEditable', true);

		function loadSiswaRuang(ruang, sesi, jadwal) {
			var notempty = ruang && sesi && jadwal;
			if (notempty) {
				$.ajax({
					type: "GET",
					url: base_url + "cbtcetak/getSiswaRuang?ruang=" + ruang + '&sesi=' + sesi + '&jadwal=' + jadwal,
					success: function (response) {
						console.log('respon', response);
						$('#edit-jml-peserta').html('<b>' + response.siswa.length + '</b>');

						$('#edit-jenis-ujian').html('<b>' + response.info.jadwal.nama_jenis + '</b>');
						$('#edit-nama-ujian').html('<b>' + response.info.jadwal.nama_jenis + '<b>');
						$('#edit-waktu-mulai').html('<b>' + response.info.sesi.waktu_mulai.substring(0, 5) + '</b>');
						$('#edit-waktu-akhir').html('<b>' + response.info.sesi.waktu_akhir.substring(0, 5) + '</b>');
						$('#edit-mapel').html('<b>' + response.info.jadwal.nama_mapel + '</b>');
						$('#edit-pengawas').text(response.info.pengawas[0].nama_guru);
					}
				});
			}
		}

		function loadSiswaKelas(kelas, sesi, jadwal) {
			var notempty = kelas && sesi && jadwal;
			if (notempty) {
				$.ajax({
					type: "GET",
					url: base_url + "cbtcetak/getSiswaKelas?kelas=" + kelas + '&sesi=' + sesi + '&jadwal=' + jadwal,
					success: function (response) {
						console.log('respon', response);
						$('#edit-jml-peserta').html('<b>' + response.siswa.length + '</b>');

						$('#edit-jenis-ujian').html('<b>' + response.info.jadwal.nama_jenis + '</b>');
						$('#edit-nama-ujian').text(response.info.jadwal.nama_jenis);
						$('#edit-waktu-mulai').html('<b>' + response.info.sesi.waktu_mulai.substring(0, 5) + '</b>');
						$('#edit-waktu-akhir').html('<b>' + response.info.sesi.waktu_akhir.substring(0, 5) + '</b>');
						$('#edit-mapel').html('<b>' + response.info.jadwal.nama_mapel + '</b>');
						$('#edit-pengawas').text(+response.info.pengawas[0].nama_guru);
					}
				});
			}
		}

		opsiJadwal.prepend("<option value='' selected='selected'>Pilih Jadwal</option>");
		opsiRuang.prepend("<option value='' selected='selected'>Pilih Ruang</option>");
		opsiSesi.prepend("<option value='' selected='selected'>Pilih Sesi</option>");
		opsiKelas.prepend("<option value='' selected='selected'>Pilih Kelas</option>");


		opsiKelas.change(function () {
			$('#edit-ruang').text($("#kelas option:selected").text());
			loadSiswaKelas($(this).val(), opsiSesi.val(), opsiJadwal.val())
		});

		opsiRuang.change(function () {
			$('#edit-ruang').text($("#ruang option:selected").text());
			loadSiswaRuang($(this).val(), opsiSesi.val(), opsiJadwal.val())
		});

		opsiSesi.change(function () {
			$('#edit-sesi').text($("#sesi option:selected").text());
			if (printBy === 1) {
				loadSiswaRuang(opsiRuang.val(), $(this).val(), opsiJadwal.val())
			} else {
				loadSiswaKelas(opsiKelas.val(), $(this).val(), opsiJadwal.val())
			}
		});

		opsiJadwal.change(function () {
			if (printBy === 1) {
				loadSiswaRuang(opsiRuang.val(), opsiSesi.val(), $(this).val())
			} else {
				loadSiswaKelas(opsiKelas.val(), opsiSesi.val(), $(this).val())
			}
		});

		$("#btn-print").click(function () {
			var kosong = printBy === 2 ? ($('#kelas').val() === '' || ($('#sesi').val() === '') || ($('#jadwal').val() === '')) : ($('#ruang').val() === '' || ($('#sesi').val() === '') || ($('#jadwal').val() === ''));
			if (kosong) {
				Swal.fire({
					title: "ERROR",
					text: "Isi semua pilihan terlebih dulu",
					icon: "error"
				})
			} else {
				$('#print-preview').print();
				/*
				var header = '<style>' +
					'@media print {' +
					'    body{' +
					'        width: 21cm;' +
					'        height: 29.7cm;' +
					'        margin: auto;' +
					'   }' +
					'}' +
					//'* { margin:auto; padding:0; line-height:100%; }' +
					'</style>' +
					'</head>' +
					'<body onload="window.print()">';
				var divToPrint = document.getElementById('print-preview');
				var newWin = window.open('', 'Print-Window');
				newWin.document.open();
				newWin.document.write(header + divToPrint.innerHTML + '</body>');
				newWin.document.close();
	
				//setTimeout(function(){newWin.close();
				//},10);
				*/
			}
		});

	})
</script>
<?= $this->endSection() ?>