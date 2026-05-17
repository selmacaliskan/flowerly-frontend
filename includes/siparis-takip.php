<?php
// =============================================
//  includes/siparis-takip.php
//  Kullanım: $adim değişkeni tanımlı olmalı (0–3)
// =============================================

$takipAdimlar = [
    0 => 'Sipariş Alındı',
    1 => 'Hazırlanıyor',
    2 => 'Kargoda',
    3 => 'Teslim Edildi',
];
?>
<div class="sp-takip">
    <?php foreach ($takipAdimlar as $idx => $ad):
        if ($idx < $adim)        $cls = 'bitti';
        elseif ($idx === $adim)  $cls = 'aktif';
        else                     $cls = '';
    ?>
    <div class="sp-takip-adim <?= $cls ?>">
        <div class="sp-takip-nokta">
            <?php if ($idx < $adim): ?>
            <svg width="10" height="8" viewBox="0 0 10 8" fill="none">
                <path d="M1 4l3 3 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <?php endif; ?>
        </div>
        <div class="sp-takip-etiket"><?= $ad ?></div>
    </div>
    <?php if ($idx < 3): ?>
    <div class="sp-takip-cizgi <?= $idx < $adim ? 'bitti' : '' ?>"></div>
    <?php endif; ?>
    <?php endforeach; ?>
</div>