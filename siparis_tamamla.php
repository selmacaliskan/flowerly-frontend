<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   GİRİŞ & SEPET KONTROLÜ
   ========================================================= */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['sepet'])) {
    header('Location: sepetim.php');
    exit();
}

/* =========================================================
   SEPETİ VE FİYATLARI DB'DEN DOĞRULA
   ========================================================= */
$sepet = $_SESSION['sepet'];
$ids   = array_keys($sepet);
$in    = str_repeat('?,', count($ids) - 1) . '?';

$urunSorgu = $db->prepare("SELECT * FROM urunler WHERE id IN ($in) AND aktif = 1");
$urunSorgu->execute($ids);
$urunler   = $urunSorgu->fetchAll(PDO::FETCH_ASSOC);
$urunMap   = array_column($urunler, null, 'id');

/* Stok kontrolü */
foreach ($urunler as $u) {
    $adet = $sepet[$u['id']];
    if (isset($u['stok']) && $u['stok'] < $adet) {
        $_SESSION['hata'] = '"' . $u['ad'] . '" ürününde yeterli stok yok.';
        header('Location: sepetim.php');
        exit();
    }
}

/* =========================================================
   TOPLAM & KUPON
   ========================================================= */
$araToplam = 0;
foreach ($sepet as $urunId => $adet) {
    if (isset($urunMap[$urunId])) {
        $araToplam += $urunMap[$urunId]['fiyat'] * $adet;
    }
}

$indirim   = 0;
$kuponKodu = $_SESSION['kupon'] ?? null;
$kupon     = null;

if ($kuponKodu) {
    $kq = $db->prepare(
        "SELECT * FROM kuponlar WHERE kod = ? AND aktif = 1 AND son_tarih >= CURDATE()"
    );
    $kq->execute([$kuponKodu]);
    $kupon = $kq->fetch(PDO::FETCH_ASSOC);
    if ($kupon) {
        $indirim = ($araToplam * $kupon['indirim_tutari']) / 100;
    } else {
        unset($_SESSION['kupon']);
        $kuponKodu = null;
    }
}

$genelToplam = $araToplam - $indirim;

/* =========================================================
   VERİTABANINA KAYDET (Transaction)
   ========================================================= */
try {
    $db->beginTransaction();

    /* Sipariş ana kaydı */
    $siparisSorgu = $db->prepare(
        "INSERT INTO siparisler (user_id, toplam_fiyat, kupon_kodu, indirim_miktari, durum, tarih)
         VALUES (?, ?, ?, ?, 'Hazırlanıyor', NOW())"
    );
    $siparisSorgu->execute([
        $_SESSION['user_id'],
        $genelToplam,
        $kuponKodu,
        $indirim
    ]);
    $siparisId = $db->lastInsertId();

    /* Sipariş içeriği — siparis_icerik tablosunda urun_id sütunu YOK */
    $icerikSorgu = $db->prepare(
        "INSERT INTO siparis_icerik (siparis_id, urun_ad, adet, fiyat)
         VALUES (?, ?, ?, ?)"
    );
    foreach ($sepet as $urunId => $adet) {
        if (!isset($urunMap[$urunId])) continue;
        $u = $urunMap[$urunId];
        $icerikSorgu->execute([$siparisId, $u['ad'], $adet, $u['fiyat']]);
    }

    /* Stok düşür */
    $stokGuncelle = $db->prepare(
        "UPDATE urunler SET stok = stok - ? WHERE id = ? AND stok >= ?"
    );
    foreach ($sepet as $urunId => $adet) {
        if (isset($urunMap[$urunId])) {
            $stokGuncelle->execute([$adet, $urunId, $adet]);
        }
    }

    $db->commit();

    /* Başarı: sepet ve kuponu temizle */
    unset($_SESSION['sepet'], $_SESSION['kupon']);

    /* Teşekkür sayfasına yönlendir */
    header('Location: siparis_tesekkur.php?id=' . $siparisId);
    exit();

} catch (Exception $e) {
    $db->rollBack();
    error_log('siparis_tamamla.php hatası: ' . $e->getMessage());
    $_SESSION['hata'] = 'Sipariş işlenirken bir sorun oluştu. Lütfen tekrar deneyin.';
    header('Location: sepetim.php');
    exit();
}
?>