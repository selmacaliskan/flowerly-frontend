<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "urunler"; // Sidebar'da ürünler sekmesinin aktif kalması için

// 1. URL'den gelen ID'yi al (Güvenlik için int'e çevir)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Ürünün mevcut bilgilerini çek (Madde 3: Prepared Statements)
$sorgu = $db->prepare("SELECT * FROM urunler WHERE id = ?");
$sorgu->execute([$id]);
$urun = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    header("Location: urunler.php?hata=bulunamadi");
    exit();
}

// 3. Güncelleme İşlemi (Form gönderildiğinde)
if ($_POST) {
    $ad = $_POST['ad'];
    $fiyat = $_POST['fiyat'];
    $kat_id = $_POST['kategori_id'];
    $aciklama = $_POST['aciklama'];
    $resim = $_POST['resim'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    // Madde 3: Güncelleme Sorgusu
    $guncelle = $db->prepare("UPDATE urunler SET kategori_id = ?, ad = ?, fiyat = ?, resim = ?, aciklama = ?, aktif = ? WHERE id = ?");
    $sonuc = $guncelle->execute([$kat_id, $ad, $fiyat, $resim, $aciklama, $aktif, $id]);

    if ($sonuc) {
        // Madde 19: Bildirim Mesajı
        header("Location: urunler.php?mesaj=guncellendi");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Düzenle - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        
        <!-- Üst Başlık ve Geri Dön -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">✏️ Ürün Düzenle</h2>
                <small class="text-muted">Şu an <strong><?php echo $urun['ad']; ?></strong> ürününü düzenliyorsunuz.</small>
            </div>
            <a href="urunler.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-2"></i> Listeye Dön
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Düzenleme Formu Kartı -->
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Ürün Adı</label>
                                <input type="text" name="ad" class="form-control rounded-3" value="<?php echo $urun['ad']; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Kategori</label>
                                <select name="kategori_id" class="form-select rounded-3" required>
                                    <?php 
                                    $kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY ad ASC")->fetchAll();
                                    foreach($kategoriler as $k): ?>
                                        <option value="<?php echo $k['id']; ?>" <?php echo ($k['id'] == $urun['kategori_id']) ? 'selected' : ''; ?>>
                                            <?php echo $k['ad']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fiyat (TL)</label>
                                <input type="number" step="0.01" name="fiyat" class="form-control rounded-3" value="<?php echo $urun['fiyat']; ?>" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Resim Dosya Adı</label>
                                <input type="text" name="resim" class="form-control rounded-3" value="<?php echo $urun['resim']; ?>" required>
                                <small class="text-muted">Görselin assets/img/ klasöründe olduğundan emin olun.</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Ürün Açıklaması</label>
                                <textarea name="aciklama" class="form-control rounded-3" rows="5"><?php echo $urun['aciklama']; ?></textarea>
                            </div>

                            <div class="col-md-12 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="aktif" id="aktifSwitch" <?php echo $urun['aktif'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="aktifSwitch">Ürün Sitede Aktif Görünsün</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 shadow-sm fw-bold">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Değişiklikleri Kaydet
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sağ Taraf: Önizleme Kartı -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                    <h5 class="fw-bold mb-3">Mevcut Görsel</h5>
                    <div class="rounded-4 overflow-hidden shadow-sm mb-3">
                        <img src="../assets/img/<?php echo $urun['resim']; ?>" class="img-fluid" id="onizlemeResim">
                    </div>
                    <p class="small text-muted mb-0">Önizleme yukarıdaki resim adına göre gösterilir.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>