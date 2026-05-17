<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "kategoriler"; // Sidebar'da aktif butonun yanması için

// --- FONKSİYON: Türkçe Karakter Destekli Slug Oluşturucu (Madde 4) ---
function slugYap($metin) {
    $bul = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', ' ');
    $degistir = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', '-');
    $sonuc = strtolower(str_replace($bul, $degistir, $metin));
    $sonuc = preg_replace('/[^a-z0-9\-]/', '', $sonuc); 
    return $sonuc;
}

// --- İŞLEM: Kaydet (Ekle veya Güncelle) ---
if (isset($_POST['kategori_kaydet'])) {
    $ad = $_POST['ad'];
    $slug = slugYap($ad);
    $id = $_POST['kategori_id'];

    if (!empty($id)) {
        // Güncelleme
        $sorgu = $db->prepare("UPDATE kategoriler SET ad = ?, slug = ? WHERE id = ?");
        $sorgu->execute([$ad, $slug, $id]);
        $mesaj = "guncellendi";
    } else {
        // Ekleme
        $sorgu = $db->prepare("INSERT INTO kategoriler (ad, slug) VALUES (?, ?)");
        $sorgu->execute([$ad, $slug]);
        $mesaj = "eklendi";
    }
    header("Location: kategoriler.php?mesaj=$mesaj");
    exit();
}

// --- İŞLEM: Silme ---
if (isset($_GET['sil'])) {
    $db->prepare("DELETE FROM kategoriler WHERE id = ?")->execute([(int)$_GET['sil']]);
    header("Location: kategoriler.php?mesaj=silindi");
    exit();
}

// --- HAZIRLIK: Düzenlenecek Veriyi Çekme ---
$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $sorgu = $db->prepare("SELECT * FROM kategoriler WHERE id = ?");
    $sorgu->execute([(int)$_GET['duzenle']]);
    $duzenlenecek = $sorgu->fetch(PDO::FETCH_ASSOC);
}

// Kategorileri Listele
$kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategori Yönetimi - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body class="admin-body">

<div class="d-flex">
    <!-- Sidebar (Madde 6) -->
    <?php include 'sidebar.php'; ?>

    <!-- İçerik -->
    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-tags me-2"></i> Kategorileri Yönet</h2>
                <small class="text-muted">Ürün gruplarını ve menü yapısını buradan düzenleyin.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-home me-2"></i> Dashboard
            </a>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-check-circle me-2"></i> İşlem başarıyla tamamlandı.
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- SOL: Liste Tablosu -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Kategori Adı</th>
                                    <th>Slug (URL)</th>
                                    <th class="text-end pe-4">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($kategoriler as $k): ?>
                                <tr class="<?php echo ($duzenlenecek && $duzenlenecek['id'] == $k['id']) ? 'table-warning' : ''; ?>">
                                    <td class="ps-4 text-muted">#<?php echo $k['id']; ?></td>
                                    <td><strong class="text-dark"><?php echo $k['ad']; ?></strong></td>
                                    <td><code class="text-pink small">/<?php echo $k['slug']; ?></code></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3">
                                            <a href="?duzenle=<?php echo $k['id']; ?>" class="btn btn-white btn-sm px-3"><i class="fa-solid fa-edit text-info"></i></a>
                                            <a href="?sil=<?php echo $k['id']; ?>" class="btn btn-white btn-sm px-3" onclick="return confirm('Silinsin mi?')"><i class="fa-solid fa-trash text-danger"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SAĞ: Ekleme & Düzenleme Formu -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 <?php echo $duzenlenecek ? 'border-start border-warning border-5' : 'border-start border-primary border-5'; ?>">
                    <h5 class="fw-bold mb-4">
                        <?php echo $duzenlenecek ? '📝 Kategoriyi Güncelle' : '➕ Yeni Kategori Ekle'; ?>
                    </h5>
                    
                    <form method="POST">
                        <input type="hidden" name="kategori_id" value="<?php echo $duzenlenecek['id'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kategori Adı</label>
                            <input type="text" name="ad" class="form-control rounded-3" 
                                   value="<?php echo $duzenlenecek['ad'] ?? ''; ?>" 
                                   placeholder="Örn: Orkide Dünyası" required>
                        </div>

                        <button type="submit" name="kategori_kaydet" class="btn <?php echo $duzenlenecek ? 'btn-warning' : 'btn-primary'; ?> w-100 rounded-pill py-2 fw-bold shadow-sm">
                            <?php echo $duzenlenecek ? 'Değişiklikleri Kaydet' : 'Kategoriyi Oluştur'; ?>
                        </button>

                        <?php if($duzenlenecek): ?>
                            <a href="kategoriler.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted small">Vazgeç / Yeni Ekle</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 bg-light">
                    <h6 class="fw-bold"><i class="fa-solid fa-info-circle me-2"></i> Bilgi</h6>
                    <p class="small text-muted mb-0">Kategori adını değiştirdiğinizde, sistem SEO uyumlu linki (slug) otomatik olarak günceller.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>