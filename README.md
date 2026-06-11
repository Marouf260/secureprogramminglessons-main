# Omanido - Security Hardening & Webbeveiliging Project

Dit project is gericht op het analyseren, patchen en beveiligen van een (gesimuleerde) bankapplicatie. Waar de applicatie voorheen opzettelijke kwetsbaarheden bevatte voor educatieve doeleinden, ligt de focus nu op **Defensive Security**: het implementeren van robuuste beveiligingsmaatregelen volgens de OWASP-richtlijnen om de applicatie te harden tegen moderne cyberdreigingen.

## 🛡️ Geïmplementeerde Beveiligingsmaatregelen

De applicatie is stapsgewijs beveiligd tegen kritieke kwetsbaarheden uit de **OWASP Top 10**:

### 1. Identification and Authentication Failures (A07:2021)
*   **Wachtwoord Hashing:** Gebruikerswachtwoorden worden veilig gezouten en gehasht met het `PASSWORD_DEFAULT` algoritme (bcrypt) via PHP's `password_hash()`.
*   **Wachtwoord Richtlijnen & Blacklist:** Nieuwe registraties worden gecontroleerd op minimale complexiteit (lengte, hoofdletters, cijfers) én getoetst aan een blacklist van veelgebruikte/gelekte wachtwoorden (zoals `12345678` of `qwerty`).
*   **Brute-Force Bescherming (Rate Limiting):** Na 3 opeenvolgende mislukte inlogpogingen wordt het specifieke account tijdelijk vergrendeld (`lockout_until`) om geautomatiseerde aanvallen te stoppen.
*   **Legacy Account Handhaving:** Bestaande gebruikers met een onveilig (gelekt) wachtwoord worden bij inloggen direct gedwongen hun wachtwoord te wijzigen via een verplichte reset-flow.

### 2. Injection Prevention (A03:2021)
*   **SQL-injectie (SQLi) Defensie:** Alle database-interacties (inloggen, registreren, transacties) zijn gemigreerd naar **PDO Prepared Statements**. Variabelen worden strikt gescheiden van de SQL-logica, waardoor SQL-injectie onmogelijk is.

### 3. Cross-Site Scripting (XSS) & Data Validatie
*   **Output Encoding:** Alle dynamische gebruikersinvoer die op de pagina wordt getoond, wordt gefilterd met `htmlspecialchars()` om het uitvoeren van kwaadaardige JavaScript-code (XSS) in de browser te voorkomen.

---

## 🚀 Installatie & Lokale Omgeving

De applicatie draait volledig binnen een geïsoleerde Docker-omgeving om consistentie en veiligheid te garanderen.

### Vereisten
*   [Docker Desktop](https://www.docker.com/products/docker-desktop/) geïnstalleerd op je systeem.

### Starten van de applicatie
1.  Download of clone deze repository naar je lokale machine.
2.  Open een terminal of command prompt en navigeer naar de projectmap:
```bash
    cd pad/naar/omanido-project
    ```
3.  Start de Docker-containers met het volgende commando:
```bash
    docker-compose up -d
    ```
    *(De `-d` vlag zorgt ervoor dat de containers op de achtergrond draaien).*

### Toegangspunten
*   **Webapplicatie (Omanido):** [http://localhost:8000](http://localhost:8000)
*   **Database Beheer (phpMyAdmin):** [http://localhost:8080](http://localhost:8080)

---

## 🧪 Beveiliging Testen (Verificatie)

Om aan te tonen dat de beveiliging werkt, kun je de volgende scenario's testen in de applicatie:

1.  **Brute-force test:** Probeer 3 keer achter elkaar in te loggen met een verkeerd wachtwoord op een bestaand account. Het systeem zal het account daarna tijdelijk blokkeren.
2.  **Zwak wachtwoord test:** Probeer een nieuw account te registreren met het wachtwoord `12345678` of `geheim`. Het systeem zal de registratie weigeren vanwege het gebruik van een gelekt wachtwoord.
3.  **SQLi Bypass test:** Probeer in het inlogveld een SQL-injectie string te typen (bijv. `' OR '1'='1`). De applicatie behandelt dit nu puur als tekst en de aanval zal falen.