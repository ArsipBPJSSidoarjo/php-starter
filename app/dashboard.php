<?php

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| TOTAL ARSIP
|--------------------------------------------------------------------------
*/

$query_total = "
    SELECT COUNT(*) AS total
    FROM archives
";

$result_total = mysqli_query($conn, $query_total);

$data_total = mysqli_fetch_assoc($result_total);

$total_arsip = $data_total['total'];


/*
|--------------------------------------------------------------------------
| TOTAL TAHUN
|--------------------------------------------------------------------------
*/

$query_years = "
    SELECT COUNT(*) AS total
    FROM years
    WHERE status = 1
";

$result_years = mysqli_query($conn, $query_years);

$data_years = mysqli_fetch_assoc($result_years);

$total_tahun = $data_years['total'];


/*
|--------------------------------------------------------------------------
| TOTAL BULAN
|--------------------------------------------------------------------------
*/

$query_months = "
    SELECT COUNT(*) AS total
    FROM months
";

$result_months = mysqli_query($conn, $query_months);

$data_months = mysqli_fetch_assoc($result_months);

$total_bulan = $data_months['total'];

/*
|--------------------------------------------------------------------------
| LAPORAN HARI INI
|--------------------------------------------------------------------------
*/

$today = date('Y-m-d');

$query_today = "
    SELECT
        target,
        jumlah_digitalisasi,
        jumlah_bermasalah
    FROM daily_reports
    WHERE tanggal = '$today'
    ORDER BY id DESC
    LIMIT 1
";

$result_today = mysqli_query($conn, $query_today);

$data_today = mysqli_fetch_assoc($result_today);


/*
|--------------------------------------------------------------------------
| NILAI DEFAULT
|--------------------------------------------------------------------------
*/

$target_hari_ini = $data_today['target'] ?? 0;

$digitalisasi_hari_ini =
    $data_today['jumlah_digitalisasi'] ?? 0;

$bermasalah_hari_ini =
    $data_today['jumlah_bermasalah'] ?? 0;


/*
|--------------------------------------------------------------------------
| PROGRESS HARI INI
|--------------------------------------------------------------------------
*/

$progress_hari_ini = $target_hari_ini > 0
    ? ($digitalisasi_hari_ini / $target_hari_ini) * 100
    : 0;


/*
|--------------------------------------------------------------------------
| LAPORAN TERBARU
|--------------------------------------------------------------------------
*/

$query_latest = "
    SELECT *
    FROM daily_reports
    ORDER BY tanggal DESC, id DESC
    LIMIT 5
";

$result_latest = mysqli_query($conn, $query_latest);

/*
|--------------------------------------------------------------------------
| ARSIP PER BULAN
|--------------------------------------------------------------------------
*/

$query_month_stats = "
    SELECT
        months.nama_bulan,
        months.nomor_bulan,
        COUNT(archives.id) AS jumlah_arsip

    FROM months

    LEFT JOIN archives
        ON months.id = archives.month_id

    GROUP BY
        months.id,
        months.nama_bulan,
        months.nomor_bulan

    ORDER BY months.nomor_bulan ASC
";

$result_month_stats = mysqli_query(
    $conn,
    $query_month_stats
);

if (!$result_month_stats) {
    die("Query gagal: " . mysqli_error($conn));
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

    <title>Dashboard Digitalisasi Arsip</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="dashboard.php"
        >
            📁 Digitalisasi Arsip
        </a>


        <div class="d-flex gap-2">
            <a href="tambah_tahun.php" class="btn btn-success btn-sm fw-bold">
                + Tambah Tahun
            </a>
            <a href="archives/years.php" class="btn btn-outline-light btn-sm">
                Browse Folders
            </a>
            <a href="arsip.php" class="btn btn-light btn-sm fw-bold">
                🔍 Search All Archives
            </a>
        </div>

    </div>

</nav>


<!-- =========================================================
     CONTENT
========================================================= -->

<div class="container py-5">


    <!-- HEADER -->

    <div class="mb-4">

        <h1 class="fw-bold">
            Dashboard
        </h1>

        <p class="text-muted">
            Monitoring digitalisasi arsip secara keseluruhan.
        </p>

    </div>


    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <div class="row g-4 mb-5">


        <!-- TOTAL ARSIP -->

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">
                        Total Arsip Digital
                    </p>

                    <h2 class="fw-bold">
                        <?= $total_arsip; ?>
                    </h2>

                    <p class="mb-0 text-primary">
                        File terdigitalisasi
                    </p>

                </div>

            </div>

        </div>


        <!-- TOTAL TAHUN -->

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">
                        Tahun Arsip
                    </p>

                    <h2 class="fw-bold">
                        <?= $total_tahun; ?>
                    </h2>

                    <p class="mb-0 text-primary">
                        Tahun tersedia
                    </p>

                </div>

            </div>

        </div>


        <!-- TOTAL BULAN -->

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">
                        Bulan
                    </p>

                    <h2 class="fw-bold">
                        <?= $total_bulan; ?>
                    </h2>

                    <p class="mb-0 text-primary">
                        Bulan terdaftar
                    </p>

                </div>

            </div>

        </div>


    </div>
<!-- =====================================================
     LAPORAN HARI INI
====================================================== -->

<div class="row g-4 mb-5">


    <!-- TARGET -->

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    Target Hari Ini
                </p>

                <h2 class="fw-bold">
                    <?= number_format($target_hari_ini); ?>
                </h2>

                <p class="mb-0 text-primary">
                    Arsip
                </p>

            </div>

        </div>

    </div>


    <!-- DIGITALISASI -->

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    Digitalisasi Hari Ini
                </p>

                <h2 class="fw-bold">
                    <?= number_format($digitalisasi_hari_ini); ?>
                </h2>

                <p class="mb-0 text-success">
                    Berhasil
                </p>

            </div>

        </div>

    </div>


    <!-- BERMASALAH -->

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    Arsip Bermasalah
                </p>

                <h2 class="fw-bold">
                    <?= number_format($bermasalah_hari_ini); ?>
                </h2>

                <p class="mb-0 text-warning">
                    Perlu diperiksa
                </p>

            </div>

        </div>

    </div>

</div>

<!-- =====================================================
     LAPORAN TERBARU
====================================================== -->

<div class="card border-0 shadow-sm mb-5">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">
                Laporan Harian Terbaru
            </h5>


            <a
                href="daily_reports.php"
                class="btn btn-outline-primary btn-sm"
            >
                Lihat Semua
            </a>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>Tanggal</th>

                        <th>Target</th>

                        <th>Digitalisasi</th>

                        <th>Bermasalah</th>

                        <th>Progress</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (mysqli_num_rows($result_latest) == 0): ?>

                        <tr>

                            <td
                                colspan="5"
                                class="text-center text-muted"
                            >

                                Belum ada laporan.

                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php while ($row = mysqli_fetch_assoc($result_latest)): ?>


                        <?php

                        $target = (int) $row['target'];

                        $digital =
                            (int) $row['jumlah_digitalisasi'];

                        $progress = $target > 0
                            ? ($digital / $target) * 100
                            : 0;

                        ?>


                        <tr>

                            <td>

                                <?= date(
                                    'd-m-Y',
                                    strtotime($row['tanggal'])
                                ); ?>

                            </td>


                            <td>

                                <?= number_format($target); ?>

                            </td>


                            <td>

                                <?= number_format($digital); ?>

                            </td>


                            <td>

                                <?= number_format(
                                    $row['jumlah_bermasalah']
                                ); ?>

                            </td>


                            <td>

                                <span class="badge bg-primary">

                                    <?= round($progress); ?>%

                                </span>

                            </td>

                        </tr>


                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


    <!-- =====================================================
         STATISTIK BULAN
    ====================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-body">


            <h5 class="fw-bold mb-4">
                Jumlah Arsip per Bulan
            </h5>


            <?php while ($month = mysqli_fetch_assoc($result_month_stats)): ?>


                <div class="mb-4">


                    <div class="d-flex justify-content-between mb-2">

                        <span class="fw-semibold">

                            <?= htmlspecialchars(
                                $month['nama_bulan']
                            ); ?>

                        </span>


                        <span class="text-muted">

                            <?= $month['jumlah_arsip']; ?>
                            arsip

                        </span>

                    </div>


                    <?php

                    /*
                    |--------------------------------------------------
                    | Progress sementara
                    |
                    | Kita gunakan jumlah terbesar sebagai 100%.
                    |--------------------------------------------------
                    */

                    ?>

                    <div class="progress">

                        <div
                            class="progress-bar"
                            role="progressbar"
                            style="width: <?= $total_arsip > 0
                                ? ($month['jumlah_arsip'] / $total_arsip) * 100
                                : 0; ?>%"
                        >

                        </div>

                    </div>


                </div>


            <?php endwhile; ?>


        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>