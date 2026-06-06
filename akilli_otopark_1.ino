#include <Wire.h>
#include <Adafruit_PWMServoDriver.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SH110X.h> 

const char* ssid = "KMLAKGN";         
const char* password = "qazxsw123456";  



// DÜZELTME: http:// sonrasındaki boşluk silindi, yoksa ESP sunucuyu bulamaz.
const String sunucuURL = "https://proje.kamilakgun.com.tr/2026/akilli-otopark/"; 

#define i2c_Address 0x3c 
Adafruit_SH1106G display = Adafruit_SH1106G(128, 64, &Wire, -1);

Adafruit_PWMServoDriver pwm = Adafruit_PWMServoDriver();
#define SERVOMIN  150 
#define SERVOMAX  600 

const int mainGateSensorPin = 12; 
const int slot1SensorPin = 13;    
const int slot2SensorPin = 14;    
const int slot3SensorPin = 26;    
const int slot4SensorPin = 27;    

const int mainGateServo = 0; 
const int slot1Servo = 1;
const int slot2Servo = 2;
const int slot3Servo = 3;
const int slot4Servo = 4;

int mevcutAci[5] = {0, 0, 0, 0, 0}; 

void setup() {
  Serial.begin(115200);
  delay(500); 
  
  pinMode(mainGateSensorPin, INPUT_PULLUP);
  pinMode(slot1SensorPin, INPUT_PULLUP);
  pinMode(slot2SensorPin, INPUT_PULLUP);
  pinMode(slot3SensorPin, INPUT_PULLUP);
  pinMode(slot4SensorPin, INPUT_PULLUP);

  if(!display.begin(i2c_Address, true)) {
    Serial.println(F("SH1106 OLED baglantisi basarisiz!"));
  }
  
  display.clearDisplay();
  display.display(); 
  delay(200);

  display.setTextColor(SH110X_WHITE); 
  display.setTextSize(1);
  display.setCursor(10, 20);
  display.println("SISTEM BASLATILIYOR..");
  display.display();

  baglanWiFi();

  pwm.begin();
  pwm.setOscillatorFrequency(27000000);
  pwm.setPWMFreq(50);  
  delay(100);

  motorHareketHizli(mainGateServo, 0);
  motorHareketHizli(slot1Servo, 0);
  motorHareketHizli(slot2Servo, 0);
  motorHareketHizli(slot3Servo, 0);
  motorHareketHizli(slot4Servo, 0);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    baglanWiFi();
  }

  int s_ana = (digitalRead(mainGateSensorPin) == LOW) ? 1 : 0; 
  int s_s1  = (digitalRead(slot1SensorPin) == LOW) ? 1 : 0;
  int s_s2  = (digitalRead(slot2SensorPin) == LOW) ? 1 : 0;
  int s_s3  = (digitalRead(slot3SensorPin) == LOW) ? 1 : 0;
  int s_s4  = (digitalRead(slot4SensorPin) == LOW) ? 1 : 0;

  sunucuylaHaberlesVeMotorlariGuncelle(s_ana, s_s1, s_s2, s_s3, s_s4);

  delay(500); 
}

void baglanWiFi() {
  Serial.print("WiFi Baglaniliyor...");
  display.clearDisplay();
  display.setCursor(10, 20);
  display.println("WiFi'ye Baglaniliyor");
  display.display();
  
  WiFi.begin(ssid, password);
  while(WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Baglandi!");
}

void motorHareketYavas(uint8_t kanal, uint8_t hedefAci) {
  if (mevcutAci[kanal] == hedefAci) return;
  int adim = (mevcutAci[kanal] < hedefAci) ? 3 : -3;
  while (mevcutAci[kanal] != hedefAci) {
    mevcutAci[kanal] += adim;
    if ((adim > 0 && mevcutAci[kanal] > hedefAci) || (adim < 0 && mevcutAci[kanal] < hedefAci)) {
      mevcutAci[kanal] = hedefAci;
    }
    uint16_t pulse = map(mevcutAci[kanal], 0, 180, SERVOMIN, SERVOMAX);
    pwm.setPWM(kanal, 0, pulse);
    delay(3); 
  }
}

void motorHareketHizli(uint8_t kanal, uint8_t aci) {
  uint16_t pulse = map(aci, 0, 180, SERVOMIN, SERVOMAX);
  pwm.setPWM(kanal, 0, pulse);
  mevcutAci[kanal] = aci;
}

void sunucuylaHaberlesVeMotorlariGuncelle(int s_ana, int s_s1, int s_s2, int s_s3, int s_s4) {
  HTTPClient http;
  String url = sunucuURL + "?s_ana=" + String(s_ana) + "&s_s1=" + String(s_s1) + "&s_s2=" + String(s_s2) + "&s_s3=" + String(s_s3) + "&s_s4=" + String(s_s4);
                  
  http.begin(url);
  http.setTimeout(10000); 
  int httpResponseCode = http.GET();
  
  if (httpResponseCode == 200) { 
    String payload = http.getString();
    payload.trim(); 
    
    int idx1 = payload.indexOf(',');
    int idx2 = payload.indexOf(',', idx1 + 1);
    int idx3 = payload.indexOf(',', idx2 + 1);
    int idx4 = payload.indexOf(',', idx3 + 1);
    int idx5 = payload.indexOf(',', idx4 + 1);
    int idx6 = payload.indexOf(',', idx5 + 1);

    if (idx1 > 0 && idx6 > 0) {
      int cmd_ana = payload.substring(0, idx1).toInt();
      int cmd_s1  = payload.substring(idx1 + 1, idx2).toInt();
      int cmd_s2  = payload.substring(idx2 + 1, idx3).toInt();
      int cmd_s3  = payload.substring(idx3 + 1, idx4).toInt();
      int cmd_s4  = payload.substring(idx4 + 1, idx5).toInt();
      int bos_yer = payload.substring(idx5 + 1, idx6).toInt();
      int fiyat   = payload.substring(idx6 + 1).toInt();

      // =========================================================
      // 4 Slot Doluysa Kapıyı Kitle ve Boş Yer Sayısını 0 Yap
      // =========================================================
      if (s_s1 == 1 && s_s2 == 1 && s_s3 == 1 && s_s4 == 1) {
        cmd_ana = 0; // Kapı kilitli kalır
        bos_yer = 0; // Garanti sıfırlama
      }

      display.clearDisplay();
      
      if (cmd_ana == 1) {
        display.setTextSize(2);
        display.setCursor(0, 25); 
        display.println("HOSGELDINIZ");
      } else {
        // Üst Başlık Standart Kalır
        display.setTextSize(1);
        display.setCursor(15, 0);
        display.println("AKILLI OTOPARK");
        display.drawLine(0, 10, 128, 10, SH110X_WHITE); 
        
        // --- EKRAN TASARIM MANTIĞI BURADA ---
        if (bos_yer == 0) {
          // OTOPARK TAMAMEN DOLUYSA FIYATI GİZLE, KOCAMAN DOLU YAZ
          display.setTextSize(3); // Font boyutunu iyice büyüttük
          display.setCursor(20, 28); // Yazıyı ekranın ortasına hizaladık
          display.println("DOLU!");
        } else {
          // OTOPARKTA YER VARSA NORMAL TASARIMA GERİ DÖN
          display.setTextSize(2);
          display.setCursor(0, 18);
          display.print("BOS: "); 
          display.println(bos_yer);
          
          display.setCursor(0, 42);
          display.print("FIYAT:"); display.print(fiyat); display.println("TL");
        }
      }
      display.display();

      motorHareketYavas(mainGateServo, cmd_ana == 1 ? 90 : 0);
      motorHareketYavas(slot1Servo,    cmd_s1  == 1 ? 90 : 0);
      motorHareketYavas(slot2Servo,    cmd_s2  == 1 ? 90 : 0);
      motorHareketYavas(slot3Servo,    cmd_s3  == 0 ? 90 : 0);
      motorHareketYavas(slot4Servo,    cmd_s4  == 0 ? 90 : 0);
    }
  } else {
    display.clearDisplay();
    display.setTextSize(1);
    display.setCursor(0, 20);
    display.println("SUNUCU BAGLANTISI");
    display.println("KOPTU!");
    display.display();
  }
  http.end();
}
