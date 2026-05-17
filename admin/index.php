<?php 
require_once 'auth_kontrol.php';
$sayfa = "dashboard";

$uSayisi     = $db->query("SELECT COUNT(*) FROM urunler")->fetchColumn();
$kSayisi     = $db->query("SELECT COUNT(*) FROM kullanicilar")->fetchColumn();
$sSayisi     = $db->query("SELECT COUNT(*) FROM siparisler")->fetchColumn();
$toplamGelir = $db->query("SELECT SUM(toplam_fiyat) FROM siparisler WHERE durum != 'İptal Edildi'")->fetchColumn() ?: 0;
$bekleyenYorum = $db->query("SELECT COUNT(*) FROM yorumlar WHERE onaylandi = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Flowerly - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .chart-card { background: #fff; border-radius: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,.07); padding: 1.5rem; }
        .grafik-loading { display:flex; align-items:center; justify-content:center; height:250px; color:#aaa; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="fa-solid fa-chart-pie me-2"></i> Genel Durum</h2>
            <div class="d-flex align-items-center gap-3">
                <a href="../index.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-globe"></i> Siteyi Gör
                </a>
                <?php if($bekleyenYorum > 0): ?>
                <a href="yorumlar.php" class="btn btn-warning btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-star me-1"></i> <?php echo $bekleyenYorum; ?> Bekleyen Yorum
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- İstatistik Kartları -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card p-4 rounded-4 shadow-sm border-0 border-start border-primary border-5">
                    <div class="icon mb-2">🌸</div>
                    <h2 class="fw-bold mb-0"><?php echo $uSayisi; ?></h2>
                    <small class="text-muted text-uppercase fw-bold">Toplam Ürün</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4 rounded-4 shadow-sm border-0 border-start border-info border-5">
                    <div class="icon mb-2">👥</div>
                    <h2 class="fw-bold mb-0"><?php echo $kSayisi; ?></h2>
                    <small class="text-muted text-uppercase fw-bold">Üye Sayısı</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4 rounded-4 shadow-sm border-0 border-start border-warning border-5">
                    <div class="icon mb-2">📦</div>
                    <h2 class="fw-bold mb-0"><?php echo $sSayisi; ?></h2>
                    <small class="text-muted text-uppercase fw-bold">Sipariş Sayısı</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4 rounded-4 shadow-sm border-0 border-start border-success border-5">
                    <div class="icon mb-2">💰</div>
                    <h2 class="fw-bold mb-0">₺<?php echo number_format($toplamGelir, 0, ',', '.'); ?></h2>
                    <small class="text-muted text-uppercase fw-bold">Toplam Gelir</small>
                </div>
            </div>
        </div>

        <!-- GRAFİKLER -->
        <div class="row g-4 mb-4">
            <!-- Haftalık Satış Grafiği -->
            <div class="col-md-8">
                <div class="chart-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">📈 Haftalık Satışlar (Son 7 Gün)</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" id="btnTutar" onclick="gosterTutar()">Tutar</button>
                            <button class="btn btn-outline-primary" id="btnAdet" onclick="gosterAdet()">Adet</button>
                        </div>
                    </div>
                    <div class="grafik-loading" id="haftalikLoading">
                        <span><i class="fa fa-spinner fa-spin me-2"></i>Veriler yükleniyor...</span>
                    </div>
                    <canvas id="haftalikGrafik" style="display:none;"></canvas>
                </div>
            </div>

            <!-- Sipariş Durumu Pasta -->
            <div class="col-md-4">
                <div class="chart-card h-100">
                    <h5 class="fw-bold mb-3">🥧 Sipariş Dağılımı</h5>
                    <div class="grafik-loading" id="durumLoading">
                        <span><i class="fa fa-spinner fa-spin me-2"></i>Yükleniyor...</span>
                    </div>
                    <canvas id="durumGrafik" style="display:none; max-height:220px;"></canvas>
                    <div id="durumLegend" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- En Çok Satanlar + Hızlı İşlemler -->
        <div class="row g-4">
            <div class="col-md-8">
                <div class="chart-card">
                    <h5 class="fw-bold mb-3">🏆 En Çok Satılan Ürünler</h5>
                    <div class="grafik-loading" id="urunLoading">
                        <span><i class="fa fa-spinner fa-spin me-2"></i>Yükleniyor...</span>
                    </div>
                    <canvas id="urunGrafik" style="display:none; max-height:220px;"></canvas>
                </div>
            </div>

            <div class="col-md-4">
                <div class="chart-card h-100">
                    <h5 class="fw-bold mb-4">⚡ Hızlı İşlemler</h5>
                    <div class="row g-2">
                        <?php if($_SESSION['rol_id']==1||$_SESSION['rol_id']==2): ?>
                        <div class="col-6">
                            <a href="urun-ekle.php" class="btn btn-light w-100 py-3 rounded-4">
                                <i class="fa fa-plus text-primary d-block fs-4"></i>
                                <small>Ürün Ekle</small>
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if($_SESSION['rol_id']==1||$_SESSION['rol_id']==3): ?>
                        <div class="col-6">
                            <a href="siparisler.php" class="btn btn-light w-100 py-3 rounded-4">
                                <i class="fa fa-shopping-cart text-warning d-block fs-4"></i>
                                <small>Siparişler</small>
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if($_SESSION['rol_id']==1): ?>
                        <div class="col-6">
                            <a href="uyeler.php" class="btn btn-light w-100 py-3 rounded-4">
                                <i class="fa fa-users text-info d-block fs-4"></i>
                                <small>Üyeler</small>
                            </a>
                        </div>
                        <?php endif; ?>
                        <div class="col-6">
                            <a href="yorumlar.php" class="btn btn-light w-100 py-3 rounded-4">
                                <i class="fa fa-star text-warning d-block fs-4"></i>
                                <small>Yorumlar</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let haftalikChart = null;
let haftalikData  = null;
let mod = 'tutar';

// Haftalık Grafik
fetch('ajax/grafik_veri.php?tip=haftalik')
    .then(r => r.json())
    .then(d => {
        haftalikData = d;
        document.getElementById('haftalikLoading').style.display = 'none';
        const canvas = document.getElementById('haftalikGrafik');
        canvas.style.display = 'block';
        haftalikChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: d.etiketler,
                datasets: [{
                    label: 'Satış Tutarı (₺)',
                    data: d.tutarlar,
                    backgroundColor: 'rgba(214,51,132,0.25)',
                    borderColor: '#d63384',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    });

function gosterTutar() {
    if (!haftalikChart) return;
    haftalikChart.data.datasets[0].label = 'Satış Tutarı (₺)';
    haftalikChart.data.datasets[0].data  = haftalikData.tutarlar;
    haftalikChart.update();
    document.getElementById('btnTutar').classList.add('active');
    document.getElementById('btnAdet').classList.remove('active');
}
function gosterAdet() {
    if (!haftalikChart) return;
    haftalikChart.data.datasets[0].label = 'Sipariş Adedi';
    haftalikChart.data.datasets[0].data  = haftalikData.adetler;
    haftalikChart.update();
    document.getElementById('btnAdet').classList.add('active');
    document.getElementById('btnTutar').classList.remove('active');
}

// Durum Pasta Grafiği
fetch('ajax/grafik_veri.php?tip=durum')
    .then(r => r.json())
    .then(d => {
        document.getElementById('durumLoading').style.display = 'none';
        const canvas = document.getElementById('durumGrafik');
        canvas.style.display = 'block';
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: d.etiketler,
                datasets: [{ data: d.sayilar, backgroundColor: d.renkler, borderWidth: 2 }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } },
                cutout: '65%'
            }
        });
    });

// En Çok Satanlar
fetch('ajax/grafik_veri.php?tip=urunler')
    .then(r => r.json())
    .then(d => {
        document.getElementById('urunLoading').style.display = 'none';
        const canvas = document.getElementById('urunGrafik');
        canvas.style.display = 'block';
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: d.etiketler,
                datasets: [{
                    label: 'Satış Adedi',
                    data: d.adetler,
                    backgroundColor: ['#d63384','#0dcaf0','#ffc107','#198754','#6f42c1'],
                    borderRadius: 8,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid: { color:'#f0f0f0' } }, y: { grid: { display:false } } }
            }
        });
    });
</script>
</body>
</html>
