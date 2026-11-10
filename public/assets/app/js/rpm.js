
// --- 1. CONFIGURATION & HELPERS ---
const dbApiKeyElement = document.getElementById('db-api-key');
const dbApiKey = dbApiKeyElement ? dbApiKeyElement.value : "";
const apiKey = dbApiKey || ""; // System default if any
const WA_NUMBER = '6285339035155';

if (typeof mermaid !== 'undefined') {
    mermaid.initialize({ startOnLoad: true });
}

// WhatsApp Links
const waText = encodeURIComponent("Halo Admin, saya butuh bantuan aplikasi RPM.");
const waLink = document.getElementById('whatsapp-link');
if (waLink) waLink.href = `https://wa.me/${WA_NUMBER}?text=${waText}`;

const btnWaHeader = document.getElementById('btn-wa-header');
if (btnWaHeader) btnWaHeader.href = `https://wa.me/${WA_NUMBER}?text=${waText}`;

// MODAL GUIDE LOGIC
// Compatible with Bootstrap 4 (jQuery based)
function showGuide() {
    $('#guideModal').modal('show');
}

// API Key Button Logic
const btnApiKey = document.getElementById('btn-api-key');
if (btnApiKey) {
    btnApiKey.addEventListener('click', async () => {
        const { value: key } = await Swal.fire({
            title: 'Pengaturan API Key',
            text: 'Masukkan Google Gemini API Key Anda jika ingin menggunakan key pribadi.',
            input: 'text',
            inputLabel: 'API Key',
            inputPlaceholder: 'Paste API Key di sini...',
            inputValue: localStorage.getItem('custom_api_key') || '',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#6366f1'
        });

        if (key !== undefined) {
            if (key.trim() === "") {
                localStorage.removeItem('custom_api_key');
                Swal.fire('Reset', 'API Key telah direset ke default sistem.', 'info');
            } else {
                localStorage.setItem('custom_api_key', key);
                Swal.fire('Tersimpan!', 'API Key Anda akan digunakan.', 'success');
            }
        }
    });
}

function showLoading(title, subtext) {
    document.getElementById('loading-text').textContent = title || "Sedang Memproses...";
    document.getElementById('loading-subtext').textContent = subtext || "Mohon tunggu sebentar...";
    document.getElementById('loading-modal').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loading-modal').classList.add('d-none');
}

// --- STORAGE LOGIC ---
const STORAGE_KEY_CONTENT = 'rpm_saved_content';
const STORAGE_KEY_DATA = 'rpm_saved_data';

function saveState() {
    const previewContainer = document.getElementById('preview-container');
    if (previewContainer) {
        localStorage.setItem(STORAGE_KEY_CONTENT, previewContainer.innerHTML);
    }

    // Also save form data if possible to restore context
    if (Object.keys(currentData).length > 0) {
        localStorage.setItem(STORAGE_KEY_DATA, JSON.stringify(currentData));
    }
}

function loadState() {
    const savedContent = localStorage.getItem(STORAGE_KEY_CONTENT);
    const savedData = localStorage.getItem(STORAGE_KEY_DATA);

    if (savedContent && savedContent.trim() !== '') {
        const previewContainer = document.getElementById('preview-container');
        if (previewContainer) {
            previewContainer.innerHTML = savedContent;

            // If Mermaid diagrams exist, re-init them
            if (savedContent.includes('mermaid')) {
                setTimeout(() => typeof mermaid !== 'undefined' && mermaid.init(), 500);
            }
        }
    }

    if (savedData) {
        try {
            currentData = JSON.parse(savedData);
            // Optionally restore form inputs here if needed, but user asked for "document result"
            document.getElementById('doc-status').textContent = currentData.materi ? `Dokumen Aktif: ${currentData.materi}` : 'Menunggu input...';
        } catch (e) { console.error('Failed to load saved data', e); }
    }
}

// Load on startup
document.addEventListener('DOMContentLoaded', loadState);

// --- 2. MAIN GENERATOR LOGIC ---
let currentData = {};

const rppForm = document.getElementById('rpp-form');
if (rppForm) {
    rppForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Collect Data
        // Parse Date input
        const tglInput = document.getElementById('tanggal-surat');
        let tglStr = '..', blnStr = '....', thnStr = '20..';

        if (tglInput && tglInput.value) {
            const dateObj = new Date(tglInput.value);
            if (!isNaN(dateObj)) {
                tglStr = String(dateObj.getDate()).padStart(2, '0');
                blnStr = dateObj.toLocaleDateString('id-ID', { month: 'long' });
                thnStr = dateObj.getFullYear();
            }
        }

        // Collect Data
        currentData = {
            sekolah: document.getElementById('nama-sekolah').value,
            mapel: document.getElementById('mapel').value,
            kelas: document.getElementById('kelas').value,
            materi: document.getElementById('materi-pokok').value,
            lokal: document.getElementById('potensi-lokal').value,
            waktu: document.getElementById('alokasi-waktu').value,
            kepsek: document.getElementById('nama-kepsek').value || '.........................',
            nipKepsek: document.getElementById('nip-kepsek').value || '-',
            guru: document.getElementById('nama-guru').value || '.........................',
            nipGuru: document.getElementById('nip-guru').value || '-',
            kota: document.getElementById('kota').value || '......',
            tanggal: tglStr,
            bulan: blnStr,
            tahun: thnStr
        };

        showLoading("Membuat RPM", "AI by Ikasmedia Sedang Membuat RPM...");

        try {
            const rpmData = await generateRPMData(currentData);
            renderRPM(currentData, rpmData);
            saveState(); // SAVE STATE

            document.getElementById('doc-status').textContent = `Dokumen Aktif: ${currentData.materi}`;

            $('#configModal').modal('hide'); // Hide Input Modal properly

            Swal.fire({
                icon: 'success',
                title: 'RPM Berhasil Dibuat!',
                text: 'Gunakan Magic Tools by Ikasmedia untuk menambah lampiran.',
                timer: 2000,
                showConfirmButton: false
            });

        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Gagal membuat RPM: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    });
}

// --- 3. AI GENERATION FUNCTIONS ---
async function fetchGemini(payload) {
    // Read key dynamically from DOM to ensure we get the latest value (and handle case where const initialized early)
    const dbKeyInput = document.getElementById('db-api-key');
    const dbKey = dbKeyInput ? dbKeyInput.value : "";

    // Prioritize DB key first, then custom
    const currentKey = dbKey || localStorage.getItem('custom_api_key') || "";
    const finalKey = currentKey || apiKey; // apiKey fallbacks to system default

    if (!finalKey) {
        throw new Error("API Key belum diset. Silakan hubungi Admin atau set API Key di profil.");
    }

    const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=${finalKey}`;
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    if (!res.ok) {
        const errBody = await res.json();
        throw new Error(`API Error: ${errBody.error?.message || res.statusText}`);
    }
    return await res.json();
}

async function generatedResponse(prompt) {
    const payload = {
        contents: [{ parts: [{ text: prompt }] }],
        generationConfig: { responseMimeType: "application/json" }
    };
    const result = await fetchGemini(payload);
    return JSON.parse(result.candidates[0].content.parts[0].text);
}

async function generateRPMData(data) {
    const prompt = `Bertindaklah sebagai Guru Profesional...
    Input Data: Mapel: ${data.mapel}, Kelas: ${data.kelas}, Materi: ${data.materi}, Konteks: ${data.lokal}, Waktu: ${data.waktu}.
    Output JSON strict format.`;

    // Using the full prompt from rpmku.html logic
    const detailedPrompt = `Bertindaklah sebagai Guru Profesional dan Ahli Kurikulum juga Pedagogi Pendidikan Indonesia. 
    Tugas Anda adalah menyusun Rencana Pembelajaran Mendalam (RPM) Kurikulum Merdeka.
    
    ATURAN PENTING BAHASA:
    1. GUNAKAN BAHASA INDONESIA YANG BAKU, FORMAL, DAN EDUKATIF untuk seluruh isi dokumen.
    2. Sesuaikan narasi dengan konteks budaya sekolah di Indonesia khususnya di Indonesia Timur - Nusa Tenggara Timur.
    3. KHUSUS PADA PERTEMUAN KE-1, Isi kolom 'aktivitas' WAJIB mengikuti struktur urutan berikut: 
       1. Asesmen Awal Mengetahui pengalaman awal murid guru menampilkan gambar/video, mengajukan pertanyaan, murid merespon, identifikasi pemahaman. - Tindak Lanjut Asesmen.
       2. Melakukan alternatif pembelajaran sesuai hasil asesmen minimal 2 alternatif.
       3. Refleksi.

    Struktur Dokumen:
    Header: Identitas.
    A. Identifikasi: Pilih 2-3 dimensi Profil Pelajar Pancasila yang relevan.
    B. Kegiatan Pembelajaran: Tujuan, Pedagogis, Lingkungan, Kemitraan, Digital, Lintas Disiplin.
    C. Rincian Kegiatan: Pertemuan (Pengalaman Belajar VS Aktivitas).
    D. Asesmen: Awal, Proses, Akhir.

    Input Data:
    Mapel: ${data.mapel}, Kelas: ${data.kelas}, Materi: ${data.materi}, Konteks: ${data.lokal}, Alokasi Waktu: ${data.waktu}.

    Output JSON:
    {
        "judul_topik": "Judul Kreatif",
        "identifikasi_profil": ["Dimensi 1", "Dimensi 2"], 
        "tujuan_pembelajaran": "String HTML paragraf",
        "praktik_pedagogis": "String HTML paragraf",
        "lingkungan_belajar": "String HTML paragraf",
        "kemitraan_pembelajaran": "String HTML paragraf",
        "pemanfaatan_digital": "String HTML paragraf", 
        "lintas_disiplin": "String HTML paragraf",
        "langkah_pertemuan": [
            { "ke": 1, "topik": "...", "pengalaman_prinsip": "...", "aktivitas": "...", "waktu": "..." }
        ],
        "asesmen": { "awal": "...", "proses": "...", "akhir": "..." }
    }`;

    const payload = {
        contents: [{ parts: [{ text: detailedPrompt }] }],
        generationConfig: { responseMimeType: "application/json" }
    };

    const result = await fetchGemini(payload);
    // Parsing safely
    try {
        return JSON.parse(result.candidates[0].content.parts[0].text);
    } catch (e) {
        throw new Error("Gagal parsing JSON dari AI.");
    }
}

// --- 4. FORMATTING & RENDERING ---
function formatText(text) {
    if (!text) return '-';
    let html = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
    html = html.replace(/\n/g, '<br>');
    if (html.includes('- ') && !html.includes('<ul>')) {
        html = html.replace(/- (.*?)(<br>|$)/g, '<li>$1</li>');
        html = `<ul>${html}</ul>`;
        html = html.replace(/<br><ul>/g, '<ul>').replace(/<\/ul><br>/g, '</ul>');
    }
    return html;
}

function renderRPM(data, ai) {
    const container = document.getElementById('preview-container');
    container.innerHTML = '';

    const pageDiv = document.createElement('div');
    pageDiv.className = 'paper-sheet';

    const allDimensions = ["Keimanan dan Ketakwaan", "Kewargaan", "Penalaran Kritis", "Kreativitas", "Kolaborasi", "Kemandirian", "Kesehatan", "Komunikasi"];

    // Smart Keywords Mapping for Robust Matching
    const dimKeywords = {
        "Keimanan dan Ketakwaan": ["iman", "takwa", "akhlak", "beriman", "spiritual", "tuham"],
        "Kewargaan": ["global", "kebhinekaan", "bhineka", "kewargaan", "budaya", "nasionalisme"],
        "Penalaran Kritis": ["kritis", "nalar", "problem", "analisis", "evaluasi"],
        "Kreativitas": ["kreatif", "inovasi", "karya", "gagasan", "cipta"],
        "Kolaborasi": ["kolaborasi", "kerjasama", "gotong", "royong", "tim"],
        "Kemandirian": ["mandiri", "regulasi", "refleksi", "otonom"],
        "Kesehatan": ["sehat", "jasmani", "olahraga", "fisik", "bugar"],
        "Komunikasi": ["komunikasi", "interaksi", "simak", "bicara"]
    };

    let identifikasiRows = '';

    // Simple helper to create checklist rows
    for (let i = 0; i < allDimensions.length; i += 2) {
        const d1 = allDimensions[i] || "";
        const d2 = allDimensions[i + 1] || "";

        const check = (d) => {
            if (!d) return "";
            if (!ai.identifikasi_profil || !Array.isArray(ai.identifikasi_profil)) return "";

            const keys = dimKeywords[d] || [d.toLowerCase()];
            // Check if ANY keyword matches ANY profile string from AI
            const match = ai.identifikasi_profil.some(p =>
                keys.some(k => p.toLowerCase().includes(k))
            );
            return match ? "<b style='font-size:14pt; color:#000;'>√</b>" : "";
        };

        identifikasiRows += `
            <tr>
                <td class="check-box-cell">${check(d1)}</td><td width="45%">${d1}</td>
                <td class="check-box-cell">${check(d2)}</td><td width="45%">${d2}</td>
            </tr>`;
    }

    let meetingHtml = '';
    if (ai.langkah_pertemuan) {
        ai.langkah_pertemuan.forEach(p => {
            meetingHtml += `
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 11pt; font-weight: bold; margin-bottom: 5px;">Pertemuan Ke-${p.ke}: ${p.topik}</h4>
                <table class="table-data" style="width: 100%;">
                    <thead><tr><th width="30%">Pengalaman dan Prinsip Belajar</th><th width="60%">Aktivitas Pembelajaran</th><th width="10%">Alokasi Waktu</th></tr></thead>
                    <tbody><tr><td valign="top">${formatText(p.pengalaman_prinsip)}</td><td valign="top">${formatText(p.aktivitas)}</td><td valign="top">${p.waktu}</td></tr></tbody>
                </table>
            </div>`;
        });
    }

    const template = `
        <div class="document-body">
            <div style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 5px;">RENCANA PEMBELAJARAN MENDALAM (RPM)</div>
            <div class="doc-title-topic">"${ai.judul_topik || data.materi}"</div>
            <table class="identitas-table">
                <tr><td width="20%">Satuan Pendidikan</td><td width="2%">:</td><td>${data.sekolah}</td></tr>
                <tr><td>Mata Pelajaran</td><td>:</td><td>${data.mapel}</td></tr>
                <tr><td>Kelas / Fase</td><td>:</td><td>${data.kelas}</td></tr>
                <tr><td>Alokasi Waktu</td><td>:</td><td>${data.waktu}</td></tr>
            </table>

            <div class="section-header">A. Identifikasi</div>
            <p>Dimensi profil lulusan:</p>
            <table class="checklist-table">${identifikasiRows}</table>

            <div class="section-header">B. Kegiatan Pembelajaran</div>
            <ol>
                <li><div class="sub-section-header">Tujuan</div><div>${formatText(ai.tujuan_pembelajaran)}</div></li>
                <li><div class="sub-section-header">Pedagogis</div><div>${formatText(ai.praktik_pedagogis)}</div></li>
                <li><div class="sub-section-header">Lingkungan</div><div>${formatText(ai.lingkungan_belajar)}</div></li>
                <li><div class="sub-section-header">Kemitraan</div><div>${formatText(ai.kemitraan_pembelajaran)}</div></li>
                <li><div class="sub-section-header">Digital</div><div>${formatText(ai.pemanfaatan_digital)}</div></li>
                <li><div class="sub-section-header">Lintas Disiplin</div><div>${formatText(ai.lintas_disiplin)}</div></li>
            </ol>

            <div class="section-header">C. Rincian Kegiatan</div>
            ${meetingHtml}

            <div class="section-header">D. Asesmen</div>
            <ol>
                <li><div class="sub-section-header">Awal</div><div>${formatText(ai.asesmen.awal)}</div></li>
                <li><div class="sub-section-header">Proses</div><div>${formatText(ai.asesmen.proses)}</div></li>
                <li><div class="sub-section-header">Akhir</div><div>${formatText(ai.asesmen.akhir)}</div></li>
            </ol>

            <table style="width: 100%; border: none; margin-top: 40px;">
                <tr style="border: none;">
                    <td align="center" width="40%" style="border: none; vertical-align: top;">
                        Mengetahui,<br>Kepala Sekolah
                        <br><br><br><br>
                        <b><u>${data.kepsek}</u></b><br>
                        NIP. ${data.nipKepsek}
                    </td>
                    <td width="20%" style="border: none;"></td>
                    <td align="center" width="40%" style="border: none; vertical-align: top;">
                        ${data.kota}, ${data.tanggal} ${data.bulan} ${data.tahun}<br>Guru Mata Pelajaran
                        <br><br><br><br>
                        <b><u>${data.guru}</u></b><br>
                        NIP. ${data.nipGuru}
                    </td>
                </tr>
            </table>
        </div>
    `;

    pageDiv.innerHTML = template;
    container.appendChild(pageDiv);
}

function appendAttachment(title, content, type = 'general') {
    const container = document.getElementById('preview-container');
    const pageDiv = document.createElement('div');
    pageDiv.className = 'paper-sheet';

    // Inject custom styles for specific types if needed
    // ... (Use same styles as rpmku.html or rely on rpm.css)

    pageDiv.innerHTML = `
        <div style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; text-align: center;">
            <h3 style="margin: 0; text-transform: uppercase; font-size: 14pt;">${title}</h3>
            <p style="margin: 0; font-size: 10pt;">Lampiran Dokumen RPM - ${currentData.mapel || 'Mata Pelajaran'}</p>
        </div>
        <div class="attachment-content">${formatText(content)}</div>
    `;

    if (content.includes('<div') || content.includes('<table')) {
        pageDiv.querySelector('.attachment-content').innerHTML = content;
    }

    container.appendChild(pageDiv);

    if (content.includes('mermaid')) {
        setTimeout(() => mermaid.init(), 500);
    }
    pageDiv.scrollIntoView({ behavior: 'smooth' });
    saveState(); // SAVE STATE
}

// --- 5. MAGIC TOOLS & DOWNLOAD ---
async function handleMagicTool(btnId, title, promptType) {
    const btn = document.getElementById(btnId);
    if (!btn) return;

    // Specific Prompts Map
    const specificPrompts = {
        'atp': `Buatkan Alur Tujuan Pembelajaran (ATP) dalam bentuk TABEL HTML yang rapi dan estetik. 
            Gunakan emoji yang relevan pada setiap judul kolom (Contoh: 📋 No, 🧩 Elemen, 🎯 Tujuan, ⏳ Waktu).`,

        'materi': `Buatkan Materi Ajar yang Lengkap dan Mendalam seperti BUKU TEKS. 
              Gunakan struktur HTML (h3, p, ul) dan tambahkan emoji di setiap judul sub-bab. 
            Wajib sertakan minimal 3 gambar ilustrasi REALISTIK (Foto Nyata) yang relevan.
            Gunakan sumber gambar dari Unsplash Source untuk tampilan visual yang nyata dan profesional (Non-AI).
           
           Format HTML Gambar: 
           <figure style="text-align:center; margin: 20px 0;">
              <img src="https://source.unsplash.com/800x600/?KEYWORD_INGGRIS_SPESIFIK" alt="Ilustrasi Topik" style="border-radius:15px; width:80%; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
              <figcaption style="font-style:italic; color:#666; margin-top:8px;">📌 Gambar: (Deskripsi gambar dalam Bahasa Indonesia)</figcaption>
           </figure>

           PENTING: Ganti 'KEYWORD_INGGRIS_SPESIFIK' pada URL di atas dengan 2-3 kata kunci subjek spesifik dalam Bahasa Inggris (contoh: "laboratory microscope" atau "ancient temple").
              
              Gunakan class berikut untuk styling:
              - <div class="box-definisi">📖 **Definisi Konsep:** ...</div>
              - <div class="box-rumus">🔢 **Poin Kunci/Rumus:** ...</div>
              - <div class="box-info">💡 **Tahukah Kamu?** ...</div>`,

        'lkpd': `Buatkan Lembar Kerja Peserta Didik (LKPD) Siap Cetak. 
            Gunakan header yang menarik dengan emoji: 📝 Nama, 🏫 Kelas, 📅 Tanggal.
            Bagian: 🎯 A. Tujuan, 🛠️ B. Alat & Bahan, 👣 C. Langkah Kerja, 💬 D. Pertanyaan Diskusi.
            Tambahkan ikon di setiap instruksi kerja agar lebih mudah diikuti siswa.`,

        'quiz': `Buatkan 5 Soal HOTS Pilihan Ganda (5 opsi) dan 5 Soal Uraian Singkat. 
            Gunakan emoji 🧠 untuk menandai soal yang membutuhkan analisis tinggi.
            Format PG: 1. ❓ Soal... <br> a. ... b. ... 
            Sertakan Kunci Jawaban 🔑 terpisah di bagian paling bawah.`,

        'akm': `Buatkan Soal AKM (Asesmen Kompetensi Minimum). 
            1. 📚 Literasi (Wacana menarik + emoji kontekstual, lalu 2 soal PG kompleks).
            2. 📊 Numerasi (Sajikan data/tabel/grafik fiktif, lalu 2 soal isian singkat).`,

        'rubrik': `Buatkan Rubrik Penilaian dalam bentuk TABEL HTML. 
              Gunakan sistem bintang atau emoji untuk level pencapaian:
              Kriteria | 🔴 Kurang (1) | 🟡 Cukup (2) | 🟢 Baik (3) | ✨ Sangat Baik (4).`,

        'media': `Berikan 5 Ide Media Pembelajaran Kreatif dan Murah (Low Cost). 
             Gunakan emoji 🎨 untuk menunjukkan kreativitas dan ♻️ untuk bahan daur ulang.`,

        'ice': `Berikan 3 Ide Ice Breaking yang seru (⚡) dan relevan dengan topik ini.`,

        'misconception': `Identifikasi 5 Miskonsepsi umum siswa (❌) dan cara meluruskannya (✅) dalam bentuk TABEL HTML.`
    };

    btn.addEventListener('click', async () => {
        if (!currentData.materi) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Silakan Generate RPM Utama terlebih dahulu!', confirmButtonColor: '#6366f1' });
            return;
        }

        showLoading(title, "Sedang menyusun RPM...");
        try {
            let basePrompt = specificPrompts[promptType] || `Buatkan konten ${title} yang mendetail.`;
            let finalPrompt = `${basePrompt} 
                               Konteks: Materi '${currentData.materi}' untuk Kelas ${currentData.kelas}, ${currentData.sekolah}. 
                               Gunakan Bahasa Indonesia yang baku. Format output harus HTML bersih (tanpa markdown box).`;

            if (promptType === 'image') {
                const imgPrompt = `Illustration for educational material: ${currentData.materi} topic, high quality, realistic style.`;
                const imgUrl = await fetchImagen(imgPrompt);
                appendAttachment(title, `<div style="text-align: center; margin: 20px 0;"><img src="${imgUrl}" style="max-width:100%; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>`, 'general');
                hideLoading(); return;
            }

            // Call Gemini
            const payload = { contents: [{ parts: [{ text: finalPrompt }] }] };
            const res = await fetchGemini(payload);
            let content = res.candidates[0].content.parts[0].text;

            // Clean markdown
            content = content.replace(/```html/g, '').replace(/```/g, '');
            appendAttachment(title, content, promptType);

        } catch (e) {
            Swal.fire('Gagal', e.message, 'error');
        } finally {
            hideLoading();
        }
    });
}

// Initialize Tools
const tools = [
    ['btn-atp', 'Alur Tujuan Pembelajaran', 'atp'], ['btn-materi', 'Materi Ajar', 'materi'], ['btn-lkpd', 'Lembar Kerja Peserta Didik', 'lkpd'],
    ['btn-quiz', 'Kuis & Evaluasi', 'quiz'], ['btn-akm', 'Soal AKM', 'akm'], ['btn-rubric', 'Rubrik', 'rubric'],
    // ... add all other tools from list
    ['btn-media', 'Ide Media', 'media'], ['btn-ice-breaking', 'Ice Breaking', 'ice'], ['btn-illustrator', 'Visual AI', 'image']
];
tools.forEach(t => handleMagicTool(t[0], t[1], t[2]));


async function fetchImagen(prompt) {
    // Dynamically retrieve the latest key
    const dbKeyInput = document.getElementById('db-api-key');
    const dbKey = dbKeyInput ? dbKeyInput.value : "";
    const currentKey = dbKey || localStorage.getItem('custom_api_key') || apiKey;

    if (!currentKey) {
        throw new Error("API Key belum diset.");
    }

    const url = `https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-generate-001:predict?key=${currentKey}`;
    const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ instances: [{ prompt }], parameters: { sampleCount: 1 } }) });
    const json = await res.json();
    return `data:image/png;base64,${json.predictions[0].bytesBase64Encoded}`;
}

// --- IMPORTANT: PDF DOWNLOAD REPLACEMENT ---
const btnDownloadPdf = document.getElementById('download-pdf');
if (btnDownloadPdf) {
    btnDownloadPdf.addEventListener('click', () => {
        const previewContainer = document.getElementById('preview-container');
        if (!previewContainer || previewContainer.innerHTML.trim() === '') {
            Swal.fire('Error', 'Belum ada konten untuk dicetak.', 'error');
            return;
        }

        const guru = document.getElementById('nama-guru').value.replace(/\s+/g, '_');
        const sekolah = document.getElementById('nama-sekolah').value.replace(/\s+/g, '_');
        const filename = `RPM_${guru}_${sekolah}.pdf`;

        // Create Hidden Form for Server-Side PDF Generation
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'rpm/cetak_rpm'; // CodeIgniter Controller Endpoint
        form.target = '_blank'; // Open in new tab

        const inputContent = document.createElement('input');
        inputContent.type = 'hidden';
        inputContent.name = 'content';
        inputContent.value = previewContainer.innerHTML; // Send the INNER HTML of the preview

        const inputFilename = document.createElement('input');
        inputFilename.type = 'hidden';
        inputFilename.name = 'filename';
        inputFilename.value = filename;

        form.appendChild(inputContent);
        form.appendChild(inputFilename);

        // Include CSRF Token if available
        if (typeof CSRF_NAME !== 'undefined' && typeof CSRF_HASH !== 'undefined') {
            const inputCsrf = document.createElement('input');
            inputCsrf.type = 'hidden';
            inputCsrf.name = CSRF_NAME;
            inputCsrf.value = CSRF_HASH;
            form.appendChild(inputCsrf);
        }

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    });
}

// --- WORD DOWNLOAD LOGIC ---
// --- WORD DOWNLOAD LOGIC (SPLIT PER SECTION) ---
function downloadWordDoc(type) {
    const previewContainer = document.getElementById('preview-container');
    if (!previewContainer || previewContainer.innerHTML.trim() === '') {
        Swal.fire('Error', 'Belum ada konten untuk diunduh.', 'error');
        return;
    }

    // Get all sheets
    const sheets = Array.from(previewContainer.querySelectorAll('.paper-sheet'));
    if (sheets.length === 0) return;

    let contentToDownload = '';

    // Filter Logic
    if (type === 'all') {
        contentToDownload = previewContainer.innerHTML;
    } else {
        const filteredSheets = sheets.filter((sheet, index) => {
            const text = sheet.innerText.toLowerCase();
            const html = sheet.innerHTML;
            const isAttachment = html.includes('Lampiran') || sheet.querySelector('.attachment-content');

            if (type === 'rpm') {
                // RPM Utama is usually the first sheet and is NOT an attachment
                return !isAttachment;
            } else if (type === 'materi') {
                return text.includes('materi ajar');
            } else if (type === 'lkpd') {
                return text.includes('lembar kerja') || text.includes('lkpd');
            } else if (type === 'quiz') {
                return text.includes('kuis') || text.includes('soal') || text.includes('evaluasi') || text.includes('akm');
            } else if (type === 'lain') {
                // Attachments that are NOT the above
                if (!isAttachment) return false;
                return !(text.includes('materi ajar') || text.includes('lembar kerja') || text.includes('lkpd') || text.includes('kuis') || text.includes('soal') || text.includes('evaluasi') || text.includes('akm'));
            }
            return false;
        });

        if (filteredSheets.length === 0) {
            Swal.fire('Info', `Bagian '${type.toUpperCase()}' belum tersedia/dibuat.`, 'info');
            return;
        }

        // Add page break between sheets for cleanliness
        filteredSheets.forEach(s => {
            contentToDownload += s.outerHTML + '<br style="page-break-after: always;">';
        });
    }

    // CSS Styles for Word
    const css = `
        body { font-family: 'Bookman Old Style', Arial, sans-serif; font-size: 11pt; line-height: 1.15; }
        .paper-sheet { padding: 0.5in; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 1em; }
        td, th { vertical-align: top; padding: 4px; }
        
        /* Specific Tables */
        .table-data { border: 1px solid black; }
        .table-data th, .table-data td { border: 1px solid black; padding: 5px; }
        .table-data th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        
        .checklist-table { border: 1px solid black; }
        .checklist-table td { border: 1px solid black; }
        .check-box-cell { text-align: center; font-weight: bold; font-size: 14pt; width: 40px; }
        
        .identitas-table td { border: none; padding: 2px 0; }
        
        /* Typography */
        .doc-title-topic { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 16px; text-decoration: underline; }
        .section-header { font-size: 12pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px; text-transform: uppercase; }
        .sub-section-header { font-weight: bold; display: inline-block; min-width: 100px; }
        
        /* Lists */
        ol, ul { margin-top: 5px; margin-bottom: 5px; padding-left: 25px; }
        li { margin-bottom: 5px; }
        
        /* Helper */
        .text-center { text-align: center; }
        .font-weight-bold { font-weight: bold; }

        /* TEXTBOOK BOXES */
        .box-definisi { background-color: #e3f2fd; border-left: 5px solid #2196f3; padding: 15px; margin: 15px 0; }
        .box-rumus { background-color: #fff3e0; border-left: 5px solid #ff9800; padding: 15px; margin: 15px 0; font-family: 'Courier New', monospace; }
        .box-info { background-color: #fce4ec; border-left: 5px solid #ec407a; padding: 15px; margin: 15px 0; }
        
        /* IMAGES responsive in Word */
        figure { text-align: center; margin: 10px 0; }
        img { max-width: 100%; height: auto; }
    `;

    // Setup Word Header
    const preHtml = `<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
    <head>
        <meta charset='utf-8'>
        <title>RPM Document</title>
        <style>${css}</style>
    </head><body>`;
    const postHtml = "</body></html>";

    // Combine content
    const html = preHtml + contentToDownload + postHtml;

    // Create Blob
    const blob = new Blob(['\ufeff', html], {
        type: 'application/msword'
    });

    // Generate Filename
    const guruVal = document.getElementById('nama-guru') ? document.getElementById('nama-guru').value : 'Guru';
    const sekolahVal = document.getElementById('nama-sekolah') ? document.getElementById('nama-sekolah').value : 'Sekolah';
    const cleanGuru = guruVal.replace(/[^a-zA-Z0-9]/g, '_') || 'Guru';
    const cleanSekolah = sekolahVal.replace(/[^a-zA-Z0-9]/g, '_') || 'Sekolah';

    // Suffix based on type
    let suffix = 'FULL';
    if (type === 'rpm') suffix = 'UTAMA';
    if (type === 'materi') suffix = 'MATERIAJAR';
    if (type === 'lkpd') suffix = 'LKPD';
    if (type === 'quiz') suffix = 'SOAL_KUIS';
    if (type === 'lain') suffix = 'LAMPIRAN_LAIN';

    const filename = `RPM_${suffix}_${cleanGuru}_${cleanSekolah}.doc`;

    // Trigger Download
    const downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);

    if (navigator.msSaveOrOpenBlob) {
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        const url = URL.createObjectURL(blob);
        downloadLink.href = url;
        downloadLink.download = filename;
        downloadLink.click();
        URL.revokeObjectURL(url);
    }

    document.body.removeChild(downloadLink);
}

// Bind Events to Dropdown Items
['all', 'rpm', 'materi', 'lkpd', 'quiz', 'lain'].forEach(t => {
    const btn = document.getElementById('dw-' + t);
    if (btn) btn.addEventListener('click', (e) => {
        e.preventDefault();
        downloadWordDoc(t);
    });
});