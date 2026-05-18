<?php
session_start();
error_reporting(0);
include 'baglanti.php';
header('Content-Type: application/json; charset=utf-8');

$aktif_uye = isset($_SESSION['kullanici_id']) ? $_SESSION['kullanici_id'] : 0;
$sql = "SELECT * FROM cihaz_kontrol WHERE id=1";
$sonuc = $conn->query($sql);

$rez_sql = "SELECT slot_adi, uye_id, bitis_saati FROM rezervasyonlar WHERE durum='aktif'";
$rez_sonuc = $conn->query($rez_sql);

$rezerve_bilgisi = ['slot1' => 'yok', 'slot2' => 'yok', 'slot3' => 'yok', 'slot4' => 'yok'];
$kalan_sureler = ['slot1' => '', 'slot2' => '', 'slot3' => '', 'slot4' => ''];
$dolu_slot_sayisi = 0; // Doluluk hesabı için

while($r = $rez_sonuc->fetch_assoc()) {
    $s_adi = $r['slot_adi'];
    if ($r['uye_id'] == $aktif_uye) {
        $rezerve_bilgisi[$s_adi] = 'benim';
        $kalan_saniye = strtotime($r['bitis_saati']) - time();
        if ($kalan_saniye > 0) {
            $saat = floor($kalan_saniye / 3600); $dakika = floor(($kalan_saniye % 3600) / 60); $saniye = $kalan_saniye % 60;
            if ($saat > 0) { $kalan_sureler[$s_adi] = sprintf("%d sa %d dk", $saat, $dakika); } 
            else { $kalan_sureler[$s_adi] = sprintf("%d dk %d sn", $dakika, $saniye); }
        } else { $kalan_sureler[$s_adi] = "SÜRE BİTTİ"; }
    } else { $rezerve_bilgisi[$s_adi] = 'baskasinin'; }
}

if($sonuc && $sonuc->num_rows > 0) {
    $veri = $sonuc->fetch_assoc();
    
    // YENİ: DİNAMİK FİYAT HESAPLAMA
    if($veri['slot1'] == 1 || $rezerve_bilgisi['slot1'] != 'yok') $dolu_slot_sayisi++;
    if($veri['slot2'] == 1 || $rezerve_bilgisi['slot2'] != 'yok') $dolu_slot_sayisi++;
    if($veri['slot3'] == 1 || $rezerve_bilgisi['slot3'] != 'yok') $dolu_slot_sayisi++;
    if($veri['slot4'] == 1 || $rezerve_bilgisi['slot4'] != 'yok') $dolu_slot_sayisi++;

    $taban_fiyat = (int)$veri['taban_fiyat'];
    if ($taban_fiyat <= 0) $taban_fiyat = 20;

    $yuzde = ($dolu_slot_sayisi / 4) * 100;
    $anlik_fiyat = $taban_fiyat; 
    if($yuzde > 25 && $yuzde <= 50) $anlik_fiyat = $taban_fiyat * 1.5; 
    elseif($yuzde > 50 && $yuzde <= 75) $anlik_fiyat = $taban_fiyat * 2; 
    elseif($yuzde > 75) $anlik_fiyat = $taban_fiyat * 3;

    echo json_encode([
        'sensorler' => ['ana_kapi' => (int)$veri['ana_kapi'], 'slot1' => (int)$veri['slot1'], 'slot2' => (int)$veri['slot2'], 'slot3' => (int)$veri['slot3'], 'slot4' => (int)$veri['slot4']],
        'rezerve' => $rezerve_bilgisi,
        'kalan_sureler' => $kalan_sureler,
        'kontrol' => $veri,
        'anlik_fiyat' => round($anlik_fiyat) // JS'ye yolluyoruz
    ]);
}
$conn->close();
?>