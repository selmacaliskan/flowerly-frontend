<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "kuponlar"; 

// --- İŞLEM: Kupon Ekleme ---
if (isset($_POST['kupon_ekle'])) {
    $kod = strtoupper(trim($_POST['kod']));
    $indirim = (int)$_POST['indirim_tutari'];
    $tarih = $_POST['son_tarih'];

    // Madde 3: Prepared Statements
    $ekle = $db->prepare("INSERT INTO kuponlar (kod, indirim_tutari, son_tarih) VALUES (?, ?, ?)");
    $ekle->execute([$kod, $indirim, $tarih]);
    header("Location: kuponlar.php?mesaj=eklendi");
    exit();
}

// --- İŞLEM: Kupon Silme ---
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $db->prepare("DELETE FROM kuponlar WHERE id = ?")->execute([$id]);
    header("Location: kuponlar.php?mesaj=silindi");
    exit();
}

// Kuponları Listele
$kuponlar = $db->query("SELECT * FROM kuponlar ORDER BY son_tarih DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kupon Yönetimi - Flowerly Admin</title>
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
                <h2 class="fw-bold mb-0">🎟️ Kupon Yönetimi</h2>
                <small class="text-muted">Müşteriler için indirim kodları oluşturun ve yönetin.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">Dashboard</a>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-check-circle me-2"></i> İşlem başarıyla tamamlandı.
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- SOL: Kupon Listesi -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Kupon Kodu</th>
                                    <th>İndirim</th>
                                    <th>Son Tarih</th>
                                    <th>Durum</th>
                                    <th class="text-end pe-4">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($kuponlar as $k): 
                                    $gecerli = (strtotime($k['son_tarih']) >= strtotime(date('Y-m-d')));
                                ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-primary-light text-primary px-5 py-2 fs-6">#<?php echo $k['kod']; ?></span></td>
                                    <td class="fw-bold text-dark"><?php echo $k['indirim_tutari']; ?> TL</td>
                                    <td><?php echo date('d.m.Y', strtotime($k['son_tarih'])); ?></td>
                                    <td>
                                        <?php if($gecerli): ?>
                                            <span class="badge bg-success-light text-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-light text-danger">Süresi Doldu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="?sil=<?php echo $k['id']; ?>" class="btn btn-white btn-sm px-3 shadow-sm border" onclick="return confirm('Silinsin mi?')" title="Sil">
                                            <i class="fa-solid fa-trash text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SAĞ: Yeni Kupon Ekle -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-primary border-5">
                    <h5 class="fw-bold mb-4">➕ Yeni Kupon Oluştur</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kupon Kodu</label>
                            <input type="text" name="kod" class="form-control rounded-3" placeholder="Örn: BAHAR20" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">İndirim Tutarı (TL)</label>
                            <input type="number" name="indirim_tutari" class="form-control rounded-3" placeholder="20" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Son Geçerlilik Tarihi</label>
                            <input type="date" name="son_tarih" class="form-control rounded-3" required>
                        </div>
                        <button type="submit" name="kupon_ekle" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Kuponu Kaydet</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>