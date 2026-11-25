<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../config/database.php";

$pesan = "";

// ==========================================
// PROSES HAPUS TAHUN
// ==========================================
if (isset($_GET['hapus_id'])) {
    $hapus_id = (int)$_GET['hapus_id'];
    
    // Cek apakah ada bulan yang sudah punya ID Google Drive
    $cek_link = mysqli_query($conn, "SELECT COUNT(*) as total FROM months WHERE year_id = $hapus_id AND google_drive_folder_id != ''");
    $data_link = mysqli_fetch_assoc($cek_link);
    
    if ($data_link['total'] > 0) {
        $pesan = "<div class='alert alert-danger'>Gagal menghapus! Tahun ini sudah memiliki link Google Drive di beberapa bulannya. Kosongkan link terlebih dahulu jika ingin menghapus.</div>";
    } else {
        // Hapus tahun (asumsi tabel months menggunakan ON DELETE CASCADE)
        $hapus = mysqli_query($conn, "DELETE FROM years WHERE id = $hapus_id");
        if ($hapus) {
            $pesan = "<div class='alert alert-success'>Tahun berhasil dihapus!</div>";
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal menghapus: " . mysqli_error($conn) . "</div>";
        }
    }
}

// ==========================================
// AMBIL DATA TAHUN & JUMLAH BULAN YANG SUDAH ADA LINK
// ==========================================
$query = "
    SELECT 
        y.id, 
        y.tahun, 
        y.status,
        COUNT(m.id) AS total_bulan,
        SUM(CASE WHEN m.google_drive_folder_id != '' THEN 1 ELSE 0 END) AS bulan_terisi
    FROM years y
    LEFT JOIN months m ON y.id = m.year_id
    GROUP BY y.id
    ORDER BY y.tahun DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>List of Archive Years</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../dashboard.php">📁 Digitalisasi Arsip</a>
        <a href="../dashboard.php" class="btn btn-outline-light btn-sm">Kembali ke Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">List of Archive Years</h3>
        <a href="../tambah_tahun.php" class="btn btn-success fw-bold">+ Tambah Tahun Baru</a>
    </div>

    <?= $pesan; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tahun</th>
                        <th>Status Link Drive</th>
                        <th>Status Sistem</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada tahun arsip. Silakan tambah tahun baru.</td>
                        </tr>
                    <?php endif; ?>

                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= $row['tahun']; ?></td>
                            <td>
                                <?php if ($row['bulan_terisi'] > 0): ?>
                                    <span class="badge bg-success"><?= $row['bulan_terisi'] ?> / 12 Bulan terisi</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Kosong (Belum ada link)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 1): ?>
                                    <span class="badge bg-primary">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="months.php?year_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    Lihat Bulan
                                </a>
                                
                                <?php if ($row['bulan_terisi'] == 0): ?>
                                    <a href="years.php?hapus_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus tahun <?= $row['tahun'] ?>?');">
                                        Hapus
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Tahun sudah memiliki link Drive.">
                                        Hapus
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
