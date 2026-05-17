<?php 
$sayfaBaslik = "Destek Merkezi";
include 'includes/header.php'; 

// Giriş kontrolü (Madde 9)
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$hata = "";
$basarili = "";

if ($_POST) {
    $ad = $_SESSION['ad_soyad'];
    $eposta = $_POST['eposta']; // Formdan gelen e-posta
    $konu = htmlspecialchars($_POST['konu']);
    $mesaj = htmlspecialchars($_POST['mesaj']);

    if(empty($mesaj)) {
        $hata = "Lütfen mesajınızı yazın.";
    } else {
        // Madde 3: Prepared Statements
        $sorgu = $db->prepare("INSERT INTO mesajlar (ad_soyad, eposta, konu, mesaj, okundu) VALUES (?, ?, ?, ?, 0)");
        $sonuc = $sorgu->execute([$ad, $eposta, $konu, $mesaj]);

        if($sonuc) {
            $basarili = "Mesajınız başarıyla iletildi! En kısa sürede e-posta adresinize dönüş yapacağız.";
        } else {
            $hata = "Bir sistem hatası oluştu, lütfen tekrar deneyin.";
        }
    }
}
?>
<link rel="stylesheet" href="assets/css/user-panel.css">

<div class="container mt-5 py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bento-card border-0 shadow-lg p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="fs-1">💬</div>
                    <h2 class="fw-bold welcome-text">Nasıl Yardımcı Olabiliriz?</h2>
                    <p class="text-muted small">Sorularını bize ilet, Flowerly ekibi hemen çözsün.</p>
                </div>

                <?php if($hata): ?> <div class="alert alert-danger rounded-4 border-0"><?php echo $hata; ?></div> <?php endif; ?>
                <?php if($basarili): ?> <div class="alert alert-success rounded-4 border-0"><?php echo $basarili; ?></div> <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">E-posta Adresiniz</label>
                        <input type="email" name="eposta" class="form-control rounded-4 bg-light border-0 py-2" 
                               value="<?php echo $_SESSION['eposta']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Konu</label>
                        <select name="konu" class="form-select rounded-4 bg-light border-0 py-2">
                            <option>Sipariş Durumu</option>
                            <option>İade ve Değişim</option>
                            <option>Ödeme Sorunları</option>
                            <option>Ürün Şikayeti</option>
                            <option>Diğer</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Mesajınız</label>
                        <textarea name="mesaj" class="form-control rounded-4 bg-light border-0 py-2" rows="5" placeholder="Mesajınızı buraya detaylıca yazın..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-rose-pill w-100 py-3 fw-bold">Mesajı Gönder</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>