<?php
require_once "../config/database.php";

$year_id = isset($_GET['year_id']) ? (int) $_GET['year_id'] : 0;
if ($year_id <= 0) {
    die("Tahun tidak valid.");
}

// ==========================================
// AMBIL DATA TAHUN & BULAN
// ==========================================
$query_year = "SELECT * FROM years WHERE id = $year_id AND status = 1";
$result_year = mysqli_query($conn, $query_year);
if (!$result_year) {
    die("Query tahun gagal: " . mysqli_error($conn));
}
$year = mysqli_fetch_assoc($result_year);
if (!$year) {
    die("Tahun tidak ditemukan.");
}

$query_months = "
    SELECT months.*, COUNT(archives.id) AS jumlah_arsip
    FROM months
    LEFT JOIN archives ON months.id = archives.month_id
    WHERE months.year_id = $year_id
    GROUP BY months.id, months.nama_bulan, months.nomor_bulan, months.google_drive_folder_id
    ORDER BY months.nomor_bulan ASC
";
$result_months = mysqli_query($conn, $query_months);
if (!$result_months) {
    die("Query gagal: " . mysqli_error($conn));
}

$months_data = [];
while ($m = mysqli_fetch_assoc($result_months)) {
    $months_data[] = $m;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arsip Tahun <?= htmlspecialchars($year['tahun']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../dashboard.php">📁 Digitalisasi Arsip</a>
    </div>
</nav>

<div class="container py-5">
    <div class="mb-4">
        <a href="years.php" class="text-decoration-none">← Kembali ke daftar tahun</a>
        <h1 class="fw-bold mt-3">Arsip Tahun <?= htmlspecialchars($year['tahun']); ?></h1>
    </div>

    <!-- DAFTAR BULAN (KARTU) -->
    <h5 class="fw-bold mb-3">Navigasi Bulan</h5>
    <div class="row g-4">
        <?php if (empty($months_data)): ?>
            <div class="col-12">
                <div class="alert alert-info mb-0">Belum ada data bulan untuk tahun ini.</div>
            </div>
        <?php else: ?>
            <?php foreach ($months_data as $month): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="files.php?month_id=<?= (int) $month['id']; ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <div class="fs-1 mb-3">📂</div>
                                <h5 class="fw-bold text-dark"><?= htmlspecialchars($month['nama_bulan']); ?></h5>

                                <?php if (trim($month['google_drive_folder_id'] ?? '') !== ''): ?>
                                    <span class="badge bg-success mb-2">Link Terisi</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark mb-2">Belum Ada Link</span>
                                <?php endif; ?>

                                <p class="text-muted small mb-0"><?= (int) $month['jumlah_arsip']; ?> Arsip</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>