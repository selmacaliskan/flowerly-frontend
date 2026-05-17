<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "profil"; // Sidebar'da aktif buton için

// 1. Mevcut admin bilgilerini veritabanından çek (Madde 8)
$sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$sorgu->execute([$_SESSION['user_id']]);
$admin = $sorgu->fetch(PDO::FETCH_ASSOC);

// 2. Güncelleme İşlemi
if ($_POST) {
    $ad = $_POST['ad_soyad'];
    $eposta = $_POST['eposta'];
    $yeni_sifre = $_POST['sifre'];

    if (!empty($yeni_sifre)) {
        // Madde 5: Şifreyi hashle
        $hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE kullanicilar SET ad_soyad = ?, eposta = ?, sifre = ? WHERE id = ?");
        $update->execute([$ad, $eposta, $hash, $_SESSION['user_id']]);
    } else {
        $update = $db->prepare("UPDATE kullanicilar SET ad_soyad = ?, eposta = ? WHERE id = ?");
        $update->execute([$ad, $eposta, $_SESSION['user_id']]);
    }
    
    // Session bilgilerini de anlık güncelle
    $_SESSION['ad_soyad'] = $ad;
    header("Location: profil.php?mesaj=guncellendi");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Profilim - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body class="admin-body">

<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-user-gear me-2 text-primary"></i> Profil Bilgilerim</h2>
                <small class="text-muted">Kendi kişisel bilgilerinizi ve şifrenizi buradan yönetin.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-check-circle me-2"></i> Profil bilgileriniz başarıyla güncellendi.
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Form Alanı -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ad Soyad</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fa fa-user text-muted"></i></span>
                                <input type="text" name="ad_soyad" class="form-control border-0 bg-light py-2" value="<?php echo $admin['ad_soyad']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">E-posta Adresi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fa fa-envelope text-muted"></i></span>
                                <input type="email" name="eposta" class="form-control border-0 bg-light py-2" value="<?php echo $admin['eposta']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Yeni Şifre (Sadece değiştirmek isterseniz doldurun)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fa fa-lock text-muted"></i></span>
                                <input type="password" name="sifre" class="form-control border-0 bg-light py-2" placeholder="••••••••">
                            </div>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                            <i class="fa-solid fa-save me-2"></i> Bilgilerimi Kaydet
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bilgi Kartı -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fa fa-shield-halved text-primary fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Güvenlik Notu</h5>
                    </div>
                    <p class="small opacity-75 mb-0">
                        Şifrenizi güncellerken en az 8 karakter, harf ve rakam kombinasyonu kullanmanız güvenliğiniz için önerilir.
                    </p>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                    <div class="mb-3 text-muted fs-1"><i class="fa-solid fa-circle-user"></i></div>
                    <h5 class="fw-bold mb-1"><?php echo $admin['ad_soyad']; ?></h5>
                    <p class="text-muted small">Süper Admin</p>
                    <hr>
                    <small class="text-muted">Son Profil Güncelleme: 1 hafta önce</small>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>