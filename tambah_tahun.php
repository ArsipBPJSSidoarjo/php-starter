<?php
require_once "config/database.php";

$pesan = "";

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun_baru = mysqli_real_escape_string($conn, $_POST['tahun']);
    $folder_id = mysqli_real_escape_string($conn, $_POST['folder_id']);

    // 1. Cek apakah tahun sudah ada
    $cek = mysqli_query($conn, "SELECT id FROM years WHERE tahun = '$tahun_baru'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-danger'>Gagal: Tahun $tahun_baru sudah ada di database!</div>";
    } else {
            // 2. Insert ke tabel years beserta link foldernya
        $insert_year = mysqli_query($conn, "INSERT INTO years (tahun, google_drive_folder_id, status) VALUES ('$tahun_baru', '$folder_id', 1)");

        if ($insert_year) {
            $year_id = mysqli_insert_id($conn); // Ambil ID tahun yang baru dibuat

            // 3. Insert 12 bulan otomatis
            $bulan_list = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $berhasil_bulan = 0;
            foreach ($bulan_list as $nomor => $nama) {
                // Kita isi google_drive_folder_id dengan string kosong dulu
                $query_bulan = "INSERT INTO months (year_id, nama_bulan, nomor_bulan, google_drive_folder_id) 
                                VALUES ('$year_id', '$nama', '$nomor', '')";
                if (mysqli_query($conn, $query_bulan)) {
                    $berhasil_bulan++;
                }
            }

            if ($berhasil_bulan == 12) {
                $pesan = "<div class='alert alert-success'>Berhasil! Tahun $tahun_baru dan 12 bulannya telah ditambahkan. Silakan isi ID Folder Google Drive untuk setiap bulan di menu <b>Browse Folders</b>.</div>";
            } else {
                $pesan = "<div class='alert alert-warning'>Tahun ditambahkan, tetapi ada masalah saat membuat 12 bulan.</div>";
            }
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal menambahkan tahun: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Tahun Arsip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">📁 Digitalisasi Arsip</a>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Kembali ke Dashboard</a>
    </div>
</nav>

<div class="container" style="max-width: 600px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4">Tambah Tahun Arsip Baru</h4>
            
            <?= $pesan; ?>

            <form method="POST" action="">
    <div class="mb-3">
        <label class="form-label fw-bold">Tahun Arsip</label>
        <input type="number" name="tahun" class="form-control" placeholder="Contoh: 2004" required min="1900" max="2100">
    </div>
    
    <div class="mb-4">
        <label class="form-label fw-bold">ID Folder Utama Google Drive (Opsional)</label>
        <input type="text" name="folder_id" class="form-control" placeholder="Contoh: 1k6zsnMTBYyGJNvS_YRZm0u...">
        <div class="form-text">ID folder tahunan. (Boleh dikosongkan).</div>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Tahun Baru</button>
</form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>