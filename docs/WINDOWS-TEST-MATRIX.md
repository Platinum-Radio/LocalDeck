# Windows-testmatrix

Een publieke LocalDeck-versie mag pas worden vrijgegeven nadat alle verplichte rijen op een schone x64-installatie zijn uitgevoerd. De testresultaten horen bij het releasearchief en vermelden exact dezelfde EXE- en ZIP-hashes als de uiteindelijke downloads.

Deze matrix is op 28 augustus 2026 afgestemd op de [door Microsoft ondersteunde Windows-clientversies](https://learn.microsoft.com/windows/release-health/supported-versions-windows-client). Windows 10 22H2 bereikte op 14 oktober 2025 het reguliere einde van ondersteuning en is daarom alleen nog een compatibiliteitstest, bij voorkeur op een bijgewerkt ESU-systeem.

| Omgeving | Installatie | Map/ZIP | App-modus | Windows-services | SmartScreen/handtekening | Verplicht |
|---|---:|---:|---:|---:|---:|---:|
| Windows 11 24H2 x64, standaardgebruiker | ✓ | ✓ | ✓ | ✓ | ✓ | Ja |
| Windows 11 25H2 x64, standaardgebruiker | ✓ | ✓ | ✓ | ✓ | ✓ | Ja |
| Windows 11 26H1 x64, standaardgebruiker | ✓ | ✓ | ✓ | ✓ | ✓ | Ja |
| Windows 10 22H2 x64 met ESU, standaardgebruiker | ✓ | ✓ | ✓ | ✓ | ✓ | Compatibiliteit |
| Windows 11 actuele Insider-build | — | ✓ | ✓ | — | ✓ | Aanbevolen |

Per omgeving controleren:

1. Installeren of uitpakken in een pad met spaties en Unicode.
2. Starten zonder consolevenster en zonder account, activatie of internet.
3. Alle zes services afzonderlijk en gezamenlijk starten en stoppen.
4. `localhost`, `127.0.0.1`, een nieuw `.localhost`-project en optioneel een `.test`-project openen.
5. phpMyAdmin zonder extra login openen, database maken, gebruiker met beperkte rechten maken en back-up herstellen.
6. Mailbeheer openen, testmail versturen, taggen, `.eml` downloaden en kwaliteitscontroles uitvoeren.
7. App-modus afsluiten en vaststellen dat processen stoppen; servicemodus herstarten na Windows-login.
8. Product Doctor, supportbundel en volledige werkruimte-export/herstel uitvoeren.
9. SHA-256 van alle 14 runtimepakketten, installer en ZIP controleren; Authenticode van de installer en `LocalDeck.exe` uit de ZIP moet geldig zijn.
10. Updatepopup, downloadcontrole en rollbackfeed testen met een interne HTTPS-testfeed.
11. Het dashboard controleren op 100%, 125% en 150% Windows-schaal bij minimaal 1366×768 en 1920×1080.
12. Bij ieder resultaat OS-build, schaal, testtijd, tester, EXE/ZIP-SHA-256, Authenticode-uitgever en SmartScreen-uitkomst vastleggen.

`scripts\Test-ReleaseReadiness.ps1` automatiseert de lokale pakket- en omgevingscontroles. `Validate-LocalDeck.ps1` blijft de verplichte bron- en runtimevalidatie.
