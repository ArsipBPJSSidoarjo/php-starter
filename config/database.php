<?php
$host = getenv('DB_HOST') ?: 'db.fr-roub1.bengt.wasmernet.com';
$port = (int)(getenv('DB_PORT') ?: 20184);
$user = getenv('DB_USERNAME') ?: 'user_38d8167b';
$pass = getenv('DB_PASSWORD') ?: 'pw_M7ncnpJQPVC1LTYYJyLv6ZxXbhlMyu0I';
$db   = getenv('DB_NAME') ?: 'ArsipBPJSSidoarjo';

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
