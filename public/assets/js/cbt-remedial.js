/**
 * CBT Remedial Handler v2.0
 * 
 * Script ini menangani mode remedial pada ujian CBT
 * - Menampilkan indikator soal benar/salah pada navigasi nomor soal
 * - Disable input pada soal yang sudah benar (TERKUNCI)
 * - Hanya mengirim jawaban untuk soal yang salah
 * - Jawaban siswa yang benar TIDAK dihapus, hanya ditandai agar tidak bisa diubah
 */

class CbtRemedial {
    constructor() {
        this.isRemedialMode = false;
        this.soalStatus = {}; // Map id_soal -> status
        this.jadwalId = null;
        this.bankId = null;
        this._originalSaveJawaban = null;
    }

    /**
     * Inisialisasi mode remedial
     */
    async init(jadwalId, bankId) {
        this.jadwalId = jadwalId;
        this.bankId = bankId;
        this.isRemedialMode = true;

        console.log('[Remedial] Mode remedial aktif. Jadwal:', jadwalId, 'Bank:', bankId);
        await this.loadSoalStatus();
        this.injectRemedialCSS();
        this.applyRemedialUI();
        this.overrideSaveFunction();

        // Apply to current soal after a small delay (wait for soal to load)
        setTimeout(() => this.applyToCurrentSoal(), 500);
    }

    /**
     * Load status soal (benar/salah) dari server
     */
    async loadSoalStatus() {
        try {
            const response = await fetch(base_url + 'cbt/get_soal_remedial', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    jadwal: this.jadwalId,
                    bank: this.bankId
                })
            });

            const data = await response.json();
            
            if (data.status && data.soal) {
                // Map soal status
                data.soal.forEach(s => {
                    this.soalStatus[s.id_soal] = {
                        is_benar: s.is_benar,
                        is_salah: s.is_salah,
                        bisa_edit: s.bisa_edit,
                        jawaban_siswa: s.jawaban_siswa
                    };
                });

                console.log('[Remedial] Loaded', data.total, 'soal:', data.total_benar, 'benar,', data.total_salah, 'salah');
            }
        } catch (error) {
            console.error('[Remedial] Error loading soal status:', error);
        }
    }

    /**
     * Inject CSS styles for remedial mode
     */
    injectRemedialCSS() {
        const style = document.createElement('style');
        style.id = 'remedial-styles';
        style.textContent = `
            /* Soal benar - terkunci */
            .soal-correct-overlay {
                position: relative;
            }
            .soal-correct-overlay::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(232, 245, 233, 0.5);
                pointer-events: all;
                z-index: 10;
                border-radius: 8px;
            }
            .soal-correct-overlay * {
                pointer-events: none !important;
            }
            
            .remedial-correct-badge {
                background: linear-gradient(135deg, #4caf50, #2e7d32);
                color: white;
                padding: 10px 16px;
                border-radius: 8px;
                margin-bottom: 12px;
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
                box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
            }
            
            .remedial-wrong-badge {
                background: linear-gradient(135deg, #ff9800, #f57c00);
                color: white;
                padding: 10px 16px;
                border-radius: 8px;
                margin-bottom: 12px;
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
                box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
            }

            /* Nomor soal grid: benar */
            .remedial-nav-benar {
                background: #4caf50 !important;
                color: white !important;
                border-color: #388e3c !important;
                position: relative;
            }
            .remedial-nav-benar::after {
                content: '✓';
                position: absolute;
                bottom: -2px;
                right: 1px;
                font-size: 8px;
                font-weight: bold;
            }
            
            /* Nomor soal grid: salah */
            .remedial-nav-salah {
                background: #fff3e0 !important;
                color: #e65100 !important;
                border: 2px solid #ff9800 !important;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Apply remedial UI modifications (initial load)
     */
    applyRemedialUI() {
        // Apply colors to the nomor soal navigation grid
        this.applyToNavGrid();
    }

    /**
     * Apply remedial status to nomor soal navigation grid
     */
    applyToNavGrid() {
        // For the modal grid (#konten-modal) and inline grid (.grid-soal-wrapper)
        const nomorButtons = document.querySelectorAll('.grid-soal-wrapper .btn, #konten-modal .btn');
        
        if (nomorButtons.length === 0) {
            // Grid might not be created yet, retry with MutationObserver
            this.observeNavGrid();
            return;
        }

        this.colorizeNavButtons(nomorButtons);
    }

    /**
     * Observe DOM changes to detect when nav grid is created
     */
    observeNavGrid() {
        const targetModal = document.getElementById('konten-modal');
        const targetGrid = document.querySelector('.grid-soal-wrapper');

        const observer = new MutationObserver((mutations) => {
            const buttons = document.querySelectorAll('.grid-soal-wrapper .btn, #konten-modal .btn');
            if (buttons.length > 0) {
                this.colorizeNavButtons(buttons);
            }
        });

        if (targetModal) observer.observe(targetModal, { childList: true, subtree: true });
        if (targetGrid) observer.observe(targetGrid, { childList: true, subtree: true });
        
        // Also observe body for dynamically created grids
        observer.observe(document.body, { childList: true, subtree: true });
        
        // Auto-disconnect after 30 seconds to prevent memory leak
        setTimeout(() => observer.disconnect(), 30000);
    }

    /**
     * Colorize navigation buttons based on remedial status
     */
    colorizeNavButtons(buttons) {
        // We need to match nomor soal with id_soal from the soalStatus
        // The buttons typically have data attributes or text content matching the question number
        // We need to map question index -> id_soal

        // Build id_soal list from questions array (if available globally)
        const questionsList = window.cbt_questions || window.questions || window.soalJson || [];
        
        buttons.forEach((btn, index) => {
            if (index >= questionsList.length) return;
            
            const question = questionsList[index];
            const idSoal = question?.id_soal || question?.id;
            
            if (!idSoal || !this.soalStatus[idSoal]) return;
            
            const status = this.soalStatus[idSoal];
            
            // Remove existing remedial classes
            btn.classList.remove('remedial-nav-benar', 'remedial-nav-salah');
            
            if (status.is_benar) {
                btn.classList.add('remedial-nav-benar');
                btn.title = 'Soal ini sudah benar (terkunci)';
            } else {
                btn.classList.add('remedial-nav-salah');
                btn.title = 'Soal ini perlu diperbaiki';
            }
        });
    }

    /**
     * Apply remedial status to the currently displayed soal
     * This should be called every time a new soal is displayed
     */
    applyToCurrentSoal() {
        if (!this.isRemedialMode) return;

        // Get current soal ID
        const currentIdSoal = typeof idSoal !== 'undefined' ? idSoal : null;
        if (!currentIdSoal) return;

        const status = this.soalStatus[currentIdSoal];
        if (!status) return;

        // Remove any existing remedial badges
        document.querySelectorAll('.remedial-correct-badge, .remedial-wrong-badge, .soal-correct-overlay').forEach(el => el.remove());

        const jawabanContainer = document.getElementById('konten-jawaban');
        if (!jawabanContainer) return;

        if (status.is_benar) {
            // === CORRECT ANSWER: LOCK IT ===
            
            // Add badge above answers
            const badge = document.createElement('div');
            badge.className = 'remedial-correct-badge';
            badge.innerHTML = '<i class="bi bi-shield-lock-fill"></i> Jawaban sudah benar — soal ini TERKUNCI dan tidak dapat diubah';
            jawabanContainer.insertBefore(badge, jawabanContainer.firstChild);

            // Create overlay to block all interactions
            const overlay = document.createElement('div');
            overlay.className = 'soal-correct-overlay';
            
            // Move all children into overlay
            while (jawabanContainer.children.length > 1) {
                overlay.appendChild(jawabanContainer.children[1]);
            }
            jawabanContainer.appendChild(overlay);

            // Also disable all inputs inside
            const inputs = jawabanContainer.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.disabled = true;
                input.readOnly = true;
            });

            console.log('[Remedial] Soal', currentIdSoal, 'TERKUNCI (benar)');
        } else {
            // === WRONG ANSWER: ALLOW EDIT ===
            const badge = document.createElement('div');
            badge.className = 'remedial-wrong-badge';
            badge.innerHTML = '<i class="bi bi-pencil-fill"></i> Jawaban sebelumnya salah — silakan perbaiki jawaban Anda';
            jawabanContainer.insertBefore(badge, jawabanContainer.firstChild);

            console.log('[Remedial] Soal', currentIdSoal, 'BISA DIEDIT (salah)');
        }

        // Re-apply nav grid colors
        this.applyToNavGrid();
    }

    /**
     * Override fungsi save jawaban untuk remedial protection
     */
    overrideSaveFunction() {
        // Intercept the regular saveJawaban by monkey-patching
        // The main cbt-ujian.js likely calls saveJawaban or simpanJawaban
        
        if (typeof window.saveJawaban === 'function' && !this._originalSaveJawaban) {
            this._originalSaveJawaban = window.saveJawaban;
            const self = this;
            
            window.saveJawaban = function(...args) {
                // Check if the current soal is locked
                const currentIdSoal = typeof idSoal !== 'undefined' ? idSoal : null;
                if (currentIdSoal && self.soalStatus[currentIdSoal]?.is_benar) {
                    console.log('[Remedial] Blocked save for correct soal:', currentIdSoal);
                    // Don't show toast here - just silently block the save
                    // The UI already shows the lock badge, no need for annoying toast
                    return false;
                }
                
                // Allow saving for wrong questions
                return self._originalSaveJawaban.apply(this, args);
            };
        }

        // Override selesai button for remedial finish
        const btnFinish = document.getElementById('btnRealFinish');
        if (btnFinish) {
            const self = this;
            const originalOnclick = btnFinish.onclick;
            btnFinish.onclick = function(e) {
                e.preventDefault();
                self.selesaiRemedial();
            };
        }
    }

    /**
     * Selesaikan remedial
     */
    async selesaiRemedial() {
        if (!confirm('Yakin ingin menyelesaikan remedial? Nilai akan dihitung ulang berdasarkan jawaban terbaru.')) return;

        try {
            const response = await fetch(base_url + 'cbt/selesai_remedial', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    jadwal: this.jadwalId
                })
            });

            const data = await response.json();
            
            if (data.status) {
                alert(`Remedial selesai!\nNilai akhir: ${data.nilai_akhir}\nBenar: ${data.jumlah_benar}/${data.total_soal}`);
                window.location.href = data.redirect || base_url + 'cbt';
            } else {
                alert(data.message || 'Gagal menyelesaikan remedial');
            }
        } catch (error) {
            console.error('[Remedial] Error:', error);
            alert('Terjadi kesalahan saat menyelesaikan remedial');
        }
    }

    /**
     * Check if a specific soal is locked (correct answer)
     */
    isSoalLocked(idSoal) {
        return this.isRemedialMode && this.soalStatus[idSoal]?.is_benar === true;
    }
}

// Global instance
const cbtRemedial = new CbtRemedial();

// Hook into soal navigation events
// This ensures remedial badges are re-applied when navigating between questions
(function() {
    // Intercept the global "loadSoal" or "gotoSoal" to re-apply remedial UI
    const originalSetInterval = window.setInterval;
    let lastIdSoal = null;
    
    // Poll for soal changes (simple approach that works with any exam JS)
    setInterval(() => {
        if (!cbtRemedial.isRemedialMode) return;
        
        const currentIdSoal = typeof idSoal !== 'undefined' ? idSoal : null;
        if (currentIdSoal && currentIdSoal !== lastIdSoal) {
            lastIdSoal = currentIdSoal;
            // Small delay to let the DOM update
            setTimeout(() => cbtRemedial.applyToCurrentSoal(), 200);
        }
    }, 300);
})();
