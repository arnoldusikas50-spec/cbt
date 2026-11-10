const { useState, useEffect, useRef } = React;

// ==========================================
// 1. SHARED COMPONENTS & UTILS
// ==========================================

const Spinner = () => (
    <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
);

// MODIFIED: Added locked (readOnly) support with visual cue
const InputField = ({ label, name, value, onChange, placeholder, required = false, type = 'text', className, locked = false }) => (
    <div className={`mb-3 ${className}`}>
        <label className="flex items-center justify-between text-xs font-semibold text-slate-600 mb-1">
            <span>{label}</span>
            {locked && <span className="text-[10px] bg-slate-200 text-slate-500 px-1.5 rounded flex items-center gap-1"><i className="fa-solid fa-lock"></i> Terkunci Lisensi</span>}
        </label>
        <input
            type={type}
            name={name}
            value={value}
            onChange={locked ? undefined : onChange}
            placeholder={placeholder}
            required={required}
            readOnly={locked}
            className={`w-full px-3 py-2 border rounded-lg focus:outline-none transition-all text-sm shadow-sm 
                        ${locked
                    ? 'bg-slate-100 border-slate-200 text-slate-500 cursor-not-allowed'
                    : 'bg-white border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'}`}
        />
    </div>
);

const TextareaField = ({ label, name, value, onChange, placeholder, required = false, rows = 4 }) => (
    <div className="mb-4">
        <label className="block text-sm font-medium text-slate-600 mb-1">{label}</label>
        <textarea
            name={name}
            value={value}
            onChange={onChange}
            placeholder={placeholder}
            required={required}
            rows={rows}
            className="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm shadow-sm resize-none"
        />
    </div>
);

// Generic Result View for non-Soal generators (MODIFIED: REMOVED DEMO LOGIC)
const GenericResultView = ({ content, onDownload, type }) => (
    <div className="bg-slate-100 rounded-xl border border-slate-200 p-4 h-[750px] flex flex-col sticky top-8">
        {content ? (
            <>
                <div className="flex-grow overflow-y-auto bg-white p-8 shadow-sm rounded-lg mb-4 border border-slate-100 custom-scroll relative">
                    {/* Tambahkan class rpm-content jika tipe RPM agar styling tabel masuk */}
                    <div className={`relative z-10 ${type === 'rpm' ? 'rpm-content' : 'prose prose-sm max-w-none'}`} dangerouslySetInnerHTML={{ __html: content }} />
                </div>

                <button onClick={onDownload} className="w-full py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-bold shadow-md transition-all flex items-center justify-center gap-2">
                    <i className="fa-solid fa-file-word"></i> Download Dokumen
                </button>
            </>
        ) : (
            <div className="flex flex-col items-center justify-center h-full text-slate-400">
                <i className="fa-solid fa-file-lines text-5xl mb-4 opacity-50"></i>
                <p>Hasil dokumen akan muncul di sini</p>
            </div>
        )}
    </div>
);

// ==========================================
// 2. AI SERVICES (TEXT & IMAGE)
// ==========================================

const apiKey = window.APP_CONFIG.apiKey ? window.APP_CONFIG.apiKey.trim() : '';

const AiService = {
    async generate(prompt, systemInstruction = "Anda adalah asisten administrasi guru profesional.", responseFormat = "text") {
        if (!apiKey) throw new Error("API Key belum diset. Silakan update profil Anda.");

        // Kembali ke v1beta dengan model flash yang modern dan cepat
        const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=${apiKey}`;

        const generationConfig = responseFormat === "json"
            ? { responseMimeType: "application/json" }
            : undefined;

        const payload = {
            contents: [{
                parts: [{ text: `${systemInstruction}\n\n${prompt}` }]
            }],
            generationConfig: generationConfig
        };

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const errText = await response.text();
                // try to parse json error if possible to show message
                let message = errText;
                try {
                    const errJson = JSON.parse(errText);
                    message = errJson.error?.message || errText;
                } catch (e) { }

                console.error("AI Error Detail:", message);
                throw new Error(`Google AI Error (${response.status}): ${message}`);
            }

            const data = await response.json();
            let textResult = data.candidates?.[0]?.content?.parts?.[0]?.text;

            if (!textResult) return "Tidak ada respon dari sistem.";

            if (responseFormat === "json") {
                // Sanitasi JSON
                textResult = textResult.replace(/```json|```/g, '').trim();
                try {
                    return JSON.parse(textResult);
                } catch (e) {
                    console.error("JSON Parse Error:", e);
                    throw new Error("Gagal memproses data soal. Format AI tidak valid.");
                }
            } else {
                // Sanitasi HTML/Text: Hapus markdown block fences jika ada
                textResult = textResult.replace(/^```html/i, '').replace(/^```/i, '').replace(/```$/i, '').trim();
            }
            return textResult;

        } catch (error) {
            console.error("Service Error:", error);
            throw new Error("Gagal memproses permintaan: " + error.message);
        }
    }
};

const ImageService = {
    async generateImage(prompt) {
        const url = `https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-generate-001:predict?key=${apiKey}`;

        const payload = {
            instances: [{ prompt: prompt + ", educational illustration, clean vector style, white background, high quality, minimal details for students" }],
            parameters: { sampleCount: 1 }
        };

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) throw new Error('Gagal generate gambar');

            const data = await response.json();
            const base64Image = data.predictions?.[0]?.bytesBase64Encoded;

            if (base64Image) {
                return `data:image/png;base64,${base64Image}`;
            }
            return null;
        } catch (error) {
            console.error("Image Gen Error:", error);
            return null;
        }
    }
};

const downloadWord = (htmlContent, filename) => {
    const header = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='[http://www.w3.org/TR/REC-html40](http://www.w3.org/TR/REC-html40)'><head><meta charset='utf-8'><title>Export HTML to Word Document</title></head><body>";
    const footer = "</body></html>";
    const sourceHTML = header + htmlContent + footer;
    const source = 'data:application/vnd.ms-word;charset=utf-8,' + encodeURIComponent(sourceHTML);
    const fileDownload = document.createElement("a");
    document.body.appendChild(fileDownload);
    fileDownload.href = source;
    fileDownload.download = filename;
    fileDownload.click();
    document.body.removeChild(fileDownload);
}

// ==========================================
// 4. GENERATORS
// ==========================================

const GeneratorLayout = ({ title, icon, color, formContent, result, onDownload, onBack, customResultView, type }) => (
    <div className="max-w-7xl mx-auto px-4 py-6 fade-in">
        <button onClick={onBack} className={`mb-6 flex items-center text-slate-500 hover:text-${color}-600 font-medium transition-colors`}>
            <i className="fa-solid fa-arrow-left mr-2"></i> Kembali ke Menu
        </button>
        <div className="grid lg:grid-cols-2 gap-8">
            <div className="space-y-6">
                <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h2 className={`text-2xl font-bold text-slate-800 mb-1 flex items-center gap-2`}>
                        <i className={`fa-solid ${icon} text-${color}-500`}></i> {title}
                    </h2>
                    <p className="text-slate-500 text-sm mb-6">Lengkapi data berikut untuk generate dokumen.</p>
                    {formContent}
                </div>
            </div>
            {/* Pass isDemo to GenericResultView */}
            {customResultView ? customResultView : <GenericResultView content={result} onDownload={onDownload} type={type} />}
        </div>
    </div>
);

// --- 4.1 Question Generator (FIXED & IMPROVED) ---
const QuestionResultView = ({ data, viewMode, setViewMode, scale, setScale }) => {
    const contentRef = useRef(null);

    const handleDownload = () => {
        if (!contentRef.current) return;

        // CSS Injeksi Khusus untuk Word agar "Sama Persis"
        // Menggunakan Tabel untuk layouting karena Flexbox tidak didukung baik di Word
        const css = `
                    <style>
                        @page { size: A4; margin: 2cm; }
                        body { font-family: 'Times New Roman', sans-serif; color: #000; }
                        
                        /* Reset Style */
                        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                        
                        /* Table Borders */
                        .border { border: 1px solid #000; }
                        .border-bottom { border-bottom: 1px solid #000; }
                        .border-right { border-right: 1px solid #000; }
                        
                        /* Text Alignment */
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .font-bold { font-weight: bold; }
                        .uppercase { text-transform: uppercase; }
                        .underline { text-decoration: underline; }
                        
                        /* Spacing */
                        .p-2 { padding: 5px; }
                        .mb-6 { margin-bottom: 20px; }
                        .mt-12 { margin-top: 40px; }
                        .h-20 { height: 80px; }
                        
                        /* Colors - Word handles simple colors */
                        .bg-slate-100 { background-color: #f1f5f9; }
                        
                        /* Signature Table */
                        .signature-table { width: 100%; margin-top: 40px; border: none; }
                        .signature-table td { border: none; vertical-align: top; text-align: center; width: 33%; }

                        /* --- FIX GAMBAR RAKSASA DI WORD --- */
                        img { 
                            width: 180px; /* Paksa lebar gambar agar tidak raksasa */
                            height: auto; 
                            display: block;
                            margin: 10px auto; 
                        }
                    </style>
                `;

        const contentHTML = contentRef.current.innerHTML;
        const title = viewMode === 'kisi-kisi' ? 'Kisi-Kisi Soal' : 'Kartu Soal';
        const filename = `${title}_${data.input.subject || 'Dokumen'}.doc`;

        downloadWord(css + contentHTML, filename);
    };

    if (!data) {
        return (
            <div className="bg-slate-100 rounded-xl border border-slate-200 p-4 h-[750px] flex flex-col items-center justify-center text-slate-400">
                <div className="w-32 h-32 bg-slate-200 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <i className="fa-solid fa-file-invoice text-5xl text-slate-300"></i>
                </div>
                <h3 className="text-xl font-bold text-slate-600">Preview Dokumen</h3>
                <p className="max-w-md mt-2 text-sm text-center">Lengkapi formulir di kiri untuk membuat Kartu Soal dan Kisi-kisi.</p>
            </div>
        );
    }

    return (
        <div className="flex-1 flex flex-col h-[750px] bg-slate-200/50 relative rounded-xl overflow-hidden border border-slate-300">

            <div className="h-14 bg-white border-b border-slate-200 flex items-center justify-center md:justify-between px-4 shrink-0 shadow-sm z-10">
                <div className="flex bg-slate-100 p-1 rounded-lg">
                    <button onClick={() => setViewMode('kisi-kisi')} className={`px-3 py-1.5 rounded-md text-xs font-medium transition-all flex items-center gap-2 ${viewMode === 'kisi-kisi' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}>
                        <i className="fa-solid fa-table"></i> Kisi-Kisi
                    </button>
                    <button onClick={() => setViewMode('kartu-soal')} className={`px-3 py-1.5 rounded-md text-xs font-medium transition-all flex items-center gap-2 ${viewMode === 'kartu-soal' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}>
                        <i className="fa-solid fa-list-check"></i> Kartu Soal
                    </button>
                </div>
                <div className="flex items-center gap-3">
                    <div className="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 hidden sm:flex">
                        <span className="text-xs font-bold text-slate-500">Zoom</span>
                        <input type="range" min="0.5" max="1.5" step="0.1" value={scale} onChange={(e) => setScale(parseFloat(e.target.value))} className="w-20 h-1 bg-slate-300 rounded-lg appearance-none cursor-pointer accent-indigo-600" />
                        <span className="text-xs w-8 text-right font-mono text-slate-600">{Math.round(scale * 100)}%</span>
                    </div>

                    <button onClick={() => window.print()} className="p-2 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Cetak / Save PDF"><i className="fa-solid fa-print"></i></button>
                    <button onClick={handleDownload} className="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 flex items-center gap-2 shadow-md transition-all">
                        <i className="fa-solid fa-file-word"></i> Download Word
                    </button>
                </div>
            </div>

            <div className="flex-1 overflow-auto p-8 custom-scroll bg-slate-100/50 flex justify-center relative">
                <div ref={contentRef} className="a4-page printable-area relative z-10" style={{ transform: `scale(${scale})`, marginBottom: `${(scale - 1) * 297}mm` }}>
                    {/* Kop Surat Sekolah */}
                    <div className="text-center border-b-4 border-double border-slate-900 pb-4 mb-6">
                        <h3 className="text-lg font-bold uppercase tracking-wide">Pemerintah Provinsi</h3>
                        <h2 className="text-xl font-bold uppercase tracking-wide">Dinas Pendidikan dan Kebudayaan</h2>
                        <h1 className="text-2xl font-black uppercase tracking-widest mt-1 text-slate-900">{data.input.schoolName || 'NAMA SEKOLAH ...'}</h1>
                        <p className="text-sm text-slate-600 mt-1 italic">Dokumen Asesmen Pembelajaran Standar - Tahun Ajaran {data.input.academicYear}</p>
                    </div>

                    {viewMode === 'kisi-kisi' ? (
                        <div>
                            <div className="text-center mb-6">
                                <h2 className="text-lg font-bold underline uppercase">Kisi-Kisi Penulisan Soal</h2>
                                <p className="font-medium text-slate-600 text-sm">Jenis: {data.input.examType} | Kurikulum: {data.input.curriculum === 'merdeka' ? 'Merdeka' : '2013'}</p>
                            </div>

                            <table className="w-full text-sm border-collapse mb-6 border-0">
                                <tbody>
                                    <tr>
                                        <td className="font-semibold py-1 w-32 border-0">Jenjang</td>
                                        <td className="py-1 border-0">: {data.input.educationLevel}</td>
                                        <td className="font-semibold py-1 w-32 border-0">Mata Pelajaran</td>
                                        <td className="py-1 border-0">: {data.input.subject}</td>
                                    </tr>
                                    <tr>
                                        <td className="font-semibold py-1 border-0">Kelas / Fase</td>
                                        <td className="py-1 border-0">: {data.input.grade} / {data.input.phase || '-'}</td>
                                        <td className="font-semibold py-1 border-0">Alokasi Waktu</td>
                                        <td className="py-1 border-0">: {data.input.examDuration} Menit</td>
                                    </tr>
                                    <tr>
                                        <td className="font-semibold py-1 border-0">Jumlah Soal</td>
                                        <td className="py-1 border-0">: {data.input.questionCount} Butir</td>
                                        <td className="font-semibold py-1 border-0">Semester</td>
                                        <td className="py-1 border-0">: {data.input.semester}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table className="w-full text-sm border-collapse border border-slate-800">
                                <thead className="bg-slate-100 text-center font-bold">
                                    <tr>
                                        <th className="border border-slate-800 p-2 w-10">No</th>
                                        <th className="border border-slate-800 p-2 w-1/4">{data.input.curriculum === 'merdeka' ? 'CP & TP (Fase ' + data.input.phase + ')' : 'Kompetensi Dasar'}</th>
                                        <th className="border border-slate-800 p-2 w-1/6">Materi</th>
                                        <th className="border border-slate-800 p-2">Indikator Soal</th>
                                        <th className="border border-slate-800 p-2 w-20">Level</th>
                                        <th className="border border-slate-800 p-2 w-16">No Soal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.questions.map((q, idx) => (
                                        <tr key={idx}>
                                            <td className="border border-slate-800 p-2 text-center">{idx + 1}</td>
                                            <td className="border border-slate-800 p-2">
                                                <div className="mb-2 font-medium">{q.competency}</div>
                                            </td>
                                            <td className="border border-slate-800 p-2">
                                                <div>{data.input.topic}</div>
                                            </td>
                                            <td className="border border-slate-800 p-2">{q.indicator}</td>
                                            <td className="border border-slate-800 p-2 text-center whitespace-nowrap">
                                                <span className="px-2 py-1 rounded text-xs font-bold bg-slate-100 border border-slate-300">
                                                    {q.cognitive_level}
                                                </span>
                                            </td>
                                            <td className="border border-slate-800 p-2 text-center font-bold">{idx + 1}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="space-y-6">
                            <div className="text-center mb-6">
                                <h2 className="text-lg font-bold underline uppercase">Kartu Soal Pilihan Ganda</h2>
                                <div className="flex justify-center gap-8 text-sm mt-2 text-slate-700">
                                    <span><strong>Mapel:</strong> {data.input.subject}</span>
                                    <span><strong>Kelas:</strong> {data.input.grade}</span>
                                    <span><strong>Penyusun:</strong> {data.input.teacherName}</span>
                                </div>
                            </div>

                            {data.questions.map((q, idx) => (
                                <div key={idx} className="card-soal shadow-sm" style={{ border: '1px solid #000' }}>
                                    <table className="w-full border-collapse">
                                        <tr className="bg-slate-100 border-b border-slate-300">
                                            <td className="p-2 border-r border-slate-300 font-bold w-16 text-center">No. {idx + 1}</td>
                                            <td className="p-2 border-r border-slate-300"><span className="font-semibold text-slate-500">Materi:</span> {data.input.topic}</td>
                                            <td className="p-2 w-48 text-center font-semibold text-xs uppercase bg-slate-200">{q.cognitive_level}</td>
                                        </tr>
                                        <tr>
                                            <td colSpan="3" className="p-0">
                                                <table className="w-full border-collapse">
                                                    <tr>
                                                        <td className="w-1/3 border-r border-slate-300 p-3 bg-slate-50/50 text-xs align-top">
                                                            <div className="mb-2">
                                                                <strong className="block text-slate-500 mb-1">{data.input.curriculum === 'merdeka' ? 'Capaian Pembelajaran (CP):' : 'Kompetensi Dasar (KD):'}</strong>
                                                                <p className="leading-snug">{q.competency}</p>
                                                            </div>
                                                            <div className="pt-2 border-t border-slate-200">
                                                                <strong className="block text-slate-500 mb-1">Indikator Soal:</strong>
                                                                <p className="leading-snug">{q.indicator}</p>
                                                            </div>
                                                        </td>
                                                        <td className="w-2/3 p-4 align-top">
                                                            {q.imageUrl && (
                                                                <div className="mb-4 flex justify-center">
                                                                    <img
                                                                        src={q.imageUrl}
                                                                        alt="Ilustrasi Soal"
                                                                        className="max-w-full h-auto max-h-64 object-contain border border-slate-200 rounded p-1"
                                                                    />
                                                                </div>
                                                            )}

                                                            <p className="mb-4 text-base leading-relaxed font-medium">{q.question}</p>
                                                            <div className="space-y-1 ml-2">
                                                                {q.options.map((opt) => (
                                                                    <div key={opt.label} className="flex gap-2 items-start">
                                                                        <span className="font-bold w-5">{opt.label}.</span>
                                                                        <span className="text-slate-700">{opt.text}</span>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                            <div className="mt-4 pt-3 border-t border-dashed border-slate-300 text-right">
                                                                <span className="text-xs font-bold text-slate-400 uppercase mr-2">Kunci Jawaban</span>
                                                                <span className="font-bold text-white bg-slate-800 px-3 py-1 rounded shadow-sm" style={{ backgroundColor: '#1e293b', color: 'white' }}>{q.key}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* TANDA TANGAN (Menggunakan Tabel agar kompatibel dengan Word) */}
                    <table className="signature-table mt-12 pt-8" style={{ width: '100%', marginTop: '40px', border: 'none' }}>
                        <tr>
                            <td style={{ width: '33%', textAlign: 'center', border: 'none' }}>
                                <p className="text-sm mb-1">Mengetahui,</p>
                                <p className="text-sm font-semibold">Kepala Sekolah</p>
                                <div className="h-20"></div>
                                <p className="font-bold underline uppercase">{data.input.principalName || '....................................'}</p>
                                <p className="text-xs">NIP. {data.input.principalNip || '....................................'}</p>
                            </td>
                            <td style={{ width: '33%', border: 'none' }}></td>
                            <td style={{ width: '33%', textAlign: 'center', border: 'none' }}>
                                <p className="text-sm mb-1">{data.input.city || 'Kota ...'}, {new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
                                <p className="text-sm font-semibold">Guru Mata Pelajaran</p>
                                <div className="h-20"></div>
                                <p className="font-bold underline uppercase">{data.input.teacherName || '....................................'}</p>
                                <p className="text-xs">NIP. {data.input.teacherNip || '....................................'}</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    );
};

const QuestionGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        // Identity - Pre-filled from License if available
        schoolName: userProfile?.school || '',
        teacherName: userProfile?.name || '',
        teacherNip: userProfile?.nip || '',
        academicYear: '2024/2025',
        examType: 'Sumatif Tengah Semester',
        examDuration: 60,
        city: userProfile?.city || '',
        principalName: userProfile?.principalName || '',
        principalNip: userProfile?.principalNip || '',

        // Content
        educationLevel: 'SMA/MA/SMK', // SD/MI, SMP/MTs, SMA/MA/SMK
        grade: '10',
        phase: 'E',
        subject: '',
        semester: 'Ganjil',
        topic: '',

        // Params
        questionCount: 5,
        optionCount: 5, // 3 for SD low, 4 for SD high/SMP, 5 for SMA
        lotsPct: 30,
        motsPct: 50,
        hotsPct: 20,
        questionFormat: 'text'
    });
    const [generatedData, setGeneratedData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [progress, setProgress] = useState("");
    const [viewMode, setViewMode] = useState('kisi-kisi');
    const [scale, setScale] = useState(0.85);

    // Helpers for logic
    const getGradesByLevel = (level) => {
        switch (level) {
            case 'SD/MI': return ['1', '2', '3', '4', '5', '6'];
            case 'SMP/MTs': return ['7', '8', '9'];
            case 'SMA/MA/SMK': return ['10', '11', '12'];
            default: return [];
        }
    };

    const getPhaseByGrade = (grade) => {
        const g = parseInt(grade);
        if (g <= 2) return 'A';
        if (g <= 4) return 'B';
        if (g <= 6) return 'C';
        if (g <= 9) return 'D';
        if (g === 10) return 'E';
        if (g >= 11) return 'F';
        return '';
    };

    const getDefaultOptionsCount = (level) => {
        if (level === 'SD/MI') return '3';
        if (level === 'SMP/MTs') return '4';
        return '5';
    }

    const handleChange = (e) => {
        const { name, value } = e.target;
        setData(prev => {
            const newData = { ...prev, [name]: value };

            // Logic when Level changes
            if (name === 'educationLevel') {
                const newGrades = getGradesByLevel(value);
                newData.grade = newGrades[0];
                newData.phase = getPhaseByGrade(newGrades[0]);
                newData.optionCount = getDefaultOptionsCount(value);
            }

            // Logic when Grade changes
            if (name === 'grade') {
                newData.phase = getPhaseByGrade(value);
            }

            return newData;
        });
    };

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        setProgress("Menulis soal dan kisi-kisi...");
        try {
            const prompt = `
                        Buatkan ${data.questionCount} soal pilihan ganda UNIK dan BERBEDA untuk jenjang ${data.educationLevel} mata pelajaran ${data.subject} kelas ${data.grade} (Fase ${data.phase}) dengan topik "${data.topic}".
                        Kurikulum: ${data.curriculum}.
                        Format tampilan: ${data.questionFormat === 'text' ? 'Hanya Teks' : 'Sertakan "image_prompt" dalam bahasa inggris yang mendetail untuk ilustrasi soal'}.
                        Format soal harus bervariasi dan TIDAK BOLEH ada soal yang sama.
                        Distribusi Tingkat Kesulitan: ${data.lotsPct}% LOTS, ${data.motsPct}% MOTS, ${data.hotsPct}% HOTS.
                        Jumlah Opsi Jawaban: ${data.optionCount} (A-${String.fromCharCode(64 + parseInt(data.optionCount))}).
                        
                        PENTING: Output HARUS berupa JSON Array murni tanpa markdown block.
                        [
                            {
                                "id": 1,
                                "question": "Pertanyaan...",
                                "options": [{"label": "A", "text": "..."}],
                                "key": "A",
                                "cognitive_level": "L1",
                                "indicator": "...",
                                "competency": "...",
                                "image_prompt": "..." (Opsional, hanya jika butuh gambar)
                            }
                        ]
                    `;

            // 1. Generate Text Questions
            const questions = await AiService.generate(prompt, "Anda adalah penulis soal profesional. Pastikan output JSON valid dan soal bervariasi.", "json");

            // 2. Generate Images (if needed)
            if (data.questionFormat !== 'text') {
                const totalWithImages = questions.length;
                for (let i = 0; i < totalWithImages; i++) {
                    const q = questions[i];
                    // Cek jika mode 'image' (semua) atau 'mixed' (acak)
                    const shouldGenerate = data.questionFormat === 'image' || (data.questionFormat === 'mixed' && Math.random() > 0.5);

                    if (shouldGenerate) {
                        setProgress(`Menggambar ilustrasi soal ${i + 1} dari ${totalWithImages}...`);
                        // Gunakan prompt dari AI jika ada, atau buat sendiri
                        const imgPrompt = q.image_prompt || `Illustration for exam question: ${q.question}`;
                        const base64Img = await ImageService.generateImage(imgPrompt);
                        if (base64Img) {
                            q.imageUrl = base64Img;
                        }
                    }
                }
            }

            setGeneratedData({
                input: data,
                questions: questions
            });

        } catch (e) {
            alert("Terjadi kesalahan: " + e.message);
        } finally {
            setLoading(false);
            setProgress("");
        }
    };

    const formContent = (
        <form onSubmit={generate}>
            {/* SECTION 1: Identitas Administrasi */}
            <div className="mb-4 border border-slate-200 rounded-lg overflow-hidden">
                <div className="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center gap-2">
                    <i className="fa-solid fa-school text-indigo-500"></i>
                    <h3 className="text-sm font-bold text-slate-700">Identitas Administrasi</h3>
                </div>
                <div className="p-4 space-y-3">
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Jenjang Pendidikan</label>
                            <select name="educationLevel" value={data.educationLevel} onChange={handleChange} className="w-full px-3 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="SD/MI">SD / MI (Sederajat)</option>
                                <option value="SMP/MTs">SMP / MTs (Sederajat)</option>
                                <option value="SMA/MA/SMK">SMA / MA / SMK</option>
                            </select>
                        </div>
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <InputField label="Nama Guru" name="teacherName" value={data.teacherName} onChange={handleChange} placeholder="Nama Lengkap" required locked={!!userProfile?.name} />
                        <InputField label="NIP Guru" name="teacherNip" value={data.teacherNip} onChange={handleChange} placeholder="NIP..." locked={!!userProfile?.nip} />
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Jenis Asesmen</label>
                            <select name="examType" value={data.examType} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="Sumatif Akhir Semester">SAS (Akhir Semester)</option>
                                <option value="Sumatif Tengah Semester">STS (Tengah Semester)</option>
                                <option value="Penilaian Harian">Formatif (Harian)</option>
                                <option value="Asesmen Nasional">Asesmen Nasional</option>
                                <option value="Ujian Sekolah">Ujian Sekolah</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Tahun Ajaran</label>
                            <select name="academicYear" value={data.academicYear} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="2024/2025">2024/2025</option>
                                <option value="2025/2026">2025/2026</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {/* SECTION 2: Pengesahan (Tanda Tangan) */}
            <div className="mb-4 border border-slate-200 rounded-lg overflow-hidden">
                <div className="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center gap-2">
                    <i className="fa-solid fa-signature text-indigo-500"></i>
                    <h3 className="text-sm font-bold text-slate-700">Format Tanda Tangan</h3>
                </div>
                <div className="p-4 space-y-3">
                    <InputField label="Kota (Tempat TTD)" name="city" value={data.city} onChange={handleChange} placeholder="Contoh: Jakarta" />
                    <div className="grid grid-cols-2 gap-3">
                        <InputField label="Nama Kepala Sekolah" name="principalName" value={data.principalName} onChange={handleChange} placeholder="Nama Kepala Sekolah" />
                        <InputField label="NIP Kepala Sekolah" name="principalNip" value={data.principalNip} onChange={handleChange} placeholder="NIP Kepsek..." />
                    </div>
                </div>
            </div>

            {/* SECTION 3: Kurikulum & Materi */}
            <div className="mb-4 border border-slate-200 rounded-lg overflow-hidden">
                <div className="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center gap-2">
                    <i className="fa-solid fa-book-open text-indigo-500"></i>
                    <h3 className="text-sm font-bold text-slate-700">Kurikulum & Materi</h3>
                </div>
                <div className="p-4 space-y-3">
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Kurikulum</label>
                            <select name="curriculum" value={data.curriculum} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="merdeka">Kurikulum Merdeka</option>
                                <option value="k13">Kurikulum 2013</option>
                            </select>
                        </div>
                        <InputField label="Mata Pelajaran" name="subject" value={data.subject} onChange={handleChange} placeholder="Contoh: IPAS, Matematika..." required />
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Kelas</label>
                            <select name="grade" value={data.grade} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                {getGradesByLevel(data.educationLevel).map(g => (
                                    <option key={g} value={g}>Kelas {g}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Fase (Otomatis)</label>
                            <input type="text" value={`Fase ${data.phase}`} disabled className="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-sm font-bold text-slate-500 cursor-not-allowed" />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Semester</label>
                            <select name="semester" value={data.semester} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                    </div>

                    <InputField label="Materi / Topik Utama" name="topic" value={data.topic} onChange={handleChange} placeholder="Contoh: Ekosistem, Aljabar..." required />

                    <div className="p-2 bg-slate-50 border border-slate-200 rounded-lg">
                        <label className="flex items-center gap-2 text-xs font-bold text-indigo-700 mb-1">
                            <i className="fa-regular fa-image"></i> Format Tampilan Soal
                        </label>
                        <select name="questionFormat" value={data.questionFormat} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-indigo-200 text-slate-700 rounded-lg text-sm font-medium focus:ring-2 focus:ring-indigo-500">
                            <option value="text">1. Soal Teks Saja</option>
                            <option value="image">2. Soal Bergambar (Semua)</option>
                            <option value="mixed">3. Soal Teks & Bergambar (Mix)</option>
                        </select>
                    </div>
                </div>
            </div>

            {/* SECTION 4: Parameter Asesmen */}
            <div className="mb-4 border border-slate-200 rounded-lg overflow-hidden">
                <div className="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center gap-2">
                    <i className="fa-solid fa-sliders text-indigo-500"></i>
                    <h3 className="text-sm font-bold text-slate-700">Parameter Asesmen</h3>
                </div>
                <div className="p-4 space-y-4">
                    <div className="grid grid-cols-3 gap-3">
                        <InputField label="Jumlah Soal" name="questionCount" type="number" value={data.questionCount} onChange={handleChange} placeholder="Maks 10" />
                        <InputField label="Durasi (Menit)" name="examDuration" type="number" value={data.examDuration} onChange={handleChange} />
                        <div className="mb-3">
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Opsi Jawaban</label>
                            <select name="optionCount" value={data.optionCount} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="3">3 (A-C) - SD Bawah</option>
                                <option value="4">4 (A-D) - SD/SMP</option>
                                <option value="5">5 (A-E) - SMA/SMK</option>
                            </select>
                        </div>
                    </div>

                    <div className="bg-slate-100 p-3 rounded-lg border border-slate-200">
                        <label className="block text-xs font-bold text-slate-600 mb-3">Distribusi Kesulitan</label>
                        <div className="space-y-4">
                            <div>
                                <div className="flex justify-between text-xs mb-1">
                                    <span className="font-bold text-green-700">LOTS (C1-C2)</span>
                                    <span className="font-mono text-slate-600">{data.lotsPct}%</span>
                                </div>
                                <input type="range" name="lotsPct" min="0" max="100" step="5" value={data.lotsPct} onChange={handleChange} className="w-full accent-green-600 h-1.5 bg-slate-300 rounded-lg appearance-none cursor-pointer" />
                            </div>
                            <div>
                                <div className="flex justify-between text-xs mb-1">
                                    <span className="font-bold text-yellow-700">MOTS (C3)</span>
                                    <span className="font-mono text-slate-600">{data.motsPct}%</span>
                                </div>
                                <input type="range" name="motsPct" min="0" max="100" step="5" value={data.motsPct} onChange={handleChange} className="w-full accent-yellow-500 h-1.5 bg-slate-300 rounded-lg appearance-none cursor-pointer" />
                            </div>
                            <div>
                                <div className="flex justify-between text-xs mb-1">
                                    <span className="font-bold text-rose-700">HOTS (C4-C6)</span>
                                    <span className="font-mono text-slate-600">{data.hotsPct}%</span>
                                </div>
                                <input type="range" name="hotsPct" min="0" max="100" step="5" value={data.hotsPct} onChange={handleChange} className="w-full accent-rose-600 h-1.5 bg-slate-300 rounded-lg appearance-none cursor-pointer" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" disabled={loading} className="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 font-bold mt-4 flex justify-center items-center gap-2 shadow-lg shadow-indigo-200 transition-all hover:-translate-y-0.5">
                {loading ? (
                    <>
                        <Spinner /> <span className="text-sm">{progress || 'Memproses...'}</span>
                    </>
                ) : (
                    <>
                        <i className="fa-solid fa-wand-magic-sparkles"></i> Generate Dokumen (AI)
                    </>
                )}
            </button>
        </form>
    );

    return (
        <GeneratorLayout
            title="Kartu Soal"
            icon="fa-clipboard-list"
            color="emerald"
            onBack={onBack}
            isDemo={userProfile?.isDemo} // Pass Demo Flag
            formContent={formContent}
            customResultView={
                <QuestionResultView
                    data={generatedData}
                    viewMode={viewMode}
                    setViewMode={setViewMode}
                    scale={scale}
                    setScale={setScale}
                    isDemo={userProfile?.isDemo} // Pass Demo Flag
                />
            }
        />
    );
};

// --- 3.2 RPM Generator (UPGRADED FOR STANDARD MODUL AJAR PEMBELAJARAN MENDALAM) ---
const RpmGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        // Identities
        educationLevel: 'SMA/MA/SMK',
        schoolName: userProfile?.school || '',
        teacherName: userProfile?.name || '',
        teacherId: userProfile?.nip || '',
        className: '10',
        fase: 'E',
        semester: '1',
        subject: '',
        topic: '',
        timeAllocation: '2 JP (2 x 35 Menit)',

        // PM Specifics
        characteristics: 'Gaya belajar visual dan kinestetik, menyukai permainan',
        digitalTools: 'Quizizz, Canva, Video Youtube',
        partnership: 'Orang Tua / Komunitas Sekitar',
        learningModel: 'Project Based Learning (PjBL)',

        // Signature (New Request)
        principalName: userProfile?.principalName || '',
        principalNip: userProfile?.principalNip || '',
        cityDate: userProfile?.city ? `${userProfile.city}, ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}` : ''
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    // Helpers Logic
    const getGradesByLevel = (level) => {
        switch (level) {
            case 'PAUD': return ['TK A', 'TK B'];
            case 'SD/MI': return ['1', '2', '3', '4', '5', '6'];
            case 'SMP/MTs': return ['7', '8', '9'];
            case 'SMA/MA/SMK': return ['10', '11', '12'];
            default: return [];
        }
    };

    const getPhaseByGrade = (grade, level) => {
        if (level === 'PAUD') return 'Fondasi';
        const g = parseInt(grade);
        if (isNaN(g)) return 'A'; // Default
        if (g <= 2) return 'A';
        if (g <= 4) return 'B';
        if (g <= 6) return 'C';
        if (g <= 9) return 'D';
        if (g === 10) return 'E';
        if (g >= 11) return 'F';
        return '';
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setData(prev => {
            const newData = { ...prev, [name]: value };
            // Auto logic
            if (name === 'educationLevel') {
                const newGrades = getGradesByLevel(value);
                newData.className = newGrades[0];
                newData.fase = getPhaseByGrade(newGrades[0], value);
            }
            if (name === 'className') {
                newData.fase = getPhaseByGrade(value, data.educationLevel);
            }
            return newData;
        });
    };

    // Helper untuk menambah karakteristik dari dropdown
    const addCharacteristic = (e) => {
        const val = e.target.value;
        if (!val) return;

        setData(prev => {
            const current = prev.characteristics;
            // Cek jika sudah ada
            if (current.includes(val)) return prev;

            const separator = current.length > 0 ? ', ' : '';
            return { ...prev, characteristics: current + separator + val };
        });

        // Reset select ke default
        e.target.value = "";
    };

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            // Prompt Engineering berdasarkan Standar Buku "Pembelajaran Mendalam (PM)"
            const prompt = `
                    Bertindaklah sebagai Ahli Kurikulum & Desainer Instruksional. Buat **MODUL AJAR PEMBELAJARAN MENDALAM (DEEP LEARNING)** yang sangat detail, profesional, dan estetis sesuai standar Kementerian Pendidikan Indonesia terbaru.

                    **DATA INPUT:**
                    - Jenjang: ${data.educationLevel}
                    - Kelas/Fase: ${data.className} / ${data.fase}
                    - Semester: ${data.semester}
                    - Mata Pelajaran: ${data.subject}
                    - Topik: ${data.topic}
                    - Alokasi Waktu: ${data.timeAllocation}
                    - Sekolah: ${data.schoolName}
                    - Guru: ${data.teacherName}
                    - Karakteristik Siswa: ${data.characteristics}
                    - Model Pembelajaran: ${data.learningModel}
                    - Teknologi: ${data.digitalTools}
                    - Mitra: ${data.partnership}

                    **INSTRUKSI FORMAT OUTPUT (HTML ONLY):**
                    Gunakan HTML dengan inline CSS untuk styling. Jangan gunakan Markdown (seperti ** atau ##).
                    Gunakan palette warna modern:
                    - Header Biru Laut (#0284c7) untuk Identitas & Desain.
                    - Header Teal/Hijau (#059669) untuk Komponen Inti.
                    - Header Oranye (#ea580c) untuk Langkah Pembelajaran.
                    - Header Ungu (#7c3aed) untuk Asesmen.

                    **STRUKTUR DOKUMEN (WAJIB IKUTI TABEL BERIKUT):**

                    1. **HEADER MODERN (Professional Left-Aligned Style)**:
                       Gunakan struktur HTML berikut untuk header yang sangat rapi dan modern:
                       <div style="font-family: 'Segoe UI', sans-serif; margin-bottom: 35px; color: #1e293b;">
                           <div style="border-left: 8px solid #0284c7; padding-left: 25px; margin-bottom: 30px;">
                               <h1 style="color: #0284c7; font-size: 32px; font-weight: 800; margin: 0; line-height: 1.1; letter-spacing: -0.5px;">MODUL AJAR</h1>
                               <h2 style="color: #334155; font-size: 24px; font-weight: 300; margin: 5px 0 0 0; line-height: 1.2;">PEMBELAJARAN MENDALAM (DEEP LEARNING)</h2>
                           </div>
                           
                           <table style="width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 25px;">
                               <tr>
                                   <td style="width: 160px; font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; padding: 8px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top;">Mata Pelajaran</td>
                                   <td style="font-size: 15px; font-weight: 600; color: #0f172a; padding: 8px 0; border-bottom: 1px solid #f1f5f9; padding-left: 20px;">: ${data.subject}</td>
                               </tr>
                               <tr>
                                   <td style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; padding: 8px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top;">Topik Utama</td>
                                   <td style="font-size: 15px; font-weight: 600; color: #0f172a; padding: 8px 0; border-bottom: 1px solid #f1f5f9; padding-left: 20px;">: ${data.topic}</td>
                               </tr>
                               <tr>
                                   <td style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; padding: 8px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top;">Kelas / Fase</td>
                                   <td style="font-size: 15px; font-weight: 600; color: #0f172a; padding: 8px 0; border-bottom: 1px solid #f1f5f9; padding-left: 20px;">: ${data.className} / ${data.fase}</td>
                               </tr>
                               <tr>
                                   <td style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; padding: 8px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top;">Alokasi Waktu</td>
                                   <td style="font-size: 15px; font-weight: 600; color: #0f172a; padding: 8px 0; border-bottom: 1px solid #f1f5f9; padding-left: 20px;">: ${data.timeAllocation}</td>
                               </tr>
                           </table>

                           <div style="display: flex; gap: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                               <div style="flex: 1;">
                                   <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">PENYUSUN</div>
                                   <div style="font-size: 16px; font-weight: 700; color: #334155;">${data.teacherName}</div>
                                   <div style="font-size: 12px; color: #64748b;">NIP. ${data.teacherId || '-'}</div>
                               </div>
                               <div style="width: 1px; background: #cbd5e1;"></div>
                               <div style="flex: 1; padding-left: 20px;">
                                   <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">INSTITUSI</div>
                                   <div style="font-size: 16px; font-weight: 700; color: #334155;">${data.schoolName}</div>
                                   <div style="font-size: 12px; color: #64748b;">${data.educationLevel} - ${data.academicYear}</div>
                               </div>
                           </div>
                       </div>

                    2. **TABEL 1: DESAIN PEMBELAJARAN**
                       Buat tabel dengan border, header background #0284c7 (text white).
                       Baris-baris:
                       - **Identifikasi Peserta Didik**: (Analisis mendalam berdasarkan input karakteristik).
                       - **Identifikasi Materi**: (Analisis relevansi materi dengan kehidupan nyata & struktur materi).
                       - **Dimensi Profil Pelajar Pancasila**: (Pilih dimensi yang relevan).
                       - **Lingkungan Pembelajaran**: (Deskripsi Fisik, Virtual, dan Budaya Belajar).
                       - **Pemanfaatan Digital**: (Cara teknologi digunakan secara interaktif/kolaboratif).

                    3. **TABEL 2: KOMPONEN INTI**
                       Buat tabel dengan border, header background #059669 (text white).
                       Baris-baris:
                       - **Capaian Pembelajaran (CP)**: (Tuliskan CP sesuai fase).
                       - **Lintas Disiplin Ilmu**: (Koneksi dengan mapel lain).
                       - **Tujuan Pembelajaran**: (ABCD: Audience, Behavior, Condition, Degree).
                       - **Topik Pembelajaran**: (Sub-bab materi).
                       - **Praktik Pedagogis**: (Jelaskan Model ${data.learningModel} & Strategi spesifik).
                       - **Kemitraan Pembelajaran**: (Peran ${data.partnership}).

                    4. **TABEL 3: LANGKAH-LANGKAH PEMBELAJARAN (Deep Learning Cycle)**
                       Buat tabel 3 kolom: TAHAP, KEGIATAN PEMBELAJARAN, ALOKASI WAKTU.
                       Header background #ea580c (text white).
                       
                       Isi Tabel:
                       - **AWAL**: (Orientasi, Apersepsi Kontekstual, Motivasi Menggembirakan).
                       - **INTI**:
                         *Sub-Header dalam sel: MEMAHAMI (Berkesadaran)* -> Aktivitas eksplorasi/konstruksi pengetahuan.
                         *Sub-Header dalam sel: MENGAPLIKASI (Bermakna)* -> Praktik nyata/proyek.
                         *Sub-Header dalam sel: MEREFLEKSI (Menggembirakan)* -> Evaluasi diri/diskusi.
                       - **PENUTUP**: (Umpan balik konstruktif, kesimpulan).

                    5. **TABEL 4: ASESMEN PEMBELAJARAN**
                       Buat tabel 3 kolom: JENIS, TEKNIK/INSTRUMEN, KETERANGAN.
                       Header background #7c3aed (text white).
                       Isi:
                       - **Asesmen Awal**: (Diagnostik kognitif/non-kognitif).
                       - **Asesmen Proses**: (Formatif: Observasi, Kinerja, LKPD).
                       - **Asesmen Akhir**: (Sumatif: Tes/Produk).

                    6. **LAMPIRAN (Daftar Saja)**: LKPD, Rubrik, Bahan Ajar.

                    7. **PENGESAHAN**: Tabel tanda tangan (Kepala Sekolah & Guru). Nama: ${data.principalName}, NIP: ${data.principalNip}. Tanggal: ${data.cityDate}.

                    **STYLE CSS TAMBAHAN:**
                    - Tabel harus width: 100%; border-collapse: collapse; margin-bottom: 20px; font-family: sans-serif; font-size: 14px;
                    - TH: padding: 10px; text-align: left; color: white;
                    - TD: padding: 10px; border: 1px solid #cbd5e1; color: #334155; vertical-align: top;
                    - Strong text untuk poin penting.
                    - Pastikan tampilan bersih, modern, dan tidak kaku.
                    `;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    const formContent = (
        <form onSubmit={generate}>
            {/* 1. IDENTITAS UTAMA */}
            <div className="mb-4 border border-slate-200 rounded-lg overflow-hidden">
                <div className="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center gap-2">
                    <i className="fa-solid fa-id-card text-sky-500"></i>
                    <h3 className="text-sm font-bold text-slate-700">Identitas Modul (Standar PM)</h3>
                </div>
                <div className="p-4 space-y-3">
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Jenjang</label>
                            <select name="educationLevel" value={data.educationLevel} onChange={handleChange} className="w-full px-3 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold rounded-lg text-sm">
                                <option value="PAUD">PAUD (TK/KB)</option>
                                <option value="SD/MI">SD / MI</option>
                                <option value="SMP/MTs">SMP / MTs</option>
                                <option value="SMA/MA/SMK">SMA / MA / SMK</option>
                            </select>
                        </div>
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Kelas</label>
                            <select name="className" value={data.className} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                {getGradesByLevel(data.educationLevel).map(g => (
                                    <option key={g} value={g}>{g}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Fase</label>
                            <input type="text" value={data.fase} disabled className="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-sm font-bold text-slate-500" />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Semester</label>
                            <select name="semester" value={data.semester} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="1">1 (Ganjil)</option>
                                <option value="2">2 (Genap)</option>
                            </select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <InputField label="Mata Pelajaran" name="subject" value={data.subject} onChange={handleChange} placeholder="Contoh: IPAS, Matematika" required />
                        <InputField label="Alokasi Waktu" name="timeAllocation" value={data.timeAllocation} onChange={handleChange} placeholder="4 JP" required />
                    </div>

                    <InputField label="Topik / Materi Utama" name="topic" value={data.topic} onChange={handleChange} placeholder="Contoh: Daur Hidup Hewan / Persamaan Kuadrat" required />
                </div>
            </div>

            {/* 2. KONTEKS PEMBELAJARAN MENDALAM */}
            <div className="mb-4 border border-slate-200 rounded-lg overflow-hidden">
                <div className="bg-sky-50 px-4 py-2 border-b border-sky-100 flex items-center gap-2">
                    <i className="fa-solid fa-brain text-sky-600"></i>
                    <h3 className="text-sm font-bold text-sky-800">Konteks Pembelajaran Mendalam</h3>
                </div>
                <div className="p-4 space-y-3">
                    <div className="bg-yellow-50 p-3 rounded-lg border border-yellow-100 mb-2">
                        <p className="text-[10px] text-yellow-800 leading-tight">
                            <i className="fa-solid fa-circle-info mr-1"></i>
                            Bagian ini digunakan AI untuk merancang aktivitas yang <strong>Berkesadaran, Bermakna, dan Menggembirakan</strong>.
                        </p>
                    </div>

                    {/* UPDATED: Karakteristik Siswa dengan Pilihan Dropdown Banyak Filter */}
                    <div>
                        <label className="block text-xs font-semibold text-slate-600 mb-1">Karakteristik Siswa (Kesiapan Belajar)</label>
                        <div className="space-y-2">
                            <select
                                onChange={addCharacteristic}
                                className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-600 focus:ring-2 focus:ring-sky-500 cursor-pointer"
                            >
                                <option value="">+ Pilih Filter Cepat (Klik untuk menambah)</option>
                                <optgroup label="Gaya Belajar">
                                    <option value="Visual Learner">Visual Learner (Suka Gambar)</option>
                                    <option value="Auditory Learner">Auditory Learner (Suka Mendengar)</option>
                                    <option value="Kinestetik">Kinestetik (Suka Bergerak)</option>
                                    <option value="Reading/Writing">Reading/Writing (Suka Membaca)</option>
                                </optgroup>
                                <optgroup label="Minat & Preferensi">
                                    <option value="Menyukai Permainan (Gamification)">Menyukai Permainan</option>
                                    <option value="Menyukai Seni & Kreativitas">Menyukai Seni</option>
                                    <option value="Menyukai Teknologi">Menyukai Teknologi</option>
                                    <option value="Menyukai Alam/Outdoor">Menyukai Alam/Outdoor</option>
                                    <option value="Suka Bercerita (Storytelling)">Suka Bercerita</option>
                                </optgroup>
                                <optgroup label="Kesiapan Kognitif">
                                    <option value="Paham Utuh (High)">Paham Utuh (Mahir)</option>
                                    <option value="Paham Sebagian (Middle)">Paham Sebagian (Sedang)</option>
                                    <option value="Belum Paham (Low/Intervensi)">Belum Paham (Butuh Bimbingan)</option>
                                </optgroup>
                            </select>
                            <textarea
                                name="characteristics"
                                value={data.characteristics}
                                onChange={handleChange}
                                rows={2}
                                className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500"
                                placeholder="Pilih dari dropdown atau ketik manual disini..."
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Model Pembelajaran</label>
                            <select name="learningModel" value={data.learningModel} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="Project Based Learning (PjBL)">Project Based Learning (PjBL)</option>
                                <option value="Problem Based Learning (PBL)">Problem Based Learning (PBL)</option>
                                <option value="Inquiry Learning">Inquiry Learning</option>
                                <option value="Discovery Learning">Discovery Learning</option>
                                <option value="Experiential Learning">Experiential Learning</option>
                            </select>
                        </div>
                        <InputField
                            label="Alat Digital / Teknologi"
                            name="digitalTools"
                            value={data.digitalTools}
                            onChange={handleChange}
                            placeholder="Quizizz, Canva, Google Earth..."
                        />
                    </div>

                    <InputField
                        label="Kemitraan Pembelajaran (Siapa yang dilibatkan?)"
                        name="partnership"
                        value={data.partnership}
                        onChange={handleChange}
                        placeholder="Orang Tua, Petani Lokal, Puskesmas, Tokoh Adat..."
                    />
                </div>
            </div>

            {/* 3. NEW SECTION: PENGESAHAN (TANDA TANGAN) */}
            <div className="mb-4 border border-slate-200 rounded-lg overflow-hidden">
                <div className="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center gap-2">
                    <i className="fa-solid fa-signature text-sky-500"></i>
                    <h3 className="text-sm font-bold text-slate-700">Format Tanda Tangan</h3>
                </div>
                <div className="p-4 space-y-3">
                    <div className="grid grid-cols-2 gap-3">
                        <InputField label="Nama Kepala Sekolah" name="principalName" value={data.principalName} onChange={handleChange} placeholder="Nama Kepsek..." />
                        <InputField label="NIP Kepala Sekolah" name="principalNip" value={data.principalNip} onChange={handleChange} placeholder="NIP..." />
                    </div>
                    <InputField label="Kota & Tanggal (Tanda Tangan)" name="cityDate" value={data.cityDate} onChange={handleChange} placeholder="Contoh: Surabaya, 20 Juli 2025" />
                </div>
            </div>

            <div className="flex gap-3 items-center pt-2">
                <button type="submit" disabled={loading} className="flex-1 py-3 bg-gradient-to-r from-sky-600 to-blue-600 text-white rounded-xl hover:shadow-lg font-bold transition-all flex justify-center items-center gap-2">
                    {loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate RPP (Deep Learning)
                </button>
            </div>
        </form>
    );

    return (
        <GeneratorLayout
            title="RPP Pembelajaran Mendalam (PM)"
            icon="fa-network-wired"
            color="sky"
            onBack={onBack}
            result={result}
            isDemo={userProfile?.isDemo} // Pass Demo Flag
            onDownload={() => downloadWord(result, 'RPP_PM_Ikasmedia.doc')}
            formContent={formContent}
            type="rpm"
        />
    );
};

// --- 3.3 RPS Generator (EXISTING) ---
const RpsGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        universityName: userProfile?.school || '',
        faculty: '', studyProgram: '', documentCode: '',
        courseName: '', courseCode: '', courseGroup: '', courseCredits: '', semester: '',
        dateOfPreparation: '',
        rpsDeveloper: userProfile?.name || '',
        rmkCoordinator: '', headOfProgram: '',
        learningModel: 'Project Based Learning', logoUrl: ''
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buat Rencana Pembelajaran Semester (RPS) standar DIKTI dalam format HTML.
                    
                    **ATURAN STYLE (Inline CSS):**
                    1. Font: Times New Roman, 11pt.
                    2. Tabel: Border collapse, border 1px solid black pada semua sel (th dan td).
                    3. Header Tabel: Background abu-abu (#e5e7eb), teks tebal, rata tengah.
                    4. Layout: Gunakan tabel untuk kop surat (Logo di kiri jika ada URL, Teks di tengah).
                    
                    **DATA INPUT:**
                    - Univ: ${data.universityName}, Fak: ${data.faculty}, Prodi: ${data.studyProgram}
                    - MK: ${data.courseName} (${data.courseCode}), Rumpun: ${data.courseGroup}
                    - SKS: ${data.courseCredits}, Sem: ${data.semester}, Tgl: ${data.dateOfPreparation}
                    - Dosen: ${data.rpsDeveloper}, KaProdi: ${data.headOfProgram}
                    - Model: ${data.learningModel}
                    - Logo URL: ${data.logoUrl}

                    **ISI DOKUMEN:**
                    1. Identitas Mata Kuliah & Pengesahan (Tanda Tangan)
                    2. Capaian Pembelajaran (CPL-PRODI dan CPMK)
                    3. Deskripsi Singkat MK & Bahan Kajian
                    4. Rencana Pembelajaran Mingguan (Tabel 16 Pertemuan: Minggu Ke, Kemampuan Akhir, Bahan Kajian, Bentuk Pembelajaran, Waktu, Pengalaman Belajar, Kriteria Penilaian, Bobot).
                    `;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Generator RPS" icon="fa-graduation-cap" color="violet" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'RPS_Ikasmedia.doc')}
            formContent={
                <form onSubmit={generate}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Nama Universitas" name="universityName" value={data.universityName} onChange={handleChange} required locked={!!userProfile?.school} />
                        <InputField label="Fakultas" name="faculty" value={data.faculty} onChange={handleChange} required />
                    </div>
                    <InputField label="URL Logo Universitas (Opsional)" name="logoUrl" value={data.logoUrl} onChange={handleChange} />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Program Studi" name="studyProgram" value={data.studyProgram} onChange={handleChange} required />
                        <InputField label="Kode Dokumen" name="documentCode" value={data.documentCode} onChange={handleChange} />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Nama Mata Kuliah" name="courseName" value={data.courseName} onChange={handleChange} required />
                        <InputField label="Kode Mata Kuliah" name="courseCode" value={data.courseCode} onChange={handleChange} required />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <InputField label="Rumpun MK" name="courseGroup" value={data.courseGroup} onChange={handleChange} placeholder="MK Wajib" required />
                        <InputField label="Bobot (sks)" name="courseCredits" value={data.courseCredits} onChange={handleChange} placeholder="3 SKS" required />
                        <InputField label="Semester" name="semester" value={data.semester} onChange={handleChange} type="number" required />
                    </div>
                    <InputField label="Model Pembelajaran" name="learningModel" value={data.learningModel} onChange={handleChange} required />

                    <h3 className="font-bold text-slate-700 mt-4 mb-2">Otorisasi & Penanggung Jawab</h3>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Pengembang RPS" name="rpsDeveloper" value={data.rpsDeveloper} onChange={handleChange} required locked={!!userProfile?.name} />
                        <InputField label="Tanggal Penyusunan" name="dateOfPreparation" value={data.dateOfPreparation} onChange={handleChange} required />
                        <InputField label="Koordinator RMK" name="rmkCoordinator" value={data.rmkCoordinator} onChange={handleChange} required />
                        <InputField label="Ketua Program Studi" name="headOfProgram" value={data.headOfProgram} onChange={handleChange} required />
                    </div>
                    <button type="submit" disabled={loading} className="w-full py-3 bg-violet-600 text-white rounded-lg hover:bg-violet-700 font-bold mt-4 flex justify-center items-center gap-2">{loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate RPS</button>
                </form>
            }
        />
    );
};

// --- 3.4 ATP Generator (EXISTING) ---
const AtpGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        penyusun: userProfile?.name || '',
        sekolah: userProfile?.school || '',
        mataPelajaran: '', fase: '', kelas: '',
        kepalaSekolah: '', guruPengampu: userProfile?.name || '', kotaTanggalTtd: ''
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buat ALUR TUJUAN PEMBELAJARAN (ATP) Kurikulum Merdeka format HTML dengan DESAIN MODERN WARNA-WARNI.
                    
                    **INSTRUKSI DESAIN (Inline CSS):**
                    1. **JUDUL BAGIAN:** Gunakan blok berwarna solid dengan teks putih.
                       - A. INFORMASI: Background BIRU (#3b82f6)
                       - B. RASIONAL: Background TEAL (#14b8a6)
                       - C. CAPAIAN PEMBELAJARAN: Background ORANYE (#f97316)
                       - D. ALUR TUJUAN: Background UNGU (#8b5cf6)
                    2. **TABEL:** Border collapse, border 1px solid #ddd. Header tabel background abu-abu (#f3f4f6).
                    3. **CONTAINER:** Bungkus dalam div lebar 21cm (A4) dengan padding.

                    **DATA:**
                    - Mapel: ${data.mataPelajaran}, Fase: ${data.fase}, Kelas: ${data.kelas}
                    - Sekolah: ${data.sekolah}, Guru: ${data.penyusun}
                    - Kepala Sekolah: ${data.kepalaSekolah}, Guru Pengampu: ${data.guruPengampu}

                    **ISI:**
                    Lengkapi setiap bagian (Rasional, CP per Elemen, Alur Tujuan Pembelajaran [Materi, Tujuan, Profil Pelajar Pancasila, Jam Pelajaran]) dengan konten edukatif yang relevan untuk mata pelajaran tersebut.`;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Generator ATP" icon="fa-sitemap" color="fuchsia" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'ATP_Ikasmedia.doc')}
            formContent={
                <form onSubmit={generate}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Mata Pelajaran" name="mataPelajaran" value={data.mataPelajaran} onChange={handleChange} required />
                        <InputField label="Penyusun" name="penyusun" value={data.penyusun} onChange={handleChange} required locked={!!userProfile?.name} />
                        <InputField label="Nama Sekolah" name="sekolah" value={data.sekolah} onChange={handleChange} required locked={!!userProfile?.school} />
                        <InputField label="Kelas" name="kelas" value={data.kelas} onChange={handleChange} required />
                        <InputField label="Fase" name="fase" value={data.fase} onChange={handleChange} required />
                        <InputField label="Kota & Tanggal TTD" name="kotaTanggalTtd" value={data.kotaTanggalTtd} onChange={handleChange} required />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Nama Kepala Sekolah" name="kepalaSekolah" value={data.kepalaSekolah} onChange={handleChange} required />
                        <InputField label="Nama Guru Pengampu" name="guruPengampu" value={data.guruPengampu} onChange={handleChange} required locked={!!userProfile?.name} />
                    </div>
                    <button type="submit" disabled={loading} className="w-full py-3 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700 font-bold mt-4 flex justify-center items-center gap-2">{loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate ATP</button>
                </form>
            }
        />
    );
};

// --- 3.5 RPD Generator (EXISTING) ---
const RpdGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        schoolName: userProfile?.school || '',
        teacherName: userProfile?.name || '',
        subject: '', gradeAndSemester: '',
        timeAllocation: '30 Menit', mainTopic: '', principalName: '',
        curriculumVicePrincipalName: '', signaturePlaceDate: ''
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buat Rencana Pembelajaran Digital (RPD) untuk Microteaching format HTML.
                    
                    **ATURAN TAMPILAN:**
                    - Font: Times New Roman, 12pt.
                    - Tabel Identitas: Borderless (tanpa garis), rapi.
                    - Tabel Kegiatan: Border penuh (1px solid black), header abu-abu.
                    - Judul: "RENCANA PEMBELAJARAN DIGITAL (RPD)" (Tebal, Tengah).

                    **DATA:**
                    Topik: ${data.mainTopic}, Mapel: ${data.subject}, Kelas: ${data.gradeAndSemester}.
                    Sekolah: ${data.schoolName}, Guru: ${data.teacherName}, Waktu: ${data.timeAllocation}.
                    
                    **ISI:**
                    A. Tujuan Pembelajaran
                    B. Kegiatan Pembelajaran (Pendahuluan, Inti [Sintaks Model], Penutup) dalam format TABEL.
                    C. Penilaian (Sikap, Pengetahuan, Keterampilan).
                    D. Pengesahan (Kepala Sekolah, Waka Kurikulum, Guru).`;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Generator RPD" icon="fa-chalkboard-user" color="rose" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'RPD_Ikasmedia.doc')}
            formContent={
                <form onSubmit={generate}>
                    <InputField label="Topik Utama Pembelajaran" name="mainTopic" value={data.mainTopic} onChange={handleChange} required />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                        <InputField label="Nama Guru" name="teacherName" value={data.teacherName} onChange={handleChange} required locked={!!userProfile?.name} />
                        <InputField label="Mata Pelajaran" name="subject" value={data.subject} onChange={handleChange} required />
                        <InputField label="Kelas & Semester" name="gradeAndSemester" value={data.gradeAndSemester} onChange={handleChange} required />
                        <InputField label="Alokasi Waktu" name="timeAllocation" value={data.timeAllocation} onChange={handleChange} required />
                        <InputField label="Kota & Tanggal TTD" name="signaturePlaceDate" value={data.signaturePlaceDate} onChange={handleChange} required />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Nama Kepala Sekolah" name="principalName" value={data.principalName} onChange={handleChange} required />
                        <InputField label="Nama Waka Kurikulum" name="curriculumVicePrincipalName" value={data.curriculumVicePrincipalName} onChange={handleChange} required />
                    </div>
                    <button type="submit" disabled={loading} className="w-full py-3 bg-rose-600 text-white rounded-lg hover:bg-rose-700 font-bold mt-4 flex justify-center items-center gap-2">{loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate RPD</button>
                </form>
            }
        />
    );
};

// --- 3.6 Journal Generator (EXISTING) ---
const JournalGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        schoolName: userProfile?.school || '',
        teacherName: userProfile?.name || '',
        subject: '', grade: '',
        date: new Date().toISOString().split('T')[0], material: '',
        classSituation: '', studentProgress: '', reflection: ''
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buat JURNAL REFLEKSI GURU format HTML Tabel Rapi.
                    
                    **ATURAN TAMPILAN:**
                    - Gunakan tabel border 1px solid black untuk seluruh konten jurnal.
                    - Header Tabel berwarna HIJAU MUDA (#dcfce7).
                    
                    **DATA:**
                    Sekolah: ${data.schoolName}, Guru: ${data.teacherName}
                    Mapel: ${data.subject}, Kelas: ${data.grade}, Tgl: ${data.date}
                    
                    **KONTEN JURNAL:**
                    1. Materi Pokok: ${data.material}
                    2. Situasi Kelas: ${data.classSituation} (Ubah jadi poin profesional)
                    3. Kemajuan Siswa: ${data.studentProgress}
                    4. Refleksi & Tindak Lanjut: ${data.reflection}`;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Jurnal Guru" icon="fa-book-journal-whills" color="lime" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'Jurnal_Ikasmedia.doc')}
            formContent={
                <form onSubmit={generate}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                        <InputField label="Nama Guru" name="teacherName" value={data.teacherName} onChange={handleChange} required locked={!!userProfile?.name} />
                        <InputField label="Mata Pelajaran" name="subject" value={data.subject} onChange={handleChange} required />
                        <InputField label="Kelas" name="grade" value={data.grade} onChange={handleChange} required />
                        <InputField label="Tanggal Mengajar" name="date" type="date" value={data.date} onChange={handleChange} required />
                        <InputField label="Materi Pokok" name="material" value={data.material} onChange={handleChange} required />
                    </div>
                    <TextareaField label="Situasi Kelas" name="classSituation" value={data.classSituation} onChange={handleChange} required />
                    <TextareaField label="Kemajuan & Hambatan Siswa" name="studentProgress" value={data.studentProgress} onChange={handleChange} required />
                    <TextareaField label="Refleksi & Ide Anda" name="reflection" value={data.reflection} onChange={handleChange} required />
                    <button type="submit" disabled={loading} className="w-full py-3 bg-lime-600 text-white rounded-lg hover:bg-lime-700 font-bold mt-4 flex justify-center items-center gap-2">{loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Buat Jurnal</button>
                </form>
            }
        />
    );
};

// --- 3.7 CK Generator (EXISTING) ---
const CkGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        studentName: '', studentId: '',
        schoolName: userProfile?.school || '',
        teacherName: userProfile?.name || '',
        subject: '',
        grade: '', semester: 'Ganjil', academicYear: '2024/2025',
        competencyElement: '', achievementLevel: 'Baik', signaturePlaceDate: ''
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buat Laporan CAPAIAN KOMPETENSI (CK) DEEP LEARNING format HTML.
                    
                    **ATURAN TAMPILAN:**
                    - Judul Besar: "LAPORAN CAPAIAN KOMPETENSI SISWA".
                    - Header Tabel Identitas: Background KUNING AMBER (#fcd34d).
                    - Border Tabel: 1px solid #333.
                    
                    **DATA:**
                    Siswa: ${data.studentName} (${data.studentId})
                    Sekolah: ${data.schoolName}, Mapel: ${data.subject}
                    Elemen: ${data.competencyElement}, Predikat: ${data.achievementLevel}
                    
                    **ISI NARASI:**
                    Buat narasi deskriptif 2-3 paragraf yang memotivasi tentang pencapaian siswa pada elemen tersebut. Hubungkan dengan profil Deep Learning (kritis, kreatif).
                    Tambahkan tabel Rekomendasi untuk Orang Tua.`;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Capaian Kompetensi" icon="fa-award" color="amber" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'CK_Ikasmedia.doc')}
            formContent={
                <form onSubmit={generate}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Nama Siswa" name="studentName" value={data.studentName} onChange={handleChange} required />
                        <InputField label="NIS/NISN" name="studentId" value={data.studentId} onChange={handleChange} required />
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                        <InputField label="Mata Pelajaran" name="subject" value={data.subject} onChange={handleChange} required />
                        <InputField label="Kelas" name="grade" value={data.grade} onChange={handleChange} required />
                        <InputField label="Tahun Ajaran" name="academicYear" value={data.academicYear} onChange={handleChange} required />
                        <div>
                            <label className="block text-sm font-medium text-slate-600 mb-1">Predikat Capaian</label>
                            <select name="achievementLevel" value={data.achievementLevel} onChange={handleChange} className="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg">
                                <option value="Sangat Baik">Sangat Baik</option>
                                <option value="Baik">Baik</option>
                                <option value="Cukup">Cukup</option>
                                <option value="Perlu Bimbingan">Perlu Bimbingan</option>
                            </select>
                        </div>
                        <InputField label="Kota & Tanggal Rapor" name="signaturePlaceDate" value={data.signaturePlaceDate} onChange={handleChange} required />
                    </div>
                    <InputField label="Elemen Kompetensi (Materi Utama)" name="competencyElement" value={data.competencyElement} onChange={handleChange} required />
                    <InputField label="Nama Guru Mata Pelajaran" name="teacherName" value={data.teacherName} onChange={handleChange} required locked={!!userProfile?.name} />
                    <button type="submit" disabled={loading} className="w-full py-3 bg-amber-500 text-white rounded-lg hover:bg-amber-600 font-bold mt-4 flex justify-center items-center gap-2">{loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate Narasi</button>
                </form>
            }
        />
    );
};

// --- 3.8 LKM Generator (EXISTING) ---
const LkmGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        educationLevel: 'SD/MI',
        schoolName: userProfile?.school || '',
        grade: '4',
        subject: '',
        topic: '',
        timeAllocation: '60 Menit',
        learningObjective: '',
        activityModel: 'Eksperimen / Percobaan Sederhana'
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buatkan **Lembar Kerja Peserta Didik (LKPD/LKM)** standar Kemendikbudristek siap cetak dalam format HTML.
                    
                    **ATURAN STYLING (Inline CSS):**
                    - Header Background Warna: Teal (#0d9488) dengan teks putih.
                    - Tabel: Border collapse, border 1px solid #ddd.
                    - Font: Arial/Sans-serif.
                    
                    **DATA INPUT:**
                    - Jenjang: ${data.educationLevel}
                    - Sekolah: ${data.schoolName}
                    - Kelas: ${data.grade}, Mapel: ${data.subject}
                    - Topik: ${data.topic}
                    - Alokasi Waktu: ${data.timeAllocation}
                    - Tujuan Pembelajaran: ${data.learningObjective}
                    - Model Aktivitas: ${data.activityModel}

                    **STRUKTUR KONTEN:**
                    1. **HEADER:** Identitas LKPD & Sekolah.
                    2. **TUJUAN PEMBELAJARAN:** Kompetensi yang akan dicapai.
                    3. **PETUNJUK BELAJAR:** Instruksi langkah demi langkah bagi siswa.
                    4. **ALAT & BAHAN:** (Sesuaikan dengan model aktivitas ${data.activityModel}).
                    5. **LANGKAH KERJA / AKTIVITAS INTI:** (Bagian paling detail, berisi panduan kegiatan siswa sesuai model).
                    6. **DISKUSI & PERTANYAAN PEMANTIK:** Soal-soal penalaran terkait aktivitas.
                    7. **KESIMPULAN & REFLEKSI:** Kolom untuk siswa menyimpulkan.
                    
                    Buat semenarik mungkin untuk siswa.
                    `;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="LKM Generator" icon="fa-file-pen" color="teal" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'LKM_Ikasmedia.doc')}
            formContent={
                <form onSubmit={generate}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Jenjang</label>
                            <select name="educationLevel" value={data.educationLevel} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="PAUD">PAUD</option>
                                <option value="SD/MI">SD / MI</option>
                                <option value="SMP/MTs">SMP / MTs</option>
                                <option value="SMA/MA/SMK">SMA / MA / SMK</option>
                            </select>
                        </div>
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                    </div>
                    <div className="grid grid-cols-3 gap-3">
                        <InputField label="Kelas" name="grade" value={data.grade} onChange={handleChange} required />
                        <InputField label="Mata Pelajaran" name="subject" value={data.subject} onChange={handleChange} required />
                        <InputField label="Waktu" name="timeAllocation" value={data.timeAllocation} onChange={handleChange} required />
                    </div>
                    <InputField label="Topik / Materi" name="topic" value={data.topic} onChange={handleChange} placeholder="Misal: Perubahan Wujud Benda" required />
                    <TextareaField label="Tujuan Pembelajaran" name="learningObjective" value={data.learningObjective} onChange={handleChange} placeholder="Siswa mampu menjelaskan..." required rows={2} />

                    <div className="mb-3">
                        <label className="block text-xs font-semibold text-slate-600 mb-1">Model Aktivitas</label>
                        <select name="activityModel" value={data.activityModel} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                            <option value="Eksperimen / Percobaan">Eksperimen / Percobaan</option>
                            <option value="Diskusi Kelompok">Diskusi Kelompok</option>
                            <option value="Studi Kasus / Masalah">Studi Kasus / Masalah</option>
                            <option value="Proyek Kreatif">Proyek Kreatif</option>
                            <option value="Pengamatan Lapangan">Pengamatan Lapangan</option>
                        </select>
                    </div>

                    <button type="submit" disabled={loading} className="w-full py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-bold mt-4 flex justify-center items-center gap-2">{loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate LKM</button>
                </form>
            }
        />
    );
};

// --- 3.9 Rubrik Generator (NEW FEATURE) ---
const RubricGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        educationLevel: 'SMA/MA/SMK',
        schoolName: userProfile?.school || '',
        grade: '10',
        phase: 'E',
        subject: '',
        topic: '',
        taskType: 'Proyek', // Projek, Praktik, Produk, Portofolio
        learningObjective: '',
        scaleModel: 'Deskriptif (Sangat Baik - Perlu Pendampingan)'
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buatkan **Rubrik Penilaian** standar Kemendikbudristek (Kurikulum Merdeka) format HTML.
                    
                    **ATURAN TAMPILAN (Inline CSS):**
                    - Header: Background **Cyan-600 (#0891b2)**, text white.
                    - Tabel Rubrik: Border collapse, border 1px solid black.
                    - Level Header: Background abu-abu muda.
                    
                    **DATA INPUT:**
                    - Jenjang: ${data.educationLevel} (Fase ${data.phase})
                    - Sekolah: ${data.schoolName}
                    - Mapel: ${data.subject}, Kelas: ${data.grade}
                    - Jenis Tugas: ${data.taskType}
                    - Topik/Projek: ${data.topic}
                    - Tujuan Pembelajaran (TP): ${data.learningObjective}
                    - Model Skala: 4 Level (${data.scaleModel})

                    **STRUKTUR ISI:**
                    1. **IDENTITAS:** Tabel rapi berisi data input di atas.
                    2. **DESKRIPSI TUGAS:** Penjelasan singkat tugas yang dinilai.
                    3. **TABEL RUBRIK (INTI):**
                       - Kolom 1: **Kriteria / Aspek** (Misal: Konten, Kreativitas, Proses, Sikap).
                       - Kolom 2: **Indikator** (Apa yang diamati).
                       - Kolom 3-6: **4 Level Capaian** (Mulai dari Level 4: Sangat Baik/Mahir hingga Level 1: Perlu Bimbingan).
                       *Wajib ada deskriptor spesifik pada setiap sel level, bukan hanya angka.*
                    4. **TINDAK LANJUT:** Kolom refleksi guru dan umpan balik.
                    
                    Gunakan bahasa pendidikan yang profesional, objektif, dan memberdayakan siswa (Growth Mindset).
                    `;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Generator Rubrik Penilaian" icon="fa-list-check" color="cyan" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'Rubrik_Penilaian.doc')}
            formContent={
                <form onSubmit={generate}>
                    {/* Identitas */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Jenjang</label>
                            <select name="educationLevel" value={data.educationLevel} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="PAUD">PAUD</option>
                                <option value="SD/MI">SD / MI</option>
                                <option value="SMP/MTs">SMP / MTs</option>
                                <option value="SMA/MA/SMK">SMA / SMK</option>
                            </select>
                        </div>
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        <InputField label="Kelas" name="grade" value={data.grade} onChange={handleChange} required />
                        <InputField label="Fase (Kurmer)" name="phase" value={data.phase} onChange={handleChange} placeholder="A/B/C/D/E/F" required />
                        <InputField label="Mata Pelajaran" name="subject" value={data.subject} onChange={handleChange} required />
                    </div>

                    {/* Detail Tugas */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Topik / Judul Projek" name="topic" value={data.topic} onChange={handleChange} placeholder="Misal: Pembuatan Ekoenzim" required />
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Jenis Tugas / Asesmen</label>
                            <select name="taskType" value={data.taskType} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="Projek (P5)">Projek (P5)</option>
                                <option value="Produk / Karya">Produk / Karya</option>
                                <option value="Praktik / Unjuk Kerja">Praktik / Unjuk Kerja</option>
                                <option value="Presentasi">Presentasi</option>
                                <option value="Portofolio">Portofolio</option>
                                <option value="Sikap / Observasi">Sikap / Observasi</option>
                            </select>
                        </div>
                    </div>

                    <TextareaField label="Tujuan Pembelajaran (TP) / Elemen CP" name="learningObjective" value={data.learningObjective} onChange={handleChange} placeholder="Peserta didik mampu..." required rows={2} />

                    <div className="mb-3">
                        <label className="block text-xs font-semibold text-slate-600 mb-1">Model Skala Penilaian</label>
                        <select name="scaleModel" value={data.scaleModel} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                            <option value="Deskriptif (Mahir - Cakap - Layak - Baru Berkembang)">Deskriptif (Mahir - Cakap - Layak - Baru Berkembang)</option>
                            <option value="Kualitatif (Sangat Baik - Baik - Cukup - Perlu Bimbingan)">Kualitatif (Sangat Baik - Baik - Cukup - Perlu Bimbingan)</option>
                            <option value="Angka (4 - 3 - 2 - 1)">Skor Angka (4 - 3 - 2 - 1)</option>
                        </select>
                    </div>

                    <button type="submit" disabled={loading} className="w-full py-3 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 font-bold mt-4 flex justify-center items-center gap-2 shadow-lg shadow-cyan-200">
                        {loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate Rubrik Penilaian
                    </button>
                </form>
            }
        />
    );
};

// --- 3.10 Kokurikuler Generator (NEW FEATURE) ---
const KokurikulerGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        educationLevel: 'SMA/MA/SMK',
        schoolName: userProfile?.school || '',
        grade: '10',
        phase: 'E',
        theme: '',
        topic: '',
        timeAllocation: '20 JP',
        coordinator: userProfile?.name || '',
        description: ''
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buatkan **Modul Projek Kokurikuler (P5)** sesuai standar Kurikulum Merdeka 2026 format HTML.
                    
                    **ATURAN TAMPILAN (Inline CSS):**
                    - Header: Background **Rose-600 (#e11d48)**, text white.
                    - Font: Sans-serif (Cleaner look).
                    - Layout: Rapi, profesional.
                    
                    **DATA INPUT:**
                    - Jenjang: ${data.educationLevel} (Fase ${data.phase})
                    - Sekolah: ${data.schoolName}
                    - Kelas: ${data.grade}
                    - Tema: ${data.theme}
                    - Topik Spesifik: ${data.topic}
                    - Alokasi Waktu: ${data.timeAllocation}
                    - Koordinator: ${data.coordinator}
                    - Deskripsi Singkat: ${data.description}

                    **STRUKTUR ISI MODUL:**
                    1. **INFORMASI UMUM:** Identitas, Sarana Prasarana, Target Peserta Didik.
                    2. **KOMPONEN INTI:**
                       - Deskripsi Singkat Projek.
                       - Dimensi & Elemen Profil Pelajar Pancasila (Pilih yang relevan dengan tema).
                       - Tujuan Spesifik (Fase ${data.phase}).
                       - Alur Kegiatan (Tahap Pengenalan, Kontekstualisasi, Aksi, Refleksi, Tindak Lanjut).
                    3. **LAMPIRAN RINGKAS:**
                       - Lembar Kerja Peserta Didik (Contoh sederhana).
                       - Rubrik Penilaian (Awal, Proses, Akhir).
                    
                    Buat narasi yang **menginspirasi**, **kontekstual**, dan **berpusat pada siswa**.
                    `;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Modul Kokurikuler (P5)" icon="fa-hands-holding-circle" color="rose" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'Modul_P5_Kokurikuler.doc')}
            formContent={
                <form onSubmit={generate}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Jenjang</label>
                            <select name="educationLevel" value={data.educationLevel} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="PAUD">PAUD</option>
                                <option value="SD/MI">SD / MI</option>
                                <option value="SMP/MTs">SMP / MTs</option>
                                <option value="SMA/MA/SMK">SMA / SMK</option>
                            </select>
                        </div>
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        <InputField label="Kelas" name="grade" value={data.grade} onChange={handleChange} required />
                        <InputField label="Fase" name="phase" value={data.phase} onChange={handleChange} placeholder="A-F" required />
                        <InputField label="Alokasi Waktu" name="timeAllocation" value={data.timeAllocation} onChange={handleChange} required />
                    </div>

                    <div className="mb-3">
                        <label className="block text-xs font-semibold text-slate-600 mb-1">Tema Projek</label>
                        <select name="theme" value={data.theme} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                            <option value="">-- Pilih Tema --</option>
                            <option value="Gaya Hidup Berkelanjutan">Gaya Hidup Berkelanjutan</option>
                            <option value="Kearifan Lokal">Kearifan Lokal</option>
                            <option value="Bhinneka Tunggal Ika">Bhinneka Tunggal Ika</option>
                            <option value="Bangunlah Jiwa dan Raganya">Bangunlah Jiwa dan Raganya</option>
                            <option value="Suara Demokrasi">Suara Demokrasi</option>
                            <option value="Rekayasa dan Teknologi">Rekayasa dan Teknologi</option>
                            <option value="Kewirausahaan">Kewirausahaan</option>
                            <option value="Kebekerjaan">Kebekerjaan (Khusus SMK)</option>
                        </select>
                    </div>

                    <InputField label="Topik Spesifik" name="topic" value={data.topic} onChange={handleChange} placeholder="Misal: Pengolahan Sampah Organik" required />

                    <TextareaField label="Deskripsi Singkat Projek" name="description" value={data.description} onChange={handleChange} placeholder="Jelaskan gambaran umum projek ini..." required rows={2} />

                    <InputField label="Koordinator Projek" name="coordinator" value={data.coordinator} onChange={handleChange} required locked={!!userProfile?.name} />

                    <button type="submit" disabled={loading} className="w-full py-3 bg-rose-600 text-white rounded-lg hover:bg-rose-700 font-bold mt-4 flex justify-center items-center gap-2 shadow-lg shadow-rose-200">
                        {loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate Modul P5
                    </button>
                </form>
            }
        />
    );
};

// ==========================================

// --- 3.11 Materi Ajar Generator (NEW FEATURE) ---
const MateriAjarGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        educationLevel: 'SMA/MA/SMK',
        schoolName: userProfile?.school || '',
        subject: '',
        grade: '10',
        phase: 'E',
        topic: '',
        learningObjective: '',
        timeAllocation: '2 JP',
        includeImages: false
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buatkan **Modul Materi Ajar (Handout/Bahan Bacaan)** yang komprehensif untuk siswa sesuai Kurikulum Merdeka.
                    
                    **ATURAN TAMPILAN (Inline CSS):**
                    - Header: Background **Indigo-600 (#4f46e5)**, text white.
                    - Font: Sans-serif (Modern & Readable).
                    - Format: Terstruktur seperti buku teks modern.
                    
                    **DATA INPUT:**
                    - Jenjang: ${data.educationLevel} (Fase ${data.phase})
                    - Mapel: ${data.subject}
                    - Kelas: ${data.grade}
                    - Topik/Materi Pokok: ${data.topic}
                    - Tujuan Pembelajaran: ${data.learningObjective}
                    - Alokasi Waktu: ${data.timeAllocation}

                    **STRUKTUR ISI MATERI:**
                    1. **JUDUL & PETA KONSEP:** (Visualisasi teks sederhana tentang hubungan antar sub-materi).
                    2. **PENDAHULUAN:** (Apersepsi & Pertanyaan Pemantik).
                    3. **URAIAN MATERI (INTI):**
                       - Penjelasan konsep secara mendalam, runut, dan mudah dipahami.
                       - Gunakan poin-poin, tabel, atau analogi jika perlu.
                       - Sertakan "Fakta Menarik" atau "Tahukah Kamu?" dalam kotak terpisah (box info).
                    4. **CONTOH SOAL & PEMBAHASAN:** Minimal 2 contoh soal dengan penyelesaian langkah demi langkah.
                    5. **RANGKUMAN:** Poin-poin kunci materi.
                    6. **GLOSARIUM:** Daftar istilah penting dan artinya.
                    7. **DAFTAR PUSTAKA:** Referensi relevan (umum).

                    Gunakan bahasa yang **komunikatif** (seolah-olah guru sedang berbicara menjelaskan kepada siswa) dan **interaktif**.
                    `;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Generator Materi Ajar" icon="fa-book-open-reader" color="indigo" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'Materi_Ajar_Ikasmedia.doc')}
            formContent={
                <form onSubmit={generate}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Jenjang</label>
                            <select name="educationLevel" value={data.educationLevel} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="PAUD">PAUD</option>
                                <option value="SD/MI">SD / MI</option>
                                <option value="SMP/MTs">SMP / MTs</option>
                                <option value="SMA/MA/SMK">SMA / SMK</option>
                            </select>
                        </div>
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        <InputField label="Kelas" name="grade" value={data.grade} onChange={handleChange} required />
                        <InputField label="Fase" name="phase" value={data.phase} onChange={handleChange} placeholder="A-F" required />
                        <InputField label="Mata Pelajaran" name="subject" value={data.subject} onChange={handleChange} required />
                    </div>

                    <InputField label="Topik / Materi Pokok" name="topic" value={data.topic} onChange={handleChange} placeholder="Misal: Dinamika Atmosfer / Teks Eksposisi" required />

                    <TextareaField label="Tujuan Pembelajaran" name="learningObjective" value={data.learningObjective} onChange={handleChange} placeholder="Siswa dapat menganalisis..." required rows={2} />

                    <InputField label="Alokasi Waktu (Estimasi)" name="timeAllocation" value={data.timeAllocation} onChange={handleChange} placeholder="2 JP" required />

                    <button type="submit" disabled={loading} className="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold mt-4 flex justify-center items-center gap-2 shadow-lg shadow-indigo-200">
                        {loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Generate Materi Ajar
                    </button>
                </form>
            }
        />
    );
};

// --- 3.12 Analisis Hari Efektif Generator (NEW FEATURE) ---
const HariEfektifGenerator = ({ onBack, userProfile }) => {
    const [data, setData] = useState({
        schoolName: userProfile?.school || '',
        academicYear: '2025/2026',
        semester: '1 (Ganjil)',
        startDate: '',
        endDate: '',
        holidays: '', // Deskripsi libur
        principalName: userProfile?.principalName || '',
        principalNip: userProfile?.principalNip || '',
        teacherName: userProfile?.name || '',
        teacherNip: userProfile?.nip || '',
        city: userProfile?.city || ''
    });
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

    const generate = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const prompt = `Buatkan **Analisis Hari Efektif Belajar** dan **Perhitungan Jam Pelajaran** dalam format HTML Tabel yang rapi.
                    
                    **ATURAN TAMPILAN (Inline CSS):**
                    - Header: Background **Emerald-600 (#059669)**, text white.
                    - Tabel: Border 1px solid black, collapse.
                    - Font: Arial/Sans-serif.
                    
                    **DATA INPUT:**
                    - Sekolah: ${data.schoolName}
                    - Tahun Pelajaran: ${data.academicYear}
                    - Semester: ${data.semester}
                    - Periode: ${data.startDate} s.d. ${data.endDate}
                    - Catatan Libur/Agenda: ${data.holidays}
                    - Kota: ${data.city}

                    **STRUKTUR DOKUMEN:**
                    1. **KOP/HEADER:** Identitas Sekolah & Judul Dokumen.
                    2. **TABEL 1: PERHITUNGAN JUMLAH MINGGU:**
                       - Kolom: No, Nama Bulan, Jumlah Minggu, Jml Minggu Tidak Efektif, Jml Minggu Efektif.
                       - (Estimasi secara logis berdasarkan kalender pendidikan umum Indonesia).
                       - Total Minggu Efektif dihitung di baris paling bawah.
                    3. **TABEL 2: DISTRIBUSI ALOKASI WAKTU:**
                       - Hitung Total Jam Pelajaran (JP) = Total Minggu Efektif x JP per minggu (Asumsikan 4 JP/minggu mapel umum atau beri catatan).
                    4. **KETERANGAN MINGGU TIDAK EFEKTIF:**
                       - List agenda libur (PLS, Jeda Semester, Libur Nasional, Ujian, dll).
                    5. **TANDA TANGAN:** Kepala Sekolah (Kiri), Guru Mapel (Kanan).
                       - Kepsek: ${data.principalName} (NIP: ${data.principalNip})
                       - Guru: ${data.teacherName} (NIP: ${data.teacherNip})
                    
                    Buat estimasi hitungan bulan yang **REALISTIS** sesuai semester yang dipilih.
                    `;

            const res = await AiService.generate(prompt);
            setResult(res);
        } catch (e) { alert(e.message); } finally { setLoading(false); }
    };

    return (
        <GeneratorLayout title="Analisis Hari Efektif" icon="fa-calendar-check" color="emerald" onBack={onBack} result={result} isDemo={userProfile?.isDemo} onDownload={() => downloadWord(result, 'Analisis_Hari_Efektif.doc')}
            formContent={
                <form onSubmit={generate}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Nama Sekolah" name="schoolName" value={data.schoolName} onChange={handleChange} required locked={!!userProfile?.school} />
                        <InputField label="Tahun Pelajaran" name="academicYear" value={data.academicYear} onChange={handleChange} required />
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div className="sm:col-span-1">
                            <label className="block text-xs font-semibold text-slate-600 mb-1">Semester</label>
                            <select name="semester" value={data.semester} onChange={handleChange} className="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="1 (Ganjil)">1 (Ganjil)</option>
                                <option value="2 (Genap)">2 (Genap)</option>
                            </select>
                        </div>
                        <InputField label="Tanggal Mulai KBM" name="startDate" type="date" value={data.startDate} onChange={handleChange} required />
                        <InputField label="Tanggal Selesai KBM" name="endDate" type="date" value={data.endDate} onChange={handleChange} required />
                    </div>

                    <TextareaField label="Catatan Libur / Agenda Sekolah (Opsional untuk akurasi AI)" name="holidays" value={data.holidays} onChange={handleChange} placeholder="Contoh: Libur Idul Fitri 2 minggu, POR Simpel 1 minggu..." rows={2} />

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <InputField label="Kepala Sekolah" name="principalName" value={data.principalName} onChange={handleChange} />
                        <InputField label="NIP Kepala Sekolah" name="principalNip" value={data.principalNip} onChange={handleChange} />
                    </div>

                    <button type="submit" disabled={loading} className="w-full py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-bold mt-4 flex justify-center items-center gap-2 shadow-lg shadow-emerald-200">
                        {loading ? <Spinner /> : <i className="fa-solid fa-wand-magic-sparkles"></i>} Analisis Hari Efektif
                    </button>
                </form>
            }
        />
    );
};

// ==========================================
// 5. MAIN MENU COMPONENT
// ==========================================

const MenuCard = ({ title, desc, icon, color, onClick }) => {
    const colors = {
        sky: 'from-sky-500/20 to-sky-600/20 text-sky-700 border-sky-200 hover:border-sky-400',
        emerald: 'from-emerald-500/20 to-emerald-600/20 text-emerald-700 border-emerald-200 hover:border-emerald-400',
        violet: 'from-violet-500/20 to-violet-600/20 text-violet-700 border-violet-200 hover:border-violet-400',
        fuchsia: 'from-fuchsia-500/20 to-fuchsia-600/20 text-fuchsia-700 border-fuchsia-200 hover:border-fuchsia-400',
        rose: 'from-rose-500/20 to-rose-600/20 text-rose-700 border-rose-200 hover:border-rose-400',
        lime: 'from-lime-500/20 to-lime-600/20 text-lime-700 border-lime-200 hover:border-lime-400',
        amber: 'from-amber-500/20 to-amber-600/20 text-amber-700 border-amber-200 hover:border-amber-400',
        teal: 'from-teal-500/20 to-teal-600/20 text-teal-700 border-teal-200 hover:border-teal-400',
        cyan: 'from-cyan-500/20 to-cyan-600/20 text-cyan-700 border-cyan-200 hover:border-cyan-400'
    };

    const iconColors = {
        sky: 'bg-sky-500 text-white',
        emerald: 'bg-emerald-500 text-white',
        violet: 'bg-violet-500 text-white',
        fuchsia: 'bg-fuchsia-500 text-white',
        rose: 'bg-rose-500 text-white',
        lime: 'bg-lime-500 text-white',
        amber: 'bg-amber-500 text-white',
        teal: 'bg-teal-500 text-white',
        cyan: 'bg-cyan-500 text-white'
    };

    return (
        <div
            onClick={onClick}
            className={`group relative p-6 rounded-2xl border transition-all duration-300 hover:-translate-y-1 cursor-pointer overflow-hidden bg-gradient-to-br ${colors[color]} bg-white/50 backdrop-blur-sm hover:shadow-lg`}
        >
            <div className="flex items-start justify-between mb-4">
                <div className={`w-12 h-12 rounded-xl flex items-center justify-center shadow-sm text-xl transition-transform group-hover:scale-110 ${iconColors[color]}`}>
                    <i className={`fa-solid ${icon}`}></i>
                </div>
                <div className="w-8 h-8 rounded-full bg-white/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity transform translate-x-2 group-hover:translate-x-0">
                    <i className="fa-solid fa-arrow-right text-slate-400 text-sm"></i>
                </div>
            </div>

            <h3 className="text-lg font-bold mb-2 text-slate-800 tracking-tight">{title}</h3>
            <p className="text-sm text-slate-600 leading-relaxed line-clamp-2">{desc}</p>

            <div className="absolute -bottom-6 -right-6 opacity-5 rotate-12 transform group-hover:rotate-0 transition-all duration-500">
                <i className={`fa-solid ${icon} text-9xl`}></i>
            </div>
        </div>
    );
};

// ==========================================
// 5.5 LICENSE STATUS BADGE COMPONENT (NEW)
// ==========================================

const LicenseBadge = ({ expiry, isDemo }) => {
    // Handle Demo Mode
    if (isDemo) {
        return (
            <div className="px-3 py-1 rounded-lg border flex items-center gap-2 shadow-sm transition-all bg-amber-50 text-amber-700 border-amber-200">
                <div className="text-base"><i className="fa-solid fa-flask"></i></div>
                <div className="flex flex-col leading-none">
                    <span className="text-[10px] uppercase font-bold tracking-wider opacity-80">Pratinjau</span>
                    <span className="text-xs font-extrabold">Mode Demo</span>
                </div>
            </div>
        );
    }

    if (!expiry) return null;

    const now = new Date();
    const end = new Date(expiry);

    // Set jam ke 00:00:00 untuk perbandingan akurat
    now.setHours(0, 0, 0, 0);
    end.setHours(0, 0, 0, 0);

    const diffTime = end - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    let colorClass = "bg-emerald-50 text-emerald-700 border-emerald-200";
    let icon = "fa-shield-check";
    let text = "";
    let subText = "";

    if (diffDays <= 7) {
        // Critical (< 7 days)
        colorClass = "bg-rose-50 text-rose-600 border-rose-200 animate-pulse";
        icon = "fa-hourglass-half";
        text = `Sisa ${diffDays} Hari Lagi!`;
        subText = "Segera Perpanjang";
    } else if (diffDays <= 30) {
        // Warning (< 30 days)
        colorClass = "bg-amber-50 text-amber-700 border-amber-200";
        icon = "fa-clock";
        text = `Berakhir ${diffDays} Hari`;
        subText = "Masa Aktif";
    } else {
        // Safe (> 30 days)
        text = "License Active";
        // Format date nicely
        subText = `s.d. ${end.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}`;
    }

    return (
        <div className={`px-3 py-1 rounded-lg border flex items-center gap-2 shadow-sm transition-all ${colorClass}`}>
            <div className="text-base"><i className={`fa-solid ${icon}`}></i></div>
            <div className="flex flex-col leading-none">
                <span className="text-[10px] uppercase font-bold tracking-wider opacity-80">{subText}</span>
                <span className="text-xs font-extrabold">{text}</span>
            </div>
        </div>
    );
}

// ==========================================
// 6. MAIN APP LOGIC
// ==========================================

const App = () => {
    // BYPASS LOGIN: Default state is logged in and fully unlocked
    const [isLicensed, setIsLicensed] = useState(true);
    const [userProfile, setUserProfile] = useState(window.APP_CONFIG.userProfile);
    const [view, setView] = useState('menu');

    // handleAuthenticated removed as login page is bypassed

    // MODIFIED: Added featureKey mapping to align with Database columns
    const menuItems = [
        { id: 'rpm', featureKey: 'rpm', title: 'Generator RPM', desc: 'Rancangan Pembelajaran Mendalam (Modul Ajar)', icon: 'fa-network-wired', color: 'sky' },
        { id: 'question', featureKey: 'soal', title: 'Kartu Soal', desc: 'Kisi-kisi & Soal Ujian', icon: 'fa-clipboard-list', color: 'emerald' },
        { id: 'atp', featureKey: 'atp', title: 'Generator ATP', desc: 'Alur Tujuan Pembelajaran', icon: 'fa-sitemap', color: 'fuchsia' },
        { id: 'rps', featureKey: 'rps', title: 'Generator RPS', desc: 'Rencana Pembelajaran Semester', icon: 'fa-graduation-cap', color: 'violet' },
        { id: 'rpd', featureKey: 'rpd', title: 'Generator RPD', desc: 'Microteaching & Rencana Digital', icon: 'fa-chalkboard-user', color: 'rose' },
        { id: 'journal', featureKey: 'jurnal', title: 'Jurnal Guru', desc: 'Refleksi & Catatan Harian', icon: 'fa-book-journal-whills', color: 'lime' },
        { id: 'ck', featureKey: 'ck', title: 'Capaian Kompetensi', desc: 'Narasi Rapor Deep Learning', icon: 'fa-award', color: 'amber' },
        { id: 'lkm', featureKey: 'lkm', title: 'Generator LKM', desc: 'Lembar Kerja Peserta Didik', icon: 'fa-file-pen', color: 'teal' },
        { id: 'rubric', featureKey: 'rubric', title: 'Rubrik Penilaian', desc: 'Generator Rubrik Asesmen Standar Kemendikbud', icon: 'fa-list-check', color: 'cyan' },
        { id: 'kokurikuler', featureKey: 'kokurikuler', title: 'Modul P5', desc: 'Projek Penguatan Profil Pelajar Pancasila', icon: 'fa-hands-holding-circle', color: 'rose' },
        { id: 'materi', featureKey: 'materi', title: 'Materi Ajar', desc: 'Modul Bahan Bacaan & Handout Siswa', icon: 'fa-book-open-reader', color: 'indigo' },
        { id: 'he', featureKey: 'he', title: 'Analisis HE', desc: 'Analisis Hari Efektif & Minggu Efektif', icon: 'fa-calendar-check', color: 'emerald' },
    ];

    // MODIFIED: Filter Logic
    const visibleMenuItems = menuItems.filter(item => {
        // Check if userProfile and features exist, then return the boolean value of the specific feature key
        return userProfile?.features?.[item.featureKey] === true;
    });

    // Removed LicenseGate render logic

    return (
        <div className="min-h-screen bg-slate-50 pb-24 fade-in">
            {/* Header */}
            <div className="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                    <div className="flex items-center gap-3 cursor-pointer group" onClick={() => setView('menu')}>
                        <div className="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform p-1">
                            <img src={window.APP_CONFIG.logoUrl} alt="Logo" className="w-full h-full object-contain" />
                        </div>
                        <div>
                            <h1 className="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-sky-600 to-emerald-600">Ikasmedia AI Generator</h1>
                            <p className="text-[10px] text-slate-400 font-medium -mt-1 tracking-wider">INTELLIGENT SUITE</p>
                        </div>
                    </div>

                    {/* USER PROFILE & LICENSE BADGE SECTION */}
                    {userProfile && (userProfile.school || userProfile.name) && (
                        <div className="hidden md:flex items-center gap-4">
                            {/* License Countdown Badge */}
                            <LicenseBadge expiry={userProfile.expiry} isDemo={userProfile.isDemo} />

                            {/* User Info */}
                            <div className="flex flex-col items-end border-l border-slate-200 pl-4">
                                <span className="text-xs font-bold text-slate-700">{userProfile.name}</span>
                                <span className="text-[10px] text-slate-500 truncate max-w-[200px] uppercase tracking-wide">{userProfile.school}</span>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Main Content Area */}
            <div className="pt-8">
                {view === 'menu' && (
                    <div className="max-w-6xl mx-auto px-4 fade-in">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-800 mb-3">Selamat Datang, Guru Hebat!</h2>
                            <p className="text-slate-500 max-w-2xl mx-auto">Pilih alat bantu administrasi cerdas di bawah ini untuk memulai pekerjaan Anda. Akun Anda telah terhubung secara otomatis.</p>
                        </div>

                        {visibleMenuItems.length > 0 ? (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {visibleMenuItems.map(item => (
                                    <MenuCard key={item.id} {...item} onClick={() => setView(item.id)} />
                                ))}
                            </div>
                        ) : (
                            <div className="text-center p-10 bg-white rounded-xl border border-slate-200 shadow-sm max-w-lg mx-auto">
                                <div className="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i className="fa-solid fa-lock text-3xl text-slate-400"></i>
                                </div>
                                <h3 className="text-lg font-bold text-slate-700">Akses Fitur Terbatas</h3>
                                <p className="text-slate-500 text-sm mt-2">Lisensi Anda aktif, namun tidak ada fitur yang diizinkan untuk paket ini. Silakan hubungi Administrator untuk upgrade akses.</p>
                            </div>
                        )}
                    </div>
                )}

                {/* PASS USER PROFILE TO GENERATORS TO LOCK FIELDS */}
                {view === 'rpm' && <RpmGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'question' && <QuestionGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'rps' && <RpsGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'atp' && <AtpGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'rpd' && <RpdGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'journal' && <JournalGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'ck' && <CkGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'lkm' && <LkmGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'rubric' && <RubricGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'kokurikuler' && <KokurikulerGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'materi' && <MateriAjarGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
                {view === 'he' && <HariEfektifGenerator onBack={() => setView('menu')} userProfile={userProfile} />}
            </div>

            <div className="fixed bottom-0 w-full z-20">
                {/* Gradient Line */}
                <div className="h-0.5 w-full bg-gradient-to-r from-sky-400 via-emerald-400 to-violet-400"></div>

                {/* Footer Content */}
                <div className="bg-white/90 backdrop-blur-xl border-t border-slate-100 py-3 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                    <div className="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] font-medium text-slate-500 tracking-wide">

                        <div className="flex items-center gap-2">
                            <span>&copy; 2026 Ikasmedia</span>
                            <span className="hidden sm:inline text-slate-300">|</span>
                            <span className="bg-slate-100 px-2 py-0.5 rounded-full text-slate-400 border border-slate-200">v2.1 Pro</span>
                        </div>

                        <div className="flex items-center gap-1.5">
                            <span>Dibuat dengan</span>
                            <i className="fa-solid fa-heart text-rose-500 text-[10px] animate-pulse"></i>
                            <span>oleh</span>
                            <span className="bg-clip-text text-transparent bg-gradient-to-r from-sky-600 to-emerald-600 font-bold hover:opacity-80 transition-opacity cursor-default">
                                IKASMEDIA
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    );
};

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);
