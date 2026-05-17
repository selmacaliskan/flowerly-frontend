<?php
// =============================================
//  siparislerim.php
// =============================================

$sayfaBaslik = 'Siparişlerim';
$sayfa       = 'siparislerim';

include 'includes/header.php';
include 'includes/siparis-helpers.php';

// Giriş kontrolü
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$uID      = (int) $_SESSION['user_id'];
$siparisler = getSiparisler($db, $uID);
$stat       = getSiparisStat($siparisler);
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

            <!-- Sayfa başlığı -->
            <div class="sp-header d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="welcome-text mb-1">Siparişlerim</h2>
                    <p class="text-muted mb-0">Tüm siparişlerini buradan takip edebilirsin.</p>
                </div>
                <a href="urunler.php" class="btn sp-btn-primary">
                    🌷 Yeni Sipariş Ver
                </a>
            </div>

            <!-- Özet stat kartları -->
            <div class="row g-3 mb-4">
                <?php
                $statlar = [
                    ['ikon' => '📦', 'deger' => $stat['toplam'],  'etiket' => 'Toplam Sipariş'],
                    ['ikon' => '✅', 'deger' => $stat['teslim'],   'etiket' => 'Teslim Edildi'],
                    ['ikon' => '🚚', 'deger' => $stat['aktif'],    'etiket' => 'Aktif Sipariş'],
                    ['ikon' => '💸', 'deger' => '₺' . number_format($stat['harcama'], 0, ',', '.'), 'etiket' => 'Toplam Harcama'],
                ];
                foreach ($statlar as $st): ?>
                <div class="col-6 col-md-3">
                    <div class="card bento-card border-0 sp-stat-card text-center p-3">
                        <div class="sp-stat-icon"><?= $st['ikon'] ?></div>
                        <div class="sp-stat-num"><?= $st['deger'] ?></div>
                        <div class="sp-stat-lbl"><?= $st['etiket'] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Filtre çubuğu -->
            <div class="sp-filtreler d-flex gap-2 flex-wrap mb-4">
                <?php
                $filtreler = [
                    'hepsi'          => 'Tümü',
                    'Hazırlanıyor'   => '🌸 Hazırlanıyor',
                    'Kargoda'        => '🚚 Kargoda',
                    'Teslim Edildi'  => '✅ Teslim Edildi',
                    'İptal Edildi'   => '❌ İptal Edildi',
                ];
                foreach ($filtreler as $deger => $etiket): ?>
                <button
                    class="sp-filtre-btn <?= $deger === 'hepsi' ? 'active' : '' ?>"
                    data-filter="<?= htmlspecialchars($deger) ?>">
                    <?= $etiket ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Sipariş yok durumu -->
            <?php if (empty($siparisler)): ?>
            <div class="card bento-card border-0 sp-empty text-center p-5">
                <div class="sp-empty-icon">🌷</div>
                <h5 class="fw-bold mt-3 mb-2">Henüz hiç siparişin yok</h5>
                <p class="text-muted mb-3">Sevdiklerini mutlu etmek için ilk buketini seç!</p>
                <a href="urunler.php" class="btn sp-btn-primary">Alışverişe Başla</a>
            </div>

            <?php else: ?>
            <!-- Sipariş listesi -->
            <div id="sp-liste">
                <?php foreach ($siparisler as $siparis):
                    $bilgi   = durumBilgi($siparis['durum']);
                    $adim    = takipAdimi($siparis['durum']);
                    $iptal   = $siparis['durum'] === 'İptal Edildi';
                    $teslim  = $siparis['durum'] === 'Teslim Edildi';
                    $hazirlaniyor = $siparis['durum'] === 'Hazırlanıyor';
                    $urunler = getSiparisUrunler($db, (int) $siparis['id']);
                ?>
                <div class="card bento-card border-0 sp-siparis-kart mb-3"
                     data-durum="<?= htmlspecialchars($siparis['durum']) ?>">
                    <div class="card-body p-4">

                        <!-- Kart başlığı -->
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="sp-siparis-no"><?= siparisNo((int)$siparis['id']) ?></span>
                                <span class="sp-tarih"><?= formatTarih($siparis['tarih']) ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="sp-durum-badge <?= $bilgi['css'] ?>">
                                    <?= $bilgi['ikon'] ?> <?= htmlspecialchars($siparis['durum']) ?>
                                </span>
                                <span class="sp-fiyat">
                                    ₺<?= number_format((float)$siparis['toplam_fiyat'], 2, ',', '.') ?>
                                </span>
                            </div>
                        </div>

                        <!-- Ürünler -->
                        <?php if (!empty($urunler)):
                            $toplamUrunSayisi = count($urunler);
                            $gosterilenler    = array_slice($urunler, 0, 3);
                        ?>
                        <div class="sp-urun-listesi mb-3">
                            <?php foreach ($gosterilenler as $u): ?>
                            <div class="sp-urun-satir">
                                <span class="sp-urun-ikon">🌸</span>
                                <span class="sp-urun-ad"><?= htmlspecialchars($u['urun_ad']) ?></span>
                                <span class="sp-urun-adet">× <?= (int)$u['adet'] ?></span>
                                <span class="sp-urun-fiyat">
                                    ₺<?= number_format((float)$u['fiyat'], 2, ',', '.') ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                            <?php if ($toplamUrunSayisi > 3): ?>
                            <div class="sp-urun-daha">
                                +<?= $toplamUrunSayisi - 3 ?> ürün daha
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Takip çubuğu -->
                        <?php if (!$iptal): ?>
                        <?php include 'includes/siparis-takip.php'; ?>
                        <?php endif; ?>

                        <!-- Aksiyon butonları -->
                        <div class="d-flex gap-2 flex-wrap mt-3">
                            <a href="siparis-detay.php?id=<?= $siparis['id'] ?>"
                               class="btn sp-btn-outline">
                                🔍 Detayı Gör
                            </a>
                            <?php if ($teslim): ?>
                            <a href="urun-puan.php?siparis_id=<?= $siparis['id'] ?>"
                               class="btn sp-btn-outline">
                                ⭐ Değerlendir
                            </a>
                            <a href="tekrar-siparis.php?siparis_id=<?= $siparis['id'] ?>"
                               class="btn sp-btn-primary sp-btn-sm">
                                🔁 Tekrar Sipariş
                            </a>
                            <?php endif; ?>
                            <?php if ($hazirlaniyor): ?>
                            <a href="siparis-iptal.php?id=<?= $siparis['id'] ?>"
                               class="btn sp-btn-iptal"
                               onclick="return confirm('Bu siparişi iptal etmek istediğine emin misin?')">
                                İptal Et
                            </a>
                            <?php endif; ?>
                        </div>

                    </div><!-- /card-body -->
                </div><!-- /sp-siparis-kart -->
                <?php endforeach; ?>
            </div><!-- /#sp-liste -->
            <?php endif; ?>

        </div><!-- /col sağ -->
    </div><!-- /row -->
</div><!-- /container -->

<script>
(function () {
    const filtreler = document.querySelectorAll('.sp-filtre-btn');
    const kartlar   = document.querySelectorAll('.sp-siparis-kart');

    filtreler.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filtreler.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            var filtre = btn.dataset.filter;
            var gorunen = 0;

            kartlar.forEach(function (kart) {
                var eslesme = filtre === 'hepsi' || kart.dataset.durum === filtre;
                kart.style.display = eslesme ? '' : 'none';
                if (eslesme) gorunen++;
            });

            // Sonuç yok mesajı
            var bos = document.getElementById('sp-bos-filtre');
            if (!bos) {
                bos = document.createElement('div');
                bos.id = 'sp-bos-filtre';
                bos.className = 'text-center py-5 text-muted';
                bos.innerHTML = '<div style="font-size:2rem">🌿</div><p class="mt-2">Bu durumda sipariş bulunamadı.</p>';
                document.getElementById('sp-liste').after(bos);
            }
            bos.style.display = gorunen === 0 ? '' : 'none';
        });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>