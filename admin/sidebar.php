<?php
$rol_id = $_SESSION['rol_id'] ?? 4;

function rolIzni($rol_id, $sayfa, $db) {
    if ($rol_id == 1) return true;
    static $izinler = null;
    if ($izinler === null) {
        $stmt = $db->prepare("SELECT sayfa FROM rol_izinleri WHERE rol_id = ?");
        $stmt->execute([$rol_id]);
        $izinler = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    return in_array($sayfa, $izinler);
}

$okunmamis_mesaj = $db->query("SELECT COUNT(*) FROM mesajlar WHERE okundu = 0")->fetchColumn();
$bekleyen_yorum = ($rol_id == 1 || $rol_id == 3)
    ? $db->query("SELECT COUNT(*) FROM yorumlar WHERE onaylandi = 0")->fetchColumn()
    : 0;
?>
<div class="admin-sidebar shadow">
    <div class="brand p-4 text-center">
        <h4 class="fw-bold text-white mb-0">🌸 FLOWERLY</h4>
        <small class="text-white-50">ADMIN PANEL</small>
    </div>
    <div class="px-3 pb-2">
        <?php
        $rol_renk = ['','danger','info','warning','secondary'];
        $rol_adi  = ['','Süper Admin','Editör','Moderatör','Kullanıcı'];
        ?>
        <div class="text-center">
            <span class="badge bg-<?php echo $rol_renk[$rol_id]??'secondary'; ?> rounded-pill px-3 py-2 w-100">
                <i class="fa fa-shield-halved me-1"></i> <?php echo $rol_adi[$rol_id]??''; ?>
            </span>
        </div>
    </div>

    <div class="menu-section mt-2">
        <label>ANA MENÜ</label>
        <a href="index.php" class="nav-link <?php echo $sayfa=='dashboard'?'active':''; ?>">
            <i class="fas fa-home me-2"></i> Dashboard
        </a>
        <?php if(rolIzni($rol_id,'urunler',$db)): ?>
        <a href="urunler.php" class="nav-link <?php echo $sayfa=='urunler'?'active':''; ?>">
            <i class="fas fa-box me-2"></i> Ürünleri Yönet
        </a>
        <?php endif; ?>
        <?php if(rolIzni($rol_id,'kategoriler',$db)): ?>
        <a href="kategoriler.php" class="nav-link <?php echo $sayfa=='kategoriler'?'active':''; ?>">
            <i class="fas fa-tags me-2"></i> Kategorileri Yönet
        </a>
        <?php endif; ?>
        <?php if(rolIzni($rol_id,'siparisler',$db)): ?>
        <a href="siparisler.php" class="nav-link <?php echo $sayfa=='siparisler'?'active':''; ?>">
            <i class="fas fa-shopping-cart me-2"></i> Siparişleri Yönet
        </a>
        <?php endif; ?>
        <?php if(rolIzni($rol_id,'yorumlar',$db)): ?>
        <a href="yorumlar.php" class="nav-link <?php echo $sayfa=='yorumlar'?'active':''; ?>">
            <i class="fas fa-star me-2"></i> Yorum Yönetimi
            <?php if($bekleyen_yorum>0): ?>
                <span class="badge bg-warning rounded-pill float-end"><?php echo $bekleyen_yorum; ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>
    </div>

    <?php if(rolIzni($rol_id,'slider',$db)||rolIzni($rol_id,'menu',$db)): ?>
    <div class="menu-section mt-3">
        <label>İÇERİK</label>
        <?php if(rolIzni($rol_id,'slider',$db)): ?>
        <a href="slider.php" class="nav-link <?php echo $sayfa=='slider'?'active':''; ?>">
            <i class="fas fa-images me-2"></i> Slider Yönetimi
        </a>
        <?php endif; ?>
        <?php if(rolIzni($rol_id,'menu',$db)): ?>
        <a href="menu.php" class="nav-link <?php echo $sayfa=='menu'?'active':''; ?>">
            <i class="fas fa-bars me-2"></i> Menü Yönetimi
        </a>
        <?php endif; ?>
        <?php if(rolIzni($rol_id,'kuponlar',$db)): ?>
        <a href="kuponlar.php" class="nav-link <?php echo $sayfa=='kuponlar'?'active':''; ?>">
           <i class="fas fa-ticket-alt me-2"></i> Kupon Yönetimi
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="menu-section mt-3">
        <label>SİSTEM & HESAP</label>
        <?php if($rol_id==1): ?>
        <a href="uyeler.php" class="nav-link <?php echo $sayfa=='uyeler'?'active':''; ?>">
            <i class="fas fa-users me-2"></i> Üye Yönetimi
        </a>
        <?php endif; ?>
        <a href="profil.php" class="nav-link <?php echo $sayfa=='profil'?'active':''; ?>">
            <i class="fas fa-user-circle me-2"></i> Profil Bilgilerim
        </a>
        <?php if($rol_id==1): ?>
        <a href="ayarlar.php" class="nav-link <?php echo $sayfa=='ayarlar'?'active':''; ?>">
            <i class="fas fa-cog me-2"></i> Site Ayarları
        </a>
        <a href="loglar.php" class="nav-link <?php echo $sayfa=='loglar'?'active':''; ?>">
            <i class="fas fa-history me-2"></i> Sistem Logları
        </a>
        <?php endif; ?>
        <?php if(rolIzni($rol_id,'mesajlar',$db)): ?>
        <a href="mesajlar.php" class="nav-link <?php echo $sayfa=='mesajlar'?'active':''; ?>">
            <i class="fas fa-envelope me-2"></i> Mesajlar
            <?php if($okunmamis_mesaj>0): ?>
                <span class="badge bg-danger rounded-pill float-end"><?php echo $okunmamis_mesaj; ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>
        <hr class="mx-3 opacity-25">
        <a href="../logout.php" class="nav-link text-danger fw-bold">
            <i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap
        </a>
    </div>
</div>
