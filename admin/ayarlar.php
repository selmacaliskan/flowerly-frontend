<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "ayarlar"; // Sidebar'da aktif butonun yanması için

// --- VERİ ÇEKME: Mevcut Ayarları Al (ID: 1) ---
$ayarSorgu = $db->query("SELECT * FROM ayarlar WHERE id = 1");
$ayarlar = $ayarSorgu->fetch(PDO::FETCH_ASSOC);

if ($_SESSION['rol_id'] != 1) {
    // Süper Admin değilse Dashboard'a geri gönder ve uyar
    header("Location: index.php?hata=yetkisiz");
    exit();
}

// --- İŞLEM: Ayarları Güncelle ---
if ($_POST) {
    $baslik = $_POST['site_baslik'];
    $logo = $_POST['site_logo'];
    $eposta = $_POST['iletisim_eposta'];
    $whatsapp = $_POST['whatsapp_no'];
    $facebook = $_POST['facebook_url'];
    $instagram = $_POST['instagram_url'];
    $adres = $_POST['adres'];

    // Madde 3: Prepared Statements (Güvenlik)
    $guncelle = $db->prepare("UPDATE ayarlar SET 
        site_baslik = ?, 
        site_logo = ?, 
        iletisim_eposta = ?, 
        whatsapp_no = ?, 
        facebook_url = ?, 
        instagram_url = ?, 
        adres = ? 
        WHERE id = 1");
    
    $sonuc = $guncelle->execute([$baslik, $logo, $eposta, $whatsapp, $facebook, $instagram, $adres]);

    if ($sonuc) {
        header("Location: ayarlar.php?mesaj=guncellendi");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Site Ayarları - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body class="admin-body">

<div class="d-flex">
    <!-- Sidebar (Madde 6) -->
    <?php include 'sidebar.php'; ?>

    <!-- İçerik -->
    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-gears me-2 text-primary"></i> Site Ayarları</h2>
                <small class="text-muted">Kurumsal bilgileri, logoyu ve sosyal medya hesaplarını buradan yönetin.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-check-circle me-2"></i> Tüm site ayarları başarıyla güncellendi.
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-4">
                <!-- SOL SÜTUN: Genel Ayarlar ve Logo -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-2 text-primary">🖥️ Genel Bilgiler</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Site Başlığı (Title)</label>
                                <input type="text" name="site_baslik" class="form-control rounded-3" value="<?php echo $ayarlar['site_baslik']; ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Logo Dosya Adı</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-image"></i></span>
                                    <input type="text" name="site_logo" class="form-control" value="<?php echo $ayarlar['site_logo']; ?>" required>
                                </div>
                                <small class="text-muted">Görselin assets/img/ klasöründe olduğundan emin olun.</small>
                            </div>
                            <div class="col-12 mt-4">
                                <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">📍 İletişim Bilgileri</h5>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">İletişim E-postası</label>
                                <input type="email" name="iletisim_eposta" class="form-control rounded-3" value="<?php echo $ayarlar['iletisim_eposta']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">WhatsApp Hattı</label>
                                <input type="text" name="whatsapp_no" class="form-control rounded-3" value="<?php echo $ayarlar['whatsapp_no']; ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Firma Adresi</label>
                                <textarea name="adres" class="form-control rounded-3" rows="3"><?php echo $ayarlar['adres']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SAĞ SÜTUN: Sosyal Medya ve Kaydet -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-2 text-primary">📱 Sosyal Medya</h5>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Facebook URL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="fa-brands fa-facebook-f"></i></span>
                                <input type="text" name="facebook_url" class="form-control" value="<?php echo $ayarlar['facebook_url']; ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Instagram URL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-danger text-white"><i class="fa-brands fa-instagram"></i></span>
                                <input type="text" name="instagram_url" class="form-control" value="<?php echo $ayarlar['instagram_url']; ?>">
                            </div>
                        </div>
                        
                        <hr>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Ayarları Kaydet
                        </button>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-light">
                        <h6 class="fw-bold mb-2">Mevcut Logo</h6>
                        <div class="bg-white p-3 rounded-3 shadow-sm mb-2 d-flex align-items-center justify-content-center" style="height: 100px;">
                            <img src="../assets/img/<?php echo $ayarlar['site_logo']; ?>" class="img-fluid" style="max-height: 80px;">
                        </div>
                        <small class="text-muted">Logo Değişimi: assets/img/ içindeki dosyayı güncelleyin.</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>