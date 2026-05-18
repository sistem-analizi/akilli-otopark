<?php
session_start();
// Eğer kullanıcı zaten giriş yapmışsa, rolüne göre paneline yönlendir
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akıllı Otopark - Giriş</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4 text-gray-200">

    <div class="bg-gray-800 rounded-2xl shadow-2xl border border-gray-700 w-full max-w-md overflow-hidden flex flex-col">
        
        <div class="bg-gray-900 p-6 text-center border-b border-gray-700">
            <i class="fa-solid fa-car-tunnel text-4xl text-blue-500 mb-3"></i>
            <h1 class="text-2xl font-bold tracking-wider">AKILLI OTOPARK</h1>
            <p class="text-sm text-gray-400">Rezervasyon ve Yönetim Sistemi</p>
        </div>

        <div class="flex border-b border-gray-700">
            <button id="btn-giris" onclick="sekmeDegistir('giris')" class="flex-1 py-3 text-center font-bold text-blue-400 border-b-2 border-blue-500 bg-gray-800 transition">Giriş Yap</button>
            <button id="btn-kayit" onclick="sekmeDegistir('kayit')" class="flex-1 py-3 text-center font-bold text-gray-400 border-b-2 border-transparent bg-gray-800/50 hover:text-gray-200 transition">Kayıt Ol</button>
        </div>

        <div class="p-8">
            <form id="form-giris" action="giris_islem.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">E-Posta Adresi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-gray-500"></i>
                        </div>
                        <input type="email" name="eposta" required class="w-full pl-10 pr-3 py-2 bg-gray-900 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500 transition text-gray-200">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Şifre</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-500"></i>
                        </div>
                        <input type="password" name="sifre" required class="w-full pl-10 pr-3 py-2 bg-gray-900 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500 transition text-gray-200">
                    </div>
                </div>
                <button type="submit" name="giris_yap" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg transition shadow-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Sisteme Giriş
                </button>
            </form>

            <form id="form-kayit" action="giris_islem.php" method="POST" class="space-y-4 hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">İsim</label>
                        <input type="text" name="isim" required class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500 text-gray-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Soyisim</label>
                        <input type="text" name="soyisim" required class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500 text-gray-200">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Telefon</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-phone text-gray-500"></i>
                        </div>
                        <input type="tel" name="telefon" required class="w-full pl-10 pr-3 py-2 bg-gray-900 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500 text-gray-200">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">E-Posta</label>
                    <input type="email" name="eposta" required class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500 text-gray-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Şifre Belirle</label>
                    <input type="password" name="sifre" required class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500 text-gray-200">
                </div>
                <button type="submit" name="kayit_ol" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded-lg transition shadow-lg mt-2 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Üyeliğimi Oluştur
                </button>
            </form>
        </div>
        
        <?php if(isset($_GET['hata'])): ?>
        <div class="bg-red-500/20 text-red-400 text-center py-3 border-t border-red-500/50 text-sm font-bold">
            <i class="fa-solid fa-circle-exclamation mr-1"></i> <?php echo htmlspecialchars($_GET['hata']); ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['basari'])): ?>
        <div class="bg-green-500/20 text-green-400 text-center py-3 border-t border-green-500/50 text-sm font-bold">
            <i class="fa-solid fa-circle-check mr-1"></i> <?php echo htmlspecialchars($_GET['basari']); ?>
        </div>
        <?php endif; ?>

    </div>

    <script>
        function sekmeDegistir(sekme) {
            const formGiris = document.getElementById('form-giris');
            const formKayit = document.getElementById('form-kayit');
            const btnGiris = document.getElementById('btn-giris');
            const btnKayit = document.getElementById('btn-kayit');

            if(sekme === 'giris') {
                formGiris.classList.remove('hidden');
                formKayit.classList.add('hidden');
                
                btnGiris.className = "flex-1 py-3 text-center font-bold text-blue-400 border-b-2 border-blue-500 bg-gray-800 transition";
                btnKayit.className = "flex-1 py-3 text-center font-bold text-gray-400 border-b-2 border-transparent bg-gray-800/50 hover:text-gray-200 transition";
            } else {
                formKayit.classList.remove('hidden');
                formGiris.classList.add('hidden');
                
                btnKayit.className = "flex-1 py-3 text-center font-bold text-blue-400 border-b-2 border-blue-500 bg-gray-800 transition";
                btnGiris.className = "flex-1 py-3 text-center font-bold text-gray-400 border-b-2 border-transparent bg-gray-800/50 hover:text-gray-200 transition";
            }
        }
    </script>
</body>
</html>