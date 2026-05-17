<?php
require_once 'includes/db.php';
session_start();

header('Content-Type: application/json');

/* =========================================================
   GİRİŞ KONTROLÜ
   ========================================================= */
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['durum' => 'hata', 'mesaj' => 'Sipariş vermek için giriş yapmalısınız!']);
    exit();
}

/* =========================================================
   SEPET VERİSİ (SESSION'DAN — güvenli)
   JavaScript'ten fiyat almıyoruz; DB'den doğruluyoruz.
   ========================================================= */
if (empty($_SESSION['sepet'])) {
    echo json_encode(['durum' => 'hata', 'mesaj' => 'Sepetiniz boş!']);
    exit();
}

$sepet = $_SESSION['sepet']; // [ urun_id => adet ]
$ids   = array_keys($sepet);
$in    = str_repeat('?,', count($ids) - 1) . '?';

/* DB'den güncel fiyat ve stok bilgisini çek */
$urunSorgu = $db->prepare("SELECT * FROM urunler WHERE id IN ($in) AND aktif = 1");
$urunSorgu->execute($ids);
$urunler   = $urunSorgu->fetchAll(PDO::FETCH_ASSOC);

if (empty($urunler)) {
    echo json_encode(['durum' => 'hata', 'mesaj' => 'Sepetteki ürünler bulunamadı!']);
    exit();
}

/* Stok kontrolü */
foreach ($urunler as $u) {
    $adet = $sepet[$u['id']];
    if (isset($u['stok']) && $u['stok'] < $adet) {
        echo json_encode([
            'durum' => 'hata',
            'mesaj' => '"' . $u['ad'] . '" ürünü için yeterli stok yok. Mevcut stok: ' . $u['stok']
        ]);
        exit();
    }
}

/* =========================================================
   TOPLAM HESAPLA (sunucu tarafında)
   ========================================================= */
$urunMap   = array_column($urunler, null, 'id');
$araToplam = 0;

foreach ($sepet as $urunId => $adet) {
    if (isset($urunMap[$urunId])) {
        $araToplam += $urunMap[$urunId]['fiyat'] * $adet;
    }
}

/* =========================================================
   KUPON İNDİRİMİ
   ========================================================= */
$indirim    = 0;
$kuponKodu  = $_SESSION['kupon'] ?? null;

if ($kuponKodu) {
    $kuponSorgu = $db->prepare(
        "SELECT * FROM kuponlar WHERE kod = ? AND aktif = 1 AND son_tarih >= CURDATE()"
    );
    $kuponSorgu->execute([$kuponKodu]);
    $kupon = $kuponSorgu->fetch(PDO::FETCH_ASSOC);

    if ($kupon) {
        $indirim = ($araToplam * $kupon['indirim_tutari']) / 100;
    } else {
        /* Geçersiz kupon varsa session'dan temizle */
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

    /* 1. Sipariş ana kaydı */
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

    /* 2. Sipariş içeriği — siparis_icerik tablosunda urun_id sütunu YOK */
    $icerikSorgu = $db->prepare(
        "INSERT INTO siparis_icerik (siparis_id, urun_ad, adet, fiyat)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($sepet as $urunId => $adet) {
        if (!isset($urunMap[$urunId])) continue;
        $u = $urunMap[$urunId];
        $icerikSorgu->execute([$siparisId, $u['ad'], $adet, $u['fiyat']]);
    }

    /* 3. Stok düşür (varsa stok sütunu) */
    $stokGuncelle = $db->prepare(
        "UPDATE urunler SET stok = stok - ? WHERE id = ? AND stok >= ?"
    );
    foreach ($sepet as $urunId => $adet) {
        if (isset($urunMap[$urunId])) {
            $stokGuncelle->execute([$adet, $urunId, $adet]);
        }
    }

    $db->commit();

    /* Başarılıysa sepet ve kuponu temizle */
    unset($_SESSION['sepet']);
    unset($_SESSION['kupon']);

    echo json_encode([
        'durum'      => 'basarili',
        'mesaj'      => 'Siparişiniz başarıyla alındı! 🌸',
        'siparis_id' => $siparisId
    ]);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'durum' => 'hata',
        'mesaj' => 'Bir sorun oluştu, lütfen tekrar deneyin.' 
        // Canlıda $e->getMessage() gösterme — log'a yaz
    ]);
    error_log('siparis_ver.php hatası: ' . $e->getMessage());
}
?>