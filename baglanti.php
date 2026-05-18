<?php
class EnvYukleyici {
    public static function yukle($dosya_yolu) {
        if (!file_exists($dosya_yolu)) {
            return false;
        }
        
        $satirlar = file($dosya_yolu, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($satirlar as $satir) {
            if (strpos(trim($satir), '#') === 0) {
                continue;
            }
            
            list($isim, $deger) = explode('=', $satir, 2);
            $isim = trim($isim);
            $deger = trim($deger);
            
            if (!array_key_exists($isim, $_SERVER) && !array_key_exists($isim, $_ENV)) {
                putenv(sprintf('%s=%s', $isim, $deger));
                $_ENV[$isim] = $deger;
                $_SERVER[$isim] = $deger;
            }
        }
        return true;
    }
}

class SQLiteKoprusu {
    public $db;
    public $error = "";
    public $insert_id = 0;
    public $affected_rows = 0;
    
    public function __construct($dosya_yolu) {
        $ilk_kurulum = !file_exists($dosya_yolu);
        $this->db = new SQLite3($dosya_yolu);
        
        if ($ilk_kurulum) {
            $this->veritabaniniInsaEt();
        }
    }
    
    private function veritabaniniInsaEt() {
        $sql = "
        CREATE TABLE IF NOT EXISTS uyeler (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            isim TEXT,
            soyisim TEXT,
            eposta TEXT,
            telefon TEXT,
            sifre TEXT,
            rol TEXT,
            kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS araclar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uye_id INTEGER,
            marka TEXT,
            model TEXT,
            yil INTEGER,
            renk TEXT,
            plaka TEXT
        );

        CREATE TABLE IF NOT EXISTS rezervasyonlar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uye_id INTEGER,
            arac_id INTEGER,
            slot_adi TEXT,
            baslangic_saati DATETIME,
            bitis_saati DATETIME,
            toplam_tutar REAL,
            durum TEXT
        );

        CREATE TABLE IF NOT EXISTS cihaz_kontrol (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            taban_fiyat INTEGER,
            ana_kapi INTEGER,
            ana_kapi_mod INTEGER,
            slot1 INTEGER,
            slot2 INTEGER,
            slot3 INTEGER,
            slot4 INTEGER,
            slot1_mod INTEGER,
            slot2_mod INTEGER,
            slot3_mod INTEGER,
            slot4_mod INTEGER,
            slot1_satis INTEGER,
            slot2_satis INTEGER,
            slot3_satis INTEGER,
            slot4_satis INTEGER
        );

        CREATE TABLE IF NOT EXISTS sensor_kayitlari (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sensor_adi TEXT,
            durum INTEGER,
            tarih DATETIME
        );

        INSERT INTO cihaz_kontrol (id, taban_fiyat, ana_kapi, ana_kapi_mod, slot1, slot2, slot3, slot4, slot1_mod, slot2_mod, slot3_mod, slot4_mod, slot1_satis, slot2_satis, slot3_satis, slot4_satis) 
        VALUES (1, 20, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
        ";

        $this->db->exec($sql);
    }
    
    public function query($sql) {
        $this->error = ""; 
        
        $is_write = preg_match('/^\s*(INSERT|UPDATE|DELETE|REPLACE|CREATE|ALTER|DROP)/i', $sql);
        
        if ($is_write) {
            $sonuc = $this->db->exec($sql);
            if ($sonuc === false) {
                $this->error = $this->db->lastErrorMsg();
                return false;
            }
            $this->insert_id = $this->db->lastInsertRowID();
            $this->affected_rows = $this->db->changes();
            return true;
        } else {
            $sonuc = $this->db->query($sql);
            if ($sonuc === false) {
                $this->error = $this->db->lastErrorMsg();
                return false;
            }
            return new SQLiteSonuc($sonuc);
        }
    }
    
    public function real_escape_string($str) {
        return $this->db->escapeString($str);
    }
}

class SQLiteSonuc {
    private $result;
    public $num_rows = 0;
    
    public function __construct($res) {
        $this->result = $res;
        while ($res->fetchArray(SQLITE3_NUM)) {
            $this->num_rows++;
        }
        $res->reset(); 
    }
    
    public function fetch_assoc() {
        $row = $this->result->fetchArray(SQLITE3_ASSOC);
        return $row ? $row : null; 
    }
}

EnvYukleyici::yukle(__DIR__ . '/.env');

$db_adi = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'otopark.db';
$db_yolu = __DIR__ . '/' . $db_adi;

$conn = new SQLiteKoprusu($db_yolu);
?>