<?php
include 'baglanti.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sistem Modunu Değiştirme (0 = Manuel, 1 = Otomatik)
    if (isset($_POST['mod_degistir'])) {
        $yeni_mod = (int)$_POST['mod_durumu'];
        $sql = "UPDATE cihaz_kontrol SET mod_durumu = $yeni_mod WHERE id=1";
        $conn->query($sql);
        echo "Mod başarıyla güncellendi.";
    }

    // Manuel Moddayken Kapıları Kontrol Etme
    if (isset($_POST['kapi_kontrol_et'])) {
        $kapi_adi = $_POST['kapi_adi']; // 'ana_kapi', 'slot1', vb.
        $durum = (int)$_POST['durum'];  // 1 = Aç, 0 = Kapat
        
        // Sadece geçerli sütun isimlerine izin ver (Güvenlik için)
        $gecerli_kapilar = ['ana_kapi', 'slot1', 'slot2', 'slot3', 'slot4'];
        
        if (in_array($kapi_adi, $gecerli_kapilar)) {
            $sql = "UPDATE cihaz_kontrol SET $kapi_adi = $durum WHERE id=1";
            $conn->query($sql);
            echo "$kapi_adi durumu $durum olarak güncellendi.";
        }
    }
}

$conn->close();
?>