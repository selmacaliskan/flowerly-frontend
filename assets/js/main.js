/**
 * FLOWERLY - MERKEZİ JAVASCRIPT DOSYASI
 * Tüm AJAX ve UI işlemleri burada toplanmıştır.
 */

$(document).ready(function() {

    // ==========================================
    // 1. CANLI ARAMA (AJAX)
    // ==========================================
    $('#ajaxSearchInput').on('keyup', function() {
        let query = $(this).val();
        let suggestionBox = $('#searchSuggestions');

        if (query.length >= 2) {
            $.ajax({
                url: 'ajax_islem.php',
                method: 'GET',
                data: { action: 'arama', q: query },
                success: function(data) {
                    suggestionBox.empty();
                    if (data.length > 0) {
                        data.forEach(item => {
                            suggestionBox.append(`
                                <a href="urun_detay.php?id=${item.id}" class="suggestion-item">
                                    <img src="assets/img/${item.resim}">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">${item.ad}</span>
                                        <span class="price">₺${item.fiyat}</span>
                                    </div>
                                </a>`);
                        });
                        suggestionBox.removeClass('d-none');
                    } else { suggestionBox.addClass('d-none'); }
                }
            });
        } else { suggestionBox.addClass('d-none'); }
    });

    // ==========================================
    // 2. FAVORİ İŞLEMLERİ (AJAX)
    // ==========================================
    $(document).on('click', '.fav-btn', function(e) {
        e.preventDefault();
        let btn = $(this);
        let icon = btn.find('.heart-icon');

        $.ajax({
            url: 'ajax_islem.php',
            method: 'POST',
            data: { action: 'favori', urun_id: btn.data('id') },
            success: function(response) {
                if (response.durum === 'basarili') {
                    if (response.islem === 'eklendi') {
                        icon.text('❤️').css('filter', 'none');
                    } else {
                        icon.text('🤍').css('filter', 'grayscale(100%) opacity(0.3)');
                    }
                } else {
                    alert(response.mesaj);
                    if(response.mesaj.includes("Giriş")) window.location.href = 'login.php';
                }
            }
        });
    });

    // ==========================================
    // 3. SEPET İŞLEMLERİ (AJAX)
    // ==========================================
    
    // Sepete Ekle
    $(document).on('click', '.sepete-ekle-btn', function(e) {
        e.preventDefault();
        let urunId = $(this).data('id');

        $.ajax({
            url: 'ajax_islem.php',
            method: 'POST',
            data: { action: 'sepete_ekle', urun_id: urunId },
            success: function(response) {
                if (response.durum === 'basarili') {
                    $('#sepetBadge').text(response.toplam).fadeIn();
                    showToast("Ürün sepete eklendi! 🧺");
                }
            }
        });
           if (response.durum === 'basarili') {
             $('#sepetBadge').text(response.toplam).show(); // Badge'i güncelle ve görünür yap
             showToast("Ürün sepete eklendi! 🧺");
         } 
    });

    // Sepet Güncelle (Artır/Azalt/Sil)
    $(document).on('click', '.sepet-islem', function() {
        $.ajax({
            url: 'ajax_islem.php',
            method: 'POST',
            data: { 
                action: 'sepet_guncelle', 
                urun_id: $(this).data('id'), 
                islem: $(this).data('islem') 
            },
            success: function() { location.reload(); }
        });
    });

    // ==========================================
    // 4. GENEL UI FONKSİYONLARI
    // ==========================================
    
    // Toast Mesaj Gösterici (Madde 19)
    window.showToast = function(msg) {
        const box = $('<div class="alert-toast">' + msg + '</div>');
        $('body').append(box);
        box.fadeIn().delay(2000).fadeOut(function() { $(this).remove(); });
    };

    // Güvenli Çıkış Onayı
    window.confirmLogout = function() {
        if (confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
            window.location.href = 'logout.php';
        }
    };

    // Arama kutusu dışında bir yere basınca önerileri kapat
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-section').length) {
            $('#searchSuggestions').addClass('d-none');
        }
    });

});