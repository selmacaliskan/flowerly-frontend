<?php 
$sayfaBaslik = "Hatırlatıcılar";
$sayfa = "hatirlaticilar";
require_once 'includes/db.php';
session_start();

if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$uID = $_SESSION['user_id'];

if(isset($_POST['ekle'])) {
    $baslik = $_POST['baslik'];
    $tarih = $_POST['tarih'];
    $db->prepare("INSERT INTO hatirlaticilar (user_id, baslik, tarih) VALUES (?, ?, ?)")->execute([$uID, $baslik, $tarih]);
    header("Location: hatirlaticilar.php"); exit();
}

include 'includes/header.php'; 
?>
<link rel="stylesheet" href="assets/css/user-panel.css">
<div class="container mt-5 mb-5">
    <div class="row g-4">
        <div class="col-lg-3"><?php include 'includes/user-sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">📅 Hatırlatıcılarım</h2>
            <div class="card bento-card p-4 shadow-sm border-0 mb-4">
                <form method="POST" class="row g-2">
                    <div class="col-md-5"><input type="text" name="baslik" class="form-control rounded-pill" placeholder="Örn: Annemin Doğum Günü" required></div>
                    <div class="col-md-4"><input type="date" name="tarih" class="form-control rounded-pill" required></div>
                    <div class="col-md-3"><button type="submit" name="ekle" class="btn btn-dark w-100 rounded-pill">Ekle</button></div>
                </form>
            </div>
            <div class="list-group">
                <?php 
                $hatirlat = $db->prepare("SELECT * FROM hatirlaticilar WHERE user_id = ? ORDER BY tarih ASC");
                $hatirlat->execute([$uID]);
                foreach($hatirlat->fetchAll() as $h): ?>
                    <div class="list-group-item border-0 bento-card mb-2 p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="d-block"><?php echo $h['baslik']; ?></strong>
                            <small class="text-muted"><?php echo date('d.m.Y', strtotime($h['tarih'])); ?></small>
                        </div>
                        <span class="badge bg-rose-light text-rose rounded-pill">Yaklaşıyor</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>