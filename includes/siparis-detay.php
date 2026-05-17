<?php
// =============================================
//  siparis-detay.php
// =============================================

$sayfaBaslik = 'Sipariş Detayı';
$sayfa       = 'siparislerim';

include 'includes/header.php';
include 'includes/siparis-helpers.php';

// Giriş kontrolü
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$uID      = (int) $_SESSION['user_id'];
$siparisId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($siparisId <= 0) {
    header('Location: siparislerim.php');
    exit();
}

// Siparişi çek — sadece bu kullanıcıya ait olmalı (güvenlik)
$stmt = $db->prepare("
    SELECT id, toplam_fiyat, tarih, durum
    FROM siparisler
    WHERE id = :sid AND user_id = :uid
    LIMIT 1
");
$stmt->execute([':sid' => $siparisId, ':uid' => $uID]);
$siparis = $stmt->fetch(PDO::FETCH_ASSOC);

// Sipariş bulunamadıysa veya başka kullanıcıya aitse geri yönlendir
if (!$siparis) {
    header('Location: siparislerim.php');
    exit();
}

// Tüm ürün satırlarını çek (limit yok — detay sayfası)
$urunStmt = $db->prepare("
    SELECT urun_ad, adet, fiyat, (adet * fiyat) AS satir_toplam
    FROM siparis_icerik
    WHERE siparis_id = :sid
    ORDER BY id ASC
");
$urunStmt->execute([':sid' => $siparisId]);
$urunler = $urunStmt->fetchAll(PDO::FETCH_ASSOC);

// Yardımcı değerler
$bilgi = durumBilgi($siparis['durum']);
$adim  = takipAdimi($siparis['durum']);
$iptal = $siparis['durum'] === 'İptal Edildi';
$teslim = $siparis['durum'] === 'Teslim Edildi';
$hazirlaniyor = $siparis['durum'] === 'Hazırlanıyor';
?>

<link rel="stylesheet" href="assets/css/user-panel.css">
<link rel="stylesheet" href="assets/css/siparislerim.css">

<div class="container mt-5 mb-5">
    <div class="row g-4">

        <!-- SOL MENÜ -->
        <div class="col-lg-3 col-md-4">
            <?php include 'includes/user-sidebar.php'; ?>
        </div>

        <!-- SAĞ İÇERİK -->
        <div class="col-lg-9 col-md-8">

            <!-- Geri butonu + başlık -->
            <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                <a href="siparislerim.php" class="sp-btn-outline">
                    ← Siparişlerime Dön
                </a>
                <div>
                    <h2 class="welcome-text mb-0">
                        Sipariş <?= siparisNo((int)$siparis['id']) ?>
                    </h2>
                    <p class="text-muted mb-0" style="font-size:.85rem;">
                        <?= formatTarih($siparis['tarih']) ?>
                    </p>
                </div>
                <span class="sp-durum-badge <?= $bilgi['css'] ?> ms-auto">
                    <?= $bilgi['ikon'] ?> <?= htmlspecialchars($siparis['durum']) ?>
                </span>
            </div>

            <!-- Takip çubuğu -->
            <?php if (!$iptal): ?>
            <div class="card bento-card border-0 p-4 mb-4">
                <h6 class="fw-bold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.07em;color:#999;">
                    Sipariş Takibi
                </h6>
                <?php include 'includes/siparis-takip.php'; ?>
            </div>
            <?php endif; ?>

            <!-- Ürün tablosu -->
            <div class="card bento-card border-0 p-4 mb-4">
                <h6 class="fw-bold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.07em;color:#999;">
                    Sipariş İçeriği
                </h6>

                <div class="sp-detay-tablo">
                    <!-- Başlık satırı -->
                    <div class="sp-detay-baslik">
                        <span class="sp-col-urun">Ürün</span>
                        <span class="sp-col-adet">Adet</span>
                        <span class="sp-col-fiyat">Birim Fiyat</span>
                        <span class="sp-col-toplam">Toplam</span>
                    </div>

                    <!-- Ürün satırları -->
                    <?php foreach ($urunler as $u): ?>
                    <div class="sp-detay-satir">
                        <span class="sp-col-urun">
                            <span style="margin-right:6px;">🌸</span>
                            <?= htmlspecialchars($u['urun_ad']) ?>
                        </span>
                        <span class="sp-col-adet"><?= (int)$u['adet'] ?></span>
                        <span class="sp-col-fiyat">
                            ₺<?= number_format((float)$u['fiyat'], 2, ',', '.') ?>
                        </span>
                        <span class="sp-col-toplam">
                            ₺<?= number_format((float)$u['satir_toplam'], 2, ',', '.') ?>
                        </span>
                    </div>
                    <?php endforeach; ?>

                    <!-- Genel toplam -->
                    <div class="sp-detay-genel-toplam">
                        <span>Genel Toplam</span>
                        <span>₺<?= number_format((float)$siparis['toplam_fiyat'], 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <!-- Aksiyon butonları -->
            <div class="d-flex gap-2 flex-wrap">
                <?php if ($teslim): ?>
                <a href="urun-puan.php?siparis_id=<?= $siparis['id'] ?>"
                   class="btn sp-btn-outline">
                    ⭐ Siparişi Değerlendir
                </a>
                <a href="tekrar-siparis.php?siparis_id=<?= $siparis['id'] ?>"
                   class="btn sp-btn-primary">
                    🔁 Tekrar Sipariş Ver
                </a>
                <?php endif; ?>

                <?php if ($hazirlaniyor): ?>
                <a href="siparis-iptal.php?id=<?= $siparis['id'] ?>"
                   class="btn sp-btn-iptal"
                   onclick="return confirm('Bu siparişi iptal etmek istediğine emin misin?')">
                    ❌ Siparişi İptal Et
                </a>
                <?php endif; ?>

                <a href="siparislerim.php" class="btn sp-btn-outline">
                    ← Siparişlerime Dön
                </a>
            </div>

        </div><!-- /col -->
    </div><!-- /row -->
</div><!-- /container -->

<?php include 'includes/footer.php'; ?>