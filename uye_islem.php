<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'uye') { header("Location: index.php"); exit; }
$uye_id = $_SESSION['kullanici_id'];

if (isset($_POST['profil_guncelle'])) {
    $isim = $conn->real_escape_string($_POST['isim']); $soyisim = $conn->real_escape_string($_POST['soyisim']); $telefon = $conn->real_escape_string($_POST['telefon']);
    $conn->query("UPDATE uyeler SET isim='$isim', soyisim='$soyisim', telefon='$telefon' WHERE id=$uye_id");
    $_SESSION['isim'] = $isim; $_SESSION['soyisim'] = $soyisim;
    header("Location: uye_paneli.php?basari=Profil güncellendi."); exit;
}

if (isset($_POST['sifre_guncelle'])) {
    $yeni_sifre = md5($_POST['yeni_sifre']); $conn->query("UPDATE uyeler SET sifre='$yeni_sifre' WHERE id=$uye_id");
    header("Location: uye_paneli.php?basari=Şifre değişti."); exit;
}

if (isset($_POST['arac_ekle'])) {
    $marka = $conn->real_escape_string($_POST['marka']); $model = $conn->real_escape_string($_POST['model']); $yil = (int)$_POST['yil']; $renk = $conn->real_escape_string($_POST['renk']); $plaka = strtoupper($conn->real_escape_string($_POST['plaka']));
    $conn->query("INSERT INTO araclar (uye_id, marka, model, yil, renk, plaka) VALUES ($uye_id, '$marka', '$model', $yil, '$renk', '$plaka')");
    header("Location: uye_paneli.php?basari=Araç garaja eklendi."); exit;
}

if (isset($_GET['arac_sil'])) {
    $arac_id = (int)$_GET['arac_sil']; $conn->query("DELETE FROM araclar WHERE id=$arac_id AND uye_id=$uye_id");
    header("Location: uye_paneli.php?basari=Araç silindi."); exit;
}

// 1. KULLANICIYA ÖZEL KAPI KONTROLÜ (Aç/Kapat)
if (isset($_POST['uye_kapi_kontrol'])) {
    error_reporting(0); 
    $kapi = $conn->real_escape_string($_POST['kapi_adi']);
    $durum = (int)$_POST['durum'];
    
    $kontrol_sql = "SELECT id FROM rezervasyonlar WHERE slot_adi='$kapi' AND uye_id=$uye_id AND durum='aktif'";
    
    if ($conn->query($kontrol_sql)->num_rows > 0) {
        $conn->query("UPDATE cihaz_kontrol SET $kapi = $durum WHERE id=1");
        echo "OK";
    } else {
        echo "HATA: Rezervasyon süreniz bitmiş veya bu kapıda yetkiniz yok.";
    }
    exit;
}

// 2. YENİ: REZERVASYONU ERKEN BİTİRME (Çıkış Yapma)
if (isset($_POST['rezervasyon_bitir'])) {
    error_reporting(0);
    $kapi = $conn->real_escape_string($_POST['kapi_adi']);

    // Kullanıcının gerçekten aktif rezervasyonu var mı?
    $kontrol_sql = "SELECT id FROM rezervasyonlar WHERE slot_adi='$kapi' AND uye_id=$uye_id AND durum='aktif'";
    $sonuc = $conn->query($kontrol_sql);

    if ($sonuc->num_rows > 0) {
        $rez = $sonuc->fetch_assoc();
        $rez_id = $rez['id'];

        // Rezervasyonu tamamlandı olarak işaretle (Bitiş saatini şu anki anlık saate çek)
        $conn->query("UPDATE rezervasyonlar SET durum='tamamlandi', bitis_saati=DATETIME('now', 'localtime') WHERE id=$rez_id");

        // Sistemi Otonom Moda Al (1) ve Kapıyı Kapat (0)
        $kolon_mod = $kapi . "_mod";
        $conn->query("UPDATE cihaz_kontrol SET $kolon_mod = 1, $kapi = 0 WHERE id=1");

        echo "OK";
    } else {
        echo "HATA: İptal edilecek aktif bir rezervasyon bulunamadı.";
    }
    exit;
}

if (isset($_POST['rezervasyon_yap'])) {
    $arac_id = (int)$_POST['arac_id']; $slot_adi = $conn->real_escape_string($_POST['slot_adi']); $sure = (int)$_POST['sure_saat'];
    $baslangic = date('Y-m-d H:i:s'); $bitis = date('Y-m-d H:i:s', strtotime("+$sure hours"));
    
    // YENİ DİNAMİK FİYAT HESABI
    $veri = $conn->query("SELECT * FROM cihaz_kontrol WHERE id=1")->fetch_assoc();
    $taban_fiyat = (int)$veri['taban_fiyat'];
    if ($taban_fiyat <= 0) $taban_fiyat = 20;

    $rez_sonuc = $conn->query("SELECT slot_adi FROM rezervasyonlar WHERE durum='aktif'");
    $rezerve_slotlar = [];
    while($r = $rez_sonuc->fetch_assoc()) $rezerve_slotlar[] = $r['slot_adi'];

    $dolu_sayisi = 0;
    if($veri['slot1'] == 1 || in_array('slot1', $rezerve_slotlar)) $dolu_sayisi++;
    if($veri['slot2'] == 1 || in_array('slot2', $rezerve_slotlar)) $dolu_sayisi++;
    if($veri['slot3'] == 1 || in_array('slot3', $rezerve_slotlar)) $dolu_sayisi++;
    if($veri['slot4'] == 1 || in_array('slot4', $rezerve_slotlar)) $dolu_sayisi++;

    $yuzde = ($dolu_sayisi / 4) * 100;
    $fiyat = $taban_fiyat; 
    if($yuzde > 25 && $yuzde <= 50) $fiyat = $taban_fiyat * 1.5; 
    elseif($yuzde > 55 && $yuzde <= 75) $fiyat = $taban_fiyat * 2; 
    elseif($yuzde > 75) $fiyat = $taban_fiyat * 3;
    
    $toplam_tutar = $sure * round($fiyat);

    $cakisma = $conn->query("SELECT id FROM rezervasyonlar WHERE slot_adi='$slot_adi' AND durum='aktif' AND (('$baslangic' BETWEEN baslangic_saati AND bitis_saati) OR ('$bitis' BETWEEN baslangic_saati AND bitis_saati))");

    if ($cakisma->num_rows > 0) {
        header("Location: uye_paneli.php?hata=Bu slot zaten rezerve edilmiş durumda!");
    } else {
        $conn->query("INSERT INTO rezervasyonlar (uye_id, arac_id, slot_adi, baslangic_saati, bitis_saati, toplam_tutar, durum) VALUES ($uye_id, $arac_id, '$slot_adi', '$baslangic', '$bitis', $toplam_tutar, 'aktif')");
        $conn->query("UPDATE cihaz_kontrol SET {$slot_adi}_mod = 0, {$slot_adi} = 0 WHERE id=1");
        header("Location: uye_paneli.php?basari=Rezervasyon başarılı! Slot size özel olarak kilitlendi.");
    }
    exit;
}
?>