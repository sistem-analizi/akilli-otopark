<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'baglanti.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] == 'admin') {
    header("Location: index.php");
    exit;
}

$uye_id = $_SESSION['kullanici_id'];

if(isset($_POST['uye_kapi_kontrol'])) {
    $kapi = $conn->real_escape_string($_POST['kapi_adi']);
    $durum = (int)$_POST['durum'];
    
    $kontrol_sql = "SELECT id FROM rezervasyonlar WHERE slot_adi='$kapi' AND uye_id=$uye_id AND durum='aktif'";
    if ($conn->query($kontrol_sql)->num_rows > 0) {
        $conn->query("UPDATE cihaz_kontrol SET $kapi = $durum WHERE id=1");
        echo "OK";
    } else {
        echo "HATA";
    }
    exit;
}

if(isset($_POST['arac_ekle'])) {
    $marka = $conn->real_escape_string($_POST['marka']);
    $model = $conn->real_escape_string($_POST['model']);
    $yil = (int)$_POST['yil'];
    $renk = $conn->real_escape_string($_POST['renk']);
    $plaka = $conn->real_escape_string($_POST['plaka']);
    
    $sql = "INSERT INTO araclar (uye_id, marka, model, yil, renk, plaka) VALUES ($uye_id, '$marka', '$model', $yil, '$renk', '$plaka')";
    $conn->query($sql);
    header("Location: uye_paneli.php?basari=eklendi");
    exit;
}

if(isset($_GET['arac_sil'])) {
    $silinecek_id = (int)$_GET['arac_sil'];
    $sql = "DELETE FROM araclar WHERE id = $silinecek_id AND uye_id = $uye_id";
    $conn->query($sql);
    header("Location: uye_paneli.php?basari=arac_silindi");
    exit;
}

if(isset($_POST['rezervasyon_yap'])) {
    $slot_adi = $conn->real_escape_string($_POST['slot_adi']);
    $arac_id = (int)$_POST['arac_id'];
    $sure = (int)$_POST['sure'];
    
    $ayarlar_db = $conn->query("SELECT * FROM cihaz_kontrol WHERE id = 1")->fetch_assoc();
    $taban = isset($ayarlar_db['taban_fiyat']) ? (int)$ayarlar_db['taban_fiyat'] : 50;

    $rez_db = $conn->query("SELECT slot_adi FROM rezervasyonlar WHERE durum = 'aktif'");
    $d_say = 0;
    $r_slotlar = [];
    if($rez_db){
        while($r = $rez_db->fetch_assoc()) {
            $r_slotlar[] = $r['slot_adi'];
        }
    }

    for($i=1; $i<=4; $i++) {
        $s_a = 'slot'.$i;
        if(in_array($s_a, $r_slotlar) || (isset($ayarlar_db[$s_a]) && $ayarlar_db[$s_a] == 1)) {
            $d_say++;
        }
    }

    $yuz = ($d_say / 4) * 100;
    $h_fiyat = $taban;
    if($yuz > 25 && $yuz <= 50) $h_fiyat = $taban * 1.5;
    elseif($yuz > 50 && $yuz <= 75) $h_fiyat = $taban * 2;
    elseif($yuz > 75) $h_fiyat = $taban * 3;
    
    $toplam_tutar = $sure * round($h_fiyat);
    
    $baslangic = date('Y-m-d H:i:s');
    $bitis = date('Y-m-d H:i:s', strtotime("+$sure hours"));
    
    $sql = "INSERT INTO rezervasyonlar (uye_id, arac_id, slot_adi, durum, baslangic_saati, sure, bitis_saati, toplam_tutar, odeme_durumu) VALUES ($uye_id, $arac_id, '$slot_adi', 'aktif', '$baslangic', $sure, '$bitis', $toplam_tutar, 'odendi')";
    
    $sonuc = $conn->query($sql);
    
    if($sonuc === false) {
        die("VERİTABANI HATASI: " . $conn->error);
    }

    $conn->query("UPDATE cihaz_kontrol SET {$slot_adi}_mod = 0 WHERE id=1");

    header("Location: uye_paneli.php?basari=rezerve_edildi");
    exit;
}

if(isset($_GET['erken_cikis'])) {
    $rez_id = (int)$_GET['erken_cikis'];
    $simdi = date('Y-m-d H:i:s');

    $rez_sorgu = $conn->query("SELECT slot_adi FROM rezervasyonlar WHERE id = $rez_id AND uye_id = $uye_id");
    if($rez_sorgu->num_rows > 0) {
        $rez_bilgi = $rez_sorgu->fetch_assoc();
        $slot_ad = $rez_bilgi['slot_adi'];
        $conn->query("UPDATE cihaz_kontrol SET {$slot_ad}_mod = 1, {$slot_ad} = 0 WHERE id=1");
    }

    $sql = "UPDATE rezervasyonlar SET durum = 'tamamlandi', bitis_saati = '$simdi' WHERE id = $rez_id AND uye_id = $uye_id";
    $conn->query($sql);
    header("Location: uye_paneli.php?basari=erken_cikis");
    exit;
}

$araclar_sorgu = $conn->query("SELECT * FROM araclar WHERE uye_id = $uye_id ORDER BY id DESC");
$araclar = [];
if($araclar_sorgu) {
    while($a = $araclar_sorgu->fetch_assoc()) {
        $araclar[] = $a;
    }
}

$rezervasyon_sorgu = $conn->query("SELECT * FROM rezervasyonlar WHERE uye_id = $uye_id ORDER BY id DESC");

$aktif_rez_sorgu = $conn->query("SELECT slot_adi FROM rezervasyonlar WHERE durum = 'aktif'");
$dolu_slotlar = [];
if($aktif_rez_sorgu) {
    while($r = $aktif_rez_sorgu->fetch_assoc()) {
        $dolu_slotlar[] = $r['slot_adi'];
    }
}

$ayarlar_sorgu = $conn->query("SELECT * FROM cihaz_kontrol WHERE id = 1");
$ayarlar = $ayarlar_sorgu ? $ayarlar_sorgu->fetch_assoc() : [];

$g_dolu = count($dolu_slotlar);
for($i=1; $i<=4; $i++) {
    if(!in_array("slot$i", $dolu_slotlar) && isset($ayarlar["slot$i"]) && $ayarlar["slot$i"] == 1) {
        $g_dolu++;
    }
}

$t_fiyat = isset($ayarlar['taban_fiyat']) ? (int)$ayarlar['taban_fiyat'] : 50;
$y_doluluk = ($g_dolu / 4) * 100;
$g_saatlik = $t_fiyat;

if($y_doluluk > 25 && $y_doluluk <= 50) $g_saatlik = $t_fiyat * 1.5;
elseif($y_doluluk > 50 && $y_doluluk <= 75) $g_saatlik = $t_fiyat * 2;
elseif($y_doluluk > 75) $g_saatlik = $t_fiyat * 3;
$g_saatlik = round($g_saatlik);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Üye Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        const arabaVerileri = {
            "Audi": ["A3", "A4", "A6", "Q3", "Q5", "Q7"],
            "BMW": ["1 Serisi", "3 Serisi", "5 Serisi", "X3", "X5"],
            "Cupra": ["Formentor", "Leon", "Ateca", "Born"],
            "Fiat": ["Egea", "Fiorino", "Linea", "Punto", "Doblo"],
            "Ford": ["Focus", "Fiesta", "Courier", "Kuga", "Puma"],
            "Honda": ["Civic", "CR-V", "City", "Accord"],
            "Mercedes-Benz": ["A-Serisi", "C-Serisi", "E-Serisi", "GLA"],
            "Renault": ["Megane", "Clio", "Symbol", "Taliant", "Captur"],
            "Toyota": ["Corolla", "Yaris", "C-HR", "Hilux"],
            "Volkswagen": ["Golf", "Passat", "Polo", "Tiguan", "T-Roc"],
            "Togg": ["T10X"]
        };

        function markaSecildi() {
            const markaSelect = document.getElementById("markaSelect");
            const modelSelect = document.getElementById("modelSelect");
            
            if(!markaSelect || !modelSelect) return;
            
            const secilenMarka = markaSelect.value;
            modelSelect.innerHTML = '<option value="">Önce Model Seçin</option>';
            
            if(secilenMarka && arabaVerileri[secilenMarka]) {
                arabaVerileri[secilenMarka].forEach(model => {
                    let opt = document.createElement('option');
                    opt.value = model;
                    opt.innerHTML = model;
                    modelSelect.appendChild(opt);
                });
                modelSelect.disabled = false;
                modelSelect.classList.remove('bg-gray-200', 'cursor-not-allowed');
            } else {
                modelSelect.disabled = true;
                modelSelect.classList.add('bg-gray-200', 'cursor-not-allowed');
            }
        }

        function sekmeDegistir(sekmeAdi) {
            document.querySelectorAll('.sekme-icerik').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.sekme-buton').forEach(el => el.classList.remove('bg-blue-600', 'text-white', 'shadow-lg'));
            document.querySelectorAll('.sekme-buton').forEach(el => el.classList.add('bg-white', 'text-gray-600'));
            
            let icerik = document.getElementById(sekmeAdi);
            let buton = document.getElementById('btn-' + sekmeAdi);
            
            if(icerik) icerik.classList.remove('hidden');
            if(buton) {
                buton.classList.remove('bg-white', 'text-gray-600');
                buton.classList.add('bg-blue-600', 'text-white', 'shadow-lg');
            }
        }

        function modalAc(slotAdi) {
            let secilenSlotInp = document.getElementById('secilen_slot');
            let modalIsim = document.getElementById('modal_slot_isim');
            let modal = document.getElementById('rezervasyon_modal');
            
            if(secilenSlotInp) secilenSlotInp.value = slotAdi;
            if(modalIsim) modalIsim.innerText = slotAdi.toUpperCase();
            
            hesapla();
            
            if(modal) modal.classList.remove('hidden');
        }

        function modalKapat() {
            let modal = document.getElementById('rezervasyon_modal');
            if(modal) modal.classList.add('hidden');
        }
        
        function hesapla() {
            let sureSecim = document.getElementById('sure_secim');
            let tutarAlan = document.getElementById('toplam_tutar');
            
            if(sureSecim && tutarAlan) {
                let sure = parseInt(sureSecim.value) || 1;
                let fiyat = <?= $g_saatlik ?>;
                tutarAlan.innerText = sure * fiyat;
            }
        }

        function kapiKontrolUye(kapiAdi, durum) {
            let formData = new FormData();
            formData.append('uye_kapi_kontrol', '1');
            formData.append('kapi_adi', kapiAdi);
