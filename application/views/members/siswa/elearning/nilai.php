<div class="container-fluid py-4 container-p-x main-content-overlap">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Main Card -->
            <div class="card card-modern border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Header Decoration -->
                <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" style="z-index: 0;">
                    <div style="
                                    position: absolute;
                                    top: -10%;
                                    right: -5%;
                                    width: 400px;
                                    height: 400px;
                                    background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, rgba(255,255,255,0) 70%);
                                    border-radius: 50%;
                                "></div>
                </div>

                <div class="card-body p-4 position-relative z-1 ">
                    <!-- Watermark -->
                    <div style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    background-color: #e0e7ff;
                                    -webkit-mask-image: url('<?= base_url('assets/img/logo-card.png') ?>');
                                    mask-image: url('<?= base_url('assets/img/logo-card.png') ?>');
                                    -webkit-mask-size: 50%;
                                    mask-size: 50%;
                                    -webkit-mask-repeat: no-repeat;
                                    mask-repeat: no-repeat;
                                    -webkit-mask-position: center;
                                    mask-position: center;
                                    opacity: 0.05;
                                    z-index: -1;
                                    pointer-events: none;
                                "></div>

                    <!-- Header Area -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success bg-opacity-10 text-success rounded-4 me-3">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Daftar Nilai</h5>
                                <div class="text-muted small">Rekap nilai hasil ujian CBT</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('dashboard') ?>"
                                class="btn btn-primary border rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center hover-scale">
                                <i class="bi bi-arrow-left-circle-fill me-2 text-primary"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div id='list-cbt' class="table-responsive d-none">
                        <table class="table table-sm w-100" id="table-nilai-ujian">
                            <thead>
                                <tr a class="alert alert-default-success">
                                    <th class="text-center align-middle">NO</th>
                                    <th>Jenis Penilaian</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kode Penilaian</th>
                                    <th class="text-center">Nilai</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <hr>
                        <span><strong><i>Catatan:</i></strong></span>
                        <br>
                        <small a class="alert-default-warning">
                            <br><b>(**)</b> Hubungi Guru Pengampu jika ingin mengetahui nilai
                        </small>
                    </div>
                    <div class="d-none" id="empty">
                        <p>Belum ada jadwal ulangan/ujian.</p>
                    </div>
                </div> <!-- end card-body -->
            </div> <!-- end card -->

        </div>
    </div>
</div>

<hr class="mt-auto">
<div class="col-md-12 text-center mt-3 pb-4">
    <div class="copyright"><small>COPYRIGHT &copy; <?= date('Y'); ?> | IKASMEDIA</small> </div>
</div>

<script>
    var arrbulan = ['', 'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    var arrhari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
    let jadwals = [];

    async function ajaxGet(url, data) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    type: "GET",
                    data: data,
                    success: function (respon) {
                        resolve(respon);
                    },
                    error: function (xhr, status, error) {
                        reject({
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                    }
                });
            }, 100)
        });
    }

    function populateTable(data) {
        $('#list-cbt').addClass('d-none');
        $('#empty').removeClass('d-none');

        if (!data.all_jadwal) return

        jadwals = data.all_jadwal.filter(jadwal =>
            !(jadwal.bank?.soal_agama !== '-' && jadwal.bank?.soal_agama !== '0' && jadwal.bank?.soal_agama !== data.siswa?.agama)
            && data.bank_kelas[jadwal.id_jadwal]?.includes(data?.siswa?.id_kelas?.toString())
        );

        const tableBody = document.querySelector("#table-nilai-ujian tbody");
        if (jadwals.length > 0) {
            $('#list-cbt').removeClass('d-none');
            $('#empty').addClass('d-none');
            let no = 1;

            jadwals.forEach((j) => {
                const log = j.cbtsiswa?.length > 0 ? j?.cbtsiswa[0] : null;
                const blmMulai = log == null || !log?.mulai;
                const blmSelesai = log == null || !log?.selesai;

                let detail = ''
                if (blmMulai) {
                    detail = '<button type="button" class="btn btn-sm btn-danger"> Belum Ujian </button>';
                } else if (blmSelesai) {
                    detail = '<button type="button" class="btn btn-sm btn-warning"> Belum Selesai </button>';
                } else {
                    if (j.hasil_tampil === "0") {
                        detail = '**'
                    } else {
                        detail = `<button type="button"
                                    data-jadwal="${j.id_jadwal}"
                                    data-siswa="${data.siswa.id_siswa}"
                                    onclick="getNilai(this)"
                                    class="btn btn-sm ${blmSelesai ? 'btn-danger' : 'btn-primary'} ${blmSelesai ? 'btn-disabled' : ''}" ${blmSelesai ? 'disabled' : ''}>
                                        Detail
                                 </button>`
                    }
                }
                const row = `
      <tr>
        <td class="text-center">${no}</td>
        <td>${j.jenis.nama_jenis}
          <br><small>${formatDate(j.tgl_mulai)}</small>
        </td>
        <td>${j.bank?.mapel.kode}</td>
        <td>${j.bank?.bank_kode}</td>
        <td class="text-center">${detail}</td>
      </tr>
    `;
                tableBody.innerHTML += row;
                no++;
            });
            $('#table-nilai-ujian').DataTable();
        } else {
            tableBody.innerHTML = `
    <tr>
      <td colspan="6" class="text-center">
        <div class="alert align-content-center alert-default-warning" role="alert">
          Belum ada jadwal ulangan/ujian
        </div>
      </td>
    </tr>
  `;
        }
    }

    function formatDate(dateString) {
        const options = { weekday: "short", day: "2-digit", month: "short", year: "numeric" };
        return new Date(dateString).toLocaleDateString("id-ID", options);
    }

    $(document).ready(async function () {
        $('#table-nilai-materi').DataTable();
        $('#table-nilai-tugas').DataTable();

        const response = await ajaxGet('kbm/get_all_nilai/')
        console.log('res', response)
        populateTable(response)
    });

    async function getNilai(e) {
        const jadwalId = $(e).data('jadwal');
        const siswa = $(e).data('siswa');
        console.log('Fetching nilai for:', { jadwalId, siswa });

        if (jadwalId && siswa) {
            Swal.fire({
                title: "Nilai Detail",
                text: "Memuat nilai ...",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await ajaxGet('kbm/get_nilai_jadwal/', {
                    jadwal: jadwalId,
                    siswa: siswa,
                });

                console.log('Response:', response);

                if (response.success) {
                    setTimeout(function () {
                        // Close the loading alert implicitly by opening a new one in setDialog
                        setDialog(response);
                    }, 500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Gagal mendapatkan data nilai.',
                        showConfirmButton: true
                    });
                }
            } catch (err) {
                console.error('AJAX Error:', err);
                // Extract detail from custom reject object or standard error
                let detail = err.responseText || err.message || err.error || err;
                // Clean up detail if it's too long or object
                if (typeof detail === 'object') detail = JSON.stringify(detail);
                detail = String(detail).substring(0, 1000); // Limit length

                Swal.fire({
                    icon: 'error',
                    title: 'Error Server',
                    html: 'Detail Error:<br><textarea class="form-control" rows="5" style="font-size:11px;">' + detail + '</textarea>',
                    showConfirmButton: true
                });
            }
        }
    }

    function setDialog(response) {
        const jadwal = response.jadwal;
        if (!jadwal || !jadwal.sesisiswa || !jadwal.sesisiswa.durasi) {
            Swal.fire({
                icon: 'error',
                title: 'Data Tidak Lengkap',
                text: 'Data detail nilai siswa tidak lengkap atau rusak.'
            });
            return;
        }

        const id = jadwal.id_jadwal;
        const tampilNilai = jadwal?.hasil_tampil === '1';
        const dikoreksi = jadwal?.sesisiswa?.nilai_akhir?.dikoreksi == '1';

        // Safe access
        var dur = jadwal.sesisiswa.durasi;
        var skor = jadwal.sesisiswa.nilai_akhir || {};

        var sp = jadwal.tgl_mulai ? jadwal.tgl_mulai.split('-') : [];
        const tgl_jadwal = sp.length >= 3
            ? (arrhari[new Date(sp[0], sp[1] - 1, sp[2]).getDay()] || '') + ', ' + sp[2] + ' ' + (arrbulan[parseInt(sp[1]) || 0] || '') + ' ' + sp[0]
            : '-';

        const blmSelesai = !dur.selesai
        let jam_mulai = '--:--';
        let jam_selesai = '--:--';
        let durasi = '--';
        if (!blmSelesai) {
            var mnt = dur.mulai?.split(' ')[1];
            const m = mnt?.split(':')
            if (m && m.length >= 2) jam_mulai = m[0] + ':' + m[1];

            var scn = dur.selesai?.split(' ')[1];
            const s = scn?.split(':')
            if (s && s.length >= 2) jam_selesai = s[0] + ':' + s[1];
            durasi = dur.durasi || '--';
        }

        let konten = '';
        if (tampilNilai) {
            const showPg = parseInt(jadwal.bank?.tampil_pg || '0') === 0 ? 'd-none' : '';
            const jmlPg = jadwal.bank?.tampil_pg || '0';
            const benarPg = skor.pg_benar || '0';
            const skorPg = skor.skor_pg?.skor_total || '0';

            const showKompleks = parseInt(jadwal.bank?.tampil_kompleks || '0') === 0 ? 'd-none' : '';
            const jmlKompleks = jadwal.bank?.tampil_kompleks || '0';
            const benarKompleks = skor.skor_kompleks?.jml_benar || '0';
            const skorKompleks = skor.skor_kompleks?.skor_asli || '0';

            const showJodohkan = parseInt(jadwal.bank?.tampil_jodohkan || '0') === 0 ? 'd-none' : '';
            const jmlJodohkan = jadwal.bank?.tampil_jodohkan || '0';
            const benarJodohkan = skor.skor_jodohkan?.jml_benar || '0';
            const skorJodohkan = skor.skor_jodohkan?.skor_asli || '0';

            const showIsian = parseInt(jadwal.bank?.tampil_isian || '0') === 0 ? 'd-none' : '';
            const jmlIsian = jadwal.bank?.tampil_isian || '0';
            const benarIsian = skor.skor_isian?.jml_benar || '0';
            const skorIsian = dikoreksi ? (skor.skor_isian?.skor_asli || '0') : 'sedang<br>dikoreksi';

            const showEsai = parseInt(jadwal.bank?.tampil_esai || '0') === 0 ? 'd-none' : '';
            const jmlEsai = jadwal.bank?.tampil_esai || '0';
            //const benarEsai = skor.benar_esai || '0';
            const skorEsai = dikoreksi ? (skor.skor_essai?.skor_asli || '0') : 'sedang<br>dikoreksi';

            const showUrutan = parseInt(jadwal.bank?.tampil_urutan || '0') === 0 ? 'd-none' : '';
            const jmlUrutan = jadwal.bank?.tampil_urutan || '0';
            const benarUrutan = skor.skor_urutan?.jml_benar || '0';
            const skorUrutan = skor.skor_urutan?.skor_asli || '0';

            const showCloze = parseInt(jadwal.bank?.tampil_cloze || '0') === 0 ? 'd-none' : '';
            const jmlCloze = jadwal.bank?.tampil_cloze || '0';
            const benarCloze = skor.skor_cloze?.jml_benar || '0';
            const skorCloze = skor.skor_cloze?.skor_asli || '0';

            const jmlSkor = skor.skor_asli || '0';

            konten = `<div class="text-start">
                <div class="row g-3 mb-4">
                    <div class="col-12">
                         <div class="d-flex align-items-center p-3 bg-light rounded-3 border">
                            <div class="icon-box-sm bg-white rounded-circle shadow-sm text-primary me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-calendar-event-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-xs text-uppercase fw-bold">Tanggal Pelaksanaan</small>
                                <span class="fw-bold text-dark">${tgl_jadwal}</span>
                            </div>
                         </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 text-center bg-white h-100">
                             <small class="text-muted d-block text-xs mb-1">Mulai</small>
                             <span class="fw-bold fs-6 font-monospace text-dark">${jam_mulai}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 text-center bg-white h-100">
                             <small class="text-muted d-block text-xs mb-1">Selesai</small>
                             <span class="fw-bold fs-6 font-monospace text-dark">${jam_selesai}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 text-center bg-light-success h-100">
                             <small class="text-success d-block text-xs mb-1">Durasi</small>
                             <span class="fw-bold fs-6 font-monospace text-success">${durasi}</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive rounded-3 border-top">
                    <table class="table table-striped align-middle mb-0 text-sm w-100">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="py-3 px-3 border-bottom-0 text-uppercase text-xs font-weight-bold">Tipe Soal</th>
                                <th class="py-3 text-center border-bottom-0 text-uppercase text-xs font-weight-bold">Jml</th>
                                <th class="py-3 text-center border-bottom-0 text-uppercase text-xs font-weight-bold">Benar</th>
                                <th class="py-3 text-center border-bottom-0 text-uppercase text-xs font-weight-bold text-primary">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <tr class="${showPg}">
                                <td class="px-3">Pilihan Ganda</td>
                                <td class="text-center">${jmlPg}</td>
                                <td class="text-center fw-bold text-success">${benarPg}</td>
                                <td class="text-center fw-bold text-primary">${skorPg}</td>
                            </tr>
                            <tr class="${showKompleks}">
                                <td class="px-3">PG Kompleks</td>
                                <td class="text-center">${jmlKompleks}</td>
                                <td class="text-center fw-bold text-success">${benarKompleks}</td>
                                <td class="text-center fw-bold text-primary">${skorKompleks}</td>
                            </tr>
                            <tr class="${showJodohkan}">
                                <td class="px-3">Menjodohkan</td>
                                <td class="text-center">${jmlJodohkan}</td>
                                <td class="text-center fw-bold text-success">${benarJodohkan}</td>
                                <td class="text-center fw-bold text-primary">${skorJodohkan}</td>
                            </tr>
                            <tr class="${showIsian}">
                                <td class="px-3">Isian Singkat</td>
                                <td class="text-center">${jmlIsian}</td>
                                <td class="text-center fw-bold text-success">${benarIsian}</td>
                                <td class="text-center fw-bold text-primary">${skorIsian}</td>
                            </tr>
                            <tr class="${showEsai}">
                                <td class="px-3">Uraian / Esai</td>
                                <td class="text-center">${jmlEsai}</td>
                                <td class="text-center text-muted text-xs">Correction</td>
                                <td class="text-center fw-bold text-primary">${skorEsai}</td>
                            </tr>
                            <tr class="${showUrutan}">
                                <td class="px-3">Mengurutkan</td>
                                <td class="text-center">${jmlUrutan}</td>
                                <td class="text-center fw-bold text-success">${benarUrutan}</td>
                                <td class="text-center fw-bold text-primary">${skorUrutan}</td>
                            </tr>
                            <tr class="${showCloze}">
                                <td class="px-3">Isian Rumpang</td>
                                <td class="text-center">${jmlCloze}</td>
                                <td class="text-center fw-bold text-success">${benarCloze}</td>
                                <td class="text-center fw-bold text-primary">${skorCloze}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-primary bg-opacity-10">
                            <tr>
                                <td colspan="3" class="text-end fw-bold py-3 text-primary text-uppercase text-xs">Total Perolehan Nilai</td>
                                <td class="text-center fw-bold text-primary fs-5 py-3">${jmlSkor}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>`;
        } else {
            konten = `<div class="alert alert-default-warning align-content-center" role="alert">
                    Hubungi guru pengampu jika ingin mengetahui nilai.
                </div>`;
        }

        // Update the SweetAlert with the new content
        Swal.fire({
            title: "Nilai Detail",
            html: konten,
            showConfirmButton: true,
            confirmButtonText: "Tutup"
        });
    }

    function showDialog(tr) {
        Swal.fire({
            title: "Catatan Guru",
            html: '<div class="w-100 border p-4">' + $(tr).data('text') + '</div>',
            confirmButtonText: "TUTUP"
        })
    }
</script>