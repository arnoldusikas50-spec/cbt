<?php
/**
 * Tema kartu dashboard bersama untuk dashboard Guru & Panitia.
 *
 * Diekstrak dari <style> inline di app/Views/members/guru/dashboard.php supaya
 * kedua dashboard memakai definisi yang sama (kartu metrik, kotak ikon pastel,
 * kartu token). Jangan diubah sepihak di salah satu view.
 *
 * Nama kelas dipertahankan apa adanya (card-metric-*, guru-icon-box, icon-*,
 * token-card-box) supaya tidak perlu menyentuh markup view yang sudah ada.
 */
?>
<style>
.guru-icon-box {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}
.guru-icon-box-lg {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    flex-shrink: 0;
}

/* Metric Overview Cards Colored Style */
.card-metric {
    border-radius: 12px;
    padding: 0.85rem 1rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.card-metric:hover {
    transform: translateY(-3px);
}
.card-metric:hover .guru-icon-box {
    transform: scale(1.08);
}
.card-metric::after {
    content: '';
    position: absolute;
    right: -10px;
    bottom: -15px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0) 70%);
    pointer-events: none;
}

.card-metric-siswa {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #bfdbfe !important;
}
.card-metric-siswa:hover {
    border-color: #60a5fa !important;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15) !important;
}
.card-metric-siswa .metric-title { color: #1e40af !important; }
.card-metric-siswa .metric-value { color: #1d4ed8 !important; }
.card-metric-siswa .guru-icon-box {
    background: #2563eb !important;
    color: #ffffff !important;
    border: none;
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
}

.card-metric-rombel {
    background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
    border: 1px solid #ddd6fe !important;
}
.card-metric-rombel:hover {
    border-color: #a78bfa !important;
    box-shadow: 0 8px 20px rgba(124, 58, 237, 0.15) !important;
}
.card-metric-rombel .metric-title { color: #5b21b6 !important; }
.card-metric-rombel .metric-value { color: #6d28d9 !important; }
.card-metric-rombel .guru-icon-box {
    background: #7c3aed !important;
    color: #ffffff !important;
    border: none;
    box-shadow: 0 4px 10px rgba(124, 58, 237, 0.35);
}

.card-metric-guru {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 1px solid #a7f3d0 !important;
}
.card-metric-guru:hover {
    border-color: #34d399 !important;
    box-shadow: 0 8px 20px rgba(5, 150, 105, 0.15) !important;
}
.card-metric-guru .metric-title { color: #065f46 !important; }
.card-metric-guru .metric-value { color: #047857 !important; }
.card-metric-guru .guru-icon-box {
    background: #059669 !important;
    color: #ffffff !important;
    border: none;
    box-shadow: 0 4px 10px rgba(5, 150, 105, 0.35);
}

.card-metric-wali {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border: 1px solid #fde68a !important;
}
.card-metric-wali:hover {
    border-color: #fbbf24 !important;
    box-shadow: 0 8px 20px rgba(217, 119, 6, 0.15) !important;
}
.card-metric-wali .metric-title { color: #92400e !important; }
.card-metric-wali .metric-value { color: #b45309 !important; }
.card-metric-wali .guru-icon-box {
    background: #d97706 !important;
    color: #ffffff !important;
    border: none;
    box-shadow: 0 4px 10px rgba(217, 119, 6, 0.35);
}

.card-metric-mapel {
    background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
    border: 1px solid #99f6e4 !important;
}
.card-metric-mapel:hover {
    border-color: #2dd4bf !important;
    box-shadow: 0 8px 20px rgba(13, 148, 136, 0.15) !important;
}
.card-metric-mapel .metric-title { color: #115e59 !important; }
.card-metric-mapel .metric-value { color: #0f766e !important; }
.card-metric-mapel .guru-icon-box {
    background: #0d9488 !important;
    color: #ffffff !important;
    border: none;
    box-shadow: 0 4px 10px rgba(13, 148, 136, 0.35);
}

.card-metric-ekstra {
    background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
    border: 1px solid #fecdd3 !important;
}
.card-metric-ekstra:hover {
    border-color: #fb7185 !important;
    box-shadow: 0 8px 20px rgba(225, 29, 72, 0.15) !important;
}
.card-metric-ekstra .metric-title { color: #9f1239 !important; }
.card-metric-ekstra .metric-value { color: #be123c !important; }
.card-metric-ekstra .guru-icon-box {
    background: #e11d48 !important;
    color: #ffffff !important;
    border: none;
    box-shadow: 0 4px 10px rgba(225, 29, 72, 0.35);
}

/* Feature Action Sub-Cards */
.feature-card-blue {
    background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%) !important;
    border: 1px solid #bfdbfe !important;
    transition: all 0.2s ease;
}
.feature-card-blue:hover {
    border-color: #60a5fa !important;
    box-shadow: 0 6px 16px rgba(37,99,235,0.12) !important;
    transform: translateY(-2px);
}

.feature-card-green {
    background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%) !important;
    border: 1px solid #a7f3d0 !important;
    transition: all 0.2s ease;
}
.feature-card-green:hover {
    border-color: #34d399 !important;
    box-shadow: 0 6px 16px rgba(5,150,105,0.12) !important;
    transform: translateY(-2px);
}

.feature-card-amber {
    background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%) !important;
    border: 1px solid #fde68a !important;
    transition: all 0.2s ease;
}
.feature-card-amber:hover {
    border-color: #fbbf24 !important;
    box-shadow: 0 6px 16px rgba(217,119,6,0.12) !important;
    transform: translateY(-2px);
}

.feature-card-sky {
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%) !important;
    border: 1px solid #bae6fd !important;
    transition: all 0.2s ease;
}
.feature-card-sky:hover {
    border-color: #38bdf8 !important;
    box-shadow: 0 6px 16px rgba(2,132,199,0.12) !important;
    transform: translateY(-2px);
}

.feature-card-teal {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%) !important;
    border: 1px solid #99f6e4 !important;
    transition: all 0.2s ease;
}
.feature-card-teal:hover {
    border-color: #2dd4bf !important;
    box-shadow: 0 6px 16px rgba(13,148,136,0.12) !important;
    transform: translateY(-2px);
}

.feature-card-rose {
    background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%) !important;
    border: 1px solid #fecaca !important;
    transition: all 0.2s ease;
}
.feature-card-rose:hover {
    border-color: #f87171 !important;
    box-shadow: 0 6px 16px rgba(220,38,38,0.12) !important;
    transform: translateY(-2px);
}

/* Pastel Icon Colors */
.icon-siswa { background-color: #2563eb !important; color: #ffffff !important; }
.icon-rombel { background-color: #7c3aed !important; color: #ffffff !important; }
.icon-guru { background-color: #059669 !important; color: #ffffff !important; }
.icon-wali { background-color: #d97706 !important; color: #ffffff !important; }
.icon-mapel { background-color: #0d9488 !important; color: #ffffff !important; }
.icon-ekstra { background-color: #e11d48 !important; color: #ffffff !important; }

.icon-purple { background-color: #faf5ff !important; color: #9333ea !important; border: 1px solid #e9d5ff; }
.icon-primary { background-color: #eff6ff !important; color: #2563eb !important; border: 1px solid #bfdbfe; }
.icon-success { background-color: #ecfdf5 !important; color: #059669 !important; border: 1px solid #a7f3d0; }
.icon-warning { background-color: #fffbeb !important; color: #d97706 !important; border: 1px solid #fde68a; }
.icon-danger { background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid #fecaca; }
.icon-teal { background-color: #f0fdfa !important; color: #0d9488 !important; border: 1px solid #99f6e4; }
.icon-info { background-color: #f0f9ff !important; color: #0284c7 !important; border: 1px solid #bae6fd; }

/* Token Card Clean Style */
.token-card-box {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.token-card-box:hover {
    border-color: #94a3b8 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.token-val-text {
    color: #1e40af !important;
    font-weight: 800;
</style>
