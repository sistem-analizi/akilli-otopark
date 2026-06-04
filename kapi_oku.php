<?php
include 'baglanti.php';

// cihaz_kontrol tablosundaki 1 numaralı satırı (ana durumu) çek
$veri = $conn->query("SELECT * FROM cihaz_kontrol WHERE id=1")->fetch_assoc();

// ESP32'nin kolayca parçalayabilmesi için verileri araya virgül koyarak yazdırıyoruz
// Sıralama: ana_kapi, slot1, slot2, slot3, slot4
echo $veri['ana_kapi'] . "," . $veri['slot1'] . "," . $veri['slot2'] . "," . $veri['slot3'] . "," . $veri['slot4'];
?>