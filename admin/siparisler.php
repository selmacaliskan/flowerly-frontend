<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "siparisler";

// AJAX: Durum güncelleme
if (isset($_POST['ajax_durum']) && isset($_POST['siparis_id'])) {
    header('Content-Type: application/json');
    $sid     = (int)$_POST['siparis_id'];
    $durum   = $_POST['durum'];
    $kargo   = trim($_POST['kargo_kodu'] ?? '');
    $not     = trim($_POST['admin_notu'] ?? '');
    $guncelle = $db->prepare("UPDATE siparisler SET durum=?, kargo_kodu=?, admin_notu=? WHERE id=?");
    $guncelle->execute([$durum, $kargo ?: null, $not ?: null, $sid]);
    echo json_encode(['basarili' => true, 'durum' => $durum]);
    exit();
}

// AJAX: Sipariş detay
if (isset($_GET['detay_id'])) {
    header('Content-Type: application/json');
    $sid = (int)$_GET['detay_id'];
    $siparis = $db->prepare("SELECT s.*, k.ad_soyad, k.eposta FROM siparisler s JOIN kullanicilar k ON s.user_id=k.id WHERE s.id=?");
    $siparis->execute([$sid]);
    $s = $siparis->fetch(PDO::FETCH_ASSOC);
    $urunler = $db->prepare("SELECT * FROM siparis_icerik WHERE siparis_id=?");
    $urunler->execute([$sid]);
    $s['urunler'] = $urunler->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($s);
    exit();
}

// Filtreleme
$filtre_durum = $_GET['durum'] ?? '';
$filtre_arama = trim($_GET['ara'] ?? '');
$sql = "SELECT s.*, k.ad_soyad, k.eposta FROM siparisler s JOIN kullanicilar k ON s.user_id=k.id WHERE 1=1";
$params = [];
if ($filtre_durum) { $sql .= " AND s.durum = ?"; $params[] = $filtre_durum; }
if ($filtre_arama) { $sql .= " AND (k.ad_soyad LIKE ? OR k.eposta LIKE ? OR s.id LIKE ?)"; $params[] = "%$filtre_arama%"; $params[] = "%$filtre_arama%"; $params[] = "%$filtre_arama%"; }
$sql .= " ORDER BY s.tarih DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$siparisler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// İstatistikler
$istatistik = $db->query("SELECT durum, COUNT(*) as sayi, SUM(toplam_fiyat) as toplam FROM siparisler GROUP BY durum")->fetchAll(PDO::FETCH_ASSOC);
$ist_map = [];
foreach ($istatistik as $i) $ist_map[$i['durum']] = $i;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Yönetimi - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .durum-karti { border-radius: 1rem; padding: 1rem 1.2rem; cursor: pointer; transition: all .2s; border: 2px solid transparent; }
        .durum-karti:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
        .durum-karti.aktif { border-color: currentColor; }
        .badge-pill { border-radius: 2rem; padding: .4rem 1rem; font-size: .78rem; font-weight: 600; }
    </style>
</head>
<body class="admin-body">
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-truck-fast me-2 text-primary"></i> Sipariş Yönetimi</h2>
                <small class="text-muted">Toplam <?php echo count($siparisler); ?> sipariş listeleniyor.</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>
        </div>

        <!-- Durum Özet Kartları -->
        <div class="row g-3 mb-4">
            <?php
            $durumlar = ['Hazırlanıyor'=>['bg'=>'#fff3cd','ic'=>'#856404','ikon'=>'fa-clock'], 'Yolda'=>['bg'=>'#cff4fc','ic'=>'#055160','ikon'=>'fa-truck'], 'Teslim Edildi'=>['bg'=>'#d1e7dd','ic'=>'#0f5132','ikon'=>'fa-check-circle'], 'İptal Edildi'=>['bg'=>'#f8d7da','ic'=>'#842029','ikon'=>'fa-times-circle']];
            foreach ($durumlar as $d => $stil): 
                $sayi   = $ist_map[$d]['sayi'] ?? 0;
                $tutar  = $ist_map[$d]['toplam'] ?? 0;
                $aktif  = ($filtre_durum == $d) ? 'aktif' : '';
            ?>
            <div class="col-md-3">
                <a href="?durum=<?php echo urlencode($d); ?>" class="text-decoration-none">
                    <div class="durum-karti <?php echo $aktif; ?>" style="background:<?php echo $stil['bg']; ?>; color:<?php echo $stil['ic']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold fs-5"><?php echo $sayi; ?></div>
                                <div class="small fw-semibold"><?php echo $d; ?></div>
                                <div class="small opacity-75">₺<?php echo number_format($tutar,0,',','.'); ?></div>
                            </div>
                            <i class="fa-solid <?php echo $stil['ikon']; ?> fa-2x opacity-40"></i>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Arama & Filtre Çubuğu -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
                <div class="input-group" style="max-width:320px;">
                    <span class="input-group-text border-0 bg-light"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" name="ara" value="<?php echo htmlspecialchars($filtre_arama); ?>" 
                           class="form-control border-0 bg-light" placeholder="Ad, e-posta veya sipariş no...">
                </div>
                <select name="durum" class="form-select border-0 bg-light" style="width:180px;">
                    <option value="">Tüm Durumlar</option>
                    <?php foreach(array_keys($durumlar) as $d): ?>
                    <option value="<?php echo $d; ?>" <?php echo $filtre_durum==$d?'selected':''; ?>><?php echo $d; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="fa fa-filter me-1"></i> Filtrele
                </button>
                <?php if ($filtre_durum || $filtre_arama): ?>
                <a href="siparisler.php" class="btn btn-outline-secondary rounded-pill px-3">Temizle</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Sipariş Tablosu -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Sipariş No</th>
                            <th>Müşteri</th>
                            <th>Tarih</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th>Kargo Kodu</th>
                            <th class="text-end pe-4">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($siparisler as $s): 
                            $renk = "secondary";
                            if($s['durum']=='Hazırlanıyor') $renk="warning";
                            if($s['durum']=='Yolda') $renk="info";
                            if($s['durum']=='Teslim Edildi') $renk="success";
                            if($s['durum']=='İptal Edildi') $renk="danger";
                        ?>
                        <tr>
                            <td class="ps-4">
                                <strong class="text-primary">#<?php echo $s['id']; ?></strong>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($s['ad_soyad']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($s['eposta']); ?></small>
                            </td>
                            <td>
                                <div><?php echo date('d.m.Y', strtotime($s['tarih'])); ?></div>
                                <small class="text-muted"><?php echo date('H:i', strtotime($s['tarih'])); ?></small>
                            </td>
                            <td class="fw-bold text-primary">
                                <?php echo number_format($s['toplam_fiyat'],2,',','.'); ?> TL
                            </td>
                            <td>
                                <span class="badge badge-pill bg-<?php echo $renk; ?>-light text-<?php echo $renk; ?>">
                                    <?php echo $s['durum']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($s['kargo_kodu']): ?>
                                    <code class="small"><?php echo htmlspecialchars($s['kargo_kodu']); ?></code>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1"
                                        onclick="siparisDetay(<?php echo $s['id']; ?>)">
                                    <i class="fa fa-eye me-1"></i> Detay
                                </button>
                                <button class="btn btn-sm btn-outline-warning rounded-pill px-3"
                                        onclick="durumModal(<?php echo $s['id']; ?>, '<?php echo addslashes($s['durum']); ?>', '<?php echo addslashes($s['kargo_kodu']??''); ?>', '<?php echo addslashes($s['admin_notu']??''); ?>')">
                                    <i class="fa fa-edit me-1"></i> Güncelle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($siparisler)==0): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa fa-box-open fa-2x mb-2 d-block opacity-30"></i>
                            Sipariş bulunamadı.
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sipariş Detay Modal -->
<div class="modal fade" id="detayModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="detayBaslik">Sipariş Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detayIcerik">
                <div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Durum Güncelleme Modal -->
<div class="modal fade" id="durumModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Sipariş Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_siparis_id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Sipariş Durumu</label>
                    <select id="modal_durum" class="form-select rounded-3">
                        <option value="Hazırlanıyor">⏳ Hazırlanıyor</option>
                        <option value="Yolda">🚚 Yolda</option>
                        <option value="Teslim Edildi">✅ Teslim Edildi</option>
                        <option value="İptal Edildi">❌ İptal Edildi</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kargo Takip Kodu</label>
                    <input type="text" id="modal_kargo" class="form-control rounded-3" placeholder="Opsiyonel">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Admin Notu</label>
                    <textarea id="modal_not" class="form-control rounded-3" rows="3" placeholder="Dahili not..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">İptal</button>
                <button class="btn btn-primary rounded-pill px-4" id="durumKaydetBtn" onclick="durumKaydet()">
                    <i class="fa fa-save me-1"></i> Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const detayModalEl = new bootstrap.Modal(document.getElementById('detayModal'));
const durumModalEl = new bootstrap.Modal(document.getElementById('durumModal'));

function siparisDetay(id) {
    document.getElementById('detayBaslik').textContent = 'Sipariş #' + id;
    document.getElementById('detayIcerik').innerHTML = '<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>';
    detayModalEl.show();

    fetch('siparisler.php?detay_id=' + id)
        .then(r => r.json())
        .then(s => {
            const durumRenk = {
                'Hazırlanıyor': 'warning', 'Yolda': 'info',
                'Teslim Edildi': 'success', 'İptal Edildi': 'danger'
            };
            const renk = durumRenk[s.durum] || 'secondary';
            let urunSatirlar = (s.urunler || []).map(u =>
                `<tr>
                    <td>${u.urun_ad}</td>
                    <td class="text-center">${u.adet}</td>
                    <td class="text-end fw-bold">₺${parseFloat(u.fiyat).toFixed(2).replace('.',',')}</td>
                    <td class="text-end text-primary fw-bold">₺${(u.adet * u.fiyat).toFixed(2).replace('.',',')}</td>
                </tr>`
            ).join('');

            document.getElementById('detayIcerik').innerHTML = `
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-3">
                            <div class="small text-muted mb-1">Müşteri</div>
                            <div class="fw-bold">${s.ad_soyad}</div>
                            <div class="small text-muted">${s.eposta}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-3">
                            <div class="small text-muted mb-1">Sipariş Bilgisi</div>
                            <div class="fw-bold">₺${parseFloat(s.toplam_fiyat).toFixed(2).replace('.',',')}</div>
                            <div class="small">${new Date(s.tarih).toLocaleDateString('tr-TR')}</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="badge bg-${renk}-light text-${renk} rounded-pill px-3 py-2 me-2">${s.durum}</span>
                    ${s.kargo_kodu ? `<code class="small">Kargo: ${s.kargo_kodu}</code>` : ''}
                </div>
                <table class="table table-sm table-bordered rounded-3 overflow-hidden">
                    <thead class="bg-light">
                        <tr><th>Ürün</th><th class="text-center">Adet</th><th class="text-end">Birim Fiyat</th><th class="text-end">Toplam</th></tr>
                    </thead>
                    <tbody>${urunSatirlar}</tbody>
                    <tfoot>
                        <tr><td colspan="3" class="fw-bold text-end">Genel Toplam</td>
                        <td class="fw-bold text-primary text-end">₺${parseFloat(s.toplam_fiyat).toFixed(2).replace('.',',')}</td></tr>
                    </tfoot>
                </table>
                ${s.admin_notu ? `<div class="alert alert-light border rounded-3 mt-3"><small class="text-muted">📝 Admin Notu:</small><p class="mb-0">${s.admin_notu}</p></div>` : ''}
            `;
        });
}

function durumModal(id, durum, kargo, not) {
    document.getElementById('modal_siparis_id').value = id;
    document.getElementById('modal_durum').value = durum;
    document.getElementById('modal_kargo').value = kargo;
    document.getElementById('modal_not').value = not;
    durumModalEl.show();
}

function durumKaydet() {
    const btn = document.getElementById('durumKaydetBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Kaydediliyor...';

    const fd = new FormData();
    fd.append('ajax_durum', '1');
    fd.append('siparis_id', document.getElementById('modal_siparis_id').value);
    fd.append('durum', document.getElementById('modal_durum').value);
    fd.append('kargo_kodu', document.getElementById('modal_kargo').value);
    fd.append('admin_notu', document.getElementById('modal_not').value);

    fetch('siparisler.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            durumModalEl.hide();
            location.reload();
        });
}
</script>
</body>
</html>
