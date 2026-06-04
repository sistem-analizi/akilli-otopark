<?php
session_start();

if (isset($_SESSION['kullanici_id'])) {
    if ($_SESSION['rol'] == 'admin') {
        header("Location: admin_paneli.php");
    } else {
        header("Location: uye_paneli.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Akıllı Otopark - Giriş</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-200 min-h-screen flex items-center justify-center">
    
    <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-8 p-6">
        
        <div class="bg-gray-800 p-8 rounded-xl shadow-2xl border border-gray-700">
            <h2 class="text-2xl font-bold mb-6 text-blue-400">Giriş Yap</h2>
            
            <?php if(isset($_GET['hata']) && $_GET['hata'] == 'giris_basarisiz'): ?>
                <div class="bg-red-500/20 text-red-400 p-3 mb-4 rounded text-sm font-bold border border-red-500/50">E-posta veya şifre hatalı!</div>
            <?php endif; ?>
            
            <form action="giris_islem.php" method="POST" class="space-y-4">
                <input type="email" name="eposta" placeholder="E-Posta" required class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded focus:outline-none focus:border-blue-500">
                <input type="password" name="sifre" placeholder="Şifre" required class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded focus:outline-none focus:border-blue-500">
                <button type="submit" name="giris_yap" class="w-full bg-blue-600 hover:bg-blue-500 py-3 rounded font-bold text-white transition">Giriş Yap</button>
            </form>
        </div>

        <div class="bg-gray-800 p-8 rounded-xl shadow-2xl border border-gray-700">
            <h2 class="text-2xl font-bold mb-6 text-green-400">Yeni Kayıt Oluştur</h2>
            
            <?php if(isset($_GET['hata']) && $_GET['hata'] == 'eposta_kullanimda'): ?>
                <div class="bg-red-500/20 text-red-400 p-3 mb-4 rounded text-sm font-bold border border-red-500/50">Bu e-posta adresi zaten kullanılıyor.</div>
            <?php endif; ?>

            <?php if(isset($_GET['basari']) && $_GET['basari'] == 'kayit_basarili'): ?>
                <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded text-sm font-bold border border-green-500/50">Kayıt başarılı! Giriş yapabilirsiniz.</div>
            <?php endif; ?>

            <form action="giris_islem.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="isim" placeholder="İsim" required class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded focus:outline-none focus:border-green-500">
                    <input type="text" name="soyisim" placeholder="Soyisim" required class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded focus:outline-none focus:border-green-500">
                </div>
                <input type="text" name="telefon" placeholder="Telefon" required class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded focus:outline-none focus:border-green-500">
                <input type="email" name="eposta" placeholder="E-Posta" required class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded focus:outline-none focus:border-green-500">
                <input type="password" name="sifre" placeholder="Şifre" required class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded focus:outline-none focus:border-green-500">
                <button type="submit" name="kayit_ol" class="w-full bg-green-600 hover:bg-green-500 py-3 rounded font-bold text-white transition">Kayıt Ol</button>
            </form>
        </div>

    </div>
</body>
</html>