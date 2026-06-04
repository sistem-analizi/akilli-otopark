<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] == 'admin') {
    header("Location: index.php");
    exit;
}

$uye_id = $_SESSION['kullanici_id'];

if(isset($_POST['arac_ekle'])) {
    $marka = $conn->real_escape_string($_POST['marka']);
    $model = $conn->real_escape_string($_POST['model']);
    $yil = (int)$_POST['yil'];
    $renk = $conn->real_escape_string($_POST['renk']);
    $plaka = $conn->real_escape_string($_POST['plaka']);
    
    $sql = "INSERT INTO araclar (uye_id, marka, model, yil, renk, plaka) VALUES ($uye_id, '$marka', '$model', $yil, '$renk', '$plaka')";
    $conn->query($sql);
    header("Location: uye_paneli.php?basari=eklendi");
    exit;
}

if(isset($_GET['arac_sil'])) {
    $silinecek_id = (int)$_GET['arac_sil'];
    $sql = "DELETE FROM araclar WHERE id = $silinecek_id AND uye_id = $uye_id";
    $conn->query($sql);
    header("Location: uye_paneli.php?basari=arac_silindi");
    exit;
}

if(isset($_POST['rezervasyon_yap'])) {
    $slot_adi = $conn->real_escape_string($_POST['slot_adi']);
    $arac_id = (int)$_POST['arac_id'];
    $sure = (int)$_POST['sure'];
    
    $fiyat_sorgu = $conn->query("SELECT taban_fiyat FROM cihaz_kontrol WHERE id = 1");
    $ayarlar = $fiyat_sorgu->fetch_assoc();
    $toplam_tutar = $sure * $ayarlar['taban_fiyat'];
    
    $baslangic = date('Y-m-d H:i:s');
    $bitis = date('Y-m-d H:i:s', strtotime("+$sure hours"));
    
    $sql = "INSERT INTO rezervasyonlar (uye_id, arac_id, slot_adi, durum, baslangic_saati, sure, bitis_saati, toplam_tutar, odeme_durumu) VALUES ($uye_id, $arac_id, '$slot_adi', 'aktif', '$baslangic', $sure, '$bitis', $toplam_tutar, 'odendi')";
    $conn->query($sql);
    header("Location: uye_paneli.php?basari=rezerve_edildi");
    exit;
}

if(isset($_GET['erken_cikis'])) {
    $rez_id = (int)$_GET['erken_cikis'];
    $simdi = date('Y-m-d H:i:s');
    $sql = "UPDATE rezervasyonlar SET durum = 'tamamlandi', bitis_saati = '$simdi' WHERE id = $rez_id AND uye_id = $uye_id";
    $conn->query($sql);
    header("Location: uye_paneli.php?basari=erken_cikis");
    exit;
}

$araclar_sorgu = $conn->query("SELECT * FROM araclar WHERE uye_id = $uye_id ORDER BY id DESC");
$araclar = [];
while($a = $araclar_sorgu->fetch_assoc()) {
    $araclar[] = $a;
}

$rezervasyon_sorgu = $conn->query("SELECT * FROM rezervasyonlar WHERE uye_id = $uye_id ORDER BY id DESC");

$aktif_rez_sorgu = $conn->query("SELECT slot_adi FROM rezervasyonlar WHERE durum = 'aktif'");
$dolu_slotlar = [];
while($r = $aktif_rez_sorgu->fetch_assoc()) {
    $dolu_slotlar[] = $r['slot_adi'];
}

$ayarlar_sorgu = $conn->query("SELECT * FROM cihaz_kontrol WHERE id = 1");
$ayarlar = $ayarlar_sorgu->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Üye Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        const arabaVerileri = {
            "Audi": ["A3", "A4", "A6", "Q3", "Q5", "Q7"],
            "BMW": ["1 Serisi", "3 Serisi", "5 Serisi", "X3", "X5"],
            "Cupra": ["Formentor", "Leon", "Ateca", "Born"],
            "Fiat": ["Egea", "Fiorino", "Linea", "Punto", "Doblo"],
            "Ford": ["Focus", "Fiesta", "Courier", "Kuga", "Puma"],
            "Honda": ["Civic", "CR-V", "City", "Accord"],
            "Mercedes-Benz": ["A-Serisi", "C-Serisi", "E-Serisi", "GLA"],
            "Renault": ["Megane", "Clio", "Symbol", "Taliant", "Captur"],
            "Toyota": ["Corolla", "Yaris", "C-HR", "Hilux"],
            "Volkswagen": ["Golf", "Passat", "Polo", "Tiguan", "T-Roc"],
            "Togg": ["T10X"]
        };

        function markaSecildi() {
            const markaSelect = document.getElementById("markaSelect");
            const modelSelect = document.getElementById("modelSelect");
            
            if(!markaSelect || !modelSelect) return;
            
            const secilenMarka = markaSelect.value;
            modelSelect.innerHTML = '<option value="">Önce Model Seçin</option>';
            
            if(secilenMarka && arabaVerileri[secilenMarka]) {
                arabaVerileri[secilenMarka].forEach(model => {
                    let opt = document.createElement('option');
                    opt.value = model;
                    opt.innerHTML = model;
                    modelSelect.appendChild(opt);
                });
                modelSelect.disabled = false;
                modelSelect.classList.remove('bg-gray-200', 'cursor-not-allowed');
            } else {
                modelSelect.disabled = true;
                modelSelect.classList.add('bg-gray-200', 'cursor-not-allowed');
            }
        }

        function sekmeDegistir(sekmeAdi) {
            document.querySelectorAll('.sekme-icerik').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.sekme-buton').forEach(el => el.classList.remove('bg-blue-600', 'text-white', 'shadow-lg'));
            document.querySelectorAll('.sekme-buton').forEach(el => el.classList.add('bg-white', 'text-gray-600'));
            
            let icerik = document.getElementById(sekmeAdi);
            let buton = document.getElementById('btn-' + sekmeAdi);
            
            if(icerik) icerik.classList.remove('hidden');
            if(buton) {
                buton.classList.remove('bg-white', 'text-gray-600');
                buton.classList.add('bg-blue-600', 'text-white', 'shadow-lg');
            }
        }

        function modalAc(slotAdi) {
            let secilenSlotInp = document.getElementById('secilen_slot');
            let modalIsim = document.getElementById('modal_slot_isim');
            let modal = document.getElementById('rezervasyon_modal');
            
            if(secilenSlotInp) secilenSlotInp.value = slotAdi;
            if(modalIsim) modalIsim.innerText = slotAdi.toUpperCase();
            
            hesapla();
            
            if(modal) modal.classList.remove('hidden');
        }

        function modalKapat() {
            let modal = document.getElementById('rezervasyon_modal');
            if(modal) modal.classList.add('hidden');
        }
        
        function hesapla() {
            let sureSecim = document.getElementById('sure_secim');
            let tutarAlan = document.getElementById('toplam_tutar');
            
            if(sureSecim && tutarAlan) {
                let sure = parseInt(sureSecim.value) || 1;
                let fiyat = <?= isset($ayarlar['taban_fiyat']) ? (int)$ayarlar['taban_fiyat'] : 50 ?>;
                tutarAlan.innerText = sure * fiyat;
            }
        }
    </script>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-indigo-900 text-white shadow-xl p-4 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="text-2xl font-black tracking-wider"><i class="fa-solid fa-square-parking text-indigo-400 mr-2"></i>PARK<span class="text-indigo-400">NET</span></div>
            <div class="flex items-center space-x-6">
                <div class="flex items-center bg-indigo-800 px-4 py-2 rounded-full border border-indigo-700">
                    <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center font-bold mr-3 shadow-inner">
                        <?= substr($_SESSION['isim'], 0, 1) ?>
                    </div>
                    <span class="font-semibold"><?= $_SESSION['isim'] ?> <?= $_SESSION['soyisim'] ?></span>
                </div>
                <a href="giris_islem.php?cikis=1" class="text-indigo-200 hover:text-white hover:bg-red-500 px-4 py-2 rounded-lg font-bold transition duration-300"><i class="fa-solid fa-arrow-right-from-bracket mr-2"></i>Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-6 mt-8">
        
        <?php if(isset($_GET['basari'])): ?>
            <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-8 rounded shadow-sm flex items-center">
                <i class="fa-solid fa-circle-check text-2xl mr-4"></i>
                <div>
                    <p class="font-bold">İşlem Başarılı</p>
                    <p>Sistem isteğinizi başarıyla kaydetti.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex space-x-4 mb-8 overflow-x-auto pb-2">
            <button id="btn-kroki" onclick="sekmeDegistir('kroki')" class="sekme-buton bg-blue-600 text-white shadow-lg px-8 py-3 rounded-xl font-bold transition-all duration-300 flex items-center whitespace-nowrap"><i class="fa-solid fa-map-location-dot mr-3 text-lg"></i>Otopark Krokisi</button>
            <button id="btn-profil" onclick="sekmeDegistir('profil')" class="sekme-buton bg-white text-gray-600 hover:bg-gray-50 px-8 py-3 rounded-xl font-bold transition-all duration-300 shadow flex items-center whitespace-nowrap"><i class="fa-solid fa-user-astronaut mr-3 text-lg"></i>Kullanıcı Profili</button>
            <button id="btn-araclar" onclick="sekmeDegistir('araclar')" class="sekme-buton bg-white text-gray-600 hover:bg-gray-50 px-8 py-3 rounded-xl font-bold transition-all duration-300 shadow flex items-center whitespace-nowrap"><i class="fa-solid fa-car-side mr-3 text-lg"></i>Garajım</button>
            <button id="btn-gecmis" onclick="sekmeDegistir('gecmis')" class="sekme-buton bg-white text-gray-600 hover:bg-gray-50 px-8 py-3 rounded-xl font-bold transition-all duration-300 shadow flex items-center whitespace-nowrap"><i class="fa-solid fa-clock-rotate-left mr-3 text-lg"></i>İşlem Geçmişi</button>
        </div>

        <div id="kroki" class="sekme-icerik bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
            <h2 class="text-3xl font-black mb-8 text-gray-800 border-b pb-4"><i class="fa-solid fa-warehouse text-indigo-500 mr-3"></i>Otopark Durumu & Yer Seçimi</h2>
            
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-10 rounded-2xl relative shadow-inner">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 bg-yellow-400 text-gray-900 font-black px-8 py-2 rounded-b-xl shadow-md tracking-wider">
                    GİRİŞ KAPISI
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-8">
                    <?php 
                    for($i=1; $i<=4; $i++): 
                        $slot_adi = "slot" . $i;
                        $satista_mi = $ayarlar[$slot_adi . '_satis'];
                        $dolu_mu = (in_array($slot_adi, $dolu_slotlar) || $ayarlar[$slot_adi] == 1);
                    ?>
                    <div class="border-4 <?= $dolu_mu ? 'border-red-500 bg-red-500/10' : ($satista_mi == 0 ? 'border-gray-600 bg-gray-800' : 'border-dashed border-indigo-400 bg-slate-800 hover:bg-slate-700 hover:border-indigo-300') ?> p-6 rounded-2xl h-72 flex flex-col items-center justify-center relative transition duration-300">
                        <div class="absolute top-3 left-4 text-slate-500 font-black text-2xl">S<?= $i ?></div>
                        
                        <?php if($satista_mi == 0): ?>
                            <i class="fa-solid fa-triangle-exclamation text-6xl text-gray-600 mb-4"></i>
                            <span class="bg-gray-700 text-gray-300 font-bold px-4 py-2 rounded-lg text-sm">BAKIMDA</span>
                        <?php elseif($dolu_mu): ?>
                            <i class="fa-solid fa-car text-7xl text-red-500 mb-4 drop-shadow-lg"></i>
                            <span class="bg-red-500 text-white font-black px-5 py-2 rounded-lg shadow-lg tracking-wider">DOLU</span>
                        <?php else: ?>
                            <i class="fa-solid fa-square-parking text-6xl text-emerald-400 mb-4"></i>
                            <span class="text-emerald-400 font-black text-xl mb-6 tracking-wide">MÜSAİT</span>
                            <button onclick="modalAc('<?= $slot_adi ?>')" class="bg-indigo-500 hover:bg-indigo-400 text-white font-black px-6 py-3 rounded-xl shadow-[0_0_15px_rgba(99,102,241,0.4)] transition w-full">REZERVE ET</button>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <div id="profil" class="sekme-icerik hidden bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-10">
                <div class="w-48 h-48 bg-gradient-to-tr from-indigo-500 to-blue-400 rounded-full flex items-center justify-center text-white shadow-2xl border-4 border-white">
                    <i class="fa-solid fa-user text-7xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-4xl font-black text-gray-800 mb-2"><?= $_SESSION['isim'] ?> <?= $_SESSION['soyisim'] ?></h2>
                    <span class="bg-indigo-100 text-indigo-700 px-4 py-1 rounded-full text-sm font-bold tracking-wide">Onaylı Üye</span>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                        <div class="bg-slate-50 p-5 rounded-xl border border-slate-100 flex items-center">
                            <div class="w-12 h-12 bg-white rounded-lg shadow flex items-center justify-center text-indigo-500 mr-4">
                                <i class="fa-solid fa-id-badge text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Müşteri Numarası</p>
                                <p class="font-mono text-lg font-bold text-gray-700">#PRK-<?= str_pad($_SESSION['kullanici_id'], 4, '0', STR_PAD_LEFT) ?></p>
                            </div>
                        </div>
                        <div class="bg-slate-50 p-5 rounded-xl border border-slate-100 flex items-center">
                            <div class="w-12 h-12 bg-white rounded-lg shadow flex items-center justify-center text-indigo-500 mr-4">
                                <i class="fa-solid fa-shield-halved text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Hesap Yetkisi</p>
                                <p class="font-bold text-lg text-gray-700">Standart Kullanıcı</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="araclar" class="sekme-icerik hidden bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">
                <div class="lg:col-span-2">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-8 rounded-2xl shadow-lg text-white">
                        <h3 class="font-black text-2xl mb-6 flex items-center"><i class="fa-solid fa-plus-circle text-blue-400 mr-3"></i>Yeni Araç Ekle</h3>
                        <form action="uye_paneli.php" method="POST" class="space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Araç Markası</label>
                                <div class="relative">
                                    <select name="marka" id="markaSelect" onchange="markaSecildi()" required class="w-full bg-slate-700 border-none text-white p-4 rounded-xl appearance-none focus:ring-2 focus:ring-blue-500 font-semibold cursor-pointer">
                                        <option value="">Marka Seçiniz</option>
                                        <option value="Audi">Audi</option>
                                        <option value="BMW">BMW</option>
                                        <option value="Cupra">Cupra</option>
                                        <option value="Fiat">Fiat</option>
                                        <option value="Ford">Ford</option>
                                        <option value="Honda">Honda</option>
                                        <option value="Mercedes-Benz">Mercedes-Benz</option>
                                        <option value="Renault">Renault</option>
                                        <option value="Toyota">Toyota</option>
                                        <option value="Volkswagen">Volkswagen</option>
                                        <option value="Togg">Togg</option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down absolute right-4 top-5 text-slate-400 pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Araç Modeli</label>
                                <div class="relative">
                                    <select name="model" id="modelSelect" required disabled class="w-full bg-slate-700 border-none text-white p-4 rounded-xl appearance-none focus:ring-2 focus:ring-blue-500 font-semibold bg-gray-200 cursor-not-allowed">
                                        <option value="">Önce Marka Seçin</option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down absolute right-4 top-5 text-slate-400 pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Plaka</label>
                                <input type="text" name="plaka" placeholder="Örn: 34 ABC 123" required class="w-full bg-slate-700 border-none text-white p-4 rounded-xl focus:ring-2 focus:ring-blue-500 font-bold uppercase placeholder-slate-500 tracking-wider">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Üretim Yılı</label>
                                    <select name="yil" required class="w-full bg-slate-700 border-none text-white p-4 rounded-xl appearance-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                        <?php for($y=2024; $y>=1990; $y--): ?>
                                            <option value="<?= $y ?>"><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Renk</label>
                                    <input type="text" name="renk" placeholder="Siyah, Beyaz vb." required class="w-full bg-slate-700 border-none text-white p-4 rounded-xl focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <button type="submit" name="arac_ekle" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-xl transition duration-300 shadow-[0_0_15px_rgba(37,99,235,0.5)] mt-4">GARAJA EKLE</button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <h3 class="font-black text-2xl text-gray-800 mb-6 flex items-center border-b pb-4"><i class="fa-solid fa-warehouse text-indigo-500 mr-3"></i>Kayıtlı Araçlarım</h3>
                    <?php if(count($araclar) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach($araclar as $arac): ?>
                                <div class="bg-white border-2 border-slate-100 rounded-2xl p-6 hover:border-indigo-500 transition duration-300 shadow-sm hover:shadow-xl group relative overflow-hidden">
                                    <div class="absolute top-0 right-0 bg-indigo-500 text-white px-4 py-1 rounded-bl-2xl font-bold text-sm shadow-md">
                                        <?= $arac['yil'] ?>
                                    </div>
                                    
                                    <a href="uye_paneli.php?arac_sil=<?= $arac['id'] ?>" onclick="return confirm('Bu aracı silmek istediğinize emin misiniz?');" class="absolute bottom-4 right-4 w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition duration-300 shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>

                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-indigo-300 group-hover:text-indigo-600 group-hover:scale-110 transition duration-300">
                                        <i class="fa-solid fa-car-side text-3xl"></i>
                                    </div>
                                    <h4 class="text-2xl font-black text-gray-800 tracking-wider mb-1"><?= $arac['plaka'] ?></h4>
                                    <p class="text-lg font-bold text-indigo-600 mb-2"><?= $arac['marka'] ?> <span class="text-gray-500 font-medium"><?= $arac['model'] ?></span></p>
                                    <div class="flex items-center text-sm font-bold text-gray-400">
                                        <i class="fa-solid fa-palette mr-2"></i><?= $arac['renk'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-indigo-50 text-indigo-800 p-8 rounded-2xl border border-indigo-100 text-center">
                            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-300 shadow">
                                <i class="fa-solid fa-car-tunnel text-4xl"></i>
                            </div>
                            <p class="text-xl font-bold">Garajınız şu an boş</p>
                            <p class="text-indigo-600 mt-2">Sol taraftaki formu kullanarak hemen bir araç ekleyin.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="gecmis" class="sekme-icerik hidden bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
            <h2 class="text-3xl font-black mb-8 text-gray-800 border-b pb-4"><i class="fa-solid fa-clock-rotate-left text-indigo-500 mr-3"></i>Otopark Kullanım Geçmişi</h2>
            
            <?php if($rezervasyon_sorgu->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($rez = $rezervasyon_sorgu->fetch_assoc()): ?>
                        <div class="p-6 border-2 border-slate-50 hover:border-slate-200 rounded-2xl bg-white flex flex-col md:flex-row justify-between items-center transition duration-300 shadow-sm relative overflow-hidden">
                            
                            <?php 
                            $zaman_metni = "Süre Belirsiz";
                            $zaman_renk = "text-gray-500";

                            if($rez['durum'] == 'aktif' && $rez['bitis_saati'] != null) {
                                $bitis = strtotime($rez['bitis_saati']);
                                $simdi = time();
                                $kalan_saniye = $bitis - $simdi;
                                
                                if($kalan_saniye > 0) {
                                    $kalan_saat = floor($kalan_saniye / 3600);
                                    $kalan_dakika = floor(($kalan_saniye % 3600) / 60);
                                    $zaman_metni = $kalan_saat . " Saat " . $kalan_dakika . " Dk Kaldı";
                                    $zaman_renk = "text-indigo-600";
                                } else {
                                    $zaman_metni = "Süre Doldu!";
                                    $zaman_renk = "text-red-500";
                                }
                            }
                            ?>
                            
                            <div class="flex items-center mb-4 md:mb-0 w-full md:w-auto">
                                <div class="w-16 h-16 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center mr-6 text-3xl font-black border-2 border-slate-200">
                                    <?= str_replace('slot', 'S', strtolower($rez['slot_adi'])) ?>
                                </div>
                                <div>
                                    <p class="font-black text-gray-800 text-xl"><?= strtoupper($rez['slot_adi']) ?> Rezervasyonu</p>
                                    <p class="text-sm font-bold text-gray-400 mt-1"><i class="fa-regular fa-calendar-check mr-2"></i>Giriş: <?= date('d.m.Y H:i', strtotime($rez['baslangic_saati'])) ?></p>
                                    <p class="text-sm font-bold text-gray-400"><i class="fa-solid fa-money-bill-wave mr-1 mt-1"></i> Toplam Tutar: <span class="text-green-600 font-black"><?= $rez['toplam_tutar'] ?> TL</span></p>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-3 w-full md:w-auto">
                                <span class="px-5 py-2 rounded-lg text-sm font-black shadow-sm <?= $rez['durum'] == 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>">
                                    <?= strtoupper($rez['durum']) ?>
                                </span>
                                
                                <?php if($rez['durum'] == 'aktif'): ?>
                                    <div class="font-black text-sm <?= $zaman_renk ?> bg-slate-50 px-3 py-1 rounded border border-slate-200">
                                        <i class="fa-solid fa-hourglass-half mr-1"></i> <?= $zaman_metni ?>
                                    </div>
                                    <a href="uye_paneli.php?erken_cikis=<?= $rez['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-black shadow transition text-sm flex items-center mt-1 w-full md:w-auto justify-center">
                                        <i class="fa-solid fa-door-open mr-2"></i>Erken Çıkış Yap
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="py-16 text-center">
                    <i class="fa-regular fa-folder-open text-6xl text-slate-300 mb-4"></i>
                    <p class="text-xl font-bold text-slate-500">Sistemde kayıtlı park işleminiz bulunmamaktadır.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <div id="rezervasyon_modal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-2xl w-full max-w-md shadow-2xl relative">
            <div class="absolute top-0 right-0 bg-indigo-500 text-white px-4 py-2 rounded-bl-2xl rounded-tr-2xl font-black tracking-wider">
                GÜVENLİ ÖDEME
            </div>
            
            <h3 class="text-3xl font-black text-gray-800 mb-6 mt-2 border-b pb-4"><span id="modal_slot_isim" class="text-indigo-600"></span> REZERVASYONU</h3>
            
            <?php if(count($araclar) > 0): ?>
            <form action="uye_paneli.php" method="POST">
                <input type="hidden" name="slot_adi" id="secilen_slot">
                
                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Park Edecek Araç</label>
                <div class="relative mb-5">
                    <select name="arac_id" required class="w-full bg-slate-50 border-2 border-slate-200 text-gray-800 p-4 rounded-xl appearance-none focus:border-indigo-500 font-bold outline-none cursor-pointer">
                        <?php foreach($araclar as $arac): ?>
                            <option value="<?= $arac['id'] ?>"><?= $arac['plaka'] ?> (<?= $arac['marka'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-4 top-5 text-slate-400 pointer-events-none"></i>
                </div>

                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Park Süresi</label>
                <div class="relative mb-5">
                    <select name="sure" id="sure_secim" onchange="hesapla()" required class="w-full bg-slate-50 border-2 border-slate-200 text-gray-800 p-4 rounded-xl appearance-none focus:border-indigo-500 font-bold outline-none cursor-pointer">
                        <?php for($i=1; $i<=24; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> Saat Kalacağım</option>
                        <?php endfor; ?>
                    </select>
                    <i class="fa-solid fa-clock absolute right-4 top-5 text-slate-400 pointer-events-none"></i>
                </div>

                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Kart Bilgileri</label>
                <input type="text" placeholder="Kart Numarası (16 Hane)" required class="w-full bg-slate-50 border-2 border-slate-200 text-gray-800 p-4 rounded-xl focus:border-indigo-500 font-bold mb-3 outline-none" maxlength="16">
                <div class="flex gap-3 mb-6">
                    <input type="text" placeholder="AA/YY" required class="w-1/2 bg-slate-50 border-2 border-slate-200 text-gray-800 p-4 rounded-xl focus:border-indigo-500 font-bold outline-none text-center" maxlength="5">
                    <input type="text" placeholder="CVV" required class="w-1/2 bg-slate-50 border-2 border-slate-200 text-gray-800 p-4 rounded-xl focus:border-indigo-500 font-bold outline-none text-center" maxlength="3">
                </div>

                <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl mb-6 flex justify-between items-center shadow-sm">
                    <span class="font-bold text-indigo-900 text-sm uppercase">Ödenecek Tutar</span>
                    <span class="font-black text-2xl text-indigo-600"><span id="toplam_tutar"><?= isset($ayarlar['taban_fiyat']) ? $ayarlar['taban_fiyat'] : 50 ?></span> TL</span>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="modalKapat()" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-4 rounded-xl transition">İPTAL</button>
                    <button type="submit" name="rezervasyon_yap" class="w-2/3 bg-green-500 hover:bg-green-600 text-white font-black py-4 rounded-xl transition shadow-[0_0_15px_rgba(34,197,94,0.4)] flex items-center justify-center">
                        <i class="fa-solid fa-lock mr-2"></i>ÖDE & ONAYLA
                    </button>
                </div>
            </form>
            <?php else: ?>
                <div class="bg-red-50 text-red-600 p-6 rounded-xl font-bold mb-4 text-center border border-red-200 flex flex-col items-center">
                    <i class="fa-solid fa-triangle-exclamation text-4xl mb-3 text-red-400"></i>
                    Sisteme kayıtlı aracınız bulunmamaktadır. Lütfen önce profilinizden bir araç ekleyin.
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="modalKapat()" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-4 rounded-xl transition">İPTAL</button>
                    <button type="button" onclick="modalKapat(); sekmeDegistir('araclar');" class="w-2/3 bg-indigo-600 hover:bg-indigo-500 text-white font-black py-4 rounded-xl transition shadow-lg">ARAÇ EKLEMEYE GİT</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
