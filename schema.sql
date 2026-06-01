-- ─── uyeler ────────────────────────────────────────────────────────────────
CREATE TABLE uyeler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    isim TEXT NOT NULL,
    soyisim TEXT NOT NULL,
    telefon TEXT NOT NULL,
    eposta TEXT NOT NULL,
    sifre TEXT NOT NULL,
    rol TEXT DEFAULT 'uye',
    kayit_tarihi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO uyeler (id, isim, soyisim, telefon, eposta, sifre, rol, kayit_tarihi) VALUES
(1, 'Kurucu', 'Yönetici', '05555555555', 'admin@otopark.com', 'e10adc3949ba59abbe56e057f20f883e', 'admin', '2026-04-19 16:42:44'),
(2, 'can', 'palantöken', '05308639412', 'plntkncan12@gmail.com', '926d19e70f74c20f796d376aafa3c6e2', 'admin', '2026-04-19 17:06:21');

-- ─── araclar ───────────────────────────────────────────────────────────────
CREATE TABLE araclar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uye_id INTEGER NOT NULL,
    marka TEXT NOT NULL,
    model TEXT NOT NULL,
    yil INTEGER NOT NULL,
    renk TEXT NOT NULL,
    plaka TEXT NOT NULL,
    eklenme_tarihi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_araclar_uye_id ON araclar(uye_id);

INSERT INTO araclar (id, uye_id, marka, model, yil, renk, plaka, eklenme_tarihi) VALUES
(2, 2, 'Volkswagen', 'T-Roc', 2023, 'Siyah', '34 BZU 127', '2026-04-20 06:37:15');

-- ─── cihaz_kontrol (singleton — id=1 sabit) ────────────────────────────────
CREATE TABLE cihaz_kontrol (
    id INTEGER PRIMARY KEY,
    ana_kapi_mod INTEGER NOT NULL DEFAULT 1,
    slot1_mod INTEGER NOT NULL DEFAULT 1,
    slot2_mod INTEGER NOT NULL DEFAULT 1,
    slot3_mod INTEGER NOT NULL DEFAULT 1,
    slot4_mod INTEGER NOT NULL DEFAULT 1,
    ana_kapi INTEGER NOT NULL DEFAULT 0,
    slot1 INTEGER NOT NULL DEFAULT 0,
    slot2 INTEGER NOT NULL DEFAULT 0,
    slot3 INTEGER NOT NULL DEFAULT 0,
    slot4 INTEGER NOT NULL DEFAULT 0,
    taban_fiyat INTEGER NOT NULL DEFAULT 20,
    slot1_satis INTEGER NOT NULL DEFAULT 1,
    slot2_satis INTEGER NOT NULL DEFAULT 1,
    slot3_satis INTEGER NOT NULL DEFAULT 1,
    slot4_satis INTEGER NOT NULL DEFAULT 1
);

INSERT INTO cihaz_kontrol (id, ana_kapi_mod, slot1_mod, slot2_mod, slot3_mod, slot4_mod, ana_kapi, slot1, slot2, slot3, slot4, taban_fiyat, slot1_satis, slot2_satis, slot3_satis, slot4_satis) VALUES
(1, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 50, 1, 1, 1, 1);

-- ─── rezervasyonlar ────────────────────────────────────────────────────────
CREATE TABLE rezervasyonlar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uye_id INTEGER NOT NULL,
    arac_id INTEGER NOT NULL,
    slot_adi TEXT NOT NULL,
    baslangic_saati DATETIME NOT NULL,
    bitis_saati DATETIME NOT NULL,
    toplam_tutar REAL NOT NULL,
    durum TEXT DEFAULT 'aktif',
    islem_tarihi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO rezervasyonlar (id, uye_id, arac_id, slot_adi, baslangic_saati, bitis_saati, toplam_tutar, durum, islem_tarihi) VALUES
(1,  2, 1, 'slot3', '2026-04-19 19:07:55', '2026-04-19 20:48:58', 40.00, 'tamamlandi', '2026-04-19 17:07:55'),
(2,  2, 1, 'slot3', '2026-04-19 19:52:38', '2026-04-19 21:16:39', 20.00, 'tamamlandi', '2026-04-19 17:52:38'),
(3,  2, 1, 'slot1', '2026-04-19 20:09:54', '2026-04-20 09:35:11', 20.00, 'tamamlandi', '2026-04-19 18:09:54'),
(4,  2, 1, 'slot1', '2026-04-20 08:36:14', '2026-04-20 09:36:26', 20.00, 'tamamlandi', '2026-04-20 06:36:14'),
(5,  2, 2, 'slot1', '2026-04-20 08:48:43', '2026-04-20 09:49:04', 20.00, 'tamamlandi', '2026-04-20 06:48:43'),
(6,  2, 2, 'slot1', '2026-04-20 08:55:01', '2026-04-20 09:55:04', 40.00, 'tamamlandi', '2026-04-20 06:55:01'),
(7,  2, 2, 'slot1', '2026-04-20 08:55:39', '2026-04-28 15:12:41', 40.00, 'tamamlandi', '2026-04-20 06:55:39'),
(8,  2, 2, 'slot1', '2026-04-28 14:15:54', '2026-04-28 15:16:03', 20.00, 'tamamlandi', '2026-04-28 12:15:54'),
(9,  2, 2, 'slot2', '2026-05-11 09:10:54', '2026-05-11 10:11:06', 40.00, 'tamamlandi', '2026-05-11 07:10:54'),
(10, 2, 2, 'slot2', '2026-05-11 09:12:12', '2026-05-11 10:15:26', 40.00, 'tamamlandi', '2026-05-11 07:12:12'),
(11, 2, 2, 'slot1', '2026-05-11 09:50:52', '2026-05-11 10:53:40', 50.00, 'tamamlandi', '2026-05-11 07:50:52');

-- ─── sensor_kayitlari (boş başlar, runtime'da Arduino doldurur) ───────────
CREATE TABLE sensor_kayitlari (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ana_kapi INTEGER DEFAULT 0,
    slot1 INTEGER DEFAULT 0,
    slot2 INTEGER DEFAULT 0,
    slot3 INTEGER DEFAULT 0,
    slot4 INTEGER DEFAULT 0,
    islem_zamani DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
