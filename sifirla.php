<?php
$dosya = 'otopark.db';

if (file_exists($dosya)) {
    if (unlink($dosya)) {
        echo "<h1>Eski veritabanı başarıyla silindi!</h1>";
        echo "<p>Şimdi sitenizin ana sayfasına gidin, yeni tablolar (sure, bitis_saati vb.) otomatik olarak oluşturulacak.</p>";
        echo "<a href='index.php'>Ana Sayfaya Git</a>";
    } else {
        echo "Dosya silinemedi. Lütfen hosting panelinizden otopark.db dosyasını elle silin.";
    }
} else {
    echo "otopark.db dosyası zaten yok. Ana sayfaya gidip sistemin yeniden oluşturmasını sağlayın.";
}
?>