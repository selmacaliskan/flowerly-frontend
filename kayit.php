<?php 
$sayfaBaslik = "Üye Ol";
include 'includes/header.php'; // Veritabanı bağlantısı burada zaten var

if ($_POST) {
    $ad_soyad = $_POST['ad_soyad'];
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];
    $rol_id = 4; // Madde 7: Yeni üyeler varsayılan olarak "Kullanıcı" rolünde başlar (Rol ID: 4)

    // Kontroller
    if ($sifre != $sifre_tekrar) {
        $hata = "Şifreler birbiriyle eşleşmiyor!";
    } else {
        // Madde 3: E-posta adresi daha önce kullanılmış mı kontrol et (Prepared Statement)
        $kontrol = $db->prepare("SELECT id FROM kullanicilar WHERE eposta = ?");
        $kontrol->execute([$email]);
        
        if ($kontrol->rowCount() > 0) {
            $hata = "Bu e-posta adresi zaten kayıtlı!";
        } else {
            // Madde 5: Şifreyi Hashleme (Geri döndürülemez şekilde şifrele)
            $yeni_sifre = password_hash($sifre, PASSWORD_DEFAULT);

            // Veritabanına Kaydet
            $ekle = $db->prepare("INSERT INTO kullanicilar (rol_id, ad_soyad, eposta, sifre) VALUES (?, ?, ?, ?)");
            $sonuc = $ekle->execute([$rol_id, $ad_soyad, $email, $yeni_sifre]);

            if ($sonuc) {
                // Madde 19: Başarılı mesajı ve yönlendirme
                echo "<script>alert('Üyeliğiniz başarıyla oluşturuldu! Giriş yapabilirsiniz.'); window.location.href='login.php';</script>";
                exit();
            } else {
                $hata = "Kayıt sırasında bir hata oluştu.";
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5 bg-white p-5 shadow rounded border-top border-primary border-4">
            <h3 class="text-center mb-4 fw-bold">🌸 Yeni Üyelik</h3>
            
            <?php if(isset($hata)): ?>
                <div class="alert alert-danger"><?php echo $hata; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Ad Soyad</label>
                    <input type="text" name="ad_soyad" class="form-control" placeholder="Adınız ve Soyadınız" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-posta Adresi</label>
                    <input type="email" name="email" class="form-control" placeholder="ornek@mail.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="sifre" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Şifre Tekrar</label>
                    <input type="password" name="sifre_tekrar" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="kvkk" required>
                    <label class="form-check-label" for="kvkk" style="font-size:12px;">KVKK ve kullanım sözleşmesini kabul ediyorum.</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Hesap Oluştur</button>
                
                <div class="text-center mt-4">
                    <p class="small text-muted">Zaten hesabınız var mı? <a href="login.php" class="text-decoration-none fw-bold">Giriş Yap</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>