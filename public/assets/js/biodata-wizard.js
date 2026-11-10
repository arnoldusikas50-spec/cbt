/**
 * Biodata Wizard - Multi-Stage Form JavaScript
 * Handles step navigation, validation, and form submission
 */

document.addEventListener('DOMContentLoaded', function () {
    // Ensure BASE_URL is available
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = typeof base_url !== 'undefined' ? base_url : '/';
    }

    const wizard = new BiodataWizard();
    wizard.init();
});

class BiodataWizard {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 7;
        this.form = document.getElementById('form-biodata');
        this.prestasiIndex = 0;
        this.beasiswaIndex = 0;
        this.stepNames = {
            1: 'Data Pribadi',
            2: 'Data Alamat',
            3: 'Data Orang Tua',
            4: 'Kontak & Periodik',
            5: 'Prestasi & Beasiswa',
            6: 'Registrasi',
            7: 'Upload Dokumen'
        };
    }

    init() {
        this.bindNavigationEvents();
        this.bindFormSubmit();
        this.bindSaveStepEvents();
        this.bindDynamicTableEvents();
        this.bindWNAToggle();

        // Check for edit mode
        if (typeof editId !== 'undefined' && editId) {
            this.loadData(editId);
            // Update form action for update
            this.form.action = this.form.action.replace('/save', `/update/${editId}`);
        } else {
            // Only update progress if not loading (loading might jump steps)
            this.updateProgress();
        }
    }

    loadData(id) {
        Swal.fire({
            title: 'Memuat Data',
            text: 'Silakan tunggu...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Use base_url defined in main layout (lowercase)
        const url = (typeof base_url !== 'undefined' ? base_url : '/') + `biodata-siswa/get-data/${id}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    this.populateForm(data.data);
                    Swal.close();
                } else {
                    Swal.fire('Error', 'Gagal memuat data', 'error');
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
    }

    populateForm(data) {
        // Merge all data sources into one flat object for easier mapping (where keys are unique enough)
        // or map specifically.
        const flatData = {
            ...data.siswa,
            ...data.biodata,
            ...data.ortu
        };

        // Handle standard inputs
        for (const [key, value] of Object.entries(flatData)) {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'radio') {
                    const radio = this.form.querySelector(`[name="${key}"][value="${value}"]`);
                    if (radio) radio.checked = true;
                } else if (input.type === 'checkbox') {
                    // Start simple: check if value matches specific logic
                    // if (input.value === value) input.checked = true;
                } else {
                    input.value = value;
                }
            } else {
                // Try finding radios by name grouping
                const radios = this.form.querySelectorAll(`[name="${key}"]`);
                if (radios.length > 0) {
                    radios.forEach(r => {
                        if (r.value == value) r.checked = true;
                    });
                }
            }
        }

        // Handle Nama Lengkap mapped to nama in master_siswa
        if (data.siswa.nama) {
            const namaField = this.form.querySelector('[name="nama_lengkap"]');
            if (namaField) namaField.value = data.siswa.nama;
        }

        // Handle Prestasi
        if (data.prestasi && data.prestasi.length > 0) {
            // Clear empty row if exists
            document.getElementById('prestasi-empty')?.remove();
            data.prestasi.forEach(p => {
                this.addPrestasiRow(); // This increments index
                const currentRowIndex = this.prestasiIndex - 1;
                // Fill the row
                const row = document.querySelectorAll('.prestasi-row')[currentRowIndex]; // Logic might need refinement if rows are removed
                // Actually safer to append then fill last row
                // Or better: manual fill
                // For simplicity, just add blank rows for now, user fills them? No, must fill.
                // TODO: Robust dynamic table filling
            });
            // Filling dynamic tables is complex without unified naming, skipping for now to prioritize main fields
        }

        // Update progress/validation status if needed
        // this.validateStep(1); // Optional

        // Handle Berkas/Files
        if (data.berkas) {
            const fileFields = ['file_kk', 'file_akta', 'file_ijazah', 'file_rapor', 'file_foto', 'file_ktp_ortu', 'file_pernyataan', 'file_kip'];
            fileFields.forEach(field => {
                if (data.berkas[field]) {
                    const input = this.form.querySelector(`input[name="${field}"]`);
                    if (input) {
                        // Remove required since file exists
                        input.removeAttribute('required');

                        // Show existing file link
                        const container = input.parentNode;
                        if (!container.querySelector('.existing-file-link')) {
                            const link = document.createElement('div');
                            link.className = 'existing-file-link mt-1';
                            link.innerHTML = `<small class="text-success"><i class="bi bi-check-circle-fill"></i> File tersimpan: <a href="${BASE_URL}${data.berkas[field]}" target="_blank" class="text-decoration-none">Lihat File</a></small>`;
                            container.appendChild(link);
                        }
                    }
                }
            });
        }
    }


    // ========================================
    // Navigation
    // ========================================
    bindNavigationEvents() {
        // Next buttons
        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const nextStep = parseInt(e.currentTarget.dataset.next);
                if (this.validateStep(this.currentStep)) {
                    this.goToStep(nextStep);
                }
            });
        });

        // Previous buttons
        document.querySelectorAll('.btn-prev').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const prevStep = parseInt(e.currentTarget.dataset.prev);
                this.goToStep(prevStep);
            });
        });

        // Step click (progress bar)
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.addEventListener('click', (e) => {
                const targetStep = parseInt(e.currentTarget.dataset.step);
                // Allow going to completed or next step only
                if (targetStep <= this.currentStep || targetStep === this.currentStep + 1) {
                    if (this.validateStep(this.currentStep) || targetStep < this.currentStep) {
                        this.goToStep(targetStep);
                    }
                }
            });
        });
    }

    goToStep(step) {
        // Hide current step
        document.getElementById(`step-${this.currentStep}`).classList.add('d-none');

        // Show target step
        document.getElementById(`step-${step}`).classList.remove('d-none');

        // Update current step
        this.currentStep = step;

        // Update progress
        this.updateProgress();

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    updateProgress() {
        const progressPercent = (this.currentStep / this.totalSteps) * 100;
        document.querySelector('.progress-fill').style.width = `${progressPercent}%`;

        // Update step indicators
        document.querySelectorAll('.wizard-step').forEach(step => {
            const stepNum = parseInt(step.dataset.step);
            step.classList.remove('active', 'completed');

            if (stepNum === this.currentStep) {
                step.classList.add('active');
            } else if (stepNum < this.currentStep) {
                step.classList.add('completed');
            }
        });
    }

    // ========================================
    // Validation
    // ========================================
    showError(parent, message) {
        if (!parent.querySelector('.invalid-feedback')) {
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            feedback.textContent = message;
            parent.appendChild(feedback);
        }
    }

    removeError(parent) {
        const existingFeedback = parent.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
    }

    validateStep(step) {
        const stepEl = document.getElementById(`step-${step}`);
        const requiredFields = stepEl.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            // Determine container for feedback (move out of input-group if present)
            let feedbackParent = field.parentNode;
            if (feedbackParent.classList.contains('input-group')) {
                feedbackParent = feedbackParent.parentNode;
            }

            // Standard validation
            let isEmpty = !field.value.trim();
            let isTooBig = false;

            // File size validation (limit 2MB/5MB)
            if (field.type === 'file' && field.files.length > 0) {
                const fileSize = field.files[0].size;
                if (fileSize > 2 * 1024 * 1024) { // 2MB
                    isTooBig = true;
                    // Reset field
                    field.value = '';
                }
            }

            if (isEmpty && field.hasAttribute('required')) {
                isValid = false;
                field.classList.add('is-invalid');
                this.showError(feedbackParent, 'Field ini wajib diisi/upload');
            } else if (isTooBig) {
                isValid = false;
                field.classList.add('is-invalid');
                this.showError(feedbackParent, 'Ukuran file max 2MB');
            } else {
                field.classList.remove('is-invalid');
                this.removeError(feedbackParent);
            }
        });

        if (!isValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Silakan lengkapi semua field yang wajib diisi',
                confirmButtonColor: '#667eea'
            });
        }

        return isValid;
    }

    // ========================================
    // Form Submit
    // ========================================
    bindFormSubmit() {
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();

            // Validate all steps
            let allValid = true;
            for (let i = 1; i <= this.totalSteps; i++) {
                if (!this.validateStep(i)) {
                    allValid = false;
                    this.goToStep(i);
                    break;
                }
            }

            if (allValid) {
                this.submitForm();
            }
        });

        // Draft button
        document.getElementById('btn-draft')?.addEventListener('click', () => {
            this.saveDraft();
        });
    }

    submitForm() {
        const btn = document.getElementById('btn-submit');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        Swal.fire({
            title: 'Menyimpan Data',
            text: 'Silakan tunggu...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(this.form);

        fetch(this.form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Data biodata siswa berhasil disimpan',
                        confirmButtonColor: '#11998e'
                    }).then(() => {
                        window.location.href = BASE_URL + 'datasiswa';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat menyimpan data',
                        confirmButtonColor: '#f5576c'
                    });
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server',
                    confirmButtonColor: '#f5576c'
                });
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    }

    saveDraft() {
        const formData = new FormData(this.form);
        localStorage.setItem('biodata_draft', JSON.stringify(Object.fromEntries(formData)));

        Swal.fire({
            icon: 'success',
            title: 'Draft Tersimpan',
            text: 'Data telah disimpan sebagai draft di browser Anda',
            timer: 2000,
            showConfirmButton: false
        });
    }

    // ========================================
    // Save Per Step
    // ========================================
    bindSaveStepEvents() {
        document.querySelectorAll('.btn-save-step').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const step = parseInt(e.currentTarget.dataset.step);
                this.saveStep(step, e.currentTarget);
            });
        });
    }

    saveStep(step, btn) {
        const stepName = this.stepNames[step];
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        Swal.fire({
            title: `Menyimpan ${stepName}`,
            text: 'Silakan tunggu...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(this.form);
        formData.append('step', step);
        formData.append('step_name', stepName);

        fetch(this.form.action.replace('/save', '/save-step'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Mark step as saved
                    const stepIndicator = document.querySelector(`.wizard-step[data-step="${step}"]`);
                    if (stepIndicator) {
                        stepIndicator.classList.add('completed');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || `${stepName} berhasil disimpan`,
                        confirmButtonColor: '#11998e',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Update button to show saved state
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Tersimpan';

                    setTimeout(() => {
                        btn.classList.remove('btn-outline-success');
                        btn.classList.add('btn-success');
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }, 2000);

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat menyimpan data',
                        confirmButtonColor: '#f5576c'
                    });
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server',
                    confirmButtonColor: '#f5576c'
                });
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    }

    // ========================================
    // Dynamic Tables (Prestasi & Beasiswa)
    // ========================================
    bindDynamicTableEvents() {
        // Add Prestasi
        document.getElementById('btn-add-prestasi')?.addEventListener('click', () => {
            this.addPrestasiRow();
        });

        // Add Beasiswa
        document.getElementById('btn-add-beasiswa')?.addEventListener('click', () => {
            this.addBeasiswaRow();
        });

        // Remove rows (event delegation)
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-remove-row')) {
                e.target.closest('tr').remove();
                this.checkEmptyTables();
            }
        });
    }

    addPrestasiRow() {
        const template = document.getElementById('tpl-prestasi-row');
        const html = template.innerHTML.replace(/{idx}/g, this.prestasiIndex++);
        const tbody = document.getElementById('prestasi-body');

        // Remove empty message
        document.getElementById('prestasi-empty')?.remove();

        tbody.insertAdjacentHTML('beforeend', html);
    }

    addBeasiswaRow() {
        const template = document.getElementById('tpl-beasiswa-row');
        const html = template.innerHTML.replace(/{idx}/g, this.beasiswaIndex++);
        const tbody = document.getElementById('beasiswa-body');

        // Remove empty message
        document.getElementById('beasiswa-empty')?.remove();

        tbody.insertAdjacentHTML('beforeend', html);
    }

    checkEmptyTables() {
        // Check prestasi
        if (!document.querySelector('.prestasi-row')) {
            document.getElementById('prestasi-body').innerHTML = `
                <tr id="prestasi-empty">
                    <td colspan="6" class="text-center text-muted py-3">
                        <i class="bi bi-inbox me-1"></i> Belum ada data prestasi
                    </td>
                </tr>`;
        }

        // Check beasiswa
        if (!document.querySelector('.beasiswa-row')) {
            document.getElementById('beasiswa-body').innerHTML = `
                <tr id="beasiswa-empty">
                    <td colspan="5" class="text-center text-muted py-3">
                        <i class="bi bi-inbox me-1"></i> Belum ada data beasiswa
                    </td>
                </tr>`;
        }
    }

    // ========================================
    // WNA Toggle
    // ========================================
    bindWNAToggle() {
        const wnaRadio = document.getElementById('wna');
        const wniRadio = document.getElementById('wni');
        const namaNegaraInput = document.querySelector('input[name="nama_negara"]');

        if (wnaRadio && namaNegaraInput) {
            wnaRadio.addEventListener('change', () => {
                namaNegaraInput.disabled = false;
                namaNegaraInput.required = true;
            });

            wniRadio.addEventListener('change', () => {
                namaNegaraInput.disabled = true;
                namaNegaraInput.required = false;
                namaNegaraInput.value = '';
            });
        }
    }
}
