#include <MFRC522.h>

#define RST_PIN         9          // Pin de reseteo del MFRC522
#define SS_PIN          10         // Pin SS (Slave Select) del MFRC522

MFRC522 mfrc522(SS_PIN, RST_PIN);  // Crea una instancia del objeto MFRC522

const byte AUTHORIZED_UISTIC_1[] = {0x4B, 0x1F, 0x35, 0x03};
const byte AUTHORIZED_UISTIC_2[] = {0x1C, 0x7E, 0x4B, 0x00};

const String AUTHORIZED_NAME_1 = "ALAN AMADOR";
const String AUTHORIZED_NAME_2 = "MARCO RULFO";

unsigned long startTimeMillis;

void setup() {
  Serial.begin(9600);
  while (!Serial);
  SPI.begin();
  mfrc522.PCD_Init();
  delay(4);
  mfrc522.PCD_DumpVersionToSerial();
  Serial.println(F("--- SISTEMA DE ASISTENCIA SIMULADO ---"));
  Serial.println(F("Acerque una tarjeta RFID para registrar la asistencia."));
  Serial.println(F("------------------------------------"));

  startTimeMillis = millis();
}

void loop() {
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  String currentUID = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) {
      currentUID += "0";
    }
    currentUID += String(mfrc522.uid.uidByte[i], HEX);
  }
  currentUID.toUpperCase();

  Serial.print(F("UID Leído: "));
  Serial.println(currentUID);

  unsigned long elapsedTime = millis() - startTimeMillis;
  long seconds = elapsedTime / 1000;
  long minutes = seconds / 60;
  long hours = minutes / 60;
  seconds %= 60;
  minutes %= 60;

  String simulatedTime = "";
  if (hours < 10) simulatedTime += "0";
  simulatedTime += String(hours);
  simulatedTime += ":";
  if (minutes < 10) simulatedTime += "0";
  simulatedTime += String(minutes);
  simulatedTime += ":";
  if (seconds < 10) simulatedTime += "0";
  simulatedTime += String(seconds);

  String simulatedDateTime = "01/01/2025 " + simulatedTime;

  String registeredName = "Desconocido";
  bool isAuthorized = false;

  String authUID1 = "";
  for (byte i = 0; i < sizeof(AUTHORIZED_UISTIC_1); i++) {
    if (AUTHORIZED_UISTIC_1[i] < 0x10) authUID1 += "0";
    authUID1 += String(AUTHORIZED_UISTIC_1[i], HEX);
  }
  authUID1.toUpperCase();
  if (currentUID == authUID1) {
    isAuthorized = true;
    registeredName = AUTHORIZED_NAME_1;
  }

  String authUID2 = "";
  for (byte i = 0; i < sizeof(AUTHORIZED_UISTIC_2); i++) {
    if (AUTHORIZED_UISTIC_2[i] < 0x10) authUID2 += "0";
    authUID2 += String(AUTHORIZED_UISTIC_2[i], HEX);
  }
  authUID2.toUpperCase();
  if (currentUID == authUID2) {
    isAuthorized = true;
    registeredName = AUTHORIZED_NAME_2;
  }

  Serial.print(F("Registro de Asistencia: "));
  Serial.print(simulatedDateTime);
  Serial.print(F(" - "));

  if (isAuthorized) {
    Serial.print(F("ACCESO AUTORIZADO para: "));
    Serial.print(registeredName);
    Serial.print(F(" (UID: "));
    Serial.print(currentUID);
    Serial.println(F(")"));
  } else {
    Serial.print(F("TARJETA NO AUTORIZADA (UID: "));
    Serial.print(currentUID);
    Serial.println(F(")"));
  }
  Serial.println(F("------------------------------------"));

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
  delay(2000);
}