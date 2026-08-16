# LocalDeck Windows-releasechecklist

## Verplicht vóór publieke uitgave

1. Gebruik bij voorkeur een vertrouwd Windows-code-signingcertificaat via `CSC_LINK` en waar nodig `CSC_KEY_PASSWORD`. Zonder certificaat is alleen de expliciete, zichtbaar gemarkeerde `-AllowUnsigned`-route toegestaan.
2. Bouw en test de NSIS-installer op schone Windows 10- en Windows 11-machines.
3. Controleer alle componentversies, downloadlinks, SHA-256-waarden en licenties.
4. Publiceer installer, portable EXE en uitpakbare map-ZIP via HTTPS.
5. Laat `Publish-LocalDeck.ps1` de HTTPS-updatefeed, installer-SHA-256, uitgever en beschikbare rollback automatisch vastleggen.
6. Test app-modus, Windows-servicemodus, UAC, verwijderen, herstel en rollback.
7. Test installatie met bezette standaardpoorten en zonder bestaande PHP-, MySQL- of XAMPP-installatie te wijzigen.
8. Controleer `THIRD-PARTY-NOTICES.md` en voeg de volledige vereiste licentieteksten toe.
9. Maak vóór updates een kopie van configuratie, projectenmetadata en databases.
10. Publiceer privacybeleid, ondersteuningsadres en kwetsbaarhedenbeleid.
11. Controleer SmartScreen en Smart App Control op een schone Windows 11-installatie met exact dezelfde installer- en portable-hashes als de publieke download. Een ongetekende release moet op site en feed waarschuwen; ook een EV-certificaat alleen garandeert geen automatische reputatie.
12. Test alle gebruikte PHP-versiepools, Git-worktrees, webbeheer, MCP/CLI en LAN-delen met Windows Firewall actief.

Voor een bewust ongetekende publieke release blijven SHA-256, de volledige runtime- en rooktest en een zichtbare SmartScreen-waarschuwing verplicht. De updatefeed krijgt `signed: false`; een ongetekende release bevat geen rollbackverwijzing die ten onrechte een handtekening afdwingt.

Definitieve ondertekende opdracht:

```powershell
$env:CSC_LINK='C:\beveiligd\localdeck.pfx'
$env:CSC_KEY_PASSWORD='...'
    .\Publish-LocalDeck.ps1 -Finalize -Publisher 'CN=LocalDeck B.V.' -DownloadBaseUrl 'https://localdeck.nl/downloads/releases/1.0.0'
```

Expliciet ongetekend, zoals voor de eerste 1.0.0-publicatie:

```powershell
.\Publish-LocalDeck.ps1 -Finalize -AllowUnsigned -DownloadBaseUrl 'https://localdeck.nl/downloads/releases/1.0.0'
```
