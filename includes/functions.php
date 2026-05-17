<?php
session_start();

// Hata/Başarı Mesajı Göster (Madde 19)
function alertGoster($mesaj, $tur = "info") {
    $_SESSION['mesaj'] = "<div class='alert alert-$tur'>$mesaj</div>";
}

// Log Kaydet (Madde 18)
function logKaydet($db, $mesaj) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $sorgu = $db->prepare("INSERT INTO loglar (mesaj, ip_adresi) VALUES (?, ?)");
    $sorgu->execute([$mesaj, $ip]);
}

// Yetki Kontrolü (Madde 7, 9)
function yetkiKontrol($min_rol = 1) {
    if(!isset($_SESSION['user_id']) || $_SESSION['rol_id'] < $min_rol) {
        header("Location: login.php?hata=yetkisiz");
        exit();
    }
}
?>