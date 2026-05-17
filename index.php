<?php 
require_once 'includes/db.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$sayfaBaslik = "Anasayfa";
include 'includes/header.php'; 
?>

<!-- Üst Bölüm: Promosyon ve Slider -->
<aside class="container mt-4">
    <div class="row g-4">
        <!-- SOL: Promosyon -->
        <div class="col-md-6">
            <div class="card flowerly-promo border-0 rounded-5 h-100 p-4">
                <div class="card-body">
                    <h1 class="fw-bold text-success">Hoş Geldiniz!</h1>
                    <p class="text-muted">Taze çiçeklerle sevdiklerinize gülümseme hediye edin.</p>
                    <a href="urunler.php">
                        <img src="https://i.pinimg.com/originals/f2/f3/1b/f2f31baa64307c47661157068d278fd3.gif" alt="" class="img-fluid rounded-4" style="max-height: 280px;">
                    </a>
                </div>
            </div>
        </div>

        <!-- SAĞ: Slider (Sadece Görsel) -->
        <div class="col-md-6">
            <div id="flowerlySlider" class="carousel slide shadow-sm h-100 rounded-5 overflow-hidden" data-bs-ride="carousel">
                <div class="carousel-inner h-100">
                    <?php
                    $sliderSorgu = $db->query("SELECT * FROM slider ORDER BY sira ASC");
                    $aktifMi = true;
                    while($slide = $sliderSorgu->fetch(PDO::FETCH_ASSOC)):
                    ?>
                        <div class="carousel-item <?php echo $aktifMi ? 'active' : ''; ?> h-100">
                            <div class="slider-wrapper h-100 position-relative">
                                 <!-- Arka Plan Bulanık -->
                                 <img src="assets/img/<?php echo $slide['resim']; ?>" class="w-100 h-100 position-absolute" style="object-fit: cover; filter: blur(15px); opacity: 0.4;">
                                 <!-- Ana Resim -->
                                 <img src="assets/img/<?php echo $slide['resim']; ?>" class="d-block w-100 h-100 position-relative" style="object-fit: contain; z-index: 1;">
                            </div>
                        </div>
                    <?php $aktifMi = false; endwhile; ?>
                </div>
                
                <!-- SLIDER TUŞLARI (Z-index ile tıklanabilir yapıldı) -->
                <button class="carousel-control-prev" type="button" data-bs-target="#flowerlySlider" data-bs-slide="prev" style="z-index: 10;">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#flowerlySlider" data-bs-slide="next" style="z-index: 10;">
                    <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</aside>

<!-- ORİJİNAL VİTRİN (Yatay Kaydırmalı) -->
<div class="container mt-5">
    <h4 class="fw-bold mb-0">Vitrin 🌸</h4>
    <div class="fanus-row">
        <?php
        $urunSorgu = $db->query("SELECT * FROM urunler WHERE aktif = 1 LIMIT 6");
        while($urun = $urunSorgu->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="fanus" onclick="window.location.href='urun_detay.php?id=<?php echo $urun['id']; ?>'">
                <div class="dome">
                     <img src="assets/img/<?php echo $urun['resim']; ?>">
                </div>
                <div class="p">
                    <p class="flower-names"><?php echo htmlspecialchars($urun['ad']); ?></p>
                    <p class="fiyat"><?php echo number_format($urun['fiyat'], 2); ?> TL</p>
                    <p class="aciklama text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($urun['aciklama']); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- ALT BÖLÜM: TÜM ÜRÜNLER (Izgara Yapısı) -->
<div class="container mt-5">
    <hr class="mb-5 opacity-5">
    <h4 class="fw-bold mb-4">Ürünler ✨</h4>
    <div class="row g-4">
        <?php
        // Geri kalan ürünleri listeleyelim
        $digerUrunler = $db->query("SELECT * FROM urunler WHERE aktif = 1 ORDER BY id DESC LIMIT 12");
        while($urun = $digerUrunler->fetch(PDO::FETCH_ASSOC)):
            // Favori Kontrolü
            $isFav = false;
            if(isset($_SESSION['user_id'])) {
                $fS = $db->prepare("SELECT id FROM favoriler WHERE user_id=? AND urun_id=?");
                $fS->execute([$_SESSION['user_id'], $urun['id']]);
                $isFav = $fS->rowCount() > 0;
            }
        ?>
        <div class="col-lg-3 col-md-4 col-6">
            <!-- Dome Tasarımlı Ürün Kartı -->
            <div class="product-grid-card shadow-sm border-0 p-3">
                <div class="fav-badge">
                    <button type="button" class="fav-btn" data-id="<?php echo $urun['id']; ?>">
                        <span class="heart-icon"><?php echo $isFav ? '❤️' : '🤍'; ?></span>
                    </button>
                </div>
                <div class="product-image-dome" style="height: 160px;" onclick="window.location.href='urun_detay.php?id=<?php echo $urun['id']; ?>'">
                    <img src="assets/img/<?php echo $urun['resim']; ?>" alt="">
                </div>
                <div class="product-info text-center">
                    <h6 class="small fw-bold text-truncate"><?php echo $urun['ad']; ?></h6>
                    <div class="price small">₺<?php echo number_format($urun['fiyat'], 2); ?></div>
                </div>
                <button type="button" class="btn btn-dark btn-sm w-100 rounded-pill mt-2 sepete-ekle-btn" data-id="<?php echo $urun['id']; ?>">
                    Sepete Ekle
                </button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="urunler.php" class="btn btn-outline-dark rounded-pill px-5">Tüm Ürünleri Gör</a>
    </div>
</div>

<style>
/* Slider Tuşları Fix */
.carousel-control-prev, .carousel-control-next {
    width: 10%;
    opacity: 1;
}
.carousel-control-prev-icon, .carousel-control-next-icon {
    width: 40px;
    height: 40px;
    background-size: 50%;
}
/* Orijinal Vitrin Stilleri */
.fanus-row { display: flex; gap: 20px; overflow-x: auto; padding: 20px 0; scroll-behavior: smooth; scrollbar-width: none; }
.fanus-row::-webkit-scrollbar { display: none; }
.fanus { flex: 0 0 280px; background: white; padding: 20px; border-radius: 25px; text-align: center; transition: 0.3s; cursor: pointer; border: 1px solid #f8f9fa; }
.fanus:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.dome { width: 180px; height: 180px; margin: 0 auto 15px; background: linear-gradient(to bottom, #ffffff, #f0f0f0); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.dome img { max-width: 75%; max-height: 75%; object-fit: contain; }
</style>

<?php include 'includes/footer.php'; ?>