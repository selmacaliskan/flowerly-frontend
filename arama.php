<?php 
$sayfaBaslik = "Arama Sonuçları";
include 'includes/header.php'; 

// 1. Arama kelimesini al ve güvenli hale getir
$kelime = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($kelime != "") {
    // Madde 3: Prepared Statements (LIKE operatörü ile)
    // Ürün adı, açıklaması veya kategori adında arama yapar
    $sorgu = $db->prepare("SELECT u.*, k.ad as kat_ad 
                           FROM urunler u 
                           JOIN kategoriler k ON u.kategori_id = k.id 
                           WHERE u.ad LIKE ? OR u.aciklama LIKE ? OR k.ad LIKE ?");
    
    $aramaParam = "%$kelime%"; // Kelimenin başında veya sonunda herhangi bir karakter olabilir
    $sorgu->execute([$aramaParam, $aramaParam, $aramaParam]);
    $sonuclar = $sorgu->fetchAll();
} else {
    $sonuclar = [];
}
?>

<div class="container mt-4" style="min-height: 500px;">
    <h3 class="mb-4 fw-bold">
        "<?php echo htmlspecialchars($kelime); ?>" için arama sonuçları 
        <small class="text-muted">(<?php echo count($sonuclar); ?> sonuç bulundu)</small>
    </h3>

    <div class="row g-4">
        <?php if (count($sonuclar) > 0): ?>
            <?php foreach ($sonuclar as $urun): ?>
                <div class="col-md-3 col-sm-6">
                    <!-- Ürün detayına link (Daha önce yaptığımız yapı) -->
                    <div class="product-card border-0 shadow-sm p-3 bg-white rounded-4" 
                         onclick="window.location.href='urun_detay.php?id=<?php echo $urun['id']; ?>'" 
                         style="cursor:pointer;">
                        <img src="assets/img/<?php echo $urun['resim']; ?>" class="img-fluid rounded-3 mb-3">
                        <h6 class="fw-bold mb-1"><?php echo $urun['ad']; ?></h6>
                        <p class="text-muted small mb-2"><?php echo $urun['kat_ad']; ?></p>
                        <p class="text-primary fw-bold fs-5 mb-3"><?php echo $urun['fiyat']; ?> TL</p>
                        <button class="buy-btn" 
                                data-ad="<?php echo $urun['ad']; ?>" 
                                data-fiyat="<?php echo $urun['fiyat']; ?>" 
                                data-img="<?php echo $urun['resim']; ?>">
                            Sepete Ekle
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="fs-1">🔍❌</div>
                <h4 class="mt-3">Aradığınız kriterlere uygun ürün bulunamadı.</h4>
                <p class="text-muted">Lütfen farklı kelimelerle tekrar deneyin.</p>
                <a href="urunler.php" class="btn btn-primary mt-3">Tüm Ürünlere Göz At</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>