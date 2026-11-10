<!-- Audit Trail Dashboard View -->
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold mb-3">
                <i class="bi bi-shield-check me-2"></i>Audit Trail
            </h3>
            <p class="text-muted">Log perubahan nilai dan aktivitas sistem penilaian</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4" id="stats-cards">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Perubahan</h5>
                    <h3 id="stat-total">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">24 Jam Terakhir</h5>
                    <h3 id="stat-recent">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Koreksi Manual</h5>
                    <h3 id="stat-correct">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Recalculate</h5>
                    <h3 id="stat-recalc">-</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Filter Data</h5>
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">ID Siswa</label>
                    <input type="number" class="form-control" id="filter-siswa" placeholder="ID Siswa">
                </div>
                <div class="col-md-2">
                    <label class="form-label">ID Jadwal</label>
                    <input type="number" class="form-control" id="filter-jadwal" placeholder="ID Jadwal">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select class="form-select" id="filter-action">
                        <option value="">Semua</option>
                        <option value="INSERT">Insert</option>
                        <option value="UPDATE">Update</option>
                        <option value="CORRECT">Koreksi</option>
                        <option value="RECALCULATE">Recalculate</option>
                        <option value="MARK_CORRECTED">Tandai</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" id="filter-date-from">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="filter-date-to">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="loadData()">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Log Audit</h5>
            <div>
                <button class="btn btn-success btn-sm" onclick="exportData('csv')">
                    <i class="bi bi-download"></i> Export CSV
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="loadData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="audit-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Siswa ID</th>
                            <th>Jadwal ID</th>
                            <th>Action</th>
                            <th>Nilai Lama</th>
                            <th>Nilai Baru</th>
                            <th>Diubah Oleh</th>
                            <th>Role</th>
                            <th>IP Address</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="audit-tbody">
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center" id="pagination">
                    <!-- Pagination loaded via AJAX -->
                </ul>
            </nav>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentPage = 1;
let perPage = 25;

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    loadStats();
});

function loadData(page = 1) {
    currentPage = page;
    
    const filters = {
        siswa_id: document.getElementById('filter-siswa').value,
        jadwal_id: document.getElementById('filter-jadwal').value,
        action: document.getElementById('filter-action').value,
        date_from: document.getElementById('filter-date-from').value,
        date_to: document.getElementById('filter-date-to').value,
        page: page,
        per_page: perPage
    };

    // Show loading
    document.getElementById('audit-tbody').innerHTML = '<tr><td colspan="10" class="text-center"><i class="bi bi-arrow-repeat spin"></i> Loading...</td></tr>';

    fetch('<?= site_url('audit/getData') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(filters)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderTable(data.data);
            renderPagination(data.total, data.total_pages, data.page);
        } else {
            document.getElementById('audit-tbody').innerHTML = `<tr><td colspan="10" class="text-center text-danger">${data.message}</td></tr>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('audit-tbody').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data</td></tr>';
    });
}

function renderTable(data) {
    const tbody = document.getElementById('audit-tbody');
    
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center">Tidak ada data</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(row => `
        <tr>
            <td>${row.id}</td>
            <td>${row.siswa_id}</td>
            <td>${row.jadwal_id}</td>
            <td>${row.action}</td>
            <td>${row.old_values}</td>
            <td>${row.new_values}</td>
            <td>${row.changed_by || '-'}</td>
            <td>${row.changed_by_role || '-'}</td>
            <td><small>${row.ip_address}</small></td>
            <td>${row.created_at}</td>
        </tr>
    `).join('');
}

function renderPagination(total, totalPages, currentPage) {
    const pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let html = '';
    
    // Previous
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadData(${currentPage - 1}); return false;">Previous</a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadData(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Next
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadData(${currentPage + 1}); return false;">Next</a>
        </li>
    `;

    pagination.innerHTML = html;
}

function loadStats() {
    const jadwalId = document.getElementById('filter-jadwal').value;
    
    fetch('<?= site_url('audit/getStats') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ jadwal_id: jadwalId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('stat-total').textContent = data.stats.total_changes || 0;
            document.getElementById('stat-recent').textContent = data.recent_24h || 0;
            document.getElementById('stat-correct').textContent = data.stats.by_action?.CORRECT || 0;
            document.getElementById('stat-recalc').textContent = data.stats.by_action?.RECALCULATE || 0;
        }
    })
    .catch(error => console.error('Error loading stats:', error));
}

function exportData(format) {
    const jadwalId = document.getElementById('filter-jadwal').value;
    window.open(`<?= site_url('audit/export') ?>?format=${format}&jadwal_id=${jadwalId}`, '_blank');
}

// Auto refresh setiap 5 menit
setInterval(() => {
    loadData(currentPage);
    loadStats();
}, 300000);
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
<?= $this->endSection() ?>
