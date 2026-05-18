<?php
error_reporting(0);
include 'baglanti.php';

if(isset($_GET['s_ana'])) {
    $s_ana = (int)$_GET['s_ana']; $s_s1 = (int)$_GET['s_s1']; $s_s2 = (int)$_GET['s_s2']; $s_s3 = (int)$_GET['s_s3']; $s_s4 = (int)$_GET['s_s4'];
    $kontrol = $conn->query("SELECT * FROM cihaz_kontrol WHERE id=1")->fetch_assoc();
    $update_sql = "UPDATE cihaz_kontrol SET ";
    if($kontrol['ana_kapi_mod'] == 1) $update_sql .= "ana_kapi=$s_ana, ";
    if($kontrol['slot1_mod'] == 1) $update_sql .= "slot1=$s_s1, ";
    if($kontrol['slot2_mod'] == 1) $update_sql .= "slot2=$s_s2, ";
    if($kontrol['slot3_mod'] == 1) $update_sql .= "slot3=$s_s3, ";
    if($kontrol['slot4_mod'] == 1) $update_sql .= "slot4=$s_s4, ";
    $update_sql = rtrim($update_sql, ", ");
    if ($update_sql != "UPDATE cihaz_kontrol SET") { $update_sql .= " WHERE id=1"; $conn->query($update_sql); }
}

$guncel = $conn->query("SELECT ana_kapi, slot1, slot2, slot3, slot4, taban_fiyat FROM cihaz_kontrol WHERE id=1")->fetch_assoc();
$rez_sql = "SELECT slot_adi FROM rezervasyonlar WHERE durum='aktif'";
$rez_sonuc = $conn->query($rez_sql);
$rezerve_slotlar = [];
while($r = $rez_sonuc->fetch_assoc()) { $rezerve_slotlar[] = $r['slot_adi']; }

$dolu_sayisi = 0;
if($guncel['slot1'] == 1 || in_array('slot1', $rezerve_slotlar)) $dolu_sayisi++;
if($guncel['slot2'] == 1 || in_array('slot2', $rezerve_slotlar)) $dolu_sayisi++;
if($guncel['slot3'] == 1 || in_array('slot3', $rezerve_slotlar)) $dolu_sayisi++;
if($guncel['slot4'] == 1 || in_array('slot4', $rezerve_slotlar)) $dolu_sayisi++;

$bos_yer = 4 - $dolu_sayisi;

// YENİ: ADMİN TABAN FİYATINA GÖRE DİNAMİK HESAPLAMA
$taban_fiyat = (int)$guncel['taban_fiyat'];
if ($taban_fiyat <= 0) $taban_fiyat = 20; // Güvenlik önlemi

$yuzde = ($dolu_sayisi / 4) * 100;
$fiyat = $taban_fiyat; 
if($yuzde > 25 && $yuzde <= 50) $fiyat = $taban_fiyat * 1.5; 
elseif($yuzde > 50 && $yuzde <= 75) $fiyat = $taban_fiyat * 2; 
elseif($yuzde > 75) $fiyat = $taban_fiyat * 3;
$fiyat = round($fiyat); // Küsuratları yuvarla

echo $guncel['ana_kapi'] . "," . $guncel['slot1'] . "," . $guncel['slot2'] . "," . $guncel['slot3'] . "," . $guncel['slot4'] . "," . $bos_yer . "," . $fiyat;
$conn->close();
?>