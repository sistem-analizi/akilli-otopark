CREATE TABLE IF NOT EXISTS uyeler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    isim TEXT,
    soyisim TEXT,
    telefon TEXT,
    eposta TEXT,
    sifre TEXT,
    rol TEXT DEFAULT 'uye'
);

CREATE TABLE IF NOT EXISTS araclar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uye_id INTEGER,
    marka TEXT,
    model TEXT,
    marka_model TEXT,
    yil INTEGER,
    renk TEXT,
    plaka TEXT
);

CREATE TABLE IF NOT EXISTS rezervasyonlar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uye_id INTEGER,
    arac_id INTEGER,
    slot_adi TEXT,
    durum TEXT DEFAULT 'aktif',
    baslangic_saati DATETIME,
    sure INTEGER DEFAULT 1,
    bitis_saati DATETIME,
    toplam_tutar INTEGER DEFAULT 0,
    odeme_durumu TEXT DEFAULT 'bekliyor'
);

CREATE TABLE IF NOT EXISTS cihaz_kontrol (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ana_kapi INTEGER DEFAULT 0,
    slot1 INTEGER DEFAULT 0,
    slot2 INTEGER DEFAULT 0,
    slot3 INTEGER DEFAULT 0,
    slot4 INTEGER DEFAULT 0,
    ana_kapi_mod INTEGER DEFAULT 0,
    slot1_mod INTEGER DEFAULT 0,
    slot2_mod INTEGER DEFAULT 0,
    slot3_mod INTEGER DEFAULT 0,
    slot4_mod INTEGER DEFAULT 0,
    slot1_satis INTEGER DEFAULT 1,
    slot2_satis INTEGER DEFAULT 1,
    slot3_satis INTEGER DEFAULT 1,
    slot4_satis INTEGER DEFAULT 1,
    taban_fiyat INTEGER DEFAULT 50,
    mod_durumu INTEGER DEFAULT 0
);

INSERT INTO cihaz_kontrol (taban_fiyat) VALUES (50);
INSERT INTO uyeler (isim, soyisim, telefon, eposta, sifre, rol) VALUES ('Admin', 'Sistem', '05550000000', 'admin@otopark.com', 'e10adc3949ba59abbe56e057f20f883e', 'admin');
