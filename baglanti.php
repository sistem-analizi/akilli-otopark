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
    public $connect_error = null;

    public function __construct($dosya_yolu) {
        try {
            $ilk_kurulum = !file_exists($dosya_yolu);
            $this->db = new SQLite3($dosya_yolu);

            if ($ilk_kurulum) {
                $this->veritabaniniInsaEt();
            }
        } catch (Exception $e) {
            $this->connect_error = $e->getMessage();
        }
    }

    private function veritabaniniInsaEt() {
        $schema_yolu = __DIR__ . '/schema.sql';
        if (file_exists($schema_yolu)) {
            $sql = file_get_contents($schema_yolu);
            if ($sql !== false) {
                $this->db->exec($sql);
            }
        }
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

    public function set_charset($charset) {
        return true; 
    }

    public function close() {
        if ($this->db) {
            $this->db->close();
        }
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

if ($conn->connect_error) {
    die("Baglanti Hatasi: " . $conn->connect_error);
}
?>
