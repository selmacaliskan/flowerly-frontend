<?php
session_start();
require_once '../includes/db.php';

// 1. Oturum kontrolü (Madde 9)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?hata=girisyap");
    exit();
}

$rol_id = $_SESSION['rol_id'];
$ip_adresi = $_SERVER['REMOTE_ADDR'];
$sayfa_adi = basename($_SERVER['PHP_SELF']);

// 2. Kullanıcı (Rol ID: 4) admin panele hiç giremez
// Eğer girmeye çalışırsa bu yetkisiz erişimdir ve loglanmalıdır (Madde 18)
if ($rol_id == 4) {
    $log_mesaj = "YETKİSİZ ERİŞİM DENEMESİ: Kullanıcı (ID: ".$_SESSION['user_id'].") admin sayfasına (".$sayfa_adi.") erişmeye çalıştı.";
    $log_sorgu = $db->prepare("INSERT INTO loglar (mesaj, ip_adresi) VALUES (?, ?)");
    $log_sorgu->execute([$log_mesaj, $ip_adresi]);

    header("Location: ../login.php?hata=yetkisiz");
    exit();
}

// Süper Admin (1) her yere girebilir
if ($rol_id == 1) {
    return; 
}

// 3. Editör (2) ve Moderatör (3) için sayfa bazlı izin kontrolü (Madde 7)
$dosya_adi_temiz = basename($_SERVER['PHP_SELF'], '.php');

// Dashboard ve Profil her personele açıktır
$herkese_acik = ['index', 'profil'];
if (in_array($dosya_adi_temiz, $herkese_acik)) {
    return;
}

$izin_kontrol = $db->prepare("SELECT id FROM rol_izinleri WHERE rol_id = ? AND sayfa = ?");
$izin_kontrol->execute([$rol_id, $dosya_adi_temiz]);

if ($izin_kontrol->rowCount() == 0) {
    // Personel yetkisi olmayan bir sayfaya girmeye çalışırsa logla (Madde 18)
    $log_mesaj = "YETKİSİZ SAYFA GİRİŞİ: Personel (ID: ".$_SESSION['user_id'].", Rol: ".$rol_id.") yetkisi olmayan sayfaya (".$sayfa_adi.") girmeye çalıştı.";
    $log_sorgu = $db->prepare("INSERT INTO loglar (mesaj, ip_adresi) VALUES (?, ?)");
    $log_sorgu->execute([$log_mesaj, $ip_adresi]);

    $rol_adi = ['', 'Süper Admin', 'Editör', 'Moderatör', 'Kullanıcı'][$rol_id] ?? 'Bilinmeyen';
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <title>Erişim Reddedildi - Flowerly</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #fdfaf7; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Inter', sans-serif; }
            .error-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; max-width: 400px; }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div style="font-size: 50px; margin-bottom: 20px;">🔒</div>
            <h2 style="color: #dc3545; font-weight: 800;">Erişim Kısıtlı</h2>
            <p class="text-muted">Bu bölümü görüntülemek için yetkiniz bulunmuyor.</p>
            <p class="small">Mevcut Rolünüz: <strong><?php echo $rol_adi; ?></strong></p>
            <hr>
            <a href="index.php" class="btn btn-primary rounded-pill px-4">Dashboard'a Dön</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>