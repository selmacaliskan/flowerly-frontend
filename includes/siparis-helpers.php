<?php
// =============================================
//  siparis-helpers.php
//  Sipariş sayfasına ait yardımcı fonksiyonlar
// =============================================

/**
 * Kullanıcıya ait tüm siparişleri özet bilgileriyle çeker.
 * Tablo: siparisler (id, user_id, toplam_fiyat, tarih, durum)
 *        siparis_icerik (id, siparis_id, urun_ad, adet, fiyat)
 */
function getSiparisler(PDO $db, int $userId): array
{
    $stmt = $db->prepare("
        SELECT
            s.id,
            s.toplam_fiyat,
            s.tarih,
            s.durum,
            COUNT(si.id) AS urun_sayisi
        FROM siparisler s
        LEFT JOIN siparis_icerik si ON si.siparis_id = s.id
        WHERE s.user_id = :uid
        GROUP BY s.id, s.toplam_fiyat, s.tarih, s.durum
        ORDER BY s.tarih DESC
    ");
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Belirli bir siparişe ait ürün satırlarını çeker.
 * siparis_icerik kolonları: id, siparis_id, urun_ad, adet, fiyat
 */
function getSiparisUrunler(PDO $db, int $siparisId, int $limit = 3): array
{
    $stmt = $db->prepare("
        SELECT
            urun_ad,
            adet,
            fiyat,
            (adet * fiyat) AS satir_toplam
        FROM siparis_icerik
        WHERE siparis_id = :sid
        ORDER BY id ASC
        LIMIT :lim
    ");
    $stmt->bindValue(':sid', $siparisId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit,     PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Sipariş özetini döner: toplam sipariş, teslim, aktif, toplam harcama.
 */
function getSiparisStat(array $siparisler): array
{
    $toplam  = count($siparisler);
    $harcama = 0.0;
    $teslim  = 0;
    $aktif   = 0;

    foreach ($siparisler as $s) {
        $harcama += (float) $s['toplam_fiyat'];
        if ($s['durum'] === 'Teslim Edildi') $teslim++;
        if (in_array($s['durum'], ['Hazırlanıyor', 'Kargoda'], true)) $aktif++;
    }

    return compact('toplam', 'harcama', 'teslim', 'aktif');
}

/**
 * Duruma göre CSS sınıfı ve ikon döner.
 * match() yerine switch kullanıldı — PHP 7 uyumlu.
 */
function durumBilgi(string $durum): array
{
    switch ($durum) {
        case 'Teslim Edildi': return ['css' => 'durum-teslim',       'ikon' => '✅'];
        case 'Hazırlanıyor':  return ['css' => 'durum-hazirlaniyor', 'ikon' => '🌸'];
        case 'Kargoda':       return ['css' => 'durum-kargoda',      'ikon' => '🚚'];
        case 'İptal Edildi':  return ['css' => 'durum-iptal',        'ikon' => '❌'];
        default:              return ['css' => 'durum-bekliyor',     'ikon' => '🕐'];
    }
}

/**
 * Takip çubuğu için aktif adım indeksini döner (0–3).
 */
function takipAdimi(string $durum): int
{
    switch ($durum) {
        case 'Hazırlanıyor':  return 1;
        case 'Kargoda':       return 2;
        case 'Teslim Edildi': return 3;
        default:              return 0;
    }
}

/**
 * Sipariş ID'sini sıfır dolgulu stringe çevirir: 42 → #00042
 */
function siparisNo(int $id): string
{
    return '#' . str_pad($id, 5, '0', STR_PAD_LEFT);
}

/**
 * Tarihi Türkçe okunabilir formata çevirir.
 */
function formatTarih(string $tarih): string
{
    $aylar = [
        1  => 'Oca', 2  => 'Şub', 3  => 'Mar', 4  => 'Nis',
        5  => 'May', 6  => 'Haz', 7  => 'Tem', 8  => 'Ağu',
        9  => 'Eyl', 10 => 'Eki', 11 => 'Kas', 12 => 'Ara',
    ];
    $ts  = strtotime($tarih);
    $gun = date('d', $ts);
    $ay  = $aylar[(int) date('n', $ts)];
    $yil = date('Y', $ts);
    $saa = date('H:i', $ts);
    return "{$gun} {$ay} {$yil}, {$saa}";
}