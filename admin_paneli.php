<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit;
}

$ayarlar = $conn->query("SELECT * FROM cihaz_kontrol WHERE id=1")->fetch_assoc();
$uyeler = $conn->query("SELECT * FROM uyeler ORDER BY kayit_tarihi DESC");
$rezervasyonlar = $conn->query("SELECT r.*, u.isim, u.soyisim, a.plaka FROM rezervasyonlar r JOIN uyeler u ON r.uye_id = u.id JOIN araclar a ON r.arac_id = a.id ORDER BY r.baslangic_saati DESC");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli - Akıllı Şehir Otoparkı</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-900 text-gray-200 min-h-screen">

    <nav class="bg-gray-800 p-4 shadow-lg border-b border-gray-700 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-user-shield text-red-500 text-3xl"></i>
            <h1 class="text-xl font-bold tracking-wider">ADMİN KOMUTA MERKEZİ</h1>
        </div>
        <div class="flex items-center gap-6">
            <a href="giris_islem.php?cikis=1" class="text-red-500 hover:text-red-400 font-bold transition"><i class="fa-solid fa-power-off text-2xl"></i></a>
        </div>
    </nav>

    <?php if(isset($_GET['basari'])) echo "<div class='max-w-7xl mx-auto mt-4 bg-green-500/20 text-green-400 text-center py-3 rounded border border-green-500/50 shadow-lg'><i class='fa-solid fa-check mr-2'></i>".$_GET['basari']."</div>"; ?>

    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <section class="lg:col-span-2 bg-gray-800 p-8 rounded-xl border border-gray-700 shadow-2xl relative">
            <h2 class="text-2xl font-bold mb-6 text-blue-400 border-b border-gray-700 pb-2"><i class="fa-solid fa-map-location-dot"></i> Otopark Sahası (Bağımsız Kontrol)</h2>
            <div class="relative bg-gray-900 rounded-xl p-8 border-2 border-dashed border-gray-600 flex justify-between min-h-[380px]">
                
                <div class="w-1/4 flex flex-col justify-center items-center border-r-2 border-yellow-500/50 border-dashed pr-4 relative">
                    <div class="absolute top-0 right-2 flex gap-1 z-30">
                        <button onclick="kapiModDegistir('ana_kapi', 1)" id="btn-oto-ana_kapi" class="px-2 py-0.5 rounded text-[10px] font-bold transition bg-blue-600 text-white">OTO</button>
                        <button onclick="kapiModDegistir('ana_kapi', 0)" id="btn-man-ana_kapi" class="px-2 py-0.5 rounded text-[10px] font-bold transition bg-gray-700 text-gray-400">MAN</button>
                    </div>
                    <i class="fa-solid fa-torii-gate text-5xl mb-2 mt-4 text-gray-500 transition-all" id="icon-ana_kapi"></i>
                    <h3 class="font-bold text-gray-300">Ana Bariyer</h3>
                    <p id="durum-ana_kapi" class="text-sm font-bold py-1 px-4 mt-2 rounded bg-gray-700 transition-all">-</p>
                    <div id="kapi-kontrol-ana_kapi" class="mt-4 w-full flex gap-1 hidden z-30">
                        <button onclick="kapiKomut('ana_kapi', 1)" class="w-1/2 bg-green-600 hover:bg-green-500 py-2 rounded text-xs font-bold text-white shadow">AÇ</button>
                        <button onclick="kapiKomut('ana_kapi', 0)" class="w-1/2 bg-red-600 hover:bg-red-500 py-2 rounded text-xs font-bold text-white shadow">KAPAT</button>
                    </div>
                </div>

                <div class="w-2/3 grid grid-cols-2 gap-4 pl-4" id="slot-container"></div>
            </div>
        </section>

        <section class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-2xl">
            <h2 class="text-xl font-bold mb-6 text-yellow-400 border-b border-gray-700 pb-2"><i class="fa-solid fa-sliders"></i> Sistem Ayarları</h2>
            <form action="admin_islem.php" method="POST" class="space-y-6">
                <div class="bg-gray-900 p-4 rounded-lg border border-gray-600">
                    <label class="block text-sm font-bold text-gray-300 mb-2">Saatlik Taban Fiyat (₺)</label>
                    <input type="number" name="taban_fiyat" value="<?php echo $ayarlar['taban_fiyat']; ?>" required class="w-full px-3 py-2 bg-gray-800 border border-gray-500 rounded text-green-400 font-black text-2xl text-center focus:outline-none">
                </div>
                <div class="bg-gray-900 p-4 rounded-lg border border-gray-600">
                    <label class="block text-sm font-bold text-gray-300 mb-3 border-b border-gray-700 pb-2">Slotları Satışa Kapat/Aç</label>
                    <div class="space-y-3">
                        <label class="flex justify-between cursor-pointer items-center"><span class="font-bold text-gray-300">Slot 1</span> <input type="checkbox" name="s1_satis" class="w-5 h-5 accent-green-500" <?php echo ($ayarlar['slot1_satis']==1)?'checked':''; ?>></label>
                        <label class="flex justify-between cursor-pointer items-center"><span class="font-bold text-gray-300">Slot 2</span> <input type="checkbox" name="s2_satis" class="w-5 h-5 accent-green-500" <?php echo ($ayarlar['slot2_satis']==1)?'checked':''; ?>></label>
                        <label class="flex justify-between cursor-pointer items-center"><span class="font-bold text-gray-300">Slot 3</span> <input type="checkbox" name="s3_satis" class="w-5 h-5 accent-green-500" <?php echo ($ayarlar['slot3_satis']==1)?'checked':''; ?>></label>
                        <label class="flex justify-between cursor-pointer items-center"><span class="font-bold text-gray-300">Slot 4</span> <input type="checkbox" name="s4_satis" class="w-5 h-5 accent-green-500" <?php echo ($ayarlar['slot4_satis']==1)?'checked':''; ?>></label>
                    </div>
                </div>
                <button type="submit" name="ayarlari_kaydet" class="w-full bg-yellow-600 hover:bg-yellow-500 py-3 rounded-lg font-bold text-white shadow-lg transition"><i class="fa-solid fa-floppy-disk"></i> Ayarları Kaydet</button>
            </form>
        </section>

    </div>

    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-xl h-[400px] overflow-y-auto custom-scrollbar">
            <h2 class="text-xl font-bold mb-4 text-green-400 border-b border-gray-700 pb-2"><i class="fa-solid fa-users"></i> Kayıtlı Kullanıcılar</h2>
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="text-xs text-gray-400 uppercase bg-gray-900 sticky top-0"><tr><th class="p-3 rounded-tl-lg">Ad Soyad</th><th class="p-3">Rol</th><th class="p-3 text-right rounded-tr-lg">İşlem</th></tr></thead>
                <tbody>
                    <?php while($uye = $uyeler->fetch_assoc()): ?>
                    <tr class="border-b border-gray-700 hover:bg-gray-750 transition">
                        <td class="p-3"><div class="font-bold"><?php echo htmlspecialchars($uye['isim']." ".$uye['soyisim']); ?></div><div class="text-xs text-gray-500"><?php echo htmlspecialchars($uye['telefon']); ?></div></td>
                        <td class="p-3"><?php echo $uye['rol']=='admin' ? '<span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded text-xs font-bold">Admin</span>' : '<span class="bg-gray-600/50 text-gray-400 px-2 py-1 rounded text-xs font-bold">Üye</span>'; ?></td>
                        <td class="p-3 text-right">
                            <?php if($uye['rol'] != 'admin'): ?><a href="admin_islem.php?admin_yap=<?php echo $uye['id']; ?>" class="bg-blue-600/20 text-blue-500 hover:bg-blue-600 hover:text-white px-3 py-1 rounded text-xs font-bold transition mr-1">Yetki Ver</a><?php endif; ?>
                            <?php if($uye['id'] != $_SESSION['kullanici_id']): ?><a href="admin_islem.php?uye_sil=<?php echo $uye['id']; ?>" class="bg-red-600/20 text-red-500 hover:bg-red-600 hover:text-white px-3 py-1 rounded text-xs font-bold transition">Sil</a><?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-xl h-[400px] overflow-y-auto custom-scrollbar">
            <h2 class="text-xl font-bold mb-4 text-orange-400 border-b border-gray-700 pb-2"><i class="fa-solid fa-calendar-check"></i> Rezervasyonlar</h2>
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="text-xs text-gray-400 uppercase bg-gray-900 sticky top-0"><tr><th class="p-3 rounded-tl-lg">Üye/Plaka</th><th class="p-3">Slot/Durum</th><th class="p-3 text-right rounded-tr-lg">İşlem</th></tr></thead>
                <tbody>
                    <?php while($rez = $rezervasyonlar->fetch_assoc()): ?>
                    <tr class="border-b border-gray-700 hover:bg-gray-750 transition">
                        <td class="p-3"><div class="font-bold text-gray-200"><?php echo htmlspecialchars($rez['isim']." ".$rez['soyisim']); ?></div><div class="text-xs text-blue-400 font-bold"><?php echo htmlspecialchars($rez['plaka']); ?></div></td>
                        <td class="p-3"><div class="font-black text-gray-300"><?php echo strtoupper($rez['slot_adi']); ?></div><?php echo $rez['durum']=='aktif' ? '<span class="text-[10px] bg-green-500/20 text-green-400 px-1 rounded font-bold">AKTİF</span>' : '<span class="text-[10px] bg-red-500/20 text-red-400 px-1 rounded font-bold">İPTAL</span>'; ?></td>
                        <td class="p-3 text-right"><?php if($rez['durum']=='aktif'): ?><a href="admin_islem.php?rez_iptal=<?php echo $rez['id']; ?>" class="bg-orange-600/20 text-orange-500 hover:bg-orange-600 hover:text-white px-3 py-1 rounded text-xs font-bold transition">İptal Et</a><?php endif; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="bilgiModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100] p-4 backdrop-blur-sm">
        <div class="bg-gray-800 border border-gray-600 rounded-2xl w-full max-w-md overflow-hidden transform transition-all shadow-2xl">
            <div class="bg-blue-600 p-4 flex justify-between items-center shadow-md"><h3 class="font-bold text-white"><i class="fa-solid fa-circle-info mr-2"></i> Sistem Taraması</h3><button onclick="modalKapat()" class="text-white hover:text-gray-300 text-3xl leading-none">&times;</button></div>
            <div id="modal-icerik" class="p-6"></div>
            <div class="bg-gray-900 p-4 border-t border-gray-700 text-right"><button onclick="modalKapat()" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded font-bold transition shadow">Kapat</button></div>
        </div>
    </div>

    <script>
        const toplamSlot = 4;

        function slotHtmlOlustur(id, numara) {
            return `
            <div class="bg-gray-800 p-4 rounded-xl border-2 border-gray-600 flex flex-col justify-between items-center min-h-[190px] relative transition-all">
                <div class="absolute top-2 right-2 flex gap-1 z-30">
                    <button onclick="kapiModDegistir('${id}', 1)" id="btn-oto-${id}" class="px-2 py-0.5 rounded text-[9px] font-bold transition">OTO</button>
                    <button onclick="kapiModDegistir('${id}', 0)" id="btn-man-${id}" class="px-2 py-0.5 rounded text-[9px] font-bold transition">MAN</button>
                </div>
                <div onclick="slotDetayGoster('${id}')" class="absolute inset-0 bottom-16 cursor-pointer z-10" title="Detay İçin Tıkla"></div>
                <h3 class="font-black text-xl text-gray-500 absolute top-2 left-3">P${numara}</h3>
                <i id="ikon-${id}" class="fa-solid fa-square-parking text-4xl text-gray-600 mt-6 transition-all"></i>
                <div id="durum-${id}" class="text-center font-bold py-1 px-2 rounded bg-gray-700 mt-3 text-[11px] w-full transition-all">-</div>
                <div id="kapi-kontrol-${id}" class="absolute bottom-2 w-[90%] flex gap-1 hidden z-20">
                    <button onclick="kapiKomut('${id}', 1)" class="w-1/2 bg-green-600 hover:bg-green-500 py-1.5 rounded text-[10px] font-bold text-white shadow">AÇ</button>
                    <button onclick="kapiKomut('${id}', 0)" class="w-1/2 bg-red-600 hover:bg-red-500 py-1.5 rounded text-[10px] font-bold text-white shadow">KAPAT</button>
                </div>
            </div>`;
        }

        $(document).ready(function() {
            let slotsHTML = '';
            for(let i=1; i<=toplamSlot; i++) slotsHTML += slotHtmlOlustur('slot'+i, i);
            $('#slot-container').html(slotsHTML);
            verileriCek();
            setInterval(verileriCek, 1500); 
        });

        function verileriCek() {
            $.ajax({
                url: 'arayuz_verileri.php',
                type: 'GET',
                dataType: 'json',
                success: function(veri) {
                    if(veri.sensorler && veri.kontrol) {
                        islemleriYap('ana_kapi', veri.sensorler.ana_kapi, veri.kontrol.ana_kapi, veri.kontrol.ana_kapi_mod, 1);
                        ['slot1', 'slot2', 'slot3', 'slot4'].forEach(function(slot) {
                            islemleriYap(slot, veri.sensorler[slot], veri.kontrol[slot], veri.kontrol[slot + '_mod'], veri.kontrol[slot + '_satis']);
                        });
                    }
                }
            });
        }

        function islemleriYap(id, sensor, manuel, mod, satis) {
            let el = $('#durum-' + id);
            let icon = $('#ikon-' + id);
            let box = id === 'ana_kapi' ? $('#durum-ana_kapi').parent() : el.closest('div.bg-gray-800');
            
            let btnOto = $('#btn-oto-' + id);
            let btnMan = $('#btn-man-' + id);
            let kontrolDiv = $('#kapi-kontrol-' + id);

            if(mod == 1) { 
                btnOto.attr('class', 'px-2 py-0.5 rounded text-[9px] font-bold transition bg-blue-600 text-white shadow');
                btnMan.attr('class', 'px-2 py-0.5 rounded text-[9px] font-bold transition bg-gray-700 text-gray-400');
                kontrolDiv.addClass('hidden'); 
            } else { 
                btnMan.attr('class', 'px-2 py-0.5 rounded text-[9px] font-bold transition bg-orange-600 text-white shadow');
                btnOto.attr('class', 'px-2 py-0.5 rounded text-[9px] font-bold transition bg-gray-700 text-gray-400');
                kontrolDiv.removeClass('hidden'); 
            }

            if(satis == 0 && id !== 'ana_kapi') {
                el.text('SATIŞ KAPALI').attr('class', 'text-center font-bold py-1 px-2 mt-3 text-[11px] w-full rounded bg-yellow-500/20 text-yellow-500');
                icon.attr('class', 'fa-solid fa-lock text-4xl mt-6 text-yellow-500');
                box.css('border-color', '#eab308');
                return; 
            }

            let gDurum = (mod == 1) ? sensor : manuel;

            if (id === 'ana_kapi') {
                if(gDurum == 1) { 
                    el.text('AÇIK').attr('class','text-sm font-bold py-1 px-4 mt-2 rounded bg-green-500/20 text-green-400 transition-all');
                    $('#icon-ana_kapi').attr('class','fa-solid fa-door-open text-5xl mt-4 mb-2 text-green-400 transition-all');
                } else {
                    el.text('KAPALI').attr('class','text-sm font-bold py-1 px-4 mt-2 rounded bg-gray-700 text-gray-400 transition-all');
                    $('#icon-ana_kapi').attr('class','fa-solid fa-torii-gate text-5xl mt-4 mb-2 text-gray-500 transition-all');
                }
            } else {
                if(gDurum == 1) { 
                    el.text('DOLU / AÇIK').attr('class', 'text-center font-bold py-1 px-2 mt-3 text-[11px] w-full rounded bg-red-500/20 text-red-400 transition-all');
                    icon.attr('class', 'fa-solid fa-car text-4xl mt-6 text-red-400 transition-all');
                    box.css('border-color', '#ef4444');
                } else { 
                    el.text('BOŞ / KAPALI').attr('class', 'text-center font-bold py-1 px-2 mt-3 text-[11px] w-full rounded bg-green-500/20 text-green-400 transition-all');
                    icon.attr('class', 'fa-solid fa-square-parking text-4xl mt-6 text-green-500 transition-all');
                    box.css('border-color', '#22c55e');
                }
            }
        }

        function kapiModDegistir(kapi, durum) {
    $.post('admin_islem.php', { mod_degistir: true, kapi_adi: kapi, mod: durum }, function(cevap) {
        alert("Sunucu Cevabi: " + cevap);
        verileriCek();
    });
}
        

        function kapiKomut(kapi, durum) {
            $.post('admin_islem.php', { kapi_kontrol: true, kapi_adi: kapi, durum: durum }, function() { verileriCek(); });
        }

        function slotDetayGoster(slotAdi) {
            $('#bilgiModal').removeClass('hidden').addClass('flex');
            $('#modal-icerik').html('<div class="text-center text-gray-400 py-6"><i class="fa-solid fa-spinner fa-spin text-4xl mb-4"></i><p>Taranıyor...</p></div>');
            
            $.ajax({
                url: 'admin_islem.php?slot_detay=' + slotAdi,
                type: 'GET',
                dataType: 'json',
                success: function(c) {
                    if (c.durum === 'bulundu') {
                        let b = c.bilgi;
                        $('#modal-icerik').html(`
                            <div class="space-y-4">
                                <div class="bg-gray-900 p-4 rounded-lg text-center shadow-inner border border-gray-700">
                                    <div class="text-4xl font-black text-blue-400 tracking-widest">${b.plaka}</div>
                                    <div class="text-sm text-gray-400 font-bold mt-1">${b.marka} ${b.model} <span class="text-gray-500">(${b.renk})</span></div>
                                </div>
                                <div class="bg-gray-900 p-3 rounded-lg border border-gray-700">
                                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1"><i class="fa-solid fa-user text-green-400"></i> Üye Bilgisi</div>
                                    <div class="font-bold text-white text-lg">${b.isim} ${b.soyisim}</div>
                                    <div class="text-sm text-gray-400"><i class="fa-solid fa-phone"></i> ${b.telefon}</div>
                                </div>
                                <div class="bg-gray-900 p-3 rounded-lg border border-gray-700">
                                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1"><i class="fa-solid fa-clock text-orange-400"></i> Süre</div>
                                    <div class="text-green-400 font-bold text-sm">Giriş: <span class="text-gray-200">${b.baslangic_saati}</span></div>
                                    <div class="text-red-400 font-bold text-sm">Çıkış: <span class="text-gray-200">${b.bitis_saati}</span></div>
                                </div>
                            </div>
                        `);
                    } else {
                        $('#modal-icerik').html(`<div class="text-center py-6"><i class="fa-solid fa-triangle-exclamation text-5xl text-red-500 mb-4 animate-pulse"></i><h4 class="font-bold text-gray-300 text-xl">Kayıt Yok</h4><p class="text-sm text-gray-500 mt-2">Sensör engeli algılıyor ancak sistemde aktif rezervasyon yok. <span class="text-red-400 font-bold">Kaçak park olabilir.</span></p></div>`);
                    }
                },
                error: function() {
                    $('#modal-icerik').html('<div class="text-center text-red-500 font-bold">Sunucuya bağlanılamadı!</div>');
                }
            });
        }
        
        function modalKapat() { $('#bilgiModal').addClass('hidden').removeClass('flex'); }
    </script>
</body>
</html>