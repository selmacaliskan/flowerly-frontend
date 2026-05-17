<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$siparisId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* Siparişin bu kullanıcıya ait olduğunu doğrula */
$sorgu = $db->prepare(
    "SELECT * FROM siparisler WHERE id = ? AND user_id = ?"
);
$sorgu->execute([$siparisId, $_SESSION['user_id']]);
$siparis = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$siparis) {
    header('Location: siparislerim.php');
    exit();
}

/* Sipariş içeriğini çek */
$icerikSorgu = $db->prepare("SELECT * FROM siparis_icerik WHERE siparis_id = ?");
$icerikSorgu->execute([$siparisId]);
$urunler = $icerikSorgu->fetchAll(PDO::FETCH_ASSOC);

$sayfaBaslik = 'Siparişiniz Alındı';
include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 text-center">

            <!-- Başarı Animasyonu -->
            <div class="mb-4" style="font-size: 5rem;">🌸</div>
            <h1 class="fw-bold mb-2">Siparişiniz Alındı!</h1>
            <p class="text-muted mb-4">
                Teşekkür ederiz. Siparişiniz en kısa sürede hazırlanmaya başlanacak.
            </p>

            <!-- Sipariş Özeti Kartı -->
            <div class="card border-0 shadow-sm rounded-4 p-4 text-start mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Sipariş Özeti</h5>
                    <span class="badge bg-success">✅ Onaylandı</span>
                </div>

                <div class="text-muted small mb-3">
                    Sipariş No: <strong>#<?php echo str_pad($siparisId, 5, '0', STR_PAD_LEFT); ?></strong>
                </div>

                <!-- Ürünler -->
                <?php foreach ($urunler as $u): ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>🌷 <?php echo htmlspecialchars($u['urun_ad']); ?> × <?php echo $u['adet']; ?></span>
                    <span class="fw-bold">₺<?php echo number_format($u['fiyat'] * $u['adet'], 2, ',', '.'); ?></span>
                </div>
                <?php endforeach; ?>

                <!-- Toplam -->
                <?php if ($siparis['indirim_miktari'] > 0): ?>
                <div class="d-flex justify-content-between pt-2 text-success">
                    <span>İndirim (Kupon)</span>
                    <span>- ₺<?php echo number_format($siparis['indirim_miktari'], 2, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between pt-2 fw-bold fs-5">
                    <span>Genel Toplam</span>
                    <span style="color:#e91e63;">
                        ₺<?php echo number_format($siparis['toplam_fiyat'], 2, ',', '.'); ?>
                    </span>
                </div>
            </div>

            <!-- Butonlar -->
            <div class="d-flex gap-3 justify-content-center">
                <a href="siparislerim.php" class="btn btn-dark rounded-pill px-5 py-3 fw-bold">
                    📦 Siparişlerimi Gör
                </a>
                <a href="urunler.php" class="btn btn-outline-dark rounded-pill px-5 py-3 fw-bold">
                    🌷 Alışverişe Devam
                </a>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>