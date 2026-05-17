<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "urunler"; // Sidebar'da ürünler sekmesini aktif göstermek için

// --- ÜRÜN EKLEME İŞLEMİ ---
if ($_POST) {
    $ad = $_POST['ad'];
    $fiyat = $_POST['fiyat'];
    $kat_id = $_POST['kategori_id'];
    $aciklama = $_POST['aciklama'];
    $resim = $_POST['resim'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    // Madde 3: Prepared Statements (SQL Injection Koruması)
    $ekle = $db->prepare("INSERT INTO urunler (kategori_id, ad, fiyat, resim, aciklama, aktif) VALUES (?, ?, ?, ?, ?, ?)");
    $sonuc = $ekle->execute([$kat_id, $ad, $fiyat, $resim, $aciklama, $aktif]);

    if ($sonuc) {
        // Madde 19: Başarılı yönlendirme ve mesaj
        header("Location: urunler.php?mesaj=eklendi");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Ürün Ekle - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body class="admin-body">

<div class="d-flex">
    <!-- SOL TARAF: Sidebar (Madde 6) -->
    <?php include 'sidebar.php'; ?>

    <!-- SAĞ TARAF: İçerik Alanı -->
    <div class="main-content flex-grow-1 p-4">
        
        <!-- Üst Başlık ve Geri Dön Butonu -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-plus-circle me-2"></i> Yeni Ürün Ekle</h2>
                <small class="text-muted">Mağazanıza taze ve yeni bir çiçek ekleyin.</small>
            </div>
            <a href="urunler.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i> Listeye Dön
            </a>
        </div>

        <div class="row">
            <!-- Form Alanı -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Ürün Adı</label>
                                <input type="text" name="ad" class="form-control rounded-3 py-2" placeholder="Örn: Beyaz Papatya Buketi" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Kategori Seçin</label>
                                <select name="kategori_id" class="form-select rounded-3 py-2" required>
                                    <option value="" selected disabled>Kategori Seçiniz...</option>
                                    <?php 
                                    $kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY ad ASC")->fetchAll();
                                    foreach($kategoriler as $k): ?>
                                        <option value="<?php echo $k['id']; ?>"><?php echo $k['ad']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Satış Fiyatı (TL)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">₺</span>
                                    <input type="number" step="0.01" name="fiyat" class="form-control rounded-end-3 py-2" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Görsel Dosya Adı</label>
                                <input type="text" name="resim" class="form-control rounded-3 py-2" placeholder="cicek1.png" required>
                                <small class="text-muted">Lütfen görseli 'assets/img/' klasörüne attıktan sonra sadece ismini yazın.</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Ürün Açıklaması (Madde 10)</label>
                                <textarea name="aciklama" class="form-control rounded-3" rows="5" placeholder="Ürün hakkında detaylı bilgi giriniz..."></textarea>
                            </div>

                            <div class="col-md-12 mb-4">
                                <div class="form-check form-switch p-3 bg-light rounded-3 shadow-sm border">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="aktif" id="aktifSwitch" checked>
                                    <label class="form-check-label fw-bold" for="aktifSwitch">Ürünü Hemen Satışa Çıkar (Aktif)</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <hr>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm fw-bold w-100 w-md-auto">
                                    <i class="fa-solid fa-check-circle me-2"></i> Ürünü Veritabanına Kaydet
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sağ Taraf: Bilgi Notu -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white mb-4">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-lightbulb me-2"></i> Küçük İpucu</h5>
                    <p class="small mb-0 opacity-75">
                        Ürün eklerken görsel dosya isminin doğruluğundan emin olun. 
                        Açıklama kısmına çiçeğin anlamı ve bakım talimatlarını eklemek müşteri memnuniyetini artırır.
                    </p>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                    <div class="icon-box mb-3 mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-camera fs-2 text-muted"></i>
                    </div>
                    <p class="small text-muted mb-0">Ürün resmini 'assets/img' içine atmayı unutmayın.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>