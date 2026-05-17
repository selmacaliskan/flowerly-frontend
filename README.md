# 🌸 Flowerly — Çiçek E-Ticaret Platformu

<p align="center">
  <img src="assets/img/logo.png" alt="Flowerly Logo" width="180"/>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
  <img src="https://img.shields.io/badge/MySQL-8.4.7-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white"/>
  <img src="https://img.shields.io/badge/Apache-XAMPP-CA2136?style=for-the-badge&logo=apache&logoColor=white"/>
  <img src="https://img.shields.io/badge/Lisans-MIT-green?style=for-the-badge"/>
</p>

> Çiçek sektörüne yönelik kapsamlı bir e-ticaret ve yönetim platformu. Kullanıcılar ürünleri kolayca inceleyip sipariş verebilirken, yöneticiler tüm süreci merkezi bir admin panelinden yönetebilir.

---

## 📋 İçindekiler

- [Özellikler](#-özellikler)
- [Teknoloji Yığını](#-teknoloji-yığını)
- [Kullanıcı Rolleri](#-kullanıcı-rolleri)
- [Veritabanı Yapısı](#-veritabanı-yapısı)
- [Kurulum](#-kurulum)
- [Proje Yapısı](#-proje-yapısı)
- [Ekran Görüntüleri](#-ekran-görüntüleri)
- [Gelecek Geliştirmeler](#-gelecek-geliştirmeler)
- [Katkıda Bulunanlar](#-katkıda-bulunanlar)

---

## ✨ Özellikler

### Müşteri Tarafı
- 🛍️ Ürün listeleme, arama ve kategori/fiyat/stok filtresi
- 🛒 Session tabanlı sepet yönetimi
- 📦 Sipariş oluşturma ve takip etme
- 🎟️ İndirim kuponu uygulama
- ⭐ Ürün puanlama ve yorum yapma
- ❤️ Favorilere ürün ekleme
- 🔔 Özel gün hatırlatıcısı (doğum günü, yıl dönümü vb.)
- 🏆 Alışveriş puanı kazanma sistemi
- 👤 Profil ve adres yönetimi

### Admin Paneli
- 📊 Genel istatistik dashboard'u
- 🌷 Ürün ve kategori CRUD işlemleri
- 🚚 Sipariş durumu güncelleme (Hazırlanıyor → Kargoda → Teslim Edildi)
- 💬 Yorum onaylama / reddetme
- 🎫 Kupon oluşturma ve yönetme
- 👥 Üye yönetimi ve rol atama
- 🖼️ Slider ve menü yönetimi
- 📝 Sistem log kayıtları
- ✉️ Müşteri mesajları görüntüleme

---

## 🛠️ Teknoloji Yığını

| Katman | Teknoloji |
|--------|-----------|
| Ön Yüz | HTML5, CSS3, Bootstrap 5, JavaScript |
| Arka Yüz | PHP 8.3 |
| Veritabanı | MySQL 8.4.7 |
| Web Sunucusu | Apache (XAMPP) |
| Veritabanı Yönetimi | phpMyAdmin |
| Sürüm Kontrolü | Git & GitHub |

---

## 👥 Kullanıcı Rolleri

| Rol | Yetkiler |
|-----|----------|
| **Süper Admin** | Tüm sisteme tam erişim: ürün, kullanıcı, sipariş, kupon, menü, slider, ayarlar |
| **Editör** | Ürün/kategori ekleme-düzenleme, slider ve menü yönetimi, kupon yönetimi |
| **Moderatör** | Sipariş durumu güncelleme, yorum onaylama/reddetme, mesaj görüntüleme |
| **Müşteri** | Sipariş verme, yorum yapma, puan kazanma, favoriler, hatırlatıcı, profil yönetimi |
| **Ziyaretçi** | Ürün inceleme, sepete ekleme (sipariş için kayıt gerekli) |

---

## 🗄️ Veritabanı Yapısı

Veritabanı **17 tablo** içermektedir:

```
flowerly_db
├── kullanicilar      # Üye bilgileri ve rol ataması
├── roller            # Kullanıcı rolleri
├── rol_izinleri      # Role göre sayfa erişim izinleri
├── urunler           # Ürün bilgileri (fiyat, stok, kategori)
├── kategoriler       # Ürün kategorileri
├── siparisler        # Sipariş ana kaydı (durum, fiyat, kupon)
├── siparis_icerik    # Siparişteki ürünler (ad, adet, fiyat)
├── kuponlar          # İndirim kuponları (kod, oran, geçerlilik)
├── yorumlar          # Ürün yorumları ve puanlar (onay bekler)
├── favoriler         # Kullanıcı favori ürünleri
├── adresler          # Teslimat adresleri
├── hatirlaticilar    # Özel gün hatırlatıcıları
├── mesajlar          # İletişim formu mesajları
├── loglar            # Giriş/işlem kayıtları
├── slider            # Anasayfa slider görselleri
├── menu              # Navigasyon menü öğeleri
└── ayarlar           # Site genel ayarları (logo, iletişim vb.)
```

### Temel İlişkiler
- `kullanicilar` → `roller` (her kullanıcının bir rolü var)
- `siparisler` → `kullanicilar` + `adresler`
- `siparis_icerik` → `siparisler` (sipariş satırları)
- `urunler` → `kategoriler`
- `yorumlar` → `kullanicilar` + `urunler`
- `favoriler` → `kullanicilar` + `urunler`

---

## 🚀 Kurulum

### Gereksinimler
- [XAMPP](https://www.apachefriends.org/) (PHP 8.3+ ve MySQL 8.4+)
- Web tarayıcısı (Chrome, Firefox, Edge, Safari)

### Adımlar

**1. Repoyu klonlayın:**
```bash
git clone https://github.com/kullanici-adi/flowerly.git
```

**2. Proje dosyalarını XAMPP'ın `htdocs` klasörüne taşıyın:**
```
C:/xampp/htdocs/flowerly/
```

**3. XAMPP'tan Apache ve MySQL'i başlatın.**

**4. Veritabanını oluşturun:**
- phpMyAdmin'i açın: `http://localhost/phpmyadmin`
- `flowerly_db` adında yeni bir veritabanı oluşturun
- `flowerly_db.sql` dosyasını **İçe Aktar (Import)** edin

**5. Veritabanı bağlantısını yapılandırın:**

`includes/db.php` dosyasını açıp bilgilerinizi girin:
```php
$host = 'localhost';
$dbname = 'flowerly_db';
$username = 'root';
$password = '';  // XAMPP varsayılan: boş
```

**6. Tarayıcıdan açın:**
```
http://localhost/flowerly
```

### Varsayılan Admin Girişi
| Alan | Değer |
|------|-------|
| E-posta | `admin@flowerly.com` |
| Şifre | `admin123` *(kurulum sonrası değiştirin)* |

---

## 📁 Proje Yapısı

```
flowerly/
│
├── index.php                  # Anasayfa
├── urunler.php                # Ürün listeleme ve filtreleme
├── urun_detay.php             # Ürün detay sayfası
├── sepetim.php                # Sepet sayfası
├── siparis_tamamla.php        # Sipariş işleme
├── siparis_ver.php            # AJAX sipariş endpoint'i
├── siparis_tesekkur.php       # Sipariş onay sayfası
├── siparislerim.php           # Kullanıcı sipariş geçmişi
├── kuponlarim.php             # Kupon görüntüleme
├── login.php                  # Giriş / Kayıt
│
├── admin/                     # Admin paneli
│   ├── index.php
│   ├── urunler.php
│   ├── siparisler.php
│   ├── kullanicilar.php
│   └── ...
│
├── includes/                  # Ortak bileşenler
│   ├── db.php                 # Veritabanı bağlantısı
│   ├── header.php
│   ├── footer.php
│   ├── user-sidebar.php
│   ├── siparis-helpers.php
│   └── siparis-takip.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── img/                   # Ürün görselleri
│
└── flowerly_db.sql            # Veritabanı yedeği
```

---

## 📸 Ekran Görüntüleri

| Anasayfa | Ürünler | Admin Paneli |
|----------|---------|--------------|
| ![Anasayfa](https://github.com/user-attachments/assets/49223ba7-1be9-4707-8665-2983a3df5b9d) | ![Ürünler](https://github.com/user-attachments/assets/ea054c6d-ae2f-4564-8cf9-52c6b6dcaab3) | ![Admin](https://github.com/user-attachments/assets/5218abcf-a7e9-4bdb-b659-6b97932c3b18) |

| Sepet | Siparişlerim | Hesabım |
|-------|-------------|---------|
| ![Sepet](https://github.com/user-attachments/assets/6a3e835f-c234-4bed-8b45-7303366e8f44) | ![Siparişler](https://github.com/user-attachments/assets/7165d47e-ac49-421c-a149-0f4acfa70c0e) | ![Hesap](https://github.com/user-attachments/assets/4842a322-658f-4f2a-a8f9-7271dd584370) |

---

## 🔭 Gelecek Geliştirmeler

- [ ] 💳 Ödeme sistemi entegrasyonu (iyzico / PayTR)
- [ ] 📱 Mobil uygulama (React Native veya Flutter)
- [ ] 📧 SMS ve e-posta bildirim sistemi
- [ ] 🗺️ Google Maps API ile kargo takip entegrasyonu
- [ ] 🤖 Yapay zeka destekli ürün öneri sistemi
- [ ] 🔐 İki faktörlü kimlik doğrulama (2FA)
- [ ] 📊 Gelişmiş satış raporlama ve analitik

---

## 🏫 Proje Bilgisi

Bu proje, **Kırklareli Üniversitesi Mühendislik Fakültesi Yazılım Mühendisliği Bölümü** bünyesinde **YAZ16204 – Web Programlama II** dersi kapsamında geliştirilmiştir.

**Geliştirme Süreci:**  2025 – Mayıs 2026

---

## 🤝 Katkıda Bulunanlar

<table>
  <tr>
    <td align="center"><b>Selma ÇALIŞKAN</b></td>
    <td align="center"><b>Fatma Sude SÖNMEZ</b></td>
  </tr>
</table>

---

## 📄 Lisans

Bu proje [MIT Lisansı](LICENSE) ile lisanslanmıştır.

---

<p align="center">
  <b>🌸 Flowerly</b> — Sevdiklerinize en güzel çiçekleri gönderin.
</p>
