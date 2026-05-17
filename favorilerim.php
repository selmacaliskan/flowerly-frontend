<?php 
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$sayfa = "favorilerim"; // Sidebar aktiflik için
$sayfaBaslik = "Favorilerim";

// Favorileri Çek
$favoriler = $db->prepare("SELECT u.* FROM favoriler f JOIN urunler u ON f.urun_id = u.id WHERE f.user_id = ?");
$favoriler->execute([$user_id]);
$sonuclar = $favoriler->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php'; 
?>
<link rel="stylesheet" href="assets/css/user-panel.css">

<div class="container mt-5 mb-5">
    <div class="row g-4">
        <!-- SOL MENÜ (MODÜLER) -->
        <div class="col-lg-3">
            <?php include 'includes/user-sidebar.php'; ?>
        </div>

        <!-- SAĞ İÇERİK -->
        <div class="col-lg-9">
            <div class="p-4 bg-white bento-card shadow-sm mb-4">
                <h2 class="fw-bold mb-1">Favorilerim ❤️</h2>
                <p class="text-muted small mb-0">Beğendiğiniz tüm ürünleri burada saklıyoruz.</p>
            </div>

            <?php if(empty($sonuclar)): ?>
                <div class="text-center p-5 bg-white bento-card shadow-sm">
                    <div class="user-avatar-circle mx-auto mb-4">🌸</div>
                    <h4 class="text-muted">Favori listeniz şu an boş.</h4>
                    <a href="urunler.php" class="btn btn-rose-pill px-5 py-2 mt-3 shadow" style="background:#e91e63; color:white; border-radius:50px;">Alışverişe Başla</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($sonuclar as $urun): ?>
                        <div class="col-md-4">
                            <!-- Daha önce yaptığımız Dome Tasarımı -->
                            <div class="product-grid-card shadow-sm">
                                <div class="fav-badge">
                                    <button type="button" class="fav-btn" data-id="<?php echo $urun['id']; ?>">
                                        <span class="heart-icon">❤️</span>
                                    </button>
                                </div>
                                <div class="product-image-dome" onclick="window.location.href='urun_detay.php?id=<?php echo $urun['id']; ?>'">
                                    <img src="assets/img/<?php echo $urun['resim']; ?>" alt="">
                                </div>
                                <div class="product-info">
                                    <h6 class="small fw-bold"><?php echo htmlspecialchars($urun['ad']); ?></h6>
                                    <div class="price small">₺<?php echo number_format($urun['fiyat'], 2); ?></div>
                                </div>
                                <div class="product-actions">
                                    <button class="btn-add-cart sepete-ekle-btn w-100" data-id="<?php echo $urun['id']; ?>">Ekle</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>