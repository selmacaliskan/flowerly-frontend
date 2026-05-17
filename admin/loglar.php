<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "loglar"; // Sidebar'da aktif butonun yanması için


// ÇOKLU LOG SİLME
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['secili_loglar'])) {

    if ($_SESSION['rol_id'] != 1) {
        die("Yetkisiz erişim.");
    }

    $seciliLoglar = $_POST['secili_loglar'];

    if (!empty($seciliLoglar)) {

        // Güvenlik: sadece sayı kabul et
        $ids = array_map('intval', $seciliLoglar);

        // ?,?,? oluştur
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "DELETE FROM loglar WHERE id IN ($placeholders)";

        $sil = $db->prepare($sql);
        $sil->execute($ids);

        header("Location: loglar.php?mesaj=silindi");
        exit();
    }
}

// VERİLERİ TEMİZLEME (Madde 17) checkbox ile tüm logları temizleme
// --- VERİ ÇEKME: Tüm Logları Son Tarihe Göre Getir (Madde 18) ---
$loglar = $db->query("SELECT * FROM loglar ORDER BY tarih DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sistem Logları - Flowerly Admin</title>
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
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Sistem Logları</h2>
                <small class="text-muted">Sistemdeki kritik hataları, giriş denemelerini ve IP kayıtlarını izleyin.</small>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                    <i class="fa-solid fa-house me-2"></i> Dashboard
                </a>
            </div>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-info-circle me-2"></i> Tüm log kayıtları başarıyla temizlendi.
            </div>
        <?php endif; ?>

        <!-- Log Listesi -->
         <form method="POST" id="logForm">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                           
                            <th style="width:50px;" class="text-center">
                                  <input type="checkbox" id="tumunuSec"> </th>
                    
                            <th class="ps-4" style="width: 80px;">ID</th>
                            <th>İşlem / Mesaj</th>
                            <th style="width: 180px;">IP Adresi</th>
                            <th style="width: 200px;">Tarih & Saat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($loglar as $l): ?>
                        
                            <tr>
                            <td class="text-center">
                            <input type="checkbox" name="secili_loglar[]" value="<?php echo $l['id']; ?>" class="log-check">
                            </td>
                            <td class="ps-4 text-muted">#<?php echo $l['id']; ?></td>
                            <td>
                                <?php 
                                    // Eğer mesajda "Hata" veya "Yetkisiz" geçiyorsa kırmızı vurgula
                                    $is_error = (strpos($l['mesaj'], 'Hata') !== false || strpos($l['mesaj'], 'Yetkisiz') !== false);
                                ?>
                                <span class="<?php echo $is_error ? 'text-danger fw-bold' : 'text-dark'; ?>">
                                    <i class="fa-solid <?php echo $is_error ? 'fa-triangle-exclamation' : 'fa-circle-info'; ?> me-2"></i>
                                    <?php echo $l['mesaj']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border rounded-pill px-3">
                                    <i class="fa-solid fa-network-wired me-1 small"></i> <?php echo $l['ip_adresi']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-dark small fw-bold"><?php echo date('d.m.Y', strtotime($l['tarih'])); ?></div>
                                <div class="text-muted" style="font-size: 11px;"><?php echo date('H:i:s', strtotime($l['tarih'])); ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($loglar) == 0): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Henüz kayıtlı bir sistem logu bulunmuyor.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top bg-light d-flex justify-content-between align-items-center">

    <small class="text-muted">
        Seçili kayıtları toplu silebilirsiniz.
    </small>

    <?php if($_SESSION['rol_id'] == 1): ?>
        <button type="submit"
                class="btn btn-danger rounded-pill px-4"
                onclick="return confirm('Seçili log kayıtları silinsin mi?')">

            <i class="fa-solid fa-trash me-2"></i>
            Seçilileri Sil
        </button>
    <?php endif; ?>
</div>
</div>

</form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.getElementById('tumunuSec').addEventListener('change', function() {

    let durum = this.checked;

    document.querySelectorAll('.log-check').forEach(function(cb) {
        cb.checked = durum;
    });

});

</script>

</body>
</html>