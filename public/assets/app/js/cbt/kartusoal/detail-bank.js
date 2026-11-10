// detail-bank.js
// Aksi paket/bank dari kartu soal: resync TP, buat paket, progress.
// Kebutuhan: jQuery, Bootstrap, Swal, `window.KARTU_SOAL_DETAIL`.
function resyncItemsFromTp(idKartu) {
    if (typeof Swal === 'undefined') return;

    Swal.fire({
        title: 'Refresh Data TP?',
        text: 'Menyelaraskan deskripsi CP, TP, dan materi kisi-kisi dari Master Tujuan Pembelajaran terbaru.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selaraskan',
        cancelButtonText: 'Batal'
    }).then((res) => {
        if (res.isConfirmed) {
            Swal.showLoading();
            $.ajax({
                url: ((window.KARTU_SOAL_DETAIL || {}).urlResyncTp || '') + idKartu,
                type: 'POST',
                dataType: 'json',
                success: function(resp) {
                    if (resp.status) {
                        Swal.fire('Berhasil', resp.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', resp.message || 'Gagal menyelaraskan data', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                }
            });
        }
    });
}

function openGenerateBankModal() {
    const _judul = ((window.KARTU_SOAL_DETAIL || {}).judul || 'SOAL');
    const defaultKode = (_judul.replace(/[^a-zA-Z0-9]/g, '') || 'SOAL') + '-A';
    const defaultNama = 'Paket Soal A - ' + _judul;
    $('#gen_bank_kode').val(defaultKode);
    $('#gen_bank_nama').val(defaultNama);
    $('#gen_mode_soal').val('all');
    $('#gen_acak_opsi').prop('checked', false);
    $('#gen_jumlah_paket').val(1);
    
    var m = new bootstrap.Modal(document.getElementById('modalGenerateBankFromKartu'));
    m.show();
}

function submitGenerateBankForm(e) {
    e.preventDefault();

    const kode = String($('#gen_bank_kode').val() || '').trim();
    if (!kode) {
        Swal.fire('Kode Belum Diisi', 'Isi kode bank soal / paket terlebih dahulu.', 'warning');
        return;
    }

    const run = function() {
        const btn = $('#btnSubmitGenBank');
        btn.prop('disabled', true);
        showGenBankProgress('Menyiapkan paket kloning...');

        $.ajax({
            url: ((window.KARTU_SOAL_DETAIL || {}).urlGenerateBank || ''),
            type: 'POST',
            data: $('#formGenBank').serialize(),
            dataType: 'json',
            timeout: 600000,
            success: function(resp) {
                hideGenBankProgress();
                btn.prop('disabled', false);
                if (resp.status) {
                    bootstrap.Modal.getInstance(document.getElementById('modalGenerateBankFromKartu')).hide();
                    let htmlDetail = resp.message || 'Paket soal berhasil dibuat.';

                    Swal.fire({
                        icon: 'success',
                        title: 'Paket Soal Terbuat!',
                        html: htmlDetail,
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-folder-symlink me-1"></i>Buka Bank Soal',
                        cancelButtonText: 'Tetap di Sini',
                        confirmButtonColor: '#198754'
                    }).then((result) => {
                        if (result.isConfirmed && resp.data && resp.data.url_detail) {
                            window.location.href = resp.data.url_detail;
                        }
                    });
                } else {
                    Swal.fire('Gagal Membuat Paket', resp.message || 'Terjadi kesalahan.', 'error');
                }
            },
            error: function(xhr, status) {
                hideGenBankProgress();
                btn.prop('disabled', false);
                const msg = xhr?.responseJSON?.message || (status === 'timeout' ? 'Proses terlalu lama. Coba jumlah paket lebih sedikit atau periksa API Key.' : 'Gagal menghubungi server.');
                Swal.fire('Error', msg, 'error');
            }
        });
    };

    run();
}

function showGenBankProgress(txt) {
    $('#gen_bank_progress_txt').text(txt || 'Menyiapkan paket...');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalGenBankProgress')).show();
}

function hideGenBankProgress() {
    const m = bootstrap.Modal.getInstance(document.getElementById('modalGenBankProgress'));
    if (m) m.hide();
}
