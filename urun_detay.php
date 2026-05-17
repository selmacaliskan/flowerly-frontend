<?php 
require_once 'includes/db.php';
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Eğer ürün ID'si geçersizse ana sayfaya gönder
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// --- YORUM GÖNDERME İŞLEMİ ---
if (isset($_POST['yorum_gonder']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $puan    = max(1, min(5, (int)$_POST['puan']));
    $yorum   = trim($_POST['yorum'] ?? '');
    
    // 1. Daha önce yorum yapılmış mı kontrol et (Madde 3)
    $kontrol = $db->prepare("SELECT id FROM yorumlar WHERE user_id = ? AND urun_id = ?");
    $kontrol->execute([$user_id, $id]);
    
    if ($kontrol->rowCount() > 0) {
        $hata_yorum = "Bu ürüne zaten daha önce yorum yaptınız.";
    } elseif (strlen($yorum) < 10) {
        $hata_yorum = "Yorumunuz çok kısa! Lütfen en az 10 karakter yazın.";
    } else {
        // 2. Yorumu veritabanına ekle (Madde 3: Prepared Statements)
        // onaylandi varsayılan 0 olarak ayarlanır (Admin onayı için)
        $ekle = $db->prepare("INSERT INTO yorumlar (urun_id, user_id, puan, yorum, onaylandi) VALUES (?, ?, ?, ?, 0)");
        $sonuc = $ekle->execute([$id, $user_id, $puan, $yorum]);
        
        if ($sonuc) {
            $basari_yorum = "Teşekkürler! Yorumunuz yönetici onayından sonra yayınlanacaktır. 🌸";
        } else {
            $hata_yorum = "Yorum kaydedilirken bir hata oluştu.";
        }
    }
}

// --- FAVORİ EKLE/ÇIKAR İŞLEMİ ---
if (isset($_POST['favori_aksiyon']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $fKontrol = $db->prepare("SELECT id FROM favoriler WHERE user_id = ? AND urun_id = ?");
    $fKontrol->execute([$user_id, $id]);
    
    if ($fKontrol->rowCount() > 0) {
        $db->prepare("DELETE FROM favoriler WHERE user_id = ? AND urun_id = ?")->execute([$user_id, $id]);
    } else {
        $db->prepare("INSERT INTO favoriler (user_id, urun_id) VALUES (?, ?)")->execute([$user_id, $id]);
    }
    header("Location: urun_detay.php?id=$id");
    exit();
}

// --- ÜRÜN BİLGİLERİNİ ÇEK ---
$sorgu = $db->prepare("SELECT u.*, k.ad as kat_ad FROM urunler u LEFT JOIN kategoriler k ON u.kategori_id = k.id WHERE u.id = ?");
$sorgu->execute([$id]);
$urun = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    die("Ürün bulunamadı!");
}

// Favori Durumu Kontrolü
$isFav = false;
if (isset($_SESSION['user_id'])) {
    $fs = $db->prepare("SELECT id FROM favoriler WHERE user_id = ? AND urun_id = ?");
    $fs->execute([$_SESSION['user_id'], $id]);
    $isFav = $fs->rowCount() > 0;
}

// Onaylı Yorumları Çek
$yorumlarSorgu = $db->prepare("SELECT y.*, k.ad_soyad FROM yorumlar y JOIN kullanicilar k ON y.user_id = k.id WHERE y.urun_id = ? AND y.onaylandi = 1 ORDER BY y.tarih DESC");
$yorumlarSorgu->execute([$id]);
$yorumlar = $yorumlarSorgu->fetchAll(PDO::FETCH_ASSOC);

// Ortalama Puan Hesapla
$puan_ortalama = 0;
$puan_sayisi = count($yorumlar);
if ($puan_sayisi > 0) {
    $toplamPuan = 0;
    foreach($yorumlar as $y) { $toplamPuan += $y['puan']; }
    $puan_ortalama = $toplamPuan / $puan_sayisi;
}

$sayfaBaslik = $urun['ad'];
include 'includes/header.php'; 
?>

<div class="container mt-5 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb" style="font-size:14px;">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Anasayfa</a></li>
        <li class="breadcrumb-item active text-dark fw-bold"><?php echo htmlspecialchars($urun['ad']); ?></li>
      </ol>
    </nav>

    <div class="row g-5">
        <!-- Sol: Ürün Görseli -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                <img src="assets/img/<?php echo $urun['resim']; ?>" class="img-fluid rounded-4" style="max-height: 500px; object-fit: contain;">
            </div>
        </div>

        <!-- Sağ: Ürün Bilgileri -->
        <div class="col-md-6">
            <div class="ps-md-4">
                <span class="badge bg-light text-primary mb-2 px-3 py-2 rounded-pill border">
                    <?php echo htmlspecialchars($urun['kat_ad']); ?>
                </span>
                <h1 class="display-5 fw-bold text-dark mb-3"><?php echo htmlspecialchars($urun['ad']); ?></h1>
                
                <?php if($puan_sayisi > 0): ?>
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="text-warning fs-4">
                        <?php for($i=1; $i<=5; $i++) echo ($i <= round($puan_ortalama)) ? '★' : '☆'; ?>
                    </div>
                    <span class="fw-bold fs-5"><?php echo number_format($puan_ortalama, 1); ?></span>
                    <span class="text-muted">(<?php echo $puan_sayisi; ?> Yorum)</span>
                </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h2 class="display-4 fw-bold text-rose" style="color: #e91e63;">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></h2>
                    <p class="text-success fw-bold small"><i class="fa fa-truck me-1"></i> Ücretsiz Kargo & Aynı Gün Teslimat</p>
                </div>

                <div class="d-flex gap-3 mb-5">
                    <form method="POST" action="sepetim.php" class="flex-grow-1">
                        <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                        <button type="submit" name="sepet_ekle" class="btn btn-lg w-100 rounded-pill py-3 fw-bold shadow" style="background:#e91e63; color:white; border:none;">
                            Sepete Ekle 🛒
                        </button>
                    </form>
                    
                    <form method="POST" action="urun_detay.php?id=<?php echo $id; ?>">
                        <button type="submit" name="favori_aksiyon" class="btn btn-outline-danger btn-lg rounded-circle shadow-sm" style="width:65px; height:65px;">
                            <?php echo $isFav ? '❤️' : '🤍'; ?>
                        </button>
                    </form>
                </div>

                <div class="p-4 rounded-4 bg-white shadow-sm border">
                    <h6 class="fw-bold mb-2">Ürün Açıklaması</h6>
                    <p class="text-muted lh-lg mb-0"><?php echo nl2br(htmlspecialchars($urun['aciklama'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Yorumlar Bölümü -->
    <div class="row mt-5">
        <div class="col-12">
            <hr class="my-5 opacity-5">
            <h3 class="fw-bold mb-4">💬 Müşteri Değerlendirmeleri</h3>

            <div class="row g-4">
                <!-- Yorum Listesi -->
                <div class="col-md-7">
                    <?php if(empty($yorumlar)): ?>
                        <div class="p-5 text-center bg-white rounded-4 border">
                            <p class="text-muted mb-0">Henüz yorum yapılmamış. İlk yorumu sen yap!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($yorumlar as $y): ?>
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-3 bg-white">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong class="text-dark"><?php echo htmlspecialchars($y['ad_soyad']); ?></strong>
                                    <div class="text-warning">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $y['puan']) ? '★' : '☆'; ?>
                                    </div>
                                </div>
                                <p class="text-muted mb-1 small"><?php echo date('d.m.Y', strtotime($y['tarih'])); ?></p>
                                <p class="mb-0 text-dark"><?php echo htmlspecialchars($y['yorum']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Yorum Formu -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-light border-start border-rose border-5">
                        <h5 class="fw-bold mb-3">Yorumunu Paylaş</h5>
                        
                        <?php if(!isset($_SESSION['user_id'])): ?>
                            <div class="alert alert-warning small rounded-3">Yorum yapabilmek için <a href="login.php" class="fw-bold">giriş yapmalısınız</a>.</div>
                        <?php else: ?>
                            <?php if(isset($basari_yorum)): ?>
                                <div class="alert alert-success small rounded-3"><?php echo $basari_yorum; ?></div>
                            <?php endif; ?>
                            <?php if(isset($hata_yorum)): ?>
                                <div class="alert alert-danger small rounded-3"><?php echo $hata_yorum; ?></div>
                            <?php endif; ?>

                            <form method="POST" action="urun_detay.php?id=<?php echo $id; ?>">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Puanınız</label>
                                    <select name="puan" class="form-select rounded-3">
                                        <option value="5">⭐⭐⭐⭐⭐ (Harika)</option>
                                        <option value="4">⭐⭐⭐⭐ (Çok İyi)</option>
                                        <option value="3">⭐⭐⭐ (Orta)</option>
                                        <option value="2">⭐⭐ (Kötü)</option>
                                        <option value="1">⭐ (Çok Kötü)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Mesajınız</label>
                                    <textarea name="yorum" class="form-control rounded-3" rows="4" placeholder="Ürün hakkındaki düşüncelerinizi yazın..." required></textarea>
                                </div>
                                <button type="submit" name="yorum_gonder" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow-sm">
                                    Yorumu Gönder
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>