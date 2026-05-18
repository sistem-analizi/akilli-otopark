<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'uye') { header("Location: index.php"); exit; }

$uye_id = $_SESSION['kullanici_id'];
$uye_bilgi = $conn->query("SELECT * FROM uyeler WHERE id = $uye_id")->fetch_assoc();
$araclar = $conn->query("SELECT * FROM araclar WHERE uye_id = $uye_id");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Üye Paneli - Akıllı Otopark</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-900 text-gray-200 min-h-screen">

    <nav class="bg-gray-800 p-4 shadow-lg border-b border-gray-700 flex justify-between items-center sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-car-side text-blue-500 text-3xl"></i>
            <h1 class="text-xl font-bold">Hoş Geldiniz, <?php echo htmlspecialchars($uye_bilgi['isim']); ?></h1>
        </div>
        <div class="flex gap-4">
            <button onclick="sekmeGoster('otopark')" class="hover:text-blue-400 font-bold px-3 py-2"><i class="fa-solid fa-map"></i> Rezervasyon</button>
            <button onclick="sekmeGoster('profil')" class="hover:text-green-400 font-bold px-3 py-2"><i class="fa-solid fa-user"></i> Profil / Garaj</button>
            <a href="giris_islem.php?cikis=1" class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded text-white font-bold transition">Çıkış</a>
        </div>
    </nav>

    <?php if(isset($_GET['basari'])) echo "<div id='sistem-uyarisi' class='max-w-6xl mx-auto mt-4 bg-green-500/20 text-green-400 py-3 px-6 rounded border border-green-500/50 shadow flex justify-between items-center font-bold'><span><i class='fa-solid fa-circle-check mr-2'></i>".$_GET['basari']."</span><button onclick='this.parentElement.remove()' class='text-2xl leading-none hover:text-green-300'>&times;</button></div>"; ?>
    <?php if(isset($_GET['hata'])) echo "<div id='sistem-uyarisi' class='max-w-6xl mx-auto mt-4 bg-red-500/20 text-red-400 py-3 px-6 rounded border border-red-500/50 shadow flex justify-between items-center font-bold'><span><i class='fa-solid fa-triangle-exclamation mr-2'></i>".$_GET['hata']."</span><button onclick='this.parentElement.remove()' class='text-2xl leading-none hover:text-red-300'>&times;</button></div>"; ?>

    <div class="max-w-6xl mx-auto p-6 mt-2">
        <div id="sekme-otopark" class="block bg-gray-800 p-8 rounded-xl border border-gray-700 shadow-2xl relative">
            <div class="flex justify-between items-end mb-6 border-b border-gray-700 pb-4">
                <div><h2 class="text-2xl font-bold text-blue-400">Otopark Krokisi</h2><p class="text-sm text-gray-400 mt-1">Rezervasyon için yeşil bir slota tıklayın.</p></div>
                <div class="text-right bg-gray-900 p-3 rounded-lg border border-gray-700"><p class="text-xs text-gray-400 uppercase">Saatlik Fiyat</p><div class="text-4xl font-black text-green-400"><span id="uye-guncel-fiyat">20</span><span class="text-xl"> ₺</span></div></div>
            </div>
            <div class="relative bg-gray-900 rounded-xl p-8 border-2 border-dashed border-gray-600 flex justify-between min-h-[350px]">
                <div class="w-1/4 flex flex-col justify-center items-center border-r-2 border-yellow-500/50 border-dashed pr-6">
                    <i class="fa-solid fa-torii-gate text-6xl mb-3 text-gray-500" id="icon-ana_kapi"></i>
                    <h3 class="font-bold text-gray-300">Ana Bariyer</h3><p id="durum-ana_kapi" class="text-sm font-bold py-1 px-4 mt-2 rounded bg-gray-700">-</p>
                </div>
                <div class="w-2/3 grid grid-cols-2 gap-6 pl-6" id="uye-slot-container"></div>
            </div>
        </div>

        <div id="sekme-profil" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
                        <h2 class="text-xl font-bold text-green-400 mb-4 border-b border-gray-700 pb-2">Kişisel Bilgiler</h2>
                        <form action="uye_islem.php" method="POST" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="isim" value="<?php echo htmlspecialchars($uye_bilgi['isim']); ?>" required class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded text-white">
                                <input type="text" name="soyisim" value="<?php echo htmlspecialchars($uye_bilgi['soyisim']); ?>" required class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded text-white">
                            </div>
                            <input type="tel" name="telefon" value="<?php echo htmlspecialchars($uye_bilgi['telefon']); ?>" required class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded text-white">
                            <button type="submit" name="profil_guncelle" class="w-full bg-green-600 hover:bg-green-500 py-3 rounded font-bold transition">Kaydet</button>
                        </form>
                    </div>
                </div>

                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-xl">
                    <h2 class="text-xl font-bold text-blue-400 mb-4 border-b border-gray-700 pb-2">Garajım (Araçlarım)</h2>
                    <div class="space-y-3 mb-6">
                        <?php if($araclar->num_rows > 0): mysqli_data_seek($araclar, 0); while($arac = $araclar->fetch_assoc()): ?>
                            <div class="bg-gray-900 p-4 rounded-lg flex justify-between items-center border border-gray-600">
                                <div><div class="font-black text-white text-xl tracking-widest"><?php echo $arac['plaka']; ?></div><div class="text-sm text-gray-400"><i class="fa-solid fa-car"></i> <?php echo $arac['marka'] . " " . $arac['model']; ?></div></div>
                                <a href="uye_islem.php?arac_sil=<?php echo $arac['id']; ?>" class="text-red-500 hover:text-red-400 bg-gray-800 p-3 rounded border border-gray-700 transition"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        <?php endwhile; else: ?>
                            <div class="text-gray-500 italic text-center py-4">Henüz garajınızda araç yok.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bg-gray-900 p-5 rounded-lg border border-gray-700">
                        <h3 class="font-bold text-gray-300 mb-3 border-b border-gray-700 pb-2">Yeni Araç Ekle</h3>
                        <form action="uye_islem.php" method="POST" class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <select name="marka" id="marka_secim" onchange="modelleriGetir()" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:border-blue-500">
                                    <option value="" disabled selected>Marka Seçin</option>
                                    <option value="Fiat">Fiat</option><option value="Renault">Renault</option><option value="Toyota">Toyota</option><option value="Honda">Honda</option><option value="Volkswagen">Volkswagen</option><option value="Ford">Ford</option><option value="Mercedes">Mercedes</option><option value="BMW">BMW</option>
                                </select>
                                <select name="model" id="model_secim" required disabled class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:border-blue-500 opacity-50 transition-opacity">
                                    <option value="" disabled selected>Önce Marka Seçin</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <select name="yil" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white"><option value="" disabled selected>Yıl</option><?php for($y=2026; $y>=2000; $y--) echo "<option value='$y'>$y</option>"; ?></select>
                                <select name="renk" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white"><option value="" disabled selected>Renk</option><option value="Beyaz">Beyaz</option><option value="Siyah">Siyah</option><option value="Gri">Gri</option><option value="Kırmızı">Kırmızı</option></select>
                            </div>
                            <input type="text" name="plaka" placeholder="Plaka (Örn: 34ABC123)" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white uppercase tracking-widest font-bold focus:outline-none focus:border-blue-500">
                            <button type="submit" name="arac_ekle" class="w-full bg-blue-600 hover:bg-blue-500 py-3 rounded font-bold shadow-lg transition">Garaja Ekle</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="rezervasyonModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100] p-4 backdrop-blur-sm">
        <div class="bg-gray-800 border border-gray-600 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl transform transition-all">
            <div class="bg-blue-600 p-4 flex justify-between items-center"><h3 class="font-bold text-white"><i class="fa-solid fa-lock mr-2"></i> Güvenli Rezervasyon</h3><button onclick="rezModalKapat()" class="text-white text-2xl leading-none hover:text-gray-300">&times;</button></div>
            <form action="uye_islem.php" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="slot_adi" id="secilen_slot_input" value="">
                
                <div class="text-center"><div class="text-3xl font-black text-blue-400 uppercase bg-gray-900 py-2 rounded border border-gray-700" id="secilen_slot_gosterge">SLOT 1</div></div>
                
                <div>
                    <select name="arac_id" required class="w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded text-white font-bold focus:outline-none focus:border-blue-500">
                        <option value="" disabled selected>Garajdan Araç Seçin</option>
                        <?php if($araclar->num_rows > 0){ mysqli_data_seek($araclar, 0); while($a = $araclar->fetch_assoc()){ echo "<option value='".$a['id']."'>".$a['plaka']." (".$a['marka']." ".$a['model'].")</option>"; } } ?>
                    </select>
                </div>
                
                <div>
                    <select name="sure_saat" id="sure_saat" onchange="fiyatHesapla()" required class="w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded text-white font-bold focus:outline-none focus:border-blue-500">
                        <option value="" disabled selected>Tahmini Süre Seçin</option>
                        <option value="1">1 Saat</option><option value="2">2 Saat</option><option value="3">3 Saat</option><option value="12">Gün Boyu (12 Saat)</option>
                    </select>
                </div>

                <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 mt-2">
                    <h4 class="text-sm font-bold text-gray-400 mb-3 uppercase tracking-wider"><i class="fa-regular fa-credit-card text-blue-400"></i> Ödeme Bilgileri</h4>
                    <div class="space-y-3">
                        <input type="text" placeholder="Kart Üzerindeki İsim" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:border-blue-500 text-sm">
                        <input type="text" placeholder="Kart Numarası (16 Hane)" maxlength="16" pattern="\d{16}" title="Lütfen 16 haneli kart numaranızı boşluk bırakmadan girin" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:border-blue-500 text-sm tracking-widest font-mono">
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" placeholder="Ay/Yıl (05/28)" maxlength="5" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:border-blue-500 text-sm text-center">
                            <input type="text" placeholder="CVV (Gizli Kod)" maxlength="3" pattern="\d{3}" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:border-blue-500 text-sm text-center">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900 p-4 rounded flex justify-between items-center border border-green-500/30"><div class="text-gray-400 font-bold uppercase text-xs tracking-wider">Ödenecek Tutar:</div><div class="text-3xl font-black text-green-400"><span id="modal_toplam_tutar">0</span> ₺</div></div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="rezModalKapat()" class="w-1/3 bg-gray-700 hover:bg-gray-600 transition py-3 rounded text-white font-bold">İptal</button>
                    <button type="submit" name="rezervasyon_yap" class="w-2/3 bg-green-600 hover:bg-green-500 transition py-3 rounded text-white font-bold shadow-xl <?php echo ($araclar->num_rows==0)?'opacity-50':''; ?>" <?php echo ($araclar->num_rows==0)?'disabled':''; ?>><i class="fa-solid fa-check"></i> Öde ve Rezerve Et</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // URL Temizleme ve Bildirim Gizleme
        $(document).ready(function() {
            if ($('#sistem-uyarisi').length > 0) {
                setTimeout(function() {
                    $('#sistem-uyarisi').fadeOut('slow', function() { $(this).remove(); });
                }, 4000);
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('basari');
                    url.searchParams.delete('hata');
                    window.history.replaceState(null, null, url);
                }
            }
        });

        const aracModelleri = {
            "Fiat": ["Egea", "Fiorino", "Doblo", "Punto", "Linea", "500", "Panda"],
            "Renault": ["Clio", "Megane", "Symbol", "Taliant", "Fluence", "Kadjar", "Captur"],
            "Toyota": ["Corolla", "Yaris", "C-HR", "Auris", "Hilux", "RAV4", "Camry"],
            "Honda": ["Civic", "City", "Accord", "CR-V", "HR-V", "Jazz"],
            "Volkswagen": ["Golf", "Polo", "Passat", "Tiguan", "Jetta", "T-Roc", "Arteon"],
            "Ford": ["Focus", "Fiesta", "Courier", "Puma", "Mustang", "Kuga", "Ranger"],
            "Mercedes": ["C-Serisi", "E-Serisi", "A-Serisi", "CLA", "GLA", "Vito", "Sprinter"],
            "BMW": ["3 Serisi", "5 Serisi", "1 Serisi", "X1", "X3", "X5", "4 Serisi"]
        };

        function modelleriGetir() {
            let marka = $('#marka_secim').val();
            let modelSelect = $('#model_secim');
            modelSelect.empty().append('<option value="" disabled selected>Model Seçin</option>');
            if(marka && aracModelleri[marka]) {
                modelSelect.prop('disabled', false).removeClass('opacity-50');
                aracModelleri[marka].forEach(function(model) { modelSelect.append(new Option(model, model)); });
            } else {
                modelSelect.prop('disabled', true).addClass('opacity-50');
            }
        }

        let anlikFiyat = 20; 
        const toplamSlot = 4;

        function slotHtmlOlustur(id, numara) {
            return `
            <div id="kutu-${id}" onclick="rezervasyonBaslat('${id}')" class="bg-gray-800 p-6 rounded-xl border-2 border-gray-600 flex flex-col justify-between items-center min-h-[220px] relative transition-all cursor-pointer">
                <h3 class="font-black text-2xl text-gray-600 absolute top-4 left-4">P${numara}</h3>
                
                <div id="sure-rozeti-${id}" class="absolute top-4 right-4 bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded hidden shadow-md transition-all">
                    <i class="fa-solid fa-hourglass-half animate-pulse mr-1"></i><span id="sure-yazi-${id}">--:--</span>
                </div>

                <i id="ikon-${id}" class="fa-solid fa-square-parking text-5xl text-gray-600 mt-5 transition-all"></i>
                <div id="durum-${id}" class="text-center font-bold py-1 px-2 rounded bg-gray-700 mt-4 text-[11px] w-full transition-all">-</div>
                <div id="uyari-${id}" class="absolute inset-0 bg-gray-900/80 rounded-xl flex items-center justify-center text-red-500 font-black text-2xl hidden z-10 backdrop-blur-[1px]">DOLU</div>
                
                <div id="uye-kontrol-${id}" class="absolute bottom-2 w-[90%] flex flex-col gap-1 hidden z-50">
                    <div class="flex gap-1 w-full">
                        <button type="button" onclick="uyeKapiKomut(event, '${id}', 1)" class="w-1/2 bg-green-600 hover:bg-green-500 py-1.5 rounded text-[10px] font-bold text-white shadow relative z-50">AÇ</button>
                        <button type="button" onclick="uyeKapiKomut(event, '${id}', 0)" class="w-1/2 bg-red-600 hover:bg-red-500 py-1.5 rounded text-[10px] font-bold text-white shadow relative z-50">KAPAT</button>
                    </div>
                    <button type="button" onclick="uyeRezervasyonBitir(event, '${id}')" class="w-full bg-orange-600 hover:bg-orange-500 py-1.5 rounded text-[10px] font-bold text-white shadow relative z-50"><i class="fa-solid fa-right-from-bracket"></i> ÇIKIŞ YAP</button>
                </div>
            </div>`;
        }

        $(document).ready(function() {
            let slotsHTML = '';
            for(let i=1; i<=toplamSlot; i++) slotsHTML += slotHtmlOlustur('slot'+i, i);
            $('#uye-slot-container').html(slotsHTML);
            verileriCek();
            setInterval(verileriCek, 1000); 
        });

        function verileriCek() {
            $.ajax({
                url: 'arayuz_verileri.php',
                type: 'GET',
                dataType: 'json',
                success: function(veri) {
                    if(veri.sensorler && veri.kontrol) {
                        if(veri.anlik_fiyat) {
                            anlikFiyat = veri.anlik_fiyat;
                            $('#uye-guncel-fiyat').text(anlikFiyat);
                            fiyatHesapla();
                        }

                        gorselGuncelle('ana_kapi', veri.sensorler.ana_kapi, 1, 'yok', 0, '');
                        ['slot1', 'slot2', 'slot3', 'slot4'].forEach(function(s) {
                            let d = veri.sensorler[s];
                            let sat = veri.kontrol[s + '_satis'];
                            let rez = veri.rezerve[s]; 
                            let manuelKapiDurumu = veri.kontrol[s]; 
                            let kalanSure = veri.kalan_sureler[s]; 
                            gorselGuncelle(s, d, sat, rez, manuelKapiDurumu, kalanSure);
                        });
                    }
                }
            });
        }

        // İŞTE EKSİK OLAN O KRİTİK FONKSİYON KAFASI BURAYA EKLENDİ:
        function gorselGuncelle(id, durum, satis, rezerve, kapiDurumu, kalanSure) {
            let el = $('#durum-' + id);
            let icon = $('#ikon-' + id);
            let box = $('#kutu-' + id);
            let uyari = $('#uyari-' + id);
            let kontrolBtn = $('#uye-kontrol-' + id);
            let rozet = $('#sure-rozeti-' + id);
            let rozetYazi = $('#sure-yazi-' + id);

            if(el.text() === "SİNYAL GÖNDERİLİYOR..." || el.text() === "ÇIKIŞ YAPILIYOR...") return; 

            box.removeClass('pointer-events-none kendi-rezervasyonu');
            kontrolBtn.addClass('hidden');
            rozet.addClass('hidden'); 

            if(satis == 0 && id !== 'ana_kapi') {
                el.text('SERVİS DIŞI').attr('class', 'text-center font-bold py-1 px-4 mt-4 text-sm w-full rounded bg-yellow-500/20 text-yellow-500');
                icon.attr('class', 'fa-solid fa-lock text-5xl mt-6 text-yellow-500');
                box.css('border-color', '#eab308').addClass('pointer-events-none'); 
                uyari.text('KAPALI').removeClass('hidden text-red-500 text-blue-500').addClass('flex text-yellow-500'); 
                return;
            }

            if (id === 'ana_kapi') {
                if(durum == 1) { 
                    el.text('AÇIK').attr('class', 'text-sm font-bold py-1 px-4 mt-2 rounded bg-green-500/20 text-green-400');
                    $('#icon-ana_kapi').attr('class', 'fa-solid fa-door-open text-6xl mb-3 text-green-400');
                } else {
                    el.text('KAPALI').attr('class', 'text-sm font-bold py-1 px-4 mt-2 rounded bg-gray-700 text-gray-400');
                    $('#icon-ana_kapi').attr('class', 'fa-solid fa-torii-gate text-6xl mb-3 text-gray-500');
                }
            } else {
                if (rezerve === 'benim') {
                    el.text(kapiDurumu == 1 ? 'KAPI AÇIK' : 'KAPI KAPALI');
                    el.attr('class', kapiDurumu == 1 ? 'text-center font-bold py-1 px-2 mt-4 text-[10px] w-full rounded bg-green-500/20 text-green-400' : 'text-center font-bold py-1 px-2 mt-4 text-[10px] w-full rounded bg-gray-700 text-gray-400');
                    icon.attr('class', 'fa-solid fa-user-shield text-5xl mt-3 text-blue-400');
                    box.css('border-color', '#60a5fa').addClass('kendi-rezervasyonu'); 
                    uyari.addClass('hidden');
                    kontrolBtn.removeClass('hidden'); 
                    
                    rozet.removeClass('hidden');
                    rozetYazi.text(kalanSure);
                    if(kalanSure === "SÜRE BİTTİ") {
                        rozet.removeClass('bg-blue-600').addClass('bg-red-600');
                    } else {
                        rozet.removeClass('bg-red-600').addClass('bg-blue-600');
                    }
                }
                else if (rezerve === 'baskasinin') {
                    el.text('REZERVE EDİLDİ');
                    el.attr('class', 'text-center font-bold py-1 px-4 mt-4 text-[11px] w-full rounded bg-blue-500/20 text-blue-400 opacity-50');
                    icon.attr('class', 'fa-solid fa-clock text-5xl mt-5 text-blue-400 opacity-50');
                    box.css('border-color', '#3b82f6').addClass('pointer-events-none'); 
                    uyari.text('REZERVE').removeClass('hidden text-yellow-500 text-red-500').addClass('flex text-blue-400'); 
                }
                // --- YENİ EKLENEN MANTIK BURASI ---
                // Eğer slot rezerve değilse ve KAPI AÇIK (1) ise, sensörü dikkate alma, direkt BOŞ göster!
                else if (kapiDurumu == 1) { 
                    el.text('BOŞ (Tıkla)').attr('class', 'text-center font-bold py-1 px-4 mt-4 text-sm w-full rounded bg-green-500/20 text-green-400');
                    icon.attr('class', 'fa-solid fa-square-parking text-5xl mt-5 text-green-500');
                    box.css('border-color', '#22c55e');
                    uyari.addClass('hidden');
                }
                // Eğer kapı kapalıysa ve sensör dolu gösteriyorsa DOLU yap
                else if (durum == 1) { 
                    el.text('DOLU');
                    el.attr('class', 'text-center font-bold py-1 px-4 mt-4 text-sm w-full rounded bg-red-500/20 text-red-400');
                    icon.attr('class', 'fa-solid fa-car text-5xl mt-5 text-red-400');
                    box.css('border-color', '#ef4444').addClass('pointer-events-none'); 
                    uyari.text('DOLU').removeClass('hidden text-yellow-500 text-blue-500').addClass('flex text-red-500'); 
                } 
                // Diğer tüm durumlarda (Kapı kapalı ve sensör boşsa)
                else { 
                    el.text('BOŞ (Tıkla)').attr('class', 'text-center font-bold py-1 px-4 mt-4 text-sm w-full rounded bg-green-500/20 text-green-400');
                    icon.attr('class', 'fa-solid fa-square-parking text-5xl mt-5 text-green-500');
                    box.css('border-color', '#22c55e');
                    uyari.addClass('hidden');
                }
            }
        }

        function fiyatGuncelle(doluSlot) {
            let yuzde = (doluSlot / toplamSlot) * 100;
            anlikFiyat = 20; 
            if(yuzde > 25 && yuzde <= 50) anlikFiyat = 35;
            else if(yuzde > 50 && yuzde <= 75) anlikFiyat = 50;
            else if(yuzde > 75) anlikFiyat = 75;
            $('#uye-guncel-fiyat').text(anlikFiyat);
            fiyatHesapla(); 
        }

        function rezervasyonBaslat(slotAdi) {
            if ($('#kutu-' + slotAdi).hasClass('kendi-rezervasyonu')) return;

            $('#secilen_slot_input').val(slotAdi);
            $('#secilen_slot_gosterge').text(slotAdi.replace('slot', 'P ')); 
            $('#sure_saat').val("");
            $('#modal_toplam_tutar').text("0");
            
            $('#rezervasyonModal').removeClass('hidden').addClass('flex');
        }

        function uyeKapiKomut(event, kapi, durum) {
            if(event) { event.preventDefault(); event.stopPropagation(); }
            
            $('#durum-' + kapi).text("SİNYAL GÖNDERİLİYOR...").attr('class', 'text-center font-bold py-1 px-2 mt-4 text-[10px] w-full rounded bg-yellow-500 text-black');

            $.ajax({
                url: 'uye_islem.php',
                type: 'POST',
                data: { uye_kapi_kontrol: true, kapi_adi: kapi, durum: durum },
                success: function(cevap) {
                    if (cevap.trim() === "OK") { 
                        $('#durum-' + kapi).text("GÜNCELLENİYOR..."); 
                        setTimeout(verileriCek, 100); 
                    } else { 
                        alert("SİSTEM REDDETTİ: " + cevap); 
                        $('#durum-' + kapi).text("-"); 
                        verileriCek(); 
                    }
                },
                error: function() { 
                    alert("KRİTİK HATA: Arka plana ulaşılamıyor."); 
                    $('#durum-' + kapi).text("-"); 
                    verileriCek(); 
                }
            });
        }

        function uyeRezervasyonBitir(event, kapi) {
            if(event) { event.preventDefault(); event.stopPropagation(); }
            
            if(confirm("Rezervasyonu erken bitirmek istediğinize emin misiniz?\n\nKapı kilitlenecek ve slot başkalarının kullanımına açılacaktır.")) {
                
                $('#durum-' + kapi).text("ÇIKIŞ YAPILIYOR...").attr('class', 'text-center font-bold py-1 px-2 mt-4 text-[10px] w-full rounded bg-orange-500 text-white');

                $.ajax({
                    url: 'uye_islem.php',
                    type: 'POST',
                    data: { rezervasyon_bitir: true, kapi_adi: kapi },
                    success: function(cevap) {
                        if (cevap.trim() === "OK") {
                            $('#durum-' + kapi).text("GÜNCELLENİYOR...");
                            setTimeout(verileriCek, 100); 
                        } else {
                            alert("SİSTEM REDDETTİ: " + cevap);
                            $('#durum-' + kapi).text("-"); 
                            verileriCek();
                        }
                    },
                    error: function() {
                        alert("KRİTİK HATA: Arka plana ulaşılamıyor.");
                        $('#durum-' + kapi).text("-"); 
                        verileriCek();
                    }
                });
            }
        }

        function fiyatHesapla() { 
            let sure = $('#sure_saat').val();
            if(sure) { $('#modal_toplam_tutar').text(sure * anlikFiyat); } 
            else { $('#modal_toplam_tutar').text("0"); }
        }
        
        function rezModalKapat() { $('#rezervasyonModal').addClass('hidden').removeClass('flex'); }
        function sekmeGoster(s) {
            $('#sekme-otopark').toggleClass('hidden', s !== 'otopark').toggleClass('block', s === 'otopark');
            $('#sekme-profil').toggleClass('hidden', s !== 'profil').toggleClass('block', s === 'profil');
        }
    </script>
</body>
</html>