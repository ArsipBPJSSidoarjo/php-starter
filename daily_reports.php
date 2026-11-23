<?php

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| AMBIL DATA LAPORAN HARIAN
|--------------------------------------------------------------------------
*/

$query = "
    SELECT *
    FROM daily_reports
    ORDER BY tanggal DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
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

    <title>Laporan Harian</title>

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
            href="dashboard.php"
            class="navbar-brand fw-bold"
        >
            📁 Digitalisasi Arsip
        </a>

        <a
            href="archives/years.php"
            class="btn btn-light btn-sm"
        >
            Lihat Arsip
        </a>

    </div>

</nav>


<!-- CONTENT -->

<div class="container py-5">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold mb-1">
                Laporan Harian
            </h1>

            <p class="text-muted mb-0">
                Monitoring kegiatan digitalisasi setiap hari.
            </p>

        </div>


        <a
            href="daily_report_add.php"
            class="btn btn-primary"
        >
            + Tambah Laporan
        </a>

    </div>


    <!-- TABLE -->

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>No</th>

                            <th>Tanggal</th>

                            <th>Target</th>

                            <th>Digitalisasi</th>

                            <th>Bermasalah</th>

                            <th>Progress</th>

                            <th>Keterangan</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $no = 1;

                        if (mysqli_num_rows($result) == 0):

                        ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center text-muted py-4"
                                >

                                    Belum ada laporan harian.

                                </td>

                            </tr>

                        <?php endif; ?>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>


                            <?php

                            $target = (int) $row['target'];

                            $digitalisasi =
                                (int) $row['jumlah_digitalisasi'];

                            $progress = $target > 0
                                ? ($digitalisasi / $target) * 100
                                : 0;

                            ?>


                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>


                                <td>

                                    <?= date(
                                        'd-m-Y',
                                        strtotime($row['tanggal'])
                                    ); ?>

                                </td>


                                <td>

                                    <?= number_format(
                                        $target
                                    ); ?>

                                </td>


                                <td>

                                    <span class="fw-semibold">

                                        <?= number_format(
                                            $digitalisasi
                                        ); ?>

                                    </span>

                                </td>


                                <td>

                                    <?php if (
                                        $row['jumlah_bermasalah'] > 0
                                    ): ?>

                                        <span class="badge bg-warning text-dark">

                                            <?= number_format(
                                                $row['jumlah_bermasalah']
                                            ); ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-success">

                                            0

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td style="min-width: 150px;">

                                    <div class="progress">

                                        <div
                                            class="progress-bar"
                                            style="width: <?= min(
                                                $progress,
                                                100
                                            ); ?>%"
                                        >

                                            <?= round(
                                                $progress
                                            ); ?>%

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $row['keterangan'] ?? '-'
                                    ); ?>

                                </td>

                            </tr>


                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>