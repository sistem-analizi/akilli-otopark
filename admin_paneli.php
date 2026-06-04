<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit;
}

$ayarlar_sorgu = $conn->query("SELECT * FROM cihaz_kontrol WHERE id = 1");
$ayarlar = $ayarlar_sorgu->fetch_assoc();

$uyeler_sorgu = $conn->query("SELECT * FROM uyeler ORDER BY id DESC");

$rezervasyonlar_sorgu = $conn->query("
    SELECT r.*, u.isim, u.soyisim, a.plaka 
    FROM rezervasyonlar r 
    JOIN uyeler u ON r.uye_id = u.id 
    JOIN araclar a ON r.arac_id = a.id 
    ORDER BY r.id DESC
");

$aktif_rez_sorgu = $conn->query("
    SELECT r.slot_adi, a.plaka, u.isim, u.soyisim 
    FROM rezervasyonlar r 
    JOIN uyeler u ON r.uye_id = u.id 
    JOIN araclar a ON r.arac_id = a.id 
    WHERE r.durum = 'aktif'
");
$dolu_bilgileri = [];
while($r = $aktif_rez_sorgu->fetch_assoc()) {
    $dolu_bilgileri[$r['slot_adi']] = $r;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Akıllı Otopark - Admin Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        function sekmeDegistir(sekmeAdi) {
            document.querySelectorAll('.sekme-icerik').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.sekme-buton').forEach(el => el.classList.remove('bg-gray-800', 'text-white'));
            document.querySelectorAll('.sekme-buton').forEach(el => el.classList.add('bg-white', 'text-gray-700'));
            
            document.getElementById(sekmeAdi).classList.remove('hidden');
            document.getElementById('btn-' + sekmeAdi).classList.remove('bg-white', 'text-gray-700');
            document.getElementById('btn-' + sekmeAdi).classList.add('bg-gray-800', 'text-white');
        }

        function modDegistir(kapi, mod) {
            let formData = new FormData();
            formData.append('mod_degistir', '1');
            formData.append('kapi_adi', kapi);
            formData.append('mod', mod);
            fetch('admin_islem.php', { method: 'POST', body: formData }).then(() => location.reload());
        }

        function kapiKontrol(kapi, durum) {
            let formData = new FormData();
            formData.append('kapi_kontrol', '1');
            formData.append('kapi_adi', kapi);
            formData.append('durum', durum);
            fetch('admin_islem.php', { method: 'POST', body: formData }).then(() => location.reload());
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-slate-900 text-white shadow-lg p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="text-2xl font-black text-red-500"><i class="fa-solid fa-shield-halved mr-2"></i>ADMİN MERKEZİ</div>
            <a href="giris_islem.php?cikis=1" class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded-lg font-bold">Sistemden Çık</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-6 mt-6">
        
        <div class="flex space-x-4 mb-8 overflow-x-auto pb-2">
            <button id="btn-kroki" onclick="sekmeDegistir('kroki')" class="sekme-buton bg-gray-800 text-white px-8 py-3 rounded-lg font-bold shadow transition whitespace-nowrap"><i class="fa-solid fa-desktop mr-2"></i>Canlı Kroki & Donanım</button>
            <button id="btn-uyeler" onclick="sekmeDegistir('uyeler')" class="sekme-buton bg-white text-gray-700 px-8 py-3 rounded-lg font-bold shadow transition whitespace-nowrap"><i class="fa-solid fa-users mr-2"></i>Kullanıcılar</button>
            <button id="btn-rezervasyonlar" onclick="sekmeDegistir('rezervasyonlar')" class="sekme-buton bg-white text-gray-700 px-8 py-3 rounded-lg font-bold shadow transition whitespace-nowrap"><i class="fa-solid fa-book-open mr-2"></i>Kayıtlar</button>
            <button id="btn-ayarlar" onclick="sekmeDegistir('ayarlar')" class="sekme-buton bg-white text-gray-700 px-8 py-3 rounded-lg font-bold shadow transition whitespace-nowrap"><i class="fa-solid fa-gear mr-2"></i>Otopark Ayarları</button>
        </div>

        <div id="kroki" class="sekme-icerik bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            
            <div class="bg-gray-900 p-6 rounded-2xl relative mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php 
                    for($i=1; $i<=4; $i++): 
                        $slot_adi = "slot" . $i;
                        $dolu_bilgi = isset($dolu_bilgileri[$slot_adi]) ? $dolu_bilgileri[$slot_adi] : null;
                        $sensor_dolu = $ayarlar[$slot_adi] == 1;
                    ?>
                    <div class="border-4 border-dashed border-gray-600 p-4 rounded-xl h-48 flex flex-col items-center justify-center relative bg-gray-800">
                        <div class="absolute top-2 left-3 text-gray-500 font-black text-xl">S<?= $i ?></div>
                        
                        <?php if($dolu_bilgi): ?>
                            <i class="fa-solid fa-car text-5xl text-red-500 mb-2"></i>
                            <span class="bg-red-500 text-white font-black px-3 py-1 rounded text-sm mb-1"><?= $dolu_bilgi['plaka'] ?></span>
                            <span class="text-gray-300 text-xs font-bold"><?= $dolu_bilgi['isim'] ?> <?= $dolu_bilgi['soyisim'] ?></span>
                        <?php elseif($sensor_dolu): ?>
                            <i class="fa-solid fa-car text-5xl text-orange-500 mb-2"></i>
                            <span class="bg-orange-500 text-white font-black px-3 py-1 rounded text-sm mb-1">MİSAFİR ARAÇ</span>
                            <span class="text-gray-300 text-xs font-bold">Fiziksel Dolu</span>
                        <?php else: ?>
                            <i class="fa-solid fa-square-parking text-5xl text-green-500 mb-2"></i>
                            <span class="text-green-500 font-black">BOŞ</span>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <h3 class="text-xl font-black text-gray-800 mb-4 border-b pb-2">Donanım Manuel Kontrolü</h3>
            
            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-6 flex justify-between items-center shadow-sm">
                <div class="font-black text-lg"><i class="fa-solid fa-torii-gate text-red-500 mr-2"></i>ANA KAPI (GİRİŞ)</div>
                <div class="flex space-x-4">
                    <div class="bg-white p-1 rounded shadow flex border">
                        <button onclick="modDegistir('ana_kapi', 1)" class="px-4 py-2 font-bold rounded <?= $ayarlar['ana_kapi_mod'] == 1 ? 'bg-blue-600 text-white' : 'text-gray-600' ?>">OTO</button>
                        <button onclick="modDegistir('ana_kapi', 0)" class="px-4 py-2 font-bold rounded <?= $ayarlar['ana_kapi_mod'] == 0 ? 'bg-orange-500 text-white' : 'text-gray-600' ?>">MANUEL</button>
                    </div>
                    <?php if($ayarlar['ana_kapi_mod'] == 0): ?>
                    <div class="bg-white p-1 rounded shadow flex border">
                        <button onclick="kapiKontrol('ana_kapi', 1)" class="px-4 py-2 font-bold rounded <?= $ayarlar['ana_kapi'] == 1 ? 'bg-green-500 text-white' : 'text-gray-600' ?>">AÇ</button>
                        <button onclick="kapiKontrol('ana_kapi', 0)" class="px-4 py-2 font-bold rounded <?= $ayarlar['ana_kapi'] == 0 ? 'bg-red-500 text-white' : 'text-gray-600' ?>">KAPAT</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php for($i=1; $i<=4; $i++): $slot = "slot".$i; ?>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm text-center">
                    <div class="font-black mb-3 border-b pb-2">SLOT <?= $i ?> BARIYERI</div>
                    <div class="flex justify-center bg-white p-1 rounded border mb-2">
                        <button onclick="modDegistir('<?= $slot ?>', 1)" class="flex-1 py-1 text-sm font-bold rounded <?= $ayarlar[$slot.'_mod'] == 1 ? 'bg-blue-600 text-white' : 'text-gray-500' ?>">OTO</button>
                        <button onclick="modDegistir('<?= $slot ?>', 0)" class="flex-1 py-1 text-sm font-bold rounded <?= $ayarlar[$slot.'_mod'] == 0 ? 'bg-orange-500 text-white' : 'text-gray-500' ?>">MANUEL</button>
                    </div>
                    <?php if($ayarlar[$slot.'_mod'] == 0): ?>
                    <div class="flex justify-center bg-white p-1 rounded border">
                        <button onclick="kapiKontrol('<?= $slot ?>', 1)" class="flex-1 py-1 text-sm font-bold rounded <?= $ayarlar[$slot] == 1 ? 'bg-green-500 text-white' : 'text-gray-500' ?>">AÇ</button>
                        <button onclick="kapiKontrol('<?= $slot ?>', 0)" class="flex-1 py-1 text-sm font-bold rounded <?= $ayarlar[$slot] == 0 ? 'bg-red-500 text-white' : 'text-gray-500' ?>">KAPAT</button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div id="uyeler" class="sekme-icerik hidden bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-black text-gray-800 mb-6 border-b pb-4">Sisteme Kayıtlı Kullanıcılar</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-100 text-gray-600 text-sm">
                        <tr>
                            <th class="p-3 rounded-tl-lg">ID</th>
                            <th class="p-3">Ad Soyad</th>
                            <th class="p-3">İletişim</th>
                            <th class="p-3">Rol</th>
                            <th class="p-3 text-right rounded-tr-lg">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-bold">
                        <?php while($uye = $uyeler_sorgu->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="p-3 text-gray-500">#<?= $uye['id'] ?></td>
                            <td class="p-3"><?= $uye['isim'] ?> <?= $uye['soyisim'] ?></td>
                            <td class="p-3"><?= $uye['eposta'] ?><br><span class="text-xs text-gray-400"><?= $uye['telefon'] ?></span></td>
                            <td class="p-3">
                                <?php if($uye['rol'] == 'admin'): ?>
                                    <span class="bg-red-100 text-red-600 px-2 py-1 rounded">Admin</span>
                                <?php else: ?>
                                    <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded">Üye</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-right space-x-2 flex justify-end">
                                <?php if($uye['rol'] != 'admin'): ?>
                                    <a href="admin_islem.php?admin_yap=<?= $uye['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded shadow text-xs transition">Admin Yap</a>
                                <?php endif; ?>
                                <?php if($uye['id'] != $_SESSION['kullanici_id']): ?>
                                    <a href="admin_islem.php?uye_sil=<?= $uye['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded shadow text-xs transition" onclick="return confirm('Kullanıcıyı silmek istediğinize emin misiniz?')">Sil</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="rezervasyonlar" class="sekme-icerik hidden bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-black text-gray-800 mb-6 border-b pb-4">Tüm Rezervasyonlar</h2>
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-gray-600 text-sm">
                    <tr>
                        <th class="p-3 rounded-tl-lg">Slot</th>
                        <th class="p-3">Üye Bilgisi</th>
                        <th class="p-3">Araç</th>
                        <th class="p-3">Tarih</th>
                        <th class="p-3 text-right rounded-tr-lg">İşlem / Durum</th>
                    </tr>
                </thead>
                <tbody class="text-sm font-bold">
                    <?php while($rez = $rezervasyonlar_sorgu->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-3 text-lg"><?= strtoupper($rez['slot_adi']) ?></td>
                        <td class="p-3"><?= $rez['isim'] ?> <?= $rez['soyisim'] ?></td>
                        <td class="p-3 text-blue-600"><?= $rez['plaka'] ?></td>
                        <td class="p-3 text-gray-500"><?= date('d.m.Y H:i', strtotime($rez['baslangic_saati'])) ?></td>
                        <td class="p-3 text-right">
                            <?php if($rez['durum'] == 'aktif'): ?>
                                <a href="admin_islem.php?rez_iptal=<?= $rez['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded shadow">İptal Et</a>
                            <?php else: ?>
                                <span class="text-gray-400"><?= strtoupper($rez['durum']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="ayarlar" class="sekme-icerik hidden bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-black text-gray-800 mb-6 border-b pb-4">Otopark ve Fiyat Ayarları</h2>
            <form action="admin_islem.php" method="POST" class="max-w-xl">
                <label class="block font-bold text-gray-700 mb-2">Saatlik Ücret (TL)</label>
                <input type="number" name="taban_fiyat" value="<?= $ayarlar['taban_fiyat'] ?>" class="w-full border-2 border-gray-300 p-3 rounded-lg focus:border-red-500 outline-none font-bold text-xl mb-6" required>
                
                <label class="block font-bold text-gray-700 mb-4">Müşteri Kullanımına Açık Slotlar</label>
                <div class="grid grid-cols-2 gap-4 mb-8 bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <?php for($i=1; $i<=4; $i++): ?>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="s<?= $i ?>_satis" class="w-6 h-6" <?= $ayarlar['slot'.$i.'_satis'] ? 'checked' : '' ?>> 
                        <span class="font-black text-lg text-gray-700">Slot <?= $i ?></span>
                    </label>
                    <?php endfor; ?>
                </div>
                <button type="submit" name="ayarlari_kaydet" class="w-full bg-red-600 hover:bg-red-700 text-white py-4 rounded-lg font-black text-lg transition">Değişiklikleri Kaydet</button>
            </form>
        </div>

    </div>
</body>
</html>