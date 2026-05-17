<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   1. SEPETİ VERİTABANINDAN ÇEK
   ========================================================= */
$sepetUrunler = [];

if (!empty($_SESSION['sepet'])) {
    $ids = array_keys($_SESSION['sepet']);
    $in  = str_repeat('?,', count($ids) - 1) . '?';
    $sorgu = $db->prepare("SELECT * FROM urunler WHERE id IN ($in) AND aktif = 1");
    $sorgu->execute($ids);
    $sepetUrunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================================================
   2. KUPON UYGULAMA (POST ile gönderildiğinde)
   ========================================================= */
$kuponMesaj = '';

if (isset($_POST['kupon_kontrol']) && !empty($_POST['kupon'])) {
    $kuponKodu  = trim($_POST['kupon']);
    $kuponSorgu = $db->prepare(
        "SELECT * FROM kuponlar WHERE kod = ? AND aktif = 1 AND son_tarih >= CURDATE()"
    );
    $kuponSorgu->execute([$kuponKodu]);
    $kupon = $kuponSorgu->fetch(PDO::FETCH_ASSOC);

    if ($kupon) {
        $_SESSION['kupon'] = $kuponKodu;
        $kuponMesaj = "<div class='alert alert-success mt-2'>
            ✅ Kupon uygulandı! %{$kupon['indirim_tutari']} indirim kazandınız.
        </div>";
    } else {
        unset($_SESSION['kupon']);
        $kuponMesaj = "<div class='alert alert-danger mt-2'>
            ❌ Geçersiz veya süresi dolmuş kupon kodu.
        </div>";
    }
}

/* Kuponu kaldır butonu */
if (isset($_POST['kuponu_kaldir'])) {
    unset($_SESSION['kupon']);
    $kuponMesaj = "<div class='alert alert-secondary mt-2'>Kupon kaldırıldı.</div>";
}

/* =========================================================
   3. TOPLAM HESAPLAMA
   ========================================================= */
$araToplam = 0;
foreach ($sepetUrunler as $u) {
    $adet       = $_SESSION['sepet'][$u['id']] ?? 1;
    $araToplam += $u['fiyat'] * $adet;
}

$indirim     = 0;
$aktifKupon  = null;

if (!empty($_SESSION['kupon'])) {
    $q = $db->prepare(
        "SELECT * FROM kuponlar WHERE kod = ? AND aktif = 1 AND son_tarih >= CURDATE()"
    );
    $q->execute([$_SESSION['kupon']]);
    $aktifKupon = $q->fetch(PDO::FETCH_ASSOC);

    if ($aktifKupon) {
        $indirim = ($araToplam * $aktifKupon['indirim_tutari']) / 100;
    } else {
        /* Kupon artık geçersizse session'dan temizle */
        unset($_SESSION['kupon']);
    }
}

$genelToplam = $araToplam - $indirim;

/* =========================================================
   4. SAYFA BAŞLIĞI & HEADER
   ========================================================= */
$sayfaBaslik = 'Sepetim';
include 'includes/header.php';
?>

<div class="container mt-5 mb-5 cart-container">
    <div class="row g-4 align-items-start">

        <!-- =================== SOL: ÜRÜN LİSTESİ =================== -->
        <div class="col-lg-8">
            <div class="mb-4">
                <h2 class="fw-bold">Sepetim 🧺</h2>
                <p class="text-muted small">Alışverişinizi tamamlamak üzeresiniz.</p>
            </div>

            <?php if (empty($sepetUrunler)): ?>
            <!-- Boş Sepet -->
            <div class="text-center p-5 bg-white rounded-5 shadow-sm border">
                <div class="display-1 mb-4 opacity-25">🛒</div>
                <h4 class="fw-bold">Sepetiniz şu an boş.</h4>
                <p class="text-muted">Hemen taze çiçeklerimize göz atarak sepetinizi doldurabilirsiniz.</p>
                <a href="urunler.php" class="btn btn-primary rounded-pill px-5 py-3 fw-bold mt-3 shadow">
                    Hemen Alışverişe Başla
                </a>
            </div>

            <?php else: ?>
            <!-- Dolu Sepet -->
            <div class="cart-items">
                <?php foreach ($sepetUrunler as $u):
                    $adet      = $_SESSION['sepet'][$u['id']] ?? 1;
                    $araToplam_urun = $u['fiyat'] * $adet;
                ?>
                <div class="cart-item-card p-4 mb-3 shadow-sm border-0">
                    <div class="row align-items-center g-3">
                        <!-- Görsel -->
                        <div class="col-4 col-md-2">
                            <div class="bg-light rounded-4 p-2">
                                <img src="assets/img/<?php echo htmlspecialchars($u['resim']); ?>"
                                     class="img-fluid rounded-3"
                                     alt="<?php echo htmlspecialchars($u['ad']); ?>"
                                     onerror="this.src='https://via.placeholder.com/200'">
                            </div>
                        </div>
                        <!-- Ad & Fiyat -->
                        <div class="col-8 col-md-4">
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($u['ad']); ?></h6>
                            <div class="text-rose fw-bold">
                                ₺<?php echo number_format($u['fiyat'], 2, ',', '.'); ?>
                            </div>
                        </div>
                        <!-- Adet Kontrol -->
                        <div class="col-6 col-md-3">
                            <div class="quantity-pill border">
                                <button class="btn btn-link p-0 text-dark sepet-islem"
                                        data-id="<?php echo $u['id']; ?>" data-islem="azalt">
                                    <i class="fa fa-minus small"></i>
                                </button>
                                <span class="fw-bold"><?php echo $adet; ?></span>
                                <button class="btn btn-link p-0 text-dark sepet-islem"
                                        data-id="<?php echo $u['id']; ?>" data-islem="artir">
                                    <i class="fa fa-plus small"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Ara Toplam & Sil -->
                        <div class="col-6 col-md-3 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-3">
                                <span class="fw-bold fs-5">
                                    ₺<?php echo number_format($araToplam_urun, 2, ',', '.'); ?>
                                </span>
                                <button class="btn btn-light rounded-pill text-danger sepet-islem"
                                        data-id="<?php echo $u['id']; ?>" data-islem="sil">
                                    <i class="fa fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- =================== SAĞ: SİPARİŞ ÖZETİ =================== -->
        <?php if (!empty($sepetUrunler)): ?>
        <div class="col-lg-4">
            <div class="card summary-card border-0 shadow-lg p-4 bg-dark text-white">
                <h5 class="fw-bold mb-4">Sipariş Özeti</h5>

                <!-- Ara Toplam -->
                <div class="d-flex justify-content-between mb-3 opacity-75">
                    <span>Ara Toplam</span>
                    <span>₺<?php echo number_format($araToplam, 2, ',', '.'); ?></span>
                </div>

                <!-- İndirim (varsa) -->
                <?php if ($indirim > 0 && $aktifKupon): ?>
                <div class="d-flex justify-content-between mb-3 text-success">
                    <span>İndirim (%<?php echo $aktifKupon['indirim_tutari']; ?>)</span>
                    <span>- ₺<?php echo number_format($indirim, 2, ',', '.'); ?></span>
                </div>
                <?php endif; ?>

                <hr class="opacity-25">

                <!-- Genel Toplam -->
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold">Genel Toplam</span>
                    <span class="fw-bold fs-4" style="color:#ff4757;">
                        ₺<?php echo number_format($genelToplam, 2, ',', '.'); ?>
                    </span>
                </div>

                <!-- Kupon Formu -->
                <form method="POST">
                    <label class="form-label small opacity-75">İndirim Kuponu</label>

                    <?php if ($aktifKupon): ?>
                    <!-- Uygulanan kupon göster -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success px-3 py-2 fs-6">
                            🎟️ <?php echo htmlspecialchars($aktifKupon['kod']); ?>
                        </span>
                        <button type="submit" name="kuponu_kaldir"
                                class="btn btn-sm btn-outline-danger rounded-pill">
                            Kaldır
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="d-flex gap-2">
                        <input type="text" name="kupon"
                               class="form-control rounded-pill"
                               placeholder="Kupon kodu giriniz"
                               value="<?php echo htmlspecialchars($_POST['kupon'] ?? ''); ?>">
                        <button type="submit" name="kupon_kontrol"
                                class="btn btn-warning rounded-pill px-4">
                            Uygula
                        </button>
                    </div>
                    <?php endif; ?>

                    <?php echo $kuponMesaj; ?>
                </form>

                <hr class="opacity-25 my-4">

                <!-- Siparişi Tamamla -->
                <form action="siparis_tamamla.php" method="POST">
                    <input type="hidden" name="kupon"
                           value="<?php echo htmlspecialchars($_SESSION['kupon'] ?? ''); ?>">
                    <button type="submit"
                            class="btn btn-light btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm mb-3">
                        Siparişi Tamamla ✨
                    </button>
                </form>

                <p class="text-center small opacity-50 mb-0">
                    <i class="fa fa-lock me-1"></i> Güvenli 256-bit SSL Ödeme
                </p>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /row -->

    <!-- ======= ÖNERİLER ======= -->
    <div class="row mt-5 pt-5 border-top border-light">
        <div class="col-12">
            <h4 class="fw-bold mb-4">Senin İçin Seçtiklerimiz 🌸</h4>
            <div class="row g-4">
                <?php
                $oneriler = $db->query(
                    "SELECT * FROM urunler WHERE aktif = 1 ORDER BY RAND() LIMIT 4"
                )->fetchAll(PDO::FETCH_ASSOC);
                foreach ($oneriler as $o): ?>
                <div class="col-lg-3 col-6">
                    <div class="product-grid-card p-3 shadow-none border-light bg-white rounded-4">
                        <div class="rec-image-dome mb-3"
                             onclick="window.location.href='urun_detay.php?id=<?php echo $o['id']; ?>'"
                             style="cursor:pointer;">
                            <img src="assets/img/<?php echo htmlspecialchars($o['resim']); ?>"
                                 class="img-fluid" alt=""
                                 onerror="this.src='https://via.placeholder.com/200'">
                        </div>
                        <div class="text-center">
                            <h6 class="fw-bold small mb-1"><?php echo htmlspecialchars($o['ad']); ?></h6>
                            <div class="text-rose fw-bold small">
                                ₺<?php echo number_format($o['fiyat'], 2, ',', '.'); ?>
                            </div>
                            <button class="btn btn-outline-dark btn-sm rounded-pill w-100 mt-2 sepete-ekle-btn"
                                    data-id="<?php echo $o['id']; ?>">
                                Ekle
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div><!-- /container -->

<style>
    .text-rose { color: #e91e63; }
</style>

<?php include 'includes/footer.php'; ?>