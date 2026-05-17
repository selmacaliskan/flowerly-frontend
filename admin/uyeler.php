<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "uyeler"; 

// --- İŞLEM: Rol Güncelleme (Sadece Süper Admin yapabilir) ---
if (isset($_POST['rol_guncelle']) && $_SESSION['rol_id'] == 1) {
    $u_id = (int)$_POST['user_id'];
    $yeni_rol = (int)$_POST['yeni_rol'];
    
    // Kendisinin Süper Adminliğini almasını engelle (Güvenlik)
    if($u_id == $_SESSION['user_id'] && $yeni_rol != 1) {
        header("Location: uyeler.php?hata=adminlikten_cikamazsin");
    } else {
        $db->prepare("UPDATE kullanicilar SET rol_id = ? WHERE id = ?")->execute([$yeni_rol, $u_id]);
        header("Location: uyeler.php?mesaj=guncellendi");
    }
    exit();
}

// --- İŞLEM: Üye Silme ---
if (isset($_GET['sil']) && $_SESSION['rol_id'] == 1) {
    $id = (int)$_GET['sil'];
    if($id != $_SESSION['user_id']) {
        $db->prepare("DELETE FROM kullanicilar WHERE id = ?")->execute([$id]);
        header("Location: uyeler.php?mesaj=silindi");
    } else {
        header("Location: uyeler.php?hata=kendinisilemezsin");
    }
    exit();
}

// --- VERİ ÇEKME ---
$uyeler = $db->query("SELECT k.*, r.rol_adi FROM kullanicilar k 
                     JOIN roller r ON k.rol_id = r.id 
                     ORDER BY k.rol_id ASC, k.ad_soyad ASC")->fetchAll(PDO::FETCH_ASSOC);

$roller = $db->query("SELECT * FROM roller")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Üye Yönetimi - Flowerly Admin</title>
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
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-user-shield me-2 text-primary"></i> Üye & Yetki Yönetimi</h2>
                <small class="text-muted">Kullanıcı rollerini belirleyin ve erişim yetkilerini düzenleyin.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>
        </div>

        <!-- Bildirim Mesajları -->
        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-check-circle me-2"></i> İşlem başarıyla tamamlandı.
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['hata'])): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-circle-exclamation me-2"></i> Bu işlemi gerçekleştirmek için yetkiniz yok veya kural ihlali yapıldı.
            </div>
        <?php endif; ?>

        <!-- Üye Listesi -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Üye</th>
                            <th>E-posta</th>
                            <th>Mevcut Rol</th>
                            <?php if($_SESSION['rol_id'] == 1): ?>
                                <th style="width: 250px;">Rolü Değiştir</th>
                                <th class="text-end pe-4">İşlem</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($uyeler as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-light text-primary d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px;">
                                        <?php echo strtoupper(mb_substr($u['ad_soyad'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo $u['ad_soyad']; ?></div>
                                        <small class="text-muted">ID: #<?php echo $u['id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $u['eposta']; ?></td>
                            <td>
                                <?php 
                                    $renk = "success"; 
                                    if($u['rol_id'] == 1) $renk = "danger"; 
                                    if($u['rol_id'] == 2 || $u['rol_id'] == 3) $renk = "info";
                                ?>
                                <span class="badge bg-<?php echo $renk; ?>-light text-<?php echo $renk; ?> rounded-pill px-3 py-2">
                                    <?php echo $u['rol_adi']; ?>
                                </span>
                            </td>

                            <?php if($_SESSION['rol_id'] == 1): ?>
                            <!-- ROL DEĞİŞTİRME FORMU -->
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <select name="yeni_rol" class="form-select form-select-sm rounded-pill shadow-sm">
                                        <?php foreach($roller as $r): ?>
                                            <option value="<?php echo $r['id']; ?>" <?php echo ($u['rol_id'] == $r['id']) ? 'selected' : ''; ?>>
                                                <?php echo $r['rol_adi']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="rol_guncelle" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                        <i class="fa-solid fa-rotate"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-4">
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?sil=<?php echo $u['id']; ?>" class="btn btn-white btn-sm px-3 shadow-sm border" 
                                       onclick="return confirm('Bu üyeyi tamamen silmek istediğinize emin misiniz?')" title="Sil">
                                        <i class="fa-solid fa-trash text-danger"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted rounded-pill">Siz</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>