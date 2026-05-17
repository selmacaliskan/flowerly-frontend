<?php
require_once 'auth_kontrol.php'; // Güvenlik kontrolü

// Madde 17: CSV Dışa Aktarma
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=urunler_rapor.csv');

// Çıktı akışını aç
$output = fopen('php://output', 'w');

// Sütun başlıklarını yaz (Türkçe karakterler için UTF-8 BOM ekleyelim)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($output, array('ID', 'Kategori ID', 'Ürün Adı', 'Fiyat (TL)', 'Durum'));

// Verileri çek ve dosyaya ekle
$urunler = $db->query("SELECT id, kategori_id, ad, fiyat, aktif FROM urunler")->fetchAll(PDO::FETCH_ASSOC);

foreach ($urunler as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>