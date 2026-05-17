<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "yorumlar";

// AJAX Onayla / Reddet
if (isset($_POST['ajax_islem'])) {
    header('Content-Type: application/json');
    $id     = (int)$_POST['yorum_id'];
    $islem  = $_POST['islem']; // 'onayla' veya 'sil'
    if ($islem === 'onayla') {
        $db->prepare("UPDATE yorumlar SET onaylandi=1 WHERE id=?")->execute([$id]);
    } elseif ($islem === 'sil') {
        $db->prepare("DELETE FROM yorumlar WHERE id=?")->execute([$id]);
    }
    echo json_encode(['basarili' => true]);
    exit();
}

$filtre = $_GET['filtre'] ?? 'bekleyen';
$sql = "SELECT y.*, k.ad_soyad, u.ad as urun_ad FROM yorumlar y 
        JOIN kullanicilar k ON y.user_id = k.id 
        JOIN urunler u ON y.urun_id = u.id";
if ($filtre === 'bekleyen') $sql .= " WHERE y.onaylandi = 0";
elseif ($filtre === 'onayli') $sql .= " WHERE y.onaylandi = 1";
$sql .= " ORDER BY y.tarih DESC";
$yorumlar = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$bekleyen_sayi = $db->query("SELECT COUNT(*) FROM yorumlar WHERE onaylandi=0")->fetchColumn();
$onayli_sayi   = $db->query("SELECT COUNT(*) FROM yorumlar WHERE onaylandi=1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yorum Yönetimi - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .yildiz { color: #ffc107; font-size: 1.1rem; }
        .yorum-kart { transition: all .2s; }
        .yorum-kart:hover { transform: translateY(-1px); }
    </style>
</head>
<body class="admin-body">
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-star me-2 text-warning"></i> Yorum Yönetimi</h2>
                <small class="text-muted">Müşteri yorumlarını onaylayın veya kaldırın.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>
        </div>

        <!-- Filtre Sekmeleri -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $filtre=='bekleyen'?'active':''; ?>" href="?filtre=bekleyen">
                    ⏳ Bekleyenler
                    <?php if($bekleyen_sayi > 0): ?>
                    <span class="badge bg-danger ms-1"><?php echo $bekleyen_sayi; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filtre=='onayli'?'active':''; ?>" href="?filtre=onayli">
                    ✅ Onaylananlar
                    <span class="badge bg-success ms-1"><?php echo $onayli_sayi; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filtre=='hepsi'?'active':''; ?>" href="?filtre=hepsi">
                    📋 Tümü
                </a>
            </li>
        </ul>

        <?php if (count($yorumlar) === 0): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa fa-comments fa-3x d-block mb-3 opacity-20"></i>
                Bu filtrede yorum bulunamadı.
            </div>
        <?php endif; ?>

        <div class="row g-3" id="yorumListesi">
        <?php foreach($yorumlar as $y): ?>
        <div class="col-12" id="yorum-<?php echo $y['id']; ?>">
            <div class="card border-0 shadow-sm rounded-4 p-4 yorum-kart <?php echo $y['onaylandi']?'border-start border-success border-4':'border-start border-warning border-4'; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:38px;height:38px;">
                                <?php echo mb_strtoupper(mb_substr($y['ad_soyad'],0,1)); ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($y['ad_soyad']); ?></div>
                                <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($y['tarih'])); ?></small>
                            </div>
                            <div class="ms-2">
                                <?php for($i=1;$i<=5;$i++) echo $i<=$y['puan'] ? '<span class="yildiz">★</span>' : '<span class="text-muted">☆</span>'; ?>
                                <small class="text-muted ms-1">(<?php echo $y['puan']; ?>/5)</small>
                            </div>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-light text-dark border me-2">
                                <i class="fa fa-box me-1"></i><?php echo htmlspecialchars($y['urun_ad']); ?>
                            </span>
                            <?php if($y['onaylandi']): ?>
                                <span class="badge bg-success-light text-success">✅ Onaylı</span>
                            <?php else: ?>
                                <span class="badge bg-warning-light text-warning">⏳ Onay Bekliyor</span>
                            <?php endif; ?>
                        </div>
                        <p class="mb-0 text-dark"><?php echo htmlspecialchars($y['yorum']); ?></p>
                    </div>
                    <div class="d-flex flex-column gap-2 ms-4">
                        <?php if(!$y['onaylandi']): ?>
                        <button class="btn btn-success btn-sm rounded-pill px-3" onclick="yorumIslem(<?php echo $y['id']; ?>, 'onayla')">
                            <i class="fa fa-check me-1"></i> Onayla
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="yorumIslem(<?php echo $y['id']; ?>, 'sil')">
                            <i class="fa fa-trash me-1"></i> Sil
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function yorumIslem(id, islem) {
    if (islem === 'sil' && !confirm('Bu yorumu silmek istediğinize emin misiniz?')) return;
    const kart = document.getElementById('yorum-' + id);
    const fd = new FormData();
    fd.append('ajax_islem', '1');
    fd.append('yorum_id', id);
    fd.append('islem', islem);

    fetch('yorumlar.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (islem === 'sil') {
                kart.style.opacity = '0';
                kart.style.transform = 'scale(0.95)';
                kart.style.transition = 'all .3s';
                setTimeout(() => kart.remove(), 300);
            } else {
                kart.querySelector('.badge.bg-warning-light')?.remove();
                const badge = document.createElement('span');
                badge.className = 'badge bg-success-light text-success';
                badge.textContent = '✅ Onaylı';
                kart.querySelector('.mb-2').appendChild(badge);
                kart.querySelector('.btn-success')?.remove();
                kart.classList.remove('border-warning');
                kart.classList.add('border-success');
            }
        });
}
</script>
</body>
</html>
