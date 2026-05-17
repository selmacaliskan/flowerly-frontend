<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "urunler";

// --- İŞLEM: Tekli Silme ---
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $db->prepare("DELETE FROM urunler WHERE id = ?")->execute([$id]);
    header("Location: urunler.php?mesaj=silindi");
    exit();
}

// --- İŞLEM: Toplu Silme (Madde 16) ---
if (isset($_POST['toplu_sil']) && !empty($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);
    $yerTutucu = implode(',', array_fill(0, count($ids), '?'));
    $sorgu = $db->prepare("DELETE FROM urunler WHERE id IN ($yerTutucu)");
    $sorgu->execute($ids);
    header("Location: urunler.php?mesaj=toplusilindi");
    exit();
}

// --- VERİ ÇEKME: Filtreleme ve Arama (Madde 15) ---
$arama = isset($_GET['ara']) ? trim($_GET['ara']) : '';
$kategori_filtre = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

$sql = "SELECT u.*, k.ad as kat_ad FROM urunler u LEFT JOIN kategoriler k ON u.kategori_id = k.id WHERE 1=1";
$params = [];

if ($arama) {
    $sql .= " AND u.ad LIKE ?";
    $params[] = "%$arama%";
}
if ($kategori_filtre > 0) {
    $sql .= " AND u.kategori_id = ?";
    $params[] = $kategori_filtre;
}

$sql .= " ORDER BY u.id DESC";
$sorgu = $db->prepare($sql);
$sorgu->execute($params);
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

$kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY ad ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Yönetimi - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 10px; }
        .table-card { background: white; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; transition: 0.3s; }
        .btn-action:hover { transform: scale(1.1); }
    </style>
</head>
<body class="admin-body">

<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">📦 Ürün Yönetimi</h2>
                <small class="text-muted">Mağazanızdaki ürünleri yönetin, stok ve fiyatları güncelleyin.</small>
            </div>
            <div class="d-flex gap-2">
                <!-- Madde 17: Dışa Aktarma Butonu -->
                <a href="export.php" class="btn btn-outline-success rounded-pill px-4 shadow-sm">
                    <i class="fa-solid fa-file-csv me-2"></i> CSV Dışa Aktar
                </a>
                <a href="urun-ekle.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fa-solid fa-plus me-2"></i> Yeni Ürün Ekle
                </a>
            </div>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-check-circle me-2"></i> İşlem başarıyla gerçekleştirildi.
            </div>
        <?php endif; ?>

        <!-- Filtreleme Çubuğu (Madde 15) -->
        <div class="card table-card p-3 mb-4">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" name="ara" value="<?php echo htmlspecialchars($arama); ?>" class="form-control border-0 bg-light" placeholder="Ürün adı ara...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select border-0 bg-light">
                        <option value="0">Tüm Kategoriler</option>
                        <?php foreach($kategoriler as $k): ?>
                            <option value="<?php echo $k['id']; ?>" <?php echo $kategori_filtre == $k['id'] ? 'selected' : ''; ?>>
                                <?php echo $k['ad']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill">Filtrele</button>
                </div>
                <div class="col-md-2">
                    <a href="urunler.php" class="btn btn-outline-secondary w-100 rounded-pill">Sıfırla</a>
                </div>
            </form>
        </div>

        <form action="" method="POST" onsubmit="return confirm('Seçili ürünleri silmek istediğinize emin misiniz?');">
            <div class="card table-card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="hepsiniSec">
                                </th>
                                <th style="width: 70px;">Görsel</th>
                                <th>Ürün Adı</th>
                                <th>Kategori</th>
                                <th>Fiyat</th>
                                <th>Durum</th>
                                <th class="text-end pe-4">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($urunler as $u): ?>
                            <tr>
                                <td class="ps-4">
                                    <input type="checkbox" name="ids[]" value="<?php echo $u['id']; ?>" class="form-check-input urun-checkbox">
                                </td>
                                <td>
                                    <img src="../assets/img/<?php echo $u['resim']; ?>" class="product-img shadow-sm" onerror="this.src='https://via.placeholder.com/50'">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['ad']); ?></div>
                                    <small class="text-muted">ID: #<?php echo $u['id']; ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border rounded-pill"><?php echo $u['kat_ad'] ?: 'Kategorisiz'; ?></span></td>
                                <td class="fw-bold text-primary">₺<?php echo number_format($u['fiyat'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php if($u['aktif']): ?>
                                        <span class="badge bg-success-light text-success px-3">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-light text-danger px-3">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="urun-duzenle.php?id=<?php echo $u['id']; ?>" class="btn-action bg-info-light text-info me-1" title="Düzenle">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="?sil=<?php echo $u['id']; ?>" class="btn-action bg-danger-light text-danger" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')" title="Sil">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Madde 16: Toplu İşlem Alanı -->
                <div class="card-footer bg-white p-3 border-0">
                    <button type="submit" name="toplu_sil" class="btn btn-danger rounded-pill px-4 shadow-sm" id="topluSilBtn" disabled>
                        <i class="fa-solid fa-trash-can me-2"></i> Seçilenleri Toplu Sil
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toplu seçim mantığı
    const hepsiniSec = document.getElementById('hepsiniSec');
    const checkboxlar = document.querySelectorAll('.urun-checkbox');
    const topluSilBtn = document.getElementById('topluSilBtn');

    hepsiniSec.addEventListener('change', function() {
        checkboxlar.forEach(cb => cb.checked = this.checked);
        butonKontrol();
    });

    checkboxlar.forEach(cb => {
        cb.addEventListener('change', butonKontrol);
    });

    function butonKontrol() {
        const seciliVarMi = Array.from(checkboxlar).some(cb => cb.checked);
        topluSilBtn.disabled = !seciliVarMi;
    }
</script>
</body>
</html>