<?php 
require_once 'auth_kontrol.php'; 
$sayfa = "menu"; 

// --- İŞLEM: Kaydet (Ekle veya Güncelle) ---
if (isset($_POST['menu_kaydet'])) {
    $baslik   = $_POST['baslik'];
    $url      = $_POST['url'];
    $sira     = (int)$_POST['sira'];
    $ozel_tur = $_POST['ozel_tur'];
    $id       = $_POST['menu_id'];

    if (!empty($id)) {
        $sorgu = $db->prepare("UPDATE menu SET baslik = ?, url = ?, sira = ?, ozel_tur = ? WHERE id = ?");
        $sorgu->execute([$baslik, $url, $sira, $ozel_tur, $id]);
        $mesaj = "guncellendi";
    } else {
        $sorgu = $db->prepare("INSERT INTO menu (baslik, url, sira, ozel_tur) VALUES (?, ?, ?, ?)");
        $sorgu->execute([$baslik, $url, $sira, $ozel_tur]);
        $mesaj = "eklendi";
    }
    header("Location: menu.php?mesaj=$mesaj");
    exit();
}

// --- İŞLEM: Silme ---
if (isset($_GET['sil'])) {
    $db->prepare("DELETE FROM menu WHERE id = ?")->execute([(int)$_GET['sil']]);
    header("Location: menu.php?mesaj=silindi");
    exit();
}

// --- HAZIRLIK: Düzenleme Modu ---
$duzenle = null;
if (isset($_GET['duzenle'])) {
    $sorgu = $db->prepare("SELECT * FROM menu WHERE id = ?");
    $sorgu->execute([(int)$_GET['duzenle']]);
    $duzenle = $sorgu->fetch(PDO::FETCH_ASSOC);
}

$menuler = $db->query("SELECT * FROM menu ORDER BY sira ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Menü Yönetimi - Flowerly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .sortable-handle { cursor: move; color: #ccc; margin-right: 10px; }
        .ui-sortable-helper { background: #fff !important; display: table !important; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .ui-sortable-placeholder { visibility: visible !important; background: #f8f9fa !important; border: 2px dashed #ddd !important; height: 50px; }
        .menu-row:hover { background-color: #fcfcfc; }
        .badge-type { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body class="admin-body">

<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-bars-staggered me-2 text-primary"></i> Menü Yönetimi</h2>
                <small class="text-muted">Sürükleyerek sıralayabilir, türlerini değiştirerek özel ikonlar atayabilirsiniz.</small>
            </div>
        </div>

        <?php if(isset($_GET['mesaj'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">İşlem başarıyla tamamlandı.</div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- SOL: Menü Listesi (Sürükle-Bırak Aktif) -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">#</th>
                                    <th>Menü Başlığı & Türü</th>
                                    <th>Yönlendirme (URL)</th>
                                    <th class="text-end pe-4">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-menu">
                                <?php foreach($menuler as $m): ?>
                                <tr class="menu-row" data-id="<?php echo $m['id']; ?>">
                                    <td class="ps-4">
                                        <i class="fa-solid fa-grip-vertical sortable-handle"></i>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo $m['baslik']; ?></div>
                                        <span class="badge bg-light text-muted border badge-type"><?php echo $m['ozel_tur']; ?></span>
                                    </td>
                                    <td><code class="bg-light p-1 rounded small"><?php echo $m['url']; ?></code></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3">
                                            <a href="?duzenle=<?php echo $m['id']; ?>" class="btn btn-white btn-sm px-3"><i class="fa-solid fa-pen-to-square text-info"></i></a>
                                            <a href="?sil=<?php echo $m['id']; ?>" class="btn btn-white btn-sm px-3" onclick="return confirm('Silinsin mi?')"><i class="fa-solid fa-trash text-danger"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-light-subtle border-top">
                        <small class="text-muted"><i class="fa-solid fa-info-circle me-1"></i> Sıralamayı değiştirmek için satırları sürükleyin. Değişiklik anında kaydedilir.</small>
                    </div>
                </div>
            </div>

            <!-- SAĞ: Ekleme & Düzenleme Formu -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 <?php echo $duzenle ? 'border-start border-warning border-5' : 'border-start border-primary border-5'; ?>">
                    <h5 class="fw-bold mb-4"><?php echo $duzenle ? '📝 Menü Güncelle' : '➕ Yeni Menü Ekle'; ?></h5>
                    <form method="POST">
                        <input type="hidden" name="menu_id" value="<?php echo $duzenle['id'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Menü Adı</label>
                            <input type="text" name="baslik" class="form-control rounded-3" value="<?php echo $duzenle['baslik'] ?? ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Link (URL)</label>
                            <input type="text" name="url" class="form-control rounded-3" value="<?php echo $duzenle['url'] ?? ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Menü Türü (Davranış)</label>
                            <select name="ozel_tur" class="form-select rounded-3">
                                <option value="standart">Standart Yazı & Link</option>
                                <option value="sepet">Sepet İkonu</option>
                                <option value="uyelik">Üyelik (Giriş / Hesabım)</option>
                                <option value="kategoriler">Kategoriler Açılır Menüsü</option>
                                <option value="admin_panel">Yönetim Paneli Butonu</option>
                           </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Görünüm Sırası</label>
                            <input type="number" name="sira" class="form-control rounded-3" value="<?php echo $duzenle['sira'] ?? '0'; ?>">
                        </div>

                        <button type="submit" name="menu_kaydet" class="btn <?php echo $duzenle ? 'btn-warning' : 'btn-primary'; ?> w-100 rounded-pill py-2 fw-bold shadow-sm">
                            <?php echo $duzenle ? 'Güncelle' : 'Menüye Ekle'; ?>
                        </button>
                        <?php if($duzenle): ?>
                            <a href="menu.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted small">Vazgeç</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sürükle Bırak İçin Gerekli Kütüphaneler -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function() {
    // Sürükle bırak özelliğini başlat
    $("#sortable-menu").sortable({
        handle: ".sortable-handle",
        update: function(event, ui) {
            // Yeni sıralamayı dizi olarak al
            let order = [];
            $('.menu-row').each(function() {
                order.push($(this).data('id'));
            });

            // AJAX ile backend'e gönder
            $.ajax({
                url: 'ajax_menu_sirala.php',
                method: 'POST',
                data: { sirala: order },
                success: function(res) {
                    console.log("Sıralama güncellendi");
                }
            });
        }
    });
});
</script>
</body>
</html>