<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/vendor/autoload.php';

$client = new Google\Client();

$googleCredsJson = getenv('GOOGLE_CREDENTIALS_JSON');

if (!empty($googleCredsJson)) {
    // Jika berjalan di server Wasmer (membaca dari Environment Variables)
    $credsArray = json_decode($googleCredsJson, true);
    $client->setAuthConfig($credsArray);
} else {
    // Jika berjalan di komputer lokal / XAMPP
    $client->setAuthConfig(__DIR__ . '/credentials.json');
}

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

// Cek apakah token sudah expired
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

// Membuat service Google Drive
$driveService = new Google\Service\Drive($client);

// Mengambil 20 file/folder pertama
$response = $driveService->files->listFiles([
    'pageSize' => 20,
    'fields' => 'files(id, name, mimeType, modifiedTime, webViewLink)'
]);

echo '<h2>Daftar File Google Drive</h2>';

if (count($response->getFiles()) === 0) {

    echo '<p>Tidak ada file yang ditemukan.</p>';

} else {

    echo '<table border="1" cellpadding="8" cellspacing="0">';

    echo '<tr>';
    echo '<th>Nama</th>';
    echo '<th>Tipe</th>';
    echo '<th>Terakhir Diubah</th>';
    echo '</tr>';

    foreach ($response->getFiles() as $file) {

        echo '<tr>';

        echo '<td>' . htmlspecialchars($file->getName()) . '</td>';

        echo '<td>' . htmlspecialchars($file->getMimeType()) . '</td>';

        echo '<td>' . htmlspecialchars($file->getModifiedTime()) . '</td>';

        echo '</tr>';
    }

    echo '</table>';

}
