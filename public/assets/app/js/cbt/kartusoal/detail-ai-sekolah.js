// AI Kisi-Kisi Sekolah Handler
// Modul eksternal: tab Ujian Sekolah pada modal AI gabungan.
(function() {
    const AIK = window.KARTU_SOAL_DETAIL || {};
    let aiSekolahGeneratedItems = [];
    let aiGeneratorModal = null;

    // Initialize modal when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        aiGeneratorModal = new bootstrap.Modal(document.getElementById('aiGeneratorModal'));

        // Tombol utama AI (tab Per TP) ditangani detail-manager.js.
        // Tab Ujian Sekolah dibuka dari dalam modal gabungan.

        // Jumlah soal input change
        const aiSekolahJumlahSoal = document.getElementById('aiSekolahJumlahSoal');
        if (aiSekolahJumlahSoal) {
            aiSekolahJumlahSoal.addEventListener('input', updateDistribusiPreview);
        }

        // Segarkan preview distribusi tiap kali tab Ujian Sekolah dibuka
        const tabSekolahBtn = document.getElementById('tab-btn-sekolah');
        if (tabSekolahBtn) {
            tabSekolahBtn.addEventListener('shown.bs.tab', updateDistribusiPreview);
        }

        // Model selection buttons
        document.querySelectorAll('.ai-sekolah-model-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.ai-sekolah-model-btn').forEach(b => {
                    b.classList.remove('active', 'btn-success');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('active', 'btn-success');
                document.getElementById('aiSekolahModel').value = this.dataset.model;
            });
        });

        // Per-kelas / tingkatan buttons
        document.querySelectorAll('.btn-kelas-generate').forEach(btn => {
            btn.addEventListener('click', function() {
                const k = this.getAttribute('data-kelas');
                if (k) {
                    generateAiSekolahPerKelas(k);
                }
            });
        });

        // Save button
        const btnSaveAiSekolahGenerated = document.getElementById('btnSaveAiSekolahGenerated');
        if (btnSaveAiSekolahGenerated) {
            btnSaveAiSekolahGenerated.addEventListener('click', saveAiSekolahGenerated);
        }

        // Select All Jenis Soal
        const btnSelectAllJenis = document.getElementById('btnSelectAllJenis');
        if (btnSelectAllJenis) {
            btnSelectAllJenis.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('#jenisSoalContainer input[type="checkbox"]');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => cb.checked = !allChecked);
                this.textContent = allChecked ? 'Pilih Semua' : 'Batal Pilih';
            });
        }
    });

    function updateDistribusiPreview() {
        const jumlah = parseInt(document.getElementById('aiSekolahJumlahSoal').value) || 50;
        const maxSoal = Math.min(Math.max(jumlah, 10), 100);

        // Hitung distribusi kelas
        const kelasX = Math.round(maxSoal * 0.20);
        const kelasXI = Math.round(maxSoal * 0.30);
        const kelasXII = Math.round(maxSoal * 0.50);
        const total = kelasX + kelasXI + kelasXII;

        // Update preview jika element tersedia
        if (document.getElementById('previewKelasX')) document.getElementById('previewKelasX').textContent = kelasX;
        if (document.getElementById('previewKelasXI')) document.getElementById('previewKelasXI').textContent = kelasXI;
        if (document.getElementById('previewKelasXII')) document.getElementById('previewKelasXII').textContent = kelasXII;
        if (document.getElementById('previewTotal')) document.getElementById('previewTotal').textContent = total;

        // Hitung distribusi Bloom (menggunakan mid-range)
        if (document.getElementById('previewBloomC1')) document.getElementById('previewBloomC1').textContent = '~' + Math.round(maxSoal * 0.20);
        if (document.getElementById('previewBloomC2')) document.getElementById('previewBloomC2').textContent = '~' + Math.round(maxSoal * 0.25);
        if (document.getElementById('previewBloomC3')) document.getElementById('previewBloomC3').textContent = '~' + Math.round(maxSoal * 0.30);
        if (document.getElementById('previewBloomC4')) document.getElementById('previewBloomC4').textContent = '~' + Math.round(maxSoal * 0.20);
        if (document.getElementById('previewBloomC5')) document.getElementById('previewBloomC5').textContent = '~' + Math.round(maxSoal * 0.15);
    }

    async function generateAiSekolahPerKelas(kelas) {
        const jumlahSoal = parseInt(document.getElementById('aiSekolahJumlahSoal').value) || 50;
        const instruksi = document.getElementById('aiSekolahInstruksi').value;
        const aiModel = document.getElementById('aiSekolahModel').value;
        const jenisSoal = getSelectedJenisSoal();
        
        if (jenisSoal.length === 0) {
            alert('Pilih minimal satu bentuk soal');
            return;
        }
        
        const btn = document.getElementById('btnGenerateKelas' + kelas);
        const spinner = document.getElementById('spinner' + kelas);
        const text = document.getElementById('text' + kelas);

        // Show loading
        if (btn) btn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        if (text) text.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';

        try {
            const formData = new FormData();
            formData.append('kelas', kelas);
            formData.append('kartu_id', AIK.kartuId);
            formData.append('mapel_id', AIK.mapelId);
            formData.append('tp_id', AIK.tpId);
            formData.append('smt_id', AIK.smtId);
            formData.append('jumlah_soal', jumlahSoal);
            formData.append('instruksi_tambahan', instruksi);
            formData.append('ai_model', aiModel);
            formData.append('jenis_soal', JSON.stringify(jenisSoal));

            const response = await fetch(AIK.urlGeneratePerKelas, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.status && result.data) {
                // Add to existing items
                const newItems = result.data.items || [];
                aiSekolahGeneratedItems = aiSekolahGeneratedItems.concat(newItems);
                
                renderAiSekolahPreview(aiSekolahGeneratedItems, null);
                document.getElementById('btnSaveAiSekolahGenerated').classList.remove('d-none');
                document.getElementById('aiSekolahDashboard').classList.remove('d-none');

                // Update button to show success
                text.innerHTML = '<i class="bi bi-check-circle me-1"></i>Kelas ' + kelas + ' OK';
                btn.classList.remove('btn-outline-success', 'btn-outline-warning', 'btn-outline-primary');
                btn.classList.add('btn-success');
                
                // Update dashboard with current status
                document.getElementById('dashboardCpCount').textContent = new Set(aiSekolahGeneratedItems.map(i => i.kode_cp)).size;
            } else {
                const waitSec = result?.data?.wait_seconds || 0;
                if (waitSec > 0) {
                    Swal.fire({
                        title: 'Limit Kuota',
                        text: `${result.message}. Coba lagi dalam ${waitSec} detik.`,
                        icon: 'warning'
                    });
                } else {
                    alert(result.message || 'Gagal generate kisi-kisi kelas ' + kelas);
                }
                text.innerHTML = '<i class="bi bi-x-circle me-1"></i>Kelas ' + kelas;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat generate kisi-kisi kelas ' + kelas);
            text.innerHTML = '<i class="bi bi-x-circle me-1"></i>Kelas ' + kelas;
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    function getSelectedJenisSoal() {
        const checkboxes = document.querySelectorAll('#jenisSoalContainer input[type="checkbox"]:checked');
        return Array.from(checkboxes).map(cb => parseInt(cb.value));
    }

    function renderAiSekolahPreview(items, meta) {
        const emptyDiv = document.getElementById('aiSekolahPreviewEmpty');
        const listDiv = document.getElementById('aiSekolahPreviewList');
        const metaText = document.getElementById('aiSekolahPreviewMeta');

        if (!items || items.length === 0) {
            emptyDiv.classList.remove('d-none');
            listDiv.classList.add('d-none');
            return;
        }

        emptyDiv.classList.add('d-none');
        listDiv.classList.remove('d-none');

        // Build summary text
        let summary = `${items.length} kisi-kisi siap disimpan`;
        if (meta && meta.distribusi_kelas) {
            const d = meta.distribusi_kelas;
            summary += ` â€¢ X:${d.X} XI:${d.XI} XII:${d.XII}`;
        }
        metaText.textContent = summary;

        // Build table
        let html = '<table class="table table-sm table-hover"><thead><tr>';
        html += '<th>No</th><th>Kelas</th><th>Level</th><th>Jenis</th><th>CP</th><th>Materi</th><th>Indikator</th>';
        html += '</tr></thead><tbody>';

        items.forEach((item, idx) => {
            const kelasBadge = item.kelas === 'X' ? 'bg-success' : (item.kelas === 'XI' ? 'bg-warning text-dark' : 'bg-primary');
            const levelBadge = item.level_kognitif === 'C1' ? 'bg-secondary' : (item.level_kognitif === 'C2' ? 'bg-info' : (item.level_kognitif === 'C3' ? 'bg-primary' : (item.level_kognitif === 'C4' ? 'bg-warning text-dark' : 'bg-danger')));

            html += '<tr>';
            html += `<td>${idx + 1}</td>`;
            html += `<td><span class="badge ${kelasBadge}">${item.kelas}</span></td>`;
            html += `<td><span class="badge ${levelBadge}">${item.level_kognitif}</span></td>`;
            html += `<td>${item.jenis_soal}</td>`;
            html += `<td><small class="text-muted">${item.kode_cp || '-'}</small></td>`;
            html += `<td><small>${item.materi || '-'}</small></td>`;
            html += `<td><small>${item.indikator_soal || '-'}</small></td>`;
            html += '</tr>';
        });

        html += '</tbody></table>';
        listDiv.innerHTML = html;
    }

    async function saveAiSekolahGenerated() {
        if (!aiSekolahGeneratedItems || aiSekolahGeneratedItems.length === 0) {
            alert('Tidak ada data untuk disimpan');
            return;
        }

        const btn = document.getElementById('btnSaveAiSekolahGenerated');
        const spinner = document.getElementById('aiSekolahSaveSpinner');
        const text = document.getElementById('aiSekolahSaveText');

        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Menyimpan...';

        try {
            // Persiapkan data untuk disimpan
            const itemsToSave = aiSekolahGeneratedItems.map((item, idx) => ({
                nomor_soal: idx + 1,
                id_tujuan: item.id_tujuan || null,
                cp: item.kode_cp ? `CP ${item.kode_cp}: ${item.elemen_cp || ''}` : '',
                tp_kode: item.kode_cp || '',
                tp_tujuan: item.materi || '',
                materi: item.materi || '',
                indikator: item.indikator_soal || '',
                indikator_soal: item.indikator_soal || '',
                level_kognitif: item.level_kognitif || '',
                jenis_soal: getJenisSoalId(item.jenis_soal),
                estimasi_waktu: item.estimasi_waktu || 1,
                fase: item.kelas || ''
            }));

            // DEBUG: Log first 3 items to console
            console.log('DEBUG: Items to save (first 3):', itemsToSave.slice(0, 3));

            const response = await fetch(AIK.urlSaveGenerated, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    kartu_uuid: window.KARTU_SOAL_DETAIL.uuid,
                    items: itemsToSave
                })
            });

            const result = await response.json();

            if (result.status) {
                // Show success popup with OK button, refresh after confirmation
                Swal.fire({
                    title: 'Berhasil!',
                    html: `<div class="text-center">
                        <div class="mb-2"><i class="bi bi-check-circle-fill text-success fs-1"></i></div>
                        <div class="fs-5 fw-semibold text-success">${result.saved_count || itemsToSave.length} butir soal</div>
                        <div class="text-muted">berhasil disimpan ke kartu soal</div>
                    </div>`,
                    icon: false,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'rounded-3',
                        confirmButton: 'btn btn-success px-4'
                    }
                }).then(function() {
                    if (aiGeneratorModal) aiGeneratorModal.hide();
                    // Refresh page to show new items
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Gagal',
                    text: result.message || 'Gagal menyimpan data',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Terjadi kesalahan saat menyimpan',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
            text.innerHTML = '<i class="bi bi-floppy me-1"></i>Simpan ke Kartu Soal';
        }
    }

    function getJenisSoalId(namaJenis) {
        // If already a number, return it directly
        if (typeof namaJenis === 'number') {
            return namaJenis >= 1 && namaJenis <= 7 ? namaJenis : 1;
        }
        // If numeric string, convert to number
        if (typeof namaJenis === 'string' && /^\d+$/.test(namaJenis)) {
            const num = parseInt(namaJenis, 10);
            return num >= 1 && num <= 7 ? num : 1;
        }
        // Map string names to IDs
        const mapping = {
            'Pilihan Ganda': 1,
            'Pilihan Ganda Kompleks': 2,
            'Menjodohkan': 3,
            'Isian Singkat': 4,
            'Uraian / Essay': 5,
            'Urutan': 6,
            'Isian Rumpang': 7
        };
        return mapping[namaJenis] || 1;
    }
})();
