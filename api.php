<?php
include 'baglanti.php';

if (isset($_GET['s_ana']) && isset($_GET['s_s1'])) {
    
    // 1. Arduino'daki sensörlerden araba geldi/gitti bilgisini al
    $s_ana = (int)$_GET['s_ana'];
    $s_s1 = (int)$_GET['s_s1'];
    $s_s2 = (int)$_GET['s_s2'];
    $s_s3 = (int)$_GET['s_s3'];
    $s_s4 = (int)$_GET['s_s4'];

    // 2. Admin panelindeki (veritabanı) güncel ayarları çek
    $sql = "SELECT * FROM cihaz_kontrol WHERE id = 1";
    $sonuc = $conn->query($sql);
    $ayar = $sonuc->fetch_assoc();

    $fiyat = $ayar['taban_fiyat'];

    // 3. İŞTE OTOMATİK/MANUEL MOD BEYNİ BURASI
    // Kural: Mod 1 (Otomatik) ise sensör verisi ($s_ana) kapıyı kontrol eder.
    // Kural: Mod 0 (Manuel) ise Admin panelindeki buton durumu ($ayar['ana_kapi']) kapıyı kontrol eder.
    $cmd_ana = ($ayar['ana_kapi_mod'] == 1) ? $s_ana : $ayar['ana_kapi'];
    $cmd_s1  = ($ayar['slot1_mod'] == 1)  ? $s_s1  : $ayar['slot1'];
    $cmd_s2  = ($ayar['slot2_mod'] == 1)  ? $s_s2  : $ayar['slot2'];
    $cmd_s3  = ($ayar['slot3_mod'] == 1)  ? $s_s3  : $ayar['slot3'];
    $cmd_s4  = ($ayar['slot4_mod'] == 1)  ? $s_s4  : $ayar['slot4'];

    // (BONUS): Sensörler kapıyı otomatik açtığında, web sitesindeki Admin Panelinde 
    // yer alan Kırmızı/Yeşil butonların da canlı olarak değişmesi için veritabanını güncelliyoruz:
    $guncelle_sql = "UPDATE cihaz_kontrol SET 
                     ana_kapi = $cmd_ana, 
                     slot1 = $cmd_s1, 
                     slot2 = $cmd_s2, 
                     slot3 = $cmd_s3, 
                     slot4 = $cmd_s4 
                     WHERE id = 1";
    $conn->query($guncelle_sql);

    // 4. Boş yer sayısını hesapla
    $dolu_slot_sayisi = $s_s1 + $s_s2 + $s_s3 + $s_s4;
    $bos_yer = 4 - $dolu_slot_sayisi;
    if ($bos_yer < 0) $bos_yer = 0;

    // 5. Arduino'nun motorları hareket ettirmek için beklediği virgüllü şifreyi gönder
    echo "$cmd_ana,$cmd_s1,$cmd_s2,$cmd_s3,$cmd_s4,$bos_yer,$fiyat";
    
    exit;
}

echo "Gecersiz Istek.";
?>