<?php
session_start();
include 'baglanti.php';

// Sadece giriş yapmış üyeler bu dosyayı çalıştırabilir
if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'uye') {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uye_id = $_SESSION['kullanici_id'];
    $marka_model = $conn->real_escape_string($_POST['marka_model']);
    $yil = (int)$_POST['yil'];
    $renk = $conn->real_escape_string($_POST['renk']);
    $plaka = strtoupper($conn->real_escape_string($_POST['plaka'])); // Plakayı her zaman büyük harf yap

    $sql = "INSERT INTO araclar (uye_id, marka_model, yil, renk, plaka) 
            VALUES ($uye_id, '$marka_model', $yil, '$renk', '$plaka')";

    if ($conn->query($sql) === TRUE) {
        header("Location: uye_paneli.php?mesaj=Aracınız garajınıza eklendi.");
    } else {
        header("Location: uye_paneli.php?hata=Araç eklenirken bir sorun oluştu.");
    }
}
$conn->close();
?>