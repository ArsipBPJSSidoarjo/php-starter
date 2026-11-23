<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/vendor/autoload.php';

$client = new Google\Client();

$client->setAuthConfig(__DIR__ . '/credentials.json');
$client->setAccessType('offline');

$client->setScopes([
    Google\Service\Drive::DRIVE_METADATA_READONLY
]);

$tokenPath = __DIR__ . '/storage/token.json';

if (!file_exists($tokenPath)) {
    die('Token Google Drive belum tersedia.');
}

$token = json_decode(file_get_contents($tokenPath), true);

$client->setAccessToken($token);

// Refresh token jika expired
if ($client->isAccessTokenExpired()) {

    if (!empty($token['refresh_token'])) {

        $newToken = $client->fetchAccessTokenWithRefreshToken(
            $token['refresh_token']
        );

        $token = array_merge($token, $newToken);

        file_put_contents(
            $tokenPath,
            json_encode($token, JSON_PRETTY_PRINT)
        );

    } else {
        die('Token sudah expired dan tidak memiliki refresh token.');
    }
}

$driveService = new Google\Service\Drive($client);
require_once __DIR__ . '/koneksi.php';


// ======================================================
// FOLDER UTAMA TAHUN 2003
// ======================================================

$folderTahun2003 = '1k6zsnMTBYyGJNvS_YRZm0uNo601YyVbY';


// ======================================================
// MENCARI FOLDER BULAN
// ======================================================

$queryBulan = sprintf(
    "'%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
    $folderTahun2003
);

$responseBulan = $driveService->files->listFiles([
    'q' => $queryBulan,
    'pageSize' => 100,
    'fields' => 'files(id,name,mimeType)'
]);

$folderBulan = $responseBulan->getFiles();


// Urutkan berdasarkan nama folder
usort($folderBulan, function ($a, $b) {
    return strnatcasecmp($a->getName(), $b->getName());
});


// ======================================================
// TAMPILAN
// ======================================================

echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>Sinkronisasi Arsip 2003</title>';

echo '<style>
body {
    font-family: Arial, sans-serif;
    margin: 30px;
}

h1 {
    margin-bottom: 5px;
}

.bulan {
    margin-top: 25px;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 10px;
}

th, td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: left;
}

th {
    background: #f2f2f2;
}

.success {
    color: green;
    font-weight: bold;
}

.info {
    background: #eef6ff;
    padding: 10px;
    border-radius: 5px;
}
</style>';

echo '</head>';
echo '<body>';

echo '<h1>Sinkronisasi Arsip Tahun 2003</h1>';

echo '<div class="info">';
echo 'Folder utama: <strong>tahun 2003</strong><br>';
echo 'Jumlah folder bulan ditemukan: <strong>' . count($folderBulan) . '</strong>';
echo '</div>';


// ======================================================
// BACA SETIAP BULAN
// ======================================================

$totalFile = 0;

foreach ($folderBulan as $bulan) {

    $bulanId = $bulan->getId();
    $bulanNama = $bulan->getName();

        // Cari month_id berdasarkan folder Google Drive
    $stmtMonth = $conn->prepare("
        SELECT id 
        FROM months 
        WHERE google_drive_folder_id = ?
        LIMIT 1
    ");

    $stmtMonth->bind_param("s", $bulanId);
    $stmtMonth->execute();

    $resultMonth = $stmtMonth->get_result();
    $monthData = $resultMonth->fetch_assoc();

    if (!$monthData) {
    echo '<p style="color:red;">
        Folder bulan ini belum terdaftar di tabel months.<br>
        Nama folder: ' . htmlspecialchars($bulanNama) . '<br>
        ID Google Drive: ' . htmlspecialchars($bulanId) . '
    </p>';

    continue;
}

    $monthId = $monthData['id'];

    echo '<div class="bulan">';

    echo '<h2>' . htmlspecialchars($bulanNama) . '</h2>';

// ======================================================
// CARI SEMUA FILE PDF DI DALAM FOLDER BULAN
// Menggunakan pagination Google Drive
// ======================================================

$queryFile = sprintf(
    "'%s' in parents and mimeType = 'application/pdf' and trashed = false",
    $bulanId
);

$pageToken = null;
$semua_file = [];

do {
    $params = [
        'q' => $queryFile,
        'pageSize' => 1000,
        'fields' => 'nextPageToken, files(id,name,mimeType,modifiedTime,webViewLink)'
    ];
    
    if ($pageToken) {
        $params['pageToken'] = $pageToken;
    }
    
    $results = $driveService->files->listFiles($params);
    
    if ($results && $results->getFiles()) {
        foreach ($results->getFiles() as $file) {
            $semua_file[] = $file;
        }
    }
    
    $pageToken = $results->getNextPageToken();

} while ($pageToken);

echo '<p class="success">';
echo 'Ditemukan ' . count($semua_file) . ' file PDF';
echo '</p>';

echo '<p>';
echo 'Database bulan ini: ' . $monthId;
echo '</p>';

if (count($semua_file) > 0) {

    echo '<table>';
    echo '<tr>';
    echo '<th>No</th>';
    echo '<th>Nama File</th>';
    echo '<th>File ID</th>';
    echo '<th>Terakhir Diubah</th>';
    echo '</tr>';

    $no = 1;

    foreach ($semua_file as $file) {

        $totalFile++;

        // Simpan data file ke database
        $fileId = $file->getId();
        $fileName = $file->getName();
        $driveUrl = $file->getWebViewLink();

        $stmtArchive = $conn->prepare("
            INSERT INTO archives
             (
             month_id,
            google_drive_file_id,
            nama_file,
            google_drive_url,
            status_metadata
            )
            VALUES (?, ?, ?, ?, 'perlu_verifikasi')
            ON DUPLICATE KEY UPDATE
                nama_file = VALUES(nama_file),
                google_drive_url = VALUES(google_drive_url),
                updated_at = CURRENT_TIMESTAMP
        ");

        $stmtArchive->bind_param(
            "isss",
            $monthId,
            $fileId,
            $fileName,
            $driveUrl
         );

        $stmtArchive->execute();

        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($file->getName()) . '</td>';
        echo '<td>' . htmlspecialchars($file->getId()) . '</td>';
        echo '<td>' . htmlspecialchars($file->getModifiedTime()) . '</td>';
        echo '</tr>';
    }

    echo '</table>';

} else {
    echo '<p>Tidak ada file PDF di bulan ini.</p>';
}

echo '</div>';
}
echo '<hr>';
echo '<h2>Total File PDF Tahun 2003: ' . $totalFile . '</h2>';

echo '</body>';
echo '</html>';