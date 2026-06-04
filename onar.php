<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'baglanti.php';

// Eksik sütunları tabloya ekliyoruz
$conn->query("ALTER TABLE rezervasyonlar ADD COLUMN sure INTEGER DEFAULT 1");
$conn->query("ALTER TABLE rezervasyonlar ADD COLUMN bitis_saati DATETIME");
$conn->query("ALTER TABLE rezervasyonlar ADD COLUMN odeme_durumu TEXT DEFAULT 'bekliyor'");

echo "<h1>Veritabanı Başarıyla Onarıldı!</h1>";
echo "<p>Eksik olan 'sure' ve diğer sütunlar tabloya eklendi. Artık hatasız rezervasyon yapabilirsiniz.</p>";
echo "<a href='index.php'>Ana Sayfaya Dön</a>";
?>