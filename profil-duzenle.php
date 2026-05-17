<?php 
$sayfaBaslik = "Profil Bilgilerim";
$sayfa = "profil"; // Sidebar'da 'Profil Bilgilerim' linkinin aktif kalması için
include 'includes/header.php'; 

// Madde 9: Giriş Kontrolü
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$uID = $_SESSION['user_id'];

// --- GÜNCELLEME İŞLEMİ (Madde 8) ---
if ($_POST) {
    $ad = $_POST['ad_soyad'];
    $eposta = $_POST['eposta'];
    $sifre = $_POST['sifre'];

    if(!empty($sifre)) {
        // Madde 5: Şifreyi Hashleme
        $hash = password_hash($sifre, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE kullanicilar SET ad_soyad = ?, eposta = ?, sifre = ? WHERE id = ?");
        $update->execute([$ad, $eposta, $hash, $uID]);
    } else {
        $update = $db->prepare("UPDATE kullanicilar SET ad_soyad = ?, eposta = ? WHERE id = ?");
        $update->execute([$ad, $eposta, $uID]);
    }
    
    $_SESSION['ad_soyad'] = $ad; // Menüdeki ismi anlık güncelle
    $basarili = true;
}

// Güncel verileri çek
$u = $db->query("SELECT * FROM kullanicilar WHERE id = $uID")->fetch();
?>

<!-- Panel CSS -->
<link rel="stylesheet" href="assets/css/user-panel.css">

<div class="container mt-5 mb-5">
    <div class="row g-4">
        <!-- SOL MENÜ (Modüler Sidebar) -->
        <div class="col-lg-3 col-md-4">
            <?php include 'includes/user-sidebar.php'; ?>
        </div>

        <!-- SAĞ İÇERİK ALANI -->
        <div class="col-lg-9 col-md-8">
            
            <div class="row g-4">
                <!-- Üst Başlık Bölümü -->
                <div class="col-12">
                    <div class="p-4 bg-white rounded-4 shadow-sm">
                        <h2 class="welcome-text mb-1">Hesap Ayarları</h2>
                        <p class="text-muted mb-0 small">Kişisel bilgilerini ve güvenlik tercihlerini buradan yönetebilirsin.</p>
                    </div>
                </div>

                <?php if(isset($basarili)): ?>
                    <div class="col-12">
                        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-0 py-3">
                            ✨ Bilgilerin başarıyla güncellendi!
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Profil Düzenleme Formu (Bento Card) -->
                <div class="col-md-8">
                    <div class="card bento-card p-4 h-100 border-0">
                        <h5 class="fw-bold mb-4 d-flex align-items-center">
                            <span class="me-2">⚙️</span> Temel Bilgiler
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Adınız ve Soyadınız</label>
                                <input type="text" name="ad_soyad" class="form-control form-control-lg rounded-4 border-light bg-light shadow-none fs-6" 
                                       value="<?php echo $u['ad_soyad']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">E-posta Adresiniz</label>
                                <input type="email" name="eposta" class="form-control form-control-lg rounded-4 border-light bg-light shadow-none fs-6" 
                                       value="<?php echo $u['eposta']; ?>" required>
                            </div>

                            <hr class="my-4 opacity-5">

                            <h5 class="fw-bold mb-4 d-flex align-items-center">
                                <span class="me-2">🔒</span> Güvenlik & Şifre
                            </h5>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Yeni Şifre (Değiştirmeyeceksen boş bırak)</label>
                                <input type="password" name="sifre" class="form-control form-control-lg rounded-4 border-light bg-light shadow-none fs-6" 
                                       placeholder="••••••••">
                            </div>

                            <button type="submit" class="btn btn-rose-pill w-100 py-3 fw-bold shadow-sm">
                                Değişiklikleri Kaydet
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sağ Taraf: Ek Bilgiler -->
                <div class="col-md-4">
                    <!-- Güvenlik Durumu Kartı -->
                    <div class="card bento-card p-4 border-0 mb-4 bg-dark text-white shadow-lg">
                        <h6 class="fw-bold mb-3 small">Hesap Güvenliği</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success me-2" style="width: 8px; height: 8px;"></div>
                            <span class="small opacity-75">Şifre Güçlü</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success me-2" style="width: 8px; height: 8px;"></div>
                            <span class="small opacity-75">E-posta Onaylı</span>
                        </div>
                    </div>

                    <!-- İpucu Kartı -->
                    <div class="card bento-card p-4 border-0 info-card h-auto">
                        <div class="text-center">
                            <div class="fs-1 mb-2">💡</div>
                            <h6 class="fw-bold mb-2">Biliyor muydun?</h6>
                            <p class="small text-muted mb-0">E-posta adresini güncellediğinde, kampanya duyurularımız artık yeni adresine gönderilir.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Bu sayfaya özel buton stili (user-panel.css'e de ekleyebilirsin) */
    .btn-rose-pill {
        background: var(--f-rose);
        color: #fff;
        border-radius: 50px;
        border: none;
        transition: 0.3s;
    }
    .btn-rose-pill:hover {
        background: #c2185b;
        transform: scale(1.02);
        box-shadow: 0 10px 20px rgba(233, 30, 99, 0.2);
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    .text-pink {
        color: var(--f-rose);
    }
</style>

<?php include 'includes/footer.php'; ?>