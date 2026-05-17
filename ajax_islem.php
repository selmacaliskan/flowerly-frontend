<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['durum' => 'hata', 'mesaj' => 'Geçersiz işlem!'];

switch ($action) {
    // --- CANLI ARAMA ---
    case 'arama':
        $kelime = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($kelime) >= 2) {
            $sorgu = $db->prepare("SELECT id, ad, fiyat, resim FROM urunler WHERE ad LIKE ? AND aktif = 1 LIMIT 5");
            $sorgu->execute(["%$kelime%"]);
            echo json_encode($sorgu->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }
        break;

    // --- FAVORİ EKLE/SİL ---
    case 'favori':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['durum' => 'hata', 'mesaj' => 'Giriş yapmalısınız!']);
            exit;
        }
        $urun_id = (int)$_POST['urun_id'];
        $user_id = $_SESSION['user_id'];
        
        $check = $db->prepare("SELECT id FROM favoriler WHERE user_id = ? AND urun_id = ?");
        $check->execute([$user_id, $urun_id]);
        
        if ($check->rowCount() > 0) {
            $db->prepare("DELETE FROM favoriler WHERE user_id = ? AND urun_id = ?")->execute([$user_id, $urun_id]);
            echo json_encode(['durum' => 'basarili', 'islem' => 'silindi']);
        } else {
            $db->prepare("INSERT INTO favoriler (user_id, urun_id) VALUES (?, ?)")->execute([$user_id, $urun_id]);
            echo json_encode(['durum' => 'basarili', 'islem' => 'eklendi']);
        }
        exit;

    // --- (GELECEKTE) SEPETE EKLEME ---
     case 'sepete_ekle':
        $urun_id = (int)$_POST['urun_id'];
        
        // Sepet dizisini başlat
        if (!isset($_SESSION['sepet'])) {
            $_SESSION['sepet'] = [];
        }

        // Eğer ürün zaten varsa adedini artır, yoksa 1 adet ekle
        if (isset($_SESSION['sepet'][$urun_id])) {
            $_SESSION['sepet'][$urun_id]++;
        } else {
            $_SESSION['sepet'][$urun_id] = 1;
        }

        // Toplam ürün sayısını hesapla (Badge için)
        $toplam_adet = array_sum($_SESSION['sepet']);
        
        echo json_encode(['durum' => 'basarili', 'toplam' => $toplam_adet]);
        exit;

    case 'sepet_guncelle':
        $urun_id = (int)$_POST['urun_id'];
        $islem = $_POST['islem']; // 'artir', 'azalt' veya 'sil'

        if ($islem == 'artir') {
            $_SESSION['sepet'][$urun_id]++;
        } elseif ($islem == 'azalt') {
            $_SESSION['sepet'][$urun_id]--;
            if ($_SESSION['sepet'][$urun_id] < 1) unset($_SESSION['sepet'][$urun_id]);
        } elseif ($islem == 'sil') {
            unset($_SESSION['sepet'][$urun_id]);
        }

        echo json_encode(['durum' => 'basarili']);
        exit;
}

echo json_encode($response);