<?php
$sayfaBaslik = 'Kuponlarım';
$sayfa       = 'kuponlarim';

require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

/* =========================================================
   Geçerli ve henüz kullanılmamış tüm kuponları çek.
   Sitenizde "kullanıcıya özel kupon" tablosu varsa
   WHERE koşuluna "AND user_id = ?" ekleyebilirsiniz.
   ========================================================= */
$kuponlar = $db->query(
    "SELECT * FROM kuponlar
     WHERE aktif = 1
       AND son_tarih >= CURDATE()
     ORDER BY son_tarih ASC"
)->fetchAll(PDO::FETCH_ASSOC);

/* Aktif kuponu session'dan al (vurgulamak için) */
$aktifKupon = $_SESSION['kupon'] ?? null;

include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/user-panel.css">

<div class="container mt-5 mb-5">
    <div class="row g-4">

        <!-- SOL MENÜ -->
        <div class="col-lg-3">
            <?php include 'includes/user-sidebar.php'; ?>
        </div>

        <!-- SAĞ İÇERİK -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">🎟️ İndirim Kuponlarım</h2>

            <?php if (empty($kuponlar)): ?>
            <div class="text-center p-5 bg-white rounded-5 shadow-sm border">
                <div class="display-1 mb-3 opacity-25">🎫</div>
                <h5 class="fw-bold">Şu an aktif kupon bulunmuyor.</h5>
                <p class="text-muted">Kampanyaları takip edin, yakında yeni kuponlar gelecek!</p>
            </div>

            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($kuponlar as $k):
                    $kalanGun   = (int) ceil((strtotime($k['son_tarih']) - time()) / 86400);
                    $acilMi     = $kalanGun <= 3; // 3 gün kaldıysa uyar
                    $aktifMi    = ($aktifKupon === $k['kod']);
                ?>
                <div class="col-md-6">
                    <div class="card bento-card p-4 border-0 shadow-sm position-relative"
                         style="border: 2px dashed <?php echo $aktifMi ? '#28a745' : '#ddd'; ?> !important;
                                background: <?php echo $aktifMi ? '#f0fff4' : '#fff'; ?>;">

                        <!-- Aktif rozeti -->
                        <?php if ($aktifMi): ?>
                        <span class="position-absolute top-0 end-0 m-2 badge bg-success">
                            ✅ Sepette Aktif
                        </span>
                        <?php endif; ?>

                        <!-- Kupon Kodu -->
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="fw-bold text-rose mb-0">
                                <?php echo htmlspecialchars($k['kod']); ?>
                            </h3>
                            <button class="btn btn-sm btn-outline-secondary rounded-pill kopya-btn"
                                    data-kod="<?php echo htmlspecialchars($k['kod']); ?>"
                                    title="Kopyala">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>

                        <!-- İndirim Tutarı -->
                        <p class="fw-bold mb-1 fs-5">
                            %<?php echo $k['indirim_tutari']; ?> İndirim
                        </p>

                        <!-- Açıklama (varsa) -->
                        <?php if (!empty($k['aciklama'])): ?>
                        <p class="text-muted small mb-2">
                            <?php echo htmlspecialchars($k['aciklama']); ?>
                        </p>
                        <?php endif; ?>

                        <!-- Geçerlilik -->
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <small class="text-muted">
                                Son Geçerlilik: <?php echo date('d.m.Y', strtotime($k['son_tarih'])); ?>
                            </small>
                            <?php if ($acilMi): ?>
                            <span class="badge bg-danger">Son <?php echo $kalanGun; ?> gün!</span>
                            <?php endif; ?>
                        </div>

                        <!-- Sepete Git Butonu -->
                        <?php if (!$aktifMi): ?>
                        <a href="sepetim.php" class="btn btn-sm btn-outline-dark rounded-pill mt-3">
                            Sepette Kullan →
                        </a>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /col-lg-9 -->
    </div>
</div>

<!-- Kopyala JS -->
<script>
document.querySelectorAll('.kopya-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var kod = btn.dataset.kod;
        navigator.clipboard.writeText(kod).then(function() {
            btn.innerHTML = '<i class="fa fa-check"></i>';
            btn.classList.replace('btn-outline-secondary', 'btn-success');
            setTimeout(function() {
                btn.innerHTML = '<i class="fa fa-copy"></i>';
                btn.classList.replace('btn-success', 'btn-outline-secondary');
            }, 1500);
        });
    });
});
</script>

<style>
    .text-rose { color: #e91e63; }
</style>

<?php include 'includes/footer.php'; ?>