<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rol_id'], [1,2,3])) {
    http_response_code(403);
    echo json_encode(['hata' => 'Yetkisiz']);
    exit();
}

header('Content-Type: application/json');

$tip = $_GET['tip'] ?? 'haftalik';

if ($tip === 'haftalik') {
    // Son 7 günün günlük sipariş tutarları
    $sorgu = $db->query("
        SELECT 
            DATE(tarih) as gun,
            COUNT(*) as adet,
            COALESCE(SUM(toplam_fiyat), 0) as toplam
        FROM siparisler
        WHERE tarih >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND durum != 'İptal Edildi'
        GROUP BY DATE(tarih)
        ORDER BY gun ASC
    ");
    $veriler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    // Son 7 günü boş da olsa doldur
    $gunler = [];
    for ($i = 6; $i >= 0; $i--) {
        $gun = date('Y-m-d', strtotime("-$i days"));
        $gunler[$gun] = ['gun' => $gun, 'adet' => 0, 'toplam' => 0];
    }
    foreach ($veriler as $v) {
        if (isset($gunler[$v['gun']])) {
            $gunler[$v['gun']] = $v;
        }
    }

    $etiketler = [];
    $tutarlar  = [];
    $adetler   = [];
    foreach ($gunler as $g) {
        $etiketler[] = date('d M', strtotime($g['gun']));
        $tutarlar[]  = (float)$g['toplam'];
        $adetler[]   = (int)$g['adet'];
    }

    echo json_encode([
        'etiketler' => $etiketler,
        'tutarlar'  => $tutarlar,
        'adetler'   => $adetler,
    ]);

} elseif ($tip === 'durum') {
    // Sipariş durumlarına göre dağılım
    $sorgu = $db->query("SELECT durum, COUNT(*) as sayi FROM siparisler GROUP BY durum");
    $rows = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    $etiketler = [];
    $sayilar   = [];
    $renkler   = [];
    $renk_map  = [
        'Hazırlanıyor' => '#ffc107',
        'Yolda'        => '#0dcaf0',
        'Teslim Edildi'=> '#198754',
        'İptal Edildi' => '#dc3545',
    ];
    foreach ($rows as $r) {
        $etiketler[] = $r['durum'];
        $sayilar[]   = (int)$r['sayi'];
        $renkler[]   = $renk_map[$r['durum']] ?? '#6c757d';
    }

    echo json_encode([
        'etiketler' => $etiketler,
        'sayilar'   => $sayilar,
        'renkler'   => $renkler,
    ]);

} elseif ($tip === 'urunler') {
    // En çok satılan 5 ürün
    $sorgu = $db->query("
        SELECT si.urun_ad, SUM(si.adet) as toplam_adet, SUM(si.adet * si.fiyat) as ciro
        FROM siparis_icerik si
        JOIN siparisler s ON si.siparis_id = s.id
        WHERE s.durum != 'İptal Edildi'
        GROUP BY si.urun_ad
        ORDER BY toplam_adet DESC
        LIMIT 5
    ");
    $rows = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    $etiketler = [];
    $adetler   = [];
    $cirolar   = [];
    foreach ($rows as $r) {
        $etiketler[] = $r['urun_ad'];
        $adetler[]   = (int)$r['toplam_adet'];
        $cirolar[]   = (float)$r['ciro'];
    }

    echo json_encode([
        'etiketler' => $etiketler,
        'adetler'   => $adetler,
        'cirolar'   => $cirolar,
    ]);
}
?>
