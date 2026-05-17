<?php 
$sayfaBaslik = "Adreslerim";
$sayfa = "adreslerim";
require_once 'includes/db.php';
session_start();

if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$uID = $_SESSION['user_id'];

// --- ADRES EKLEME ---
if(isset($_POST['adres_ekle'])) {
    $baslik = $_POST['baslik'];
    $sehir = $_POST['sehir'];
    $detay = $_POST['adres_detay'];
    $sorgu = $db->prepare("INSERT INTO adresler (user_id, baslik, sehir, adres_detay) VALUES (?, ?, ?, ?)");
    $sorgu->execute([$uID, $baslik, $sehir, $detay]);
    header("Location: adreslerim.php?mesaj=eklendi"); exit();
}

// --- ADRES SİLME ---
if(isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $db->prepare("DELETE FROM adresler WHERE id = ? AND user_id = ?")->execute([$id, $uID]);
    header("Location: adreslerim.php?mesaj=silindi"); exit();
}

include 'includes/header.php'; 
?>
<link rel="stylesheet" href="assets/css/user-panel.css">

<div class="container mt-5 mb-5">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php include 'includes/user-sidebar.php'; ?>
        </div>
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">📍 Kayıtlı Adreslerim</h2>
                <button class="btn btn-rose-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#adresModal" style="background:#e91e63; color:white; border-radius:50px;">+ Yeni Adres</button>
            </div>

            <div class="row g-3">
                <?php 
                $adresler = $db->prepare("SELECT * FROM adresler WHERE user_id = ?");
                $adresler->execute([$uID]);
                foreach($adresler->fetchAll() as $a): ?>
                    <div class="col-md-6">
                        <div class="card bento-card p-4 shadow-sm border-0">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold m-0">🏠 <?php echo $a['baslik']; ?></h6>
                                <a href="?sil=<?php echo $a['id']; ?>" class="text-danger small" onclick="return confirm('Silinsin mi?')">Sil</a>
                            </div>
                            <p class="small text-muted mb-1"><?php echo $a['sehir']; ?></p>
                            <p class="small mb-0"><?php echo $a['adres_detay']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Adres Ekleme Modalı -->
<div class="modal fade" id="adresModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0"> <h5 class="fw-bold">Yeni Adres</h5> </div>
            <div class="modal-body">
                <input type="text" name="baslik" class="form-control mb-3 rounded-3" placeholder="Adres Başlığı (Örn: Ev)" required>
                <input type="text" name="sehir" class="form-control mb-3 rounded-3" placeholder="Şehir" required>
                <textarea name="adres_detay" class="form-control rounded-3" placeholder="Tam Adres" rows="3" required></textarea>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="adres_ekle" class="btn btn-rose-pill w-100" style="background:#e91e63; color:white; border-radius:50px;">Kaydet</button>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>