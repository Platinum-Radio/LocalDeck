# Windows-testmatrix

Een publieke LocalDeck-versie mag pas worden vrijgegeven nadat alle verplichte rijen op een schone x64-installatie zijn uitgevoerd. De testresultaten horen bij het releasearchief.

| Omgeving | Installatie | Map/ZIP | App-modus | Windows-services | SmartScreen/handtekening | Verplicht |
|---|---:|---:|---:|---:|---:|---:|
| Windows 10 22H2 x64, standaardgebruiker | ✓ | ✓ | ✓ | ✓ | ✓ | Ja |
| Windows 11 23H2 x64, standaardgebruiker | ✓ | ✓ | ✓ | ✓ | ✓ | Ja |
| Windows 11 24H2 x64, standaardgebruiker | ✓ | ✓ | ✓ | ✓ | ✓ | Ja |
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
9. SHA-256 van alle 13 runtimepakketten en Authenticode van app, installer en portable EXE controleren.
10. Updatepopup, downloadcontrole en rollbackfeed testen met een interne HTTPS-testfeed.

`scripts\Test-ReleaseReadiness.ps1` automatiseert de lokale pakket- en omgevingscontroles. `Validate-LocalDeck.ps1` blijft de verplichte bron- en runtimevalidatie.

