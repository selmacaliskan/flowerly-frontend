<?php
require_once 'auth_kontrol.php'; // Yetki kontrolü

if (isset($_POST['sirala'])) {
    $sira_listesi = $_POST['sirala']; // [id, id, id] şeklinde dizi gelir
    
    foreach ($sira_listesi as $index => $id) {
        $yeni_sira = $index + 1;
        $sorgu = $db->prepare("UPDATE menu SET sira = ? WHERE id = ?");
        $sorgu->execute([$yeni_sira, $id]);
    }
    echo json_encode(['durum' => 'basarili']);
    exit;
}
?>