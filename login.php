<?php 
$sayfaBaslik = "Giriş Yap";
include 'includes/header.php'; // Veritabanı ve session_start burada zaten var

if ($_POST) {
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];

    // Madde 3: Prepared Statements (SQL Injection koruması)
    $sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE eposta = ?");
    $sorgu->execute([$email]);
    $user = $sorgu->fetch();

    // Madde 5: Şifre kontrolü (password_verify kullanılır)
    if ($user && password_verify($sifre, $user['sifre'])) {
        
        // Oturum bilgilerini kaydediyoruz
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['ad_soyad'] = $user['ad_soyad'];
        $_SESSION['eposta'] = $user['eposta'];
        $_SESSION['rol_id'] = $user['rol_id'];

        // Madde 18: Log kaydı (Basit hali)
        $db->prepare("INSERT INTO loglar (mesaj, ip_adresi) VALUES (?, ?)")
           ->execute([$user['ad_soyad']." giriş yaptı.", $_SERVER['REMOTE_ADDR']]);

        // Madde 9: Admin ise admin paneline, değilse ana sayfaya yönlendir
        if ($user['rol_id'] == 1) {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $hata = "E-posta veya şifre hatalı!";
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4 bg-white p-5 shadow rounded">
            <h3 class="text-center mb-4">Giriş Yap</h3>
            
            <?php if(isset($hata)): ?>
                <div class="alert alert-danger"><?php echo $hata; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label>E-posta Adresi</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Şifre</label>
                    <input type="password" name="sifre" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>

                <div class="text-center mt-4">
                 <p class="small text-muted">Hesabınız yok mu? <a href="kayit.php" class="text-decoration-none fw-bold text-primary">Hemen Üye Ol</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>