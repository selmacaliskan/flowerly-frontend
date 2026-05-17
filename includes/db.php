<?php
$host = 'localhost';
$dbname = 'flowerly_db';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Hata modunu aktifleştir
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Hatayı log tablosuna yaz (Eğer DB bağlantısı varsa)
    $msg = "DB Hatası: " . $e->getMessage();
    // Log dosyasına yazma (Madde 18: Arka planda log dosyası)
    error_log($msg, 3, "hata_loglari.log");
    die("Sistem şu an meşgul, lütfen sonra tekrar deneyin.");
}
?>