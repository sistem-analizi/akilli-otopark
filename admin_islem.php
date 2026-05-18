<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['admin_yap'])) {
    $id = (int)$_GET['admin_yap'];
    $sql = "UPDATE uyeler SET rol = 'admin' WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_paneli.php?basari=Kullaniciya admin yetkisi verildi.");
    } else {
        die("Hata: " . $conn->error);
    }
    exit;
}

if (isset($_GET['uye_sil'])) {
    $id = (int)$_GET['uye_sil'];
    $sql = "DELETE FROM uyeler WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_paneli.php?basari=Kullanici basariyla silindi.");
    } else {
        die("Hata: " . $conn->error);
    }
    exit;
}

if (isset($_GET['rez_iptal'])) {
    $id = (int)$_GET['rez_iptal'];
    $sql = "UPDATE rezervasyonlar SET durum = 'iptal' WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_paneli.php?basari=Rezervasyon iptal edildi.");
    } else {
        die("Hata: " . $conn->error);
    }
    exit;
}

if (isset($_POST['ayarlari_kaydet'])) {
    $taban_fiyat = (int)$_POST['taban_fiyat'];
    $s1 = isset($_POST['s1_satis']) ? 1 : 0;
    $s2 = isset($_POST['s2_satis']) ? 1 : 0;
    $s3 = isset($_POST['s3_satis']) ? 1 : 0;
    $s4 = isset($_POST['s4_satis']) ? 1 : 0;

    $sql = "UPDATE cihaz_kontrol SET 
            taban_fiyat = $taban_fiyat, 
            slot1_satis = $s1, 
            slot2_satis = $s2, 
            slot3_satis = $s3, 
            slot4_satis = $s4 
            WHERE id = 1";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin_paneli.php?basari=Sistem ayarlari kaydedildi.");
    } else {
        die("Hata: " . $conn->error);
    }
    exit;
}

if (isset($_POST['mod_degistir'])) {
    $kapi = $conn->real_escape_string($_POST['kapi_adi']);
    $mod = (int)$_POST['mod'];
    
    $sql = "UPDATE cihaz_kontrol SET {$kapi}_mod = $mod WHERE id = 1";
    if ($conn->query($sql) === TRUE) {
        echo "OK";
    } else {
        echo "Hata: " . $conn->error;
    }
    exit;
}

if (isset($_POST['kapi_kontrol'])) {
    $kapi = $conn->real_escape_string($_POST['kapi_adi']);
    $durum = (int)$_POST['durum'];

    $sql = "UPDATE cihaz_kontrol SET $kapi = $durum WHERE id = 1";
    if ($conn->query($sql) === TRUE) {
        echo "OK";
    } else {
        echo "Hata: " . $conn->error;
    }
    exit;
}

if (isset($_GET['slot_detay'])) {
    $slot = $conn->real_escape_string($_GET['slot_detay']);
    
    $sql = "SELECT r.*, u.isim, u.soyisim, u.telefon, a.plaka, a.marka, a.model, a.renk 
            FROM rezervasyonlar r 
            JOIN uyeler u ON r.uye_id = u.id 
            JOIN araclar a ON r.arac_id = a.id 
            WHERE r.slot_adi = '$slot' AND r.durum = 'aktif' 
            LIMIT 1";
            
    $sonuc = $conn->query($sql);
    
    if ($sonuc && $sonuc->num_rows > 0) {
        $bilgi = $sonuc->fetch_assoc();
        echo json_encode(["durum" => "bulundu", "bilgi" => $bilgi]);
    } else {
        echo json_encode(["durum" => "yok"]);
    }
    exit;
}

header("Location: admin_paneli.php");
exit;
?>