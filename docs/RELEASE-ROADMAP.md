# Publieke Windows-releases

LocalDeck is bewust een Windows-only product. Ondersteuning richt zich op Windows 10 en 11 x64; macOS en Linux vallen buiten de productscope.

## Fase 1 — LocalDeck 1.0.0

De desktopervaring, domeinvalidatie, projecten, servicestatussen, logs en instellingen zitten in 1.0.0. De ingebouwde echte Windows-runtime is de standaard; simulatiemodus blijft uitsluitend beschikbaar voor veilige ontwikkeltests. De eerste publieke bestanden zijn bewust ongetekend, verplicht met SHA-256 gecontroleerd en zichtbaar als zodanig gemarkeerd.

## Fase 2 — native runtime

- Downloadmanifest met vastgepinde versies en SHA-256-checksums
- PHP 8.2–8.5 x64, Apache 2.4, MySQL 8, Redis, Mailpit en Composer voor Windows
- Procesbeheer met PID-bestanden, healthchecks, time-outs en gecontroleerd afsluiten
- Vrije-poortdetectie en begrijpelijke conflictdiagnose
- Per-project PHP-CGI/FastCGI-pools en uitbreidingen
- Optionele Caddy- en Nginx-runtimeadapters naast de bestaande profielvoorbereiding
- Lokale CA installeren na expliciete Windows UAC-toestemming
- Hosts/DNS-afhandeling zonder permanente, onverklaarde systeemwijzigingen

De volledige basisruntime wordt sinds 0.6.1 met een SHA-256-manifest in elk distributiebestand meegeleverd en heeft een lokale end-to-endtest. Nog vóór een publieke stabiele release: installatie-rollback, automatische Xdebug-binaire selectie per exacte PHP-build en end-to-endtests op schone Windows 10/11-images.

## Fase 3 — productkwaliteit

- Persistente instellingen met migraties
- Automatische updates met ondertekende manifesten
- Crashherstel, traybediening en starten met Windows
- Import uit XAMPP, Laragon en bestaande projectmappen
- Back-up/export voor databases en configuratie
- Integratietests op schone Windows 10- en 11-machines
- Toegankelijkheids-, security- en penetratietest

## Fase 4 — distributie

- Eigen productnaam, iconen en website definitief controleren
- Authenticode-code signing plus consequente reputatieopbouw; voor maximale Windows-distributiezekerheid ook Microsoft Store of Microsoft Artifact Signing evalueren
- Licenties en notices van alle meegeleverde componenten publiceren
- Privacybeleid, updatebeleid, supportkanaal en kwetsbaarheidsmelding
- Installer, portable EXE, uitpak-ZIP en SHA-256-bestanden publiceren; Authenticode toevoegen zodra het uitgeverscertificaat beschikbaar is

## Architectuurprincipe

De renderer start nooit rechtstreeks processen en alle bevoorrechte acties lopen via een klein, gevalideerd IPC-oppervlak in het hoofdproces. De basisruntime installeert uitsluitend vanuit het ingebouwde SHA-256-manifest; de Microsoft VC++ Runtime vereist bovendien een geldige Microsoft-handtekening. Applicatie-updates worden met SHA-256 en optioneel Authenticode gecontroleerd.
