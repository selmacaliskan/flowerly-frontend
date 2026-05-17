<?php 
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$katID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$katSorgu = $db->prepare("SELECT ad FROM kategoriler WHERE id = ?");
$katSorgu->execute([$katID]);
$kategori = $katSorgu->fetch(PDO::FETCH_ASSOC);

if(!$kategori) { header("Location: index.php"); exit(); }

$urunler = $db->prepare("SELECT * FROM urunler WHERE kategori_id = ? AND aktif = 1 ORDER BY id DESC");
$urunler->execute([$katID]);
$sonuclar = $urunler->fetchAll(PDO::FETCH_ASSOC);

$sayfaBaslik = $kategori['ad'];
include 'includes/header.php'; 
?>

<div class="container mt-5">
    <h3 class="fw-bold mb-5"><?php echo $kategori['ad']; ?> Koleksiyonu 🌸</h3>
    
    <div class="row g-4">
        <?php if(count($sonuclar) > 0): ?>
            <?php foreach($sonuclar as $urun): 
                $isFav = false;
                if(isset($_SESSION['user_id'])) {
                    $fSorgu = $db->prepare("SELECT id FROM favoriler WHERE user_id = ? AND urun_id = ?");
                    $fSorgu->execute([$_SESSION['user_id'], $urun['id']]);
                    if($fSorgu->rowCount() > 0) $isFav = true;
                }
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="product-grid-card">
                    <div class="fav-badge">
                        <button type="button" class="fav-btn" data-id="<?php echo $urun['id']; ?>">
                            <span class="heart-icon" style="filter: <?php echo $isFav ? 'none' : 'grayscale(100%) opacity(0.3)'; ?>">
                                <?php echo $isFav ? '❤️' : '🤍'; ?>
                            </span>
                        </button>
                    </div>
                    <div class="product-image-dome" onclick="window.location.href='urun_detay.php?id=<?php echo $urun['id']; ?>'">
                        <img src="assets/img/<?php echo $urun['resim']; ?>" alt="">
                    </div>
                    <div class="product-info">
                        <h6><?php echo htmlspecialchars($urun['ad']); ?></h6>
                        <div class="price">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></div>
                    </div>
                    <div class="product-actions">
                        <button type="button" class="btn-add-cart sepete-ekle-btn" data-id="<?php echo $urun['id']; ?>">
                            Sepete Ekle
                        </button>
                        <a href="urun_detay.php?id=<?php echo $urun['id']; ?>" class="btn-view-details">
                            <i class="fa fa-search"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">Bu kategoride henüz ürün bulunmuyor.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>