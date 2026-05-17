<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "mesajlar"; 

// --- İŞLEM: Mesaj Silme ---
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $db->prepare("DELETE FROM mesajlar WHERE id = ?")->execute([$id]);
    header("Location: mesajlar.php?mesaj=silindi");
    exit();
}

// --- İŞLEM: Okundu Olarak İşaretle ---
if (isset($_GET['okundu'])) {
    $id = (int)$_GET['okundu'];
    $db->prepare("UPDATE mesajlar SET okundu = 1 WHERE id = ?")->execute([$id]);
    header("Location: mesajlar.php");
    exit();
}

// Tüm mesajları tarihe göre çek
$mesajlar = $db->query("SELECT * FROM mesajlar ORDER BY okundu ASC, tarih DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Mesajlar - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body class="admin-body">

<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-envelope-open-text me-2 text-primary"></i> Gelen Mesajlar</h2>
                <small class="text-muted">Müşterilerinizden gelen soru ve talepleri buradan inceleyin.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-check-circle me-2"></i> Mesaj başarıyla silindi.
            </div>
        <?php endif; ?>

        <!-- Mesaj Listesi -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Durum</th>
                            <th>Gönderen</th>
                            <th>Konu</th>
                            <th>Tarih</th>
                            <th class="text-end pe-4">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mesajlar as $m): ?>
                        <tr class="<?php echo $m['okundu'] == 0 ? 'table-light fw-bold' : ''; ?>">
                            <td class="ps-4">
                                <?php if($m['okundu'] == 0): ?>
                                    <span class="badge bg-primary rounded-pill">Yeni</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted rounded-pill">Okundu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-dark"><?php echo $m['ad_soyad']; ?></div>
                                <small class="text-muted fw-normal"><?php echo $m['eposta']; ?></small>
                            </td>
                            <td><?php echo $m['konu']; ?></td>
                            <td>
                                <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($m['tarih'])); ?></small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-3">
                                    <!-- Mesaj Detay Butonu (Modal açabilir veya basitçe aşağıda gösterebiliriz) -->
                                    <button class="btn btn-white btn-sm px-3" data-bs-toggle="collapse" data-bs-target="#msg-<?php echo $m['id']; ?>" onclick="location.href='?okundu=<?php echo $m['id']; ?>'" title="Oku">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </button>
                                    <a href="?sil=<?php echo $m['id']; ?>" class="btn btn-white btn-sm px-3" 
                                       onclick="return confirm('Bu mesajı silmek istediğinize emin misiniz?')" title="Sil">
                                        <i class="fa-solid fa-trash text-danger"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <!-- MESAJ İÇERİĞİ (Collapse Alanı) -->
                        <tr class="collapse" id="msg-<?php echo $m['id']; ?>">
                            <td colspan="5" class="bg-light p-4">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 border-bottom pb-2">Mesaj İçeriği:</h6>
                                        <p class="mb-0 text-dark" style="white-space: pre-wrap;"><?php echo $m['mesaj']; ?></p>
                                        <div class="mt-3">
                                            <a href="mailto:<?php echo $m['eposta']; ?>?subject=RE: <?php echo $m['konu']; ?>" class="btn btn-sm btn-primary rounded-pill">
                                                <i class="fa-solid fa-reply me-1"></i> Yanıtla (E-posta)
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($mesajlar) == 0): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Henüz hiç mesajınız bulunmuyor.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>