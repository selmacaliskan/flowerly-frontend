<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Site Ayarları ve Sepet Sayısı
$ayarlar = $db->query("SELECT * FROM ayarlar WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$sepet_adet_toplam = isset($_SESSION['sepet']) ? array_sum($_SESSION['sepet']) : 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $ayarlar['site_baslik']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>
<body>
<div class="top-bar"> Kurumsal Hediye | Yardım | <i class="fa fa-phone"></i> <?php echo $ayarlar['whatsapp_no']; ?> </div>

<div class="search-section bg-white shadow-sm sticky-top py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- LOGO -->
        <div class="logo">
            <a href="index.php"><img src="assets/img/<?php echo $ayarlar['site_logo']; ?>" style="height:50px;"></a>
        </div>

        <!-- ARAMA -->
        <div class="flex-grow-1 px-4 position-relative">
            <form action="arama.php" method="GET" autocomplete="off">
                <div class="input-group">
                    <input type="text" name="q" id="ajaxSearchInput" class="form-control rounded-pill-start" placeholder="Çiçek ara...">
                    <button class="btn btn-outline-secondary rounded-pill-end bg-white" type="submit">🔍</button>
                </div>
            </form>
            <div id="searchSuggestions" class="search-suggestions d-none shadow-lg"></div>
        </div>

        <!-- TAMAMEN DİNAMİK MENÜ (Madde 12) -->
        <div class="d-flex align-items-center gap-3">
            <?php
            $menus = $db->query("SELECT * FROM menu ORDER BY sira ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach($menus as $m) {
                
                // 1. STANDART LİNK
                if($m['ozel_tur'] == 'standart') {
                    echo '<a href="'.$m['url'].'" class="text-dark fw-bold small text-decoration-none">'.$m['baslik'].'</a>';
                }

                // 2. KATEGORİLER DROPDOWN (Artık menüde nerede istersen orada!)
                elseif($m['ozel_tur'] == 'kategoriler') { ?>
                    <div class="menu-item text-dark fw-bold small">
                        Kategoriler ▾
                        <div class="dropdown-menu-custom">
                            <?php
                            $kats = $db->query("SELECT * FROM kategoriler")->fetchAll(PDO::FETCH_ASSOC);
                            foreach($kats as $k) { echo '<a href="kategori.php?id='.$k['id'].'">'.$k['ad'].'</a>'; }
                            ?>
                        </div>
                    </div>
                <?php }

                // 3. SEPET
                elseif($m['ozel_tur'] == 'sepet') { ?>
                    <a href="sepetim.php" class="position-relative text-dark ms-1">
                        <i class="fa fa-shopping-basket fs-5"></i>
                        <span id="sepetBadge" class="sepet-badge" <?php echo ($sepet_adet_toplam == 0) ? 'style="display:none;"' : ''; ?>>
                            <?php echo $sepet_adet_toplam; ?>
                        </span>
                    </a>
                <?php }

                // 4. ÜYELİK (Düz Link - Dropdown kaldırıldı)
                elseif($m['ozel_tur'] == 'uyelik') {
                    if(isset($_SESSION['user_id'])) { ?>
                        <a href="hesabim.php" class="text-dark fw-bold small text-decoration-none">
                            <i class="fa fa-user-circle"></i> Hesabım
                        </a>
                        <a href="logout.php" class="text-danger small" title="Çıkış"><i class="fa fa-sign-out-alt"></i></a>
                    <?php } else {
                        echo '<a href="login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold">Giriş</a>';
                    }
                }

                // 5. YÖNETİM PANELİ
                elseif($m['ozel_tur'] == 'admin_panel') {
                    if(isset($_SESSION['rol_id']) && $_SESSION['rol_id'] <= 3) {
                        echo '<a href="admin/index.php" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold small shadow-sm">Yönetim</a>';
                    }
                }
            }
            ?>
        </div>
    </div>
</div>