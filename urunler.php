<?php 
require_once 'includes/db.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* ------------------ INPUT ------------------ */

$sirala  = $_GET['sirala'] ?? 'yeni';

$gecerliSutunlar = [
    'fiyat_artan'  => 'fiyat ASC',
    'fiyat_azalan' => 'fiyat DESC',
    'yeni'         => 'id DESC'
];
$orderSql = $gecerliSutunlar[$sirala] ?? 'id DESC';

$search  = $_GET['q'] ?? '';
$fiyat   = $_GET['fiyat'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$stok    = $_GET['stok'] ?? '';

/* ------------------ FILTER ------------------ */

$where = "WHERE aktif = 1";
$params = [];

/* ARAMA */
if (!empty($search)) {
    $where .= " AND ad LIKE ?";
    $params[] = "%$search%";
}

/* FİYAT */
if ($fiyat == "0-200") {
    $where .= " AND fiyat BETWEEN 0 AND 200";
} elseif ($fiyat == "200-500") {
    $where .= " AND fiyat BETWEEN 200 AND 500";
} elseif ($fiyat == "500+") {
    $where .= " AND fiyat >= 500";
}

/* KATEGORİ (YENİ EKLENEN) */
if (!empty($kategori)) {
    $where .= " AND kategori_id = ?";
    $params[] = $kategori;
}

/* STOK (YENİ EKLENEN) */
if (!empty($stok) && $stok == '1') {
    $where .= " AND stok > 0";
}



$sql = "SELECT * FROM urunler $where ORDER BY $orderSql";
$stmt = $db->prepare($sql);
$stmt->execute($params);

$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';

$kategoriler = $db->query("SELECT * FROM kategoriler")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <!-- Üst Başlık ve Filtre -->
    <div class="d-flex justify-content-between align-items-center mb-5 p-4 bg-white rounded-4 shadow-sm border border-light">
        <h2 class="fw-bold m-0 italic">Taze Seçkiler ✨</h2>
       <form method="GET" class="d-flex gap-2 mb-4">

    <input type="text" name="q" class="form-control"
           placeholder="Ürün ara..."
           value="<?php echo $_GET['q'] ?? ''; ?>">

    <select name="fiyat" class="form-select">
    <option value="">Tüm Fiyatlar</option>
    <option value="0-200" <?php echo ($fiyat=='0-200')?'selected':''; ?>>0 - 200 TL</option>
    <option value="200-500" <?php echo ($fiyat=='200-500')?'selected':''; ?>>200 - 500 TL</option>
    <option value="500+" <?php echo ($fiyat=='500+')?'selected':''; ?>>500+ TL</option>
</select>

 <select name="kategori" class="form-select">
    <option value="">Tüm Kategoriler</option>

    <?php foreach ($kategoriler as $kat): ?>
        <option value="<?php echo $kat['id']; ?>"
            <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($kat['ad']); ?>
        </option>
    <?php endforeach; ?>
</select>

    <select name="sirala" class="form-select">
        <option value="yeni">En Yeniler</option>
        <option value="fiyat_artan">Fiyat ↑</option>
        <option value="fiyat_azalan">Fiyat ↓</option>
    </select>

    
<label class="form-check ms-2">
    <input type="checkbox" name="stok" value="1"
        <?php echo (isset($_GET['stok']) && $_GET['stok']=='1') ? 'checked' : ''; ?>>
    Sadece stokta olanlar
</label>
    <button class="btn btn-dark">Filtrele</button>
</form>
    </div>

    <div class="row g-4">
        <?php foreach($urunler as $urun): 
            // Favori Kontrolü
            $isFav = false;
            if(isset($_SESSION['user_id'])) {
                $fSorgu = $db->prepare("SELECT id FROM favoriler WHERE user_id = ? AND urun_id = ?");
                $fSorgu->execute([$_SESSION['user_id'], $urun['id']]);
                if($fSorgu->rowCount() > 0) $isFav = true;
            }
        ?>
        <div class="col-lg-4 col-md-6">
            <div class="product-grid-card">
                
                <!-- 1. FAVORİ BUTONU -->
                <div class="fav-badge">
                    <button type="button" class="fav-btn" data-id="<?php echo $urun['id']; ?>">
                        <span class="heart-icon" style="filter: <?php echo $isFav ? 'none' : 'grayscale(100%) opacity(0.3)'; ?>">
                            <?php echo $isFav ? '❤️' : '🤍'; ?>
                        </span>
                    </button>
                </div>

                <!-- 2. GÖRSEL (DOME/FANUS) -->
                <div class="product-image-dome" onclick="window.location.href='urun_detay.php?id=<?php echo $urun['id']; ?>'">
                    <img src="assets/img/<?php echo $urun['resim']; ?>" alt="<?php echo $urun['ad']; ?>" onerror="this.src='https://via.placeholder.com/200'">
                </div>

                <!-- 3. BİLGİLER -->
                <div class="product-info">
                    <h6><?php echo htmlspecialchars($urun['ad']); ?></h6>
                    <div class="price">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></div>
                </div>

                <!-- 4. AKSİYONLAR -->
                <div class="product-actions">
                    <button type="button" class="btn-add-cart sepete-ekle-btn" data-id="<?php echo $urun['id']; ?>">
                        <i class="fa fa-shopping-cart me-2"></i> Sepete Ekle
                    </button>
                    <a href="urun_detay.php?id=<?php echo $urun['id']; ?>" class="btn-view-details" title="İncele">
                        <i class="fa fa-search"></i>
                    </a>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>