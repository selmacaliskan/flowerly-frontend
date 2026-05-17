<?php
session_start(); // Oturumu başlat
session_unset(); // Tüm oturum değişkenlerini temizle
session_destroy(); // Oturumu tamamen yok et

// Çıkış yaptıktan sonra ana sayfaya yönlendir
header("Location: index.php");
exit();
?>