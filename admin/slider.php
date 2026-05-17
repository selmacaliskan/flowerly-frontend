<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "slider"; 

// --- İŞLEM: Yeni Slider Ekle veya Güncelle ---
if (isset($_POST['slider_kaydet'])) {
    $sira = (int)$_POST['sira'];
    $resim = $_POST['resim'];
    $id = isset($_POST['slider_id']) ? (int)$_POST['slider_id'] : 0;

    if ($id > 0) {
        // Güncelleme
        $sorgu = $db->prepare("UPDATE slider SET resim = ?, sira = ? WHERE id = ?");
        $sorgu->execute([$resim, $sira, $id]);
        $mesaj = "guncellendi";
    } else {
        // Yeni Ekleme
        $sorgu = $db->prepare("INSERT INTO slider (resim, sira) VALUES (?, ?)");
        $sorgu->execute([$resim, $sira]);
        $mesaj = "eklendi";
    }
    header("Location: slider.php?mesaj=$mesaj");
    exit();
}

// --- İŞLEM: Slider Silme ---
if (isset($_GET['sil'])) {
    $db->prepare("DELETE FROM slider WHERE id = ?")->execute([(int)$_GET['sil']]);
    header("Location: slider.php?mesaj=silindi");
    exit();
}

// --- HAZIRLIK: Düzenlenecek Veriyi Çekme ---
$duzenle = null;
if (isset($_GET['duzenle'])) {
    $sorgu = $db->prepare("SELECT * FROM slider WHERE id = ?");
    $sorgu->execute([(int)$_GET['duzenle']]);
    $duzenle = $sorgu->fetch(PDO::FETCH_ASSOC);
}

// Slider Listesini Çek
$sliderlar = $db->query("SELECT * FROM slider ORDER BY sira ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Slider Yönetimi - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .slider-preview { width: 120px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    </style>
</head>
<body class="admin-body">

<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">🖼️ Slider Yönetimi</h2>
                <small class="text-muted">Ana sayfadaki görselleri buradan yönetebilirsiniz.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">Dashboard</a>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-check-circle me-2"></i> İşlem başarıyla tamamlandı.
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- SOL: Slider Listesi -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">Sıra</th>
                                    <th style="width: 150px;">Görsel</th>
                                    <th>Dosya Adı</th>
                                    <th class="text-end pe-4">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($sliderlar as $s): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-primary rounded-pill px-3"><?php echo $s['sira']; ?></span>
                                    </td>
                                    <td>
                                        <!-- DOSYA YOLUNA DİKKAT: ../assets/img/ klasöründe olmalı -->
                                        <img src="../assets/img/<?php echo $s['resim']; ?>" 
                                             class="slider-preview" 
                                             onerror="this.src='https://via.placeholder.com/120x60?text=Resim+Yok'">
                                    </td>
                                    <td><code class="small text-muted"><?php echo $s['resim']; ?></code></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3">
                                            <a href="?duzenle=<?php echo $s['id']; ?>" class="btn btn-white btn-sm px-3" title="Düzenle">
                                                <i class="fa-solid fa-edit text-info"></i>
                                            </a>
                                            <a href="?sil=<?php echo $s['id']; ?>" class="btn btn-white btn-sm px-3" onclick="return confirm('Silinsin mi?')" title="Sil">
                                                <i class="fa-solid fa-trash text-danger"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(count($sliderlar) == 0): ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Kayıtlı slider bulunmuyor.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SAĞ: Ekleme & Düzenleme Formu -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 <?php echo $duzenle ? 'border-start border-warning border-5' : 'border-start border-success border-5'; ?>">
                    <h5 class="fw-bold mb-4">
                        <?php echo $duzenle ? '📝 Görseli Düzenle' : '➕ Yeni Görsel Ekle'; ?>
                    </h5>
                    
                    <form method="POST">
                        <input type="hidden" name="slider_id" value="<?php echo $duzenle['id'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Görsel Dosya Adı</label>
                            <input type="text" name="resim" class="form-control rounded-3" 
                                   value="<?php echo $duzenle['resim'] ?? ''; ?>" 
                                   placeholder="Örn: banner1.jpg" required>
                            <small class="text-muted" style="font-size: 11px;">Önemli: Görsel <b>assets/img/</b> klasöründe olmalıdır.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Görünüm Sırası</label>
                            <input type="number" name="sira" class="form-control rounded-3" 
                                   value="<?php echo $duzenle['sira'] ?? '1'; ?>" required>
                        </div>

                        <button type="submit" name="slider_kaydet" class="btn <?php echo $duzenle ? 'btn-warning' : 'btn-success'; ?> w-100 rounded-pill py-2 fw-bold shadow-sm">
                            <?php echo $duzenle ? 'Değişiklikleri Kaydet' : 'Sisteme Yükle'; ?>
                        </button>

                        <?php if($duzenle): ?>
                            <a href="slider.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted small">Vazgeç / Yeni Ekle</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-3 mt-4 bg-light">
                    <small class="text-muted"><i class="fa-solid fa-circle-info me-1"></i> İpucu: Resim görünmüyorsa <b>assets/img/</b> klasöründeki dosya adıyla buraya yazdığınız adın (uzantısı dahil .jpg, .png vb.) aynı olduğundan emin olun.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>