<?php
session_start();
include 'baglanti.php';

if (isset($_POST['kayit_ol'])) {
    $isim = $conn->real_escape_string($_POST['isim']);
    $soyisim = $conn->real_escape_string($_POST['soyisim']);
    $telefon = $conn->real_escape_string($_POST['telefon']);
    $eposta = $conn->real_escape_string($_POST['eposta']);
    $sifre = md5($_POST['sifre']);

    $kontrol_sql = "SELECT id FROM uyeler WHERE eposta = '$eposta'";
    $kontrol_sonuc = $conn->query($kontrol_sql);

    if ($kontrol_sonuc->num_rows > 0) {
        die("
        <!DOCTYPE html>
        <html lang='tr'>
        <head>
            <meta charset='UTF-8'>
            <title>Sistem Hatası</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
        </head>
        <body class='bg-gray-900 text-gray-200 min-h-screen flex items-center justify-center p-4'>
            <div class='max-w-md w-full bg-gray-800 p-8 rounded-xl border border-red-500/30 text-center shadow-2xl'>
                <i class='fa-solid fa-triangle-exclamation text-6xl text-red-500 mb-4'></i>
                <h2 class='text-2xl font-bold text-red-400 mb-2'>Kayıt Başarısız</h2>
                <p class='text-gray-400 mb-6'>Bu e-posta adresi sistemde zaten kayıtlı.</p>
                <a href='index.php' class='inline-block bg-red-600 hover:bg-red-500 text-white font-bold px-6 py-3 rounded-lg transition shadow-lg w-full'>Giriş Ekranına Dön</a>
            </div>
        </body>
        </html>
        ");
    }

    $sql = "INSERT INTO uyeler (isim, soyisim, telefon, eposta, sifre, rol) VALUES ('$isim', '$soyisim', '$telefon', '$eposta', '$sifre', 'uye')";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?basari=kayit_basarili");
    } else {
        die("
        <!DOCTYPE html>
        <html lang='tr'>
        <head>
            <meta charset='UTF-8'>
            <title>Veritabanı Hatası</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
        </head>
        <body class='bg-gray-900 text-gray-200 min-h-screen flex items-center justify-center p-4'>
            <div class='max-w-xl w-full bg-gray-800 p-8 rounded-xl border border-red-500/30 shadow-2xl'>
                <div class='text-center mb-4'>
                    <i class='fa-solid fa-database text-6xl text-red-500 mb-2'></i>
                    <h2 class='text-2xl font-bold text-red-400'>Sorgu Hatası</h2>
                </div>
                <div class='bg-gray-900 p-4 rounded border border-gray-700 font-mono text-sm text-red-400 overflow-x-auto mb-6'>
                    " . $conn->error . "
                </div>
                <a href='index.php' class='inline-block text-center bg-gray-700 hover:bg-gray-600 text-white font-bold px-6 py-3 rounded-lg transition w-full'>Geri Dön</a>
            </div>
        </body>
        </html>
        ");
    }
    exit;
}

if (isset($_POST['giris_yap'])) {
    $eposta = $conn->real_escape_string($_POST['eposta']);
    $sifre = md5($_POST['sifre']);

    $sql = "SELECT * FROM uyeler WHERE eposta = '$eposta' AND sifre = '$sifre'";
    $sonuc = $conn->query($sql);

    if ($sonuc->num_rows == 1) {
        $uye = $sonuc->fetch_assoc();
        
        $_SESSION['kullanici_id'] = $uye['id'];
        $_SESSION['isim'] = $uye['isim'];
        $_SESSION['soyisim'] = $uye['soyisim'];
        $_SESSION['rol'] = $uye['rol'];

        if ($uye['rol'] == 'admin') {
            header("Location: admin_paneli.php");
        } else {
            header("Location: uye_paneli.php");
        }
        exit;
    } else {
        die("
        <!DOCTYPE html>
        <html lang='tr'>
        <head>
            <meta charset='UTF-8'>
            <title>Giriş Başarısız</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
        </head>
        <body class='bg-gray-900 text-gray-200 min-h-screen flex items-center justify-center p-4'>
            <div class='max-w-md w-full bg-gray-800 p-8 rounded-xl border border-orange-500/30 text-center shadow-2xl'>
                <i class='fa-solid fa-key text-6xl text-orange-500 mb-4'></i>
                <h2 class='text-2xl font-bold text-orange-400 mb-2'>Kimlik Doğrulama Hatası</h2>
                <p class='text-gray-400 mb-6'>Girdiğiniz e-posta adresi veya şifre veritabanındaki kayıtlarla eşleşmedi.</p>
                <a href='index.php' class='inline-block bg-orange-600 hover:bg-orange-500 text-white font-bold px-6 py-3 rounded-lg transition shadow-lg w-full'>Tekrar Dene</a>
            </div>
        </body>
        </html>
        ");
    }
    exit;
}

if (isset($_GET['cikis'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

header("Location: index.php");
exit;
?>