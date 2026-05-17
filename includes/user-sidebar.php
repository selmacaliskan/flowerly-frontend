<?php
// Aktif sayfayı belirlemek için $sayfa değişkenini kullanacağız
$u_ad = $_SESSION['ad_soyad'] ?? 'Üye';
?>
<div class="card bento-sidebar shadow-sm border-0 mb-4">
    <div class="sidebar-header text-center p-4">
        <div class="user-avatar-circle shadow-sm mb-3">🌸</div>
        <h5 class="fw-bold mb-1"><?php echo $u_ad; ?></h5>
    </div>
    
    <div class="list-group list-group-flush p-2">
        <a href="hesabim.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-1 <?php echo ($sayfa == 'hesabim') ? 'active' : ''; ?>">
            <span class="me-2">🏠</span> Panel Özeti
        </a>
        <a href="profil-duzenle.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-1 <?php echo ($sayfa == 'profil') ? 'active' : ''; ?>">
            <span class="me-2">👤</span> Profil Bilgilerim
        </a>
        <a href="siparislerim.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-1 <?php echo ($sayfa == 'siparislerim') ? 'active' : ''; ?>">
            <span class="me-2">📦</span> Siparişlerim
        </a>
        <a href="favorilerim.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-1 <?php echo ($sayfa == 'favorilerim') ? 'active' : ''; ?>">
            <span class="me-2">❤️</span> Favorilerim
        </a>
        <!-- Yönergedeki eksik kısımlar -->
        <a href="adreslerim.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-1 <?php echo ($sayfa == 'adreslerim') ? 'active' : ''; ?>">
            <span class="me-2">📍</span> Adreslerim
        </a>
        <a href="kuponlarim.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-1 <?php echo ($sayfa == 'kuponlarim') ? 'active' : ''; ?>">
            <span class="me-2">🎟️</span> Kuponlarım
        </a>
        <hr class="mx-3 opacity-10">
        <a href="javascript:void(0)" onclick="confirmLogout()" class="list-group-item list-group-item-action border-0 rounded-4 text-danger">
            <span class="me-2">🚪</span> Güvenli Çıkış
        </a>
    </div>
</div>