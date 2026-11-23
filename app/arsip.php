<?php
require_once 'config/database.php';

// ==========================================
// PARAMETER PENCARIAN & FILTER
// ==========================================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'semua';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// ==========================================
// QUERY DASAR
// ==========================================
$where_clauses = [];
$params = [];
$types = "";

if ($search !== '') {
    $where_clauses[] = "(a.nama_file LIKE ? OR a.penerima LIKE ? OR a.kode_voucher LIKE ?)";
    $searchWildcard = "%" . $search . "%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $types .= "sss";
}

if ($filter_bulan > 0) {
    $where_clauses[] = "a.month_id = ?";
    $params[] = $filter_bulan;
    $types .= "i";
}

if ($filter_status !== 'semua') {
    $where_clauses[] = "a.status_metadata = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// ==========================================
// HITUNG TOTAL DATA (Untuk Pagination)
// ==========================================
$count_sql = "SELECT COUNT(*) as total FROM archives a $where_sql";
$stmt_count = $conn->prepare($count_sql);
if ($types) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_result = $stmt_count->get_result()->fetch_assoc();
$total_data = $total_result['total'];
$total_pages = ceil($total_data / $limit);

// ==========================================
// AMBIL DATA ARSIP
// ==========================================
$sql = "
    SELECT a.*, m.nama_bulan 
    FROM archives a
    LEFT JOIN months m ON a.month_id = m.id
    $where_sql
    ORDER BY a.id DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$params_data = $params;
$params_data[] = $limit;
$params_data[] = $offset;
$types_data = $types . "ii";

$stmt->bind_param($types_data, ...$params_data);
$stmt->execute();
$result = $stmt->get_result();

// ==========================================
// AMBIL DAFTAR BULAN (Untuk Dropdown Filter)
// ==========================================
$months_result = $conn->query("SELECT id, nama_bulan FROM months ORDER BY nomor_bulan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Digitalisasi Arsip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding-top: 24px; padding-bottom: 40px; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
        .badge-valid { background-color: #198754; }
        .badge-verify { background-color: #ffc107; color: #000; }
        .col-file { min-width: 320px; font-size: 0.875rem; color: #212529; }
        .col-penerima { min-width: 180px; font-weight: 500; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>

<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
            <h3 class="mb-0 fw-bold">📁 Pencarian Arsip Digital</h3>
        </div>
        <span class="text-muted">Total: <strong><?= number_format($total_data) ?></strong> data ditemukan</span>
    </div>

    <!-- FORM PENCARIAN & FILTER -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <!-- action dikosongkan ("") agar selalu memproses di halaman arsip.php -->
            <form method="GET" action="" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama penerima / voucher / nama file..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="bulan" class="form-select">
                        <option value="0">Semua Bulan</option>
                        <?php while($m = $months_result->fetch_assoc()): ?>
                            <option value="<?= $m['id'] ?>" <?= ($filter_bulan == $m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nama_bulan']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="semua" <?= ($filter_status == 'semua') ? 'selected' : '' ?>>Semua Status</option>
                        <option value="valid" <?= ($filter_status == 'valid') ? 'selected' : '' ?>>Valid</option>
                        <option value="perlu_verifikasi" <?= ($filter_status == 'perlu_verifikasi') ? 'selected' : '' ?>>Perlu Verifikasi</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Cari Arsip</button>
                    <?php if ($search !== '' || $filter_bulan > 0 || $filter_status !== 'semua'): ?>
                        <a href="arsip.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="nowrap">No</th>
                        <th class="nowrap">Bulan</th>
                        <th>Nama File</th>
                        <th class="nowrap">Tanggal</th>
                        <th class="nowrap">Voucher</th>
                        <th>Penerima</th>
                        <th class="nowrap">Status</th>
                        <th class="nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $no = $offset + 1; while($row = $result->fetch_assoc()): ?>
                            <?php
                                // Bersihkan awalan "AN " dan ekor "KPJ ..." saat ditampilkan
                                $penerima_bersih = $row['penerima'] ?? '-';
                                if ($penerima_bersih !== '-') {
                                    $penerima_bersih = preg_replace('/^AN\s+/i', '', $penerima_bersih);
                                    $penerima_bersih = preg_replace('/\s+KPJ\s+.*$/i', '', $penerima_bersih);
                                    $penerima_bersih = trim($penerima_bersih);
                                }
                            ?>
                            <tr>
                                <td class="nowrap"><?= $no++ ?></td>
                                <td class="nowrap"><?= htmlspecialchars($row['nama_bulan'] ?? '-') ?></td>
                                <td class="col-file" title="<?= htmlspecialchars($row['nama_file']) ?>"><?= htmlspecialchars($row['nama_file']) ?>
                                <td class="nowrap"><?= $row['tanggal'] ?? '-' ?></td>
                                <td class="nowrap"><code><?= htmlspecialchars($row['kode_voucher'] ?? '-') ?></code></td>
                                <td class="col-penerima"><?= htmlspecialchars($penerima_bersih) ?></td>
                                <td class="nowrap">
                                    <?php if ($row['status_metadata'] === 'valid'): ?>
                                        <span class="badge badge-valid">Valid</span>
                                    <?php else: ?>
                                        <span class="badge badge-verify">Perlu Verifikasi</span>
                                    <?php endif; ?>
                                </td>
                                <td class="nowrap">
                                    <?php if (!empty($row['google_drive_url'])): ?>
                                        <a href="<?= htmlspecialchars($row['google_drive_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            Buka PDF
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data arsip yang cocok dengan pencarian Anda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center mt-3 mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&bulan=<?= $filter_bulan ?>&status=<?= $filter_status ?>">Previous</a>
                    </li>
                    
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&bulan=<?= $filter_bulan ?>&status=<?= $filter_status ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&bulan=<?= $filter_bulan ?>&status=<?= $filter_status ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>