<?php 
$sayfaBaslik = "Hesabım";
$sayfa = "hesabim"; 
include 'includes/header.php'; 

if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$uID = $_SESSION['user_id'];

// 1. Sipariş Sayısını Çek
$siparisSayisi = $db->prepare("SELECT COUNT(*) FROM siparisler WHERE user_id = ?");
$siparisSayisi->execute([$uID]);
$sSayi = $siparisSayisi->fetchColumn();

// 2. Yaklaşan Hatırlatıcıları Çek (Bugün ve sonrası)
$hatirlaticiSorgu = $db->prepare("SELECT *, DATEDIFF(tarih, CURDATE()) as kalan_gun 
                                  FROM hatirlaticilar 
                                  WHERE user_id = ? AND tarih >= CURDATE() 
                                  ORDER BY tarih ASC LIMIT 3");
$hatirlaticiSorgu->execute([$uID]);
$hatirlaticilar = $hatirlaticiSorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Bu sayfaya özel CSS -->
<link rel="stylesheet" href="assets/css/user-panel.css">

<div class="container mt-5 mb-5">
    <div class="row g-4">
        <!-- SOL MENÜ -->
        <div class="col-lg-3 col-md-4">
            <?php include 'includes/user-sidebar.php'; ?>
        </div>

        <!-- SAĞ İÇERİK ALANI -->
        <div class="col-lg-9 col-md-8">
            <!-- Üst Karşılama ve Puan -->
            <div class="row g-4 mb-4 align-items-center">
                <div class="col-md-7">
                    <h2 class="welcome-text">Merhaba, <?php echo explode(' ', $_SESSION['ad_soyad'])[0]; ?>!</h2>
                    <p class="text-muted">Bugün kimi mutlu etmek istersin? ✨</p>
                </div>
                <div class="col-md-5">
                    <div class="card bento-card puan-card p-4 border-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold">Flowerly Puan</span>
                            <span class="stats-badge">450 Puan</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar" style="width: 85%"></div>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">500 puanda 50 TL indirim kazanırsın!</small>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- 1. Hızlı İşlemler -->
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="adreslerim.php" class="quick-action-btn">
                                <div class="icon">📍</div>
                                <div class="label">Adreslerim</div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="hatirlaticilar.php" class="quick-action-btn">
                                <div class="icon">📅</div>
                                <div class="label">Hatırlatıcılar</div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="kuponlarim.php" class="quick-action-btn">
                                <div class="icon">🎟️</div>
                                <div class="label">Kuponlarım</div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="yardim.php" class="quick-action-btn">
                                <div class="icon">💬</div>
                                <div class="label">Yardım Al</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 2. Sipariş Özeti -->
                <div class="col-md-7">
                    <div class="card bento-card status-card p-5 h-100 border-0">
                        <h6 class="text-uppercase small mb-3 opacity-75">Güncel Sipariş Durumu</h6>
                        <?php if($sSayi > 0): ?>
                            <h3 class="fw-bold mb-2" style="color: #fff;">Çiçeklerin Hazırlanıyor... 🌸</h3>
                            <p class="small opacity-75 italic" style="color: #fff;">Taze çiçeklerin seçiliyor, yola çıkmak üzere!</p>
                            <a href="siparislerim.php" class="btn btn-white-pill mt-3">Siparişi Takip Et</a>
                        <?php else: ?>
                            <h3 class="fw-bold mb-2" style="color: #fff;">Henüz Siparişin Yok.</h3>
                            <p class="small opacity-75" style="color: #fff;">Hemen ilk buketini seçmeye ne dersin?</p>
                            <a href="urunler.php" class="btn btn-white-pill mt-3">Alışverişe Başla</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. Dinamik Hatırlatıcılar Listesi -->
                <div class="col-md-5">
                    <div class="card bento-card p-4 h-100 border-0">
                        <h6 class="fw-bold mb-4 d-flex align-items-center">
                            <span class="me-2">📅</span> Yaklaşan Hatırlatıcılar
                        </h6>
                        <div class="reminder-list">
                            <?php if(empty($hatirlaticilar)): ?>
                                <p class="text-muted small italic">Henüz bir hatırlatıcı eklemedin.</p>
                            <?php else: ?>
                                <?php foreach($hatirlaticilar as $h): ?>
                                    <div class="reminder-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold small"><?php echo htmlspecialchars($h['baslik']); ?></div>
                                            <?php if($h['kalan_gun'] == 0): ?>
                                                <span class="badge bg-success">Bugün!</span>
                                            <?php else: ?>
                                                <span class="badge bg-soft-danger text-danger">Kalan <?php echo $h['kalan_gun']; ?> gün</span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted" style="font-size: 10px;"><?php echo date('d.m.Y', strtotime($h['tarih'])); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="hatirlaticilar.php" class="btn btn-add-reminder w-100 mt-auto text-decoration-none">+ Yeni Ekle / Tümünü Gör</a>
                    </div>
                </div>

                <!-- 4. Uzman Tavsiyesi -->
                <div class="col-12">
                    <div class="card bento-card p-4 border-0 info-card">
                        <div class="d-flex align-items-center">
                            <div class="info-icon me-4">🌿</div>
                            <div>
                                <h6 class="fw-bold mb-1">Uzman Tavsiyesi: Orkide Bakımı</h6>
                                <p class="small text-muted mb-0">Orkidelerini haftada bir kez buz küpü ile sulamayı dene, kökleri daha sağlıklı kalacaktır.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>