<?php

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| PROSES SIMPAN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tanggal = $_POST['tanggal'];

    $target = (int) $_POST['target'];

    $jumlah_digitalisasi =
        (int) $_POST['jumlah_digitalisasi'];

    $jumlah_bermasalah =
        (int) $_POST['jumlah_bermasalah'];

    $keterangan =
        trim($_POST['keterangan']);


    $stmt = mysqli_prepare(
        $conn,
        "
        INSERT INTO daily_reports
        (
            tanggal,
            target,
            jumlah_digitalisasi,
            jumlah_bermasalah,
            keterangan
        )
        VALUES (?, ?, ?, ?, ?)
        "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "siiis",
        $tanggal,
        $target,
        $jumlah_digitalisasi,
        $jumlah_bermasalah,
        $keterangan
    );


    if (mysqli_stmt_execute($stmt)) {

        header("Location: daily_reports.php");

        exit;

    } else {

        $error = mysqli_error($conn);

    }

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

    <title>Tambah Laporan Harian</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            href="dashboard.php"
            class="navbar-brand fw-bold"
        >
            📁 Digitalisasi Arsip
        </a>

    </div>

</nav>


<div class="container py-5">


    <div class="row justify-content-center">

        <div class="col-md-7">


            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">


                    <h3 class="fw-bold mb-1">

                        Tambah Laporan Harian

                    </h3>


                    <p class="text-muted mb-4">

                        Masukkan hasil digitalisasi hari ini.

                    </p>


                    <?php if (isset($error)): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error); ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <!-- TANGGAL -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Tanggal

                            </label>

                            <input
                                type="date"
                                name="tanggal"
                                class="form-control"
                                value="<?= date('Y-m-d'); ?>"
                                required
                            >

                        </div>


                        <!-- TARGET -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Target Digitalisasi

                            </label>

                            <input
                                type="number"
                                name="target"
                                class="form-control"
                                min="0"
                                placeholder="Contoh: 500"
                                required
                            >

                        </div>


                        <!-- DIGITALISASI -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Jumlah Berhasil Didigitalisasi

                            </label>

                            <input
                                type="number"
                                name="jumlah_digitalisasi"
                                class="form-control"
                                min="0"
                                placeholder="Contoh: 475"
                                required
                            >

                        </div>


                        <!-- BERMASALAH -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Jumlah Bermasalah

                            </label>

                            <input
                                type="number"
                                name="jumlah_bermasalah"
                                class="form-control"
                                min="0"
                                value="0"
                                required
                            >

                        </div>


                        <!-- KETERANGAN -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Keterangan

                            </label>

                            <textarea
                                name="keterangan"
                                class="form-control"
                                rows="3"
                                placeholder="Contoh: 25 arsip perlu scan ulang."
                            ></textarea>

                        </div>


                        <!-- BUTTON -->

                        <div class="d-flex gap-2">

                            <a
                                href="daily_reports.php"
                                class="btn btn-secondary"
                            >
                                Batal
                            </a>


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                Simpan Laporan

                            </button>

                        </div>


                    </form>


                </div>

            </div>


        </div>

    </div>


</div>


</body>

</html>