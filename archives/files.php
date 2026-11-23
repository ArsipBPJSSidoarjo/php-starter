<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Ambil month_id
|--------------------------------------------------------------------------
*/

$month_id = isset($_GET['month_id']) ? (int) $_GET['month_id'] : 0;

if ($month_id <= 0) {
    die("Bulan tidak valid.");
}

/*
|--------------------------------------------------------------------------
| Ambil informasi bulan dan tahun
|--------------------------------------------------------------------------
*/

$query_month = "
    SELECT
        months.id,
        months.nama_bulan,
        months.nomor_bulan,
        years.tahun
    FROM months
    INNER JOIN years
        ON months.year_id = years.id
    WHERE months.id = $month_id
";

$result_month = mysqli_query($conn, $query_month);

$month = mysqli_fetch_assoc($result_month);

if (!$month) {
    die("Bulan tidak ditemukan.");
}

/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

/*
|--------------------------------------------------------------------------
| SORTING
|--------------------------------------------------------------------------
*/

$sort = isset($_GET['sort'])
    ? $_GET['sort']
    : 'tanggal';

/*
|--------------------------------------------------------------------------
| Kolom sorting yang diperbolehkan
|--------------------------------------------------------------------------
*/

$allowed_sort = [
    'tanggal' => 'tanggal',
    'nomor' => 'nomor',
    'kode_voucher' => 'kode_voucher'
];

if (!array_key_exists($sort, $allowed_sort)) {
    $sort = 'tanggal';
}

$order_by = $allowed_sort[$sort];

/*
|--------------------------------------------------------------------------
| Arah sorting
|--------------------------------------------------------------------------
*/

$order = isset($_GET['order'])
    ? strtoupper($_GET['order'])
    : 'ASC';

if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'ASC';
}

/*
|--------------------------------------------------------------------------
| Query arsip
|--------------------------------------------------------------------------
*/

$query_archives = "
    SELECT *
    FROM archives
    WHERE month_id = $month_id
";

/*
|--------------------------------------------------------------------------
| Tambahkan pencarian
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $search_safe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $query_archives .= "
        AND (
            nama_file LIKE '%$search_safe%'
            OR nomor LIKE '%$search_safe%'
            OR jenis_file LIKE '%$search_safe%'
            OR kode_voucher LIKE '%$search_safe%'
            OR penerima LIKE '%$search_safe%'
            OR tanggal LIKE '%$search_safe%'
        )
    ";
}

/*
|--------------------------------------------------------------------------
| Tambahkan sorting
|--------------------------------------------------------------------------
*/

$query_archives .= "
    ORDER BY $order_by $order
";

$result_archives = mysqli_query(
    $conn,
    $query_archives
);

if (!$result_archives) {
    die(
        "Query gagal: "
        . mysqli_error($conn)
    );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Arsip <?= htmlspecialchars($month['nama_bulan']); ?>
        <?= htmlspecialchars($month['tahun']); ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<!-- NAVBAR -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="../index.php"
        >
            📁 Digitalisasi Arsip
        </a>

    </div>

</nav>


<!-- CONTENT -->

<div class="container py-5">


    <!-- HEADER -->

    <div class="mb-4">

        <a
            href="months.php?year_id=<?= $month['id']; ?>"
            class="text-decoration-none"
        >
            ← Kembali ke daftar bulan
        </a>


        <div class="d-flex justify-content-between align-items-center mt-3">

            <div>

                <h1 class="fw-bold mb-1">

                    Arsip
                    <?= htmlspecialchars($month['nama_bulan']); ?>
                    <?= htmlspecialchars($month['tahun']); ?>

                </h1>

                <p class="text-muted mb-0">

                    Daftar file digitalisasi arsip.

                </p>

            </div>

        </div>

    </div>


    <!-- SEARCH & SORTING -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">


            <!-- SEARCH -->

            <form method="GET" class="mb-3">

                <input
                    type="hidden"
                    name="month_id"
                    value="<?= $month_id; ?>"
                >

                <div class="input-group">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari nomor, voucher, penerima, atau nama file..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        🔍 Cari
                    </button>

                </div>

            </form>


            <!-- SORTING -->

            <form method="GET" class="row g-2 align-items-end">

                <input
                    type="hidden"
                    name="month_id"
                    value="<?= $month_id; ?>"
                >

                <input
                    type="hidden"
                    name="search"
                    value="<?= htmlspecialchars($search); ?>"
                >


                <div class="col-md-4">

                    <label class="form-label">
                        Urutkan berdasarkan
                    </label>

                    <select
                        name="sort"
                        class="form-select"
                    >

                        <option
                            value="tanggal"
                            <?= $sort === 'tanggal' ? 'selected' : ''; ?>
                        >
                            Tanggal
                        </option>

                        <option
                            value="nomor"
                            <?= $sort === 'nomor' ? 'selected' : ''; ?>
                        >
                            Nomor
                        </option>

                        <option
                            value="kode_voucher"
                            <?= $sort === 'kode_voucher' ? 'selected' : ''; ?>
                        >
                            Kode Voucher
                        </option>

                    </select>

                </div>


                <div class="col-md-4">

                    <label class="form-label">
                        Urutan
                    </label>

                    <select
                        name="order"
                        class="form-select"
                    >

                        <option
                            value="ASC"
                            <?= $order === 'ASC' ? 'selected' : ''; ?>
                        >
                            Naik
                        </option>

                        <option
                            value="DESC"
                            <?= $order === 'DESC' ? 'selected' : ''; ?>
                        >
                            Turun
                        </option>

                    </select>

                </div>


                <div class="col-md-4">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Terapkan Sorting
                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- TABLE -->

    <div class="card border-0 shadow-sm">

        <div class="card-body">


            <div class="d-flex justify-content-between mb-3">

                <h5 class="fw-bold mb-0">

                    Daftar Arsip

                </h5>

                <span class="badge bg-secondary">

                    <?= mysqli_num_rows($result_archives); ?>
                    Arsip

                </span>

            </div>


            <?php if (mysqli_num_rows($result_archives) == 0): ?>


                <div class="alert alert-info">

                    Tidak ada arsip yang ditemukan.

                </div>


            <?php else: ?>


                <div class="table-responsive">


                    <table class="table table-hover align-middle">


                        <thead class="table-light">

                            <tr>

                                <th>No</th>

                                <th>Nomor</th>

                                <th>Jenis</th>

                                <th>Tanggal</th>

                                <th>Kode Voucher</th>

                                <th>Penerima</th>

                                <th>Status</th>

                                <th>File</th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php $no = 1; ?>


                            <?php while ($archive = mysqli_fetch_assoc($result_archives)): ?>


                                <tr>


                                    <td>

                                        <?= $no++; ?>

                                    </td>


                                    <td class="fw-semibold">

                                        <?= htmlspecialchars(
                                            $archive['nomor'] ?? '-'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $archive['jenis_file'] ?? '-'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $archive['tanggal'] ?? '-'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $archive['kode_voucher'] ?? '-'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $archive['penerima'] ?? '-'
                                        ); ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>


                                        <?php if (
                                            $archive['status_metadata']
                                            === 'valid'
                                        ): ?>

                                            <span class="badge bg-success">

                                                ✓ Valid

                                            </span>


                                        <?php elseif (
                                            $archive['status_metadata']
                                            === 'perlu_dilengkapi'
                                        ): ?>

                                            <span class="badge bg-warning text-dark">

                                                ⚠ Perlu dilengkapi

                                            </span>


                                        <?php else: ?>

                                            <span class="badge bg-danger">

                                                ! Perlu verifikasi

                                            </span>

                                        <?php endif; ?>


                                    </td>


                                    <!-- FILE -->

                                    <td>


                                        <?php if (
                                            !empty(
                                                $archive['google_drive_url']
                                            )
                                        ): ?>


                                            <a
                                                href="<?= htmlspecialchars(
                                                    $archive['google_drive_url']
                                                ); ?>"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-primary"
                                            >

                                                📄 Lihat

                                            </a>


                                        <?php else: ?>


                                            <span class="text-muted">

                                                Tidak tersedia

                                            </span>


                                        <?php endif; ?>


                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>