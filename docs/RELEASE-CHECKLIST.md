# LocalDeck Windows-releasechecklist

## Verplicht vóór publieke uitgave

1. Een officiële stabiele uitgave moet een geldige Authenticode-handtekening hebben. Gebruik na toelating de vertrouwde GitHub/SignPath-route uit `CODE_SIGNING_POLICY.md`, of gebruik een eigen publiek vertrouwd RSA-codecertificaat via `CSC_LINK` en waar nodig `CSC_KEY_PASSWORD`.
2. Bouw en test de NSIS-installer op schone Windows 10- en Windows 11-machines.
3. Voer `scripts\Test-SignPathReadiness.ps1` uit en controleer alle componentversies, vaste bronlinks, bron- en distributiehashes en licenties.
4. Publiceer uitsluitend de EXE-installer en uitpakbare map-ZIP via HTTPS.
5. Laat `Publish-LocalDeck.ps1` de HTTPS-updatefeed, installer-SHA-256, uitgever en beschikbare rollback automatisch vastleggen.
6. Test app-modus, Windows-servicemodus, UAC, verwijderen, herstel en rollback.
7. Test installatie met bezette standaardpoorten en zonder bestaande PHP-, MySQL- of XAMPP-installatie te wijzigen.
8. Controleer `runtime-packages\runtime-sources.json`, `THIRD-PARTY-NOTICES.md` en alle vereiste licentieteksten.
9. Maak vóór updates een kopie van configuratie, projectenmetadata en databases.
10. Publiceer privacybeleid, ondersteuningsadres en kwetsbaarhedenbeleid.
11. Controleer SmartScreen en Smart App Control op een schone Windows 11-installatie met exact dezelfde installer- en ZIP-hashes als de publieke download. Ook een geldige handtekening garandeert niet direct opgebouwde SmartScreen-reputatie.
12. Test alle gebruikte PHP-versiepools, Git-worktrees, webbeheer, MCP/CLI en LAN-delen met Windows Firewall actief.
13. Maak SHA-256-waarden en releasefeeds pas nadat alle signingstappen zijn afgerond; ondertekenen wijzigt het bestand.
14. Controleer dat `Get-AuthenticodeSignature` voor ieder verplicht eigen uitvoerbaar bestand `Valid` toont, met de verwachte uitgever en een geldige tijdstempel.

Een bewust ongetekende build is uitsluitend toegestaan als duidelijk gemarkeerde testbuild. SHA-256, de volledige runtime- en rooktest en een zichtbare Windows-waarschuwing blijven verplicht. De updatefeed krijgt `signed: false`; een ongetekende build bevat geen rollbackverwijzing die ten onrechte een handtekening afdwingt. Een nieuwe stabiele release mag niet via deze uitzonderingsroute worden gepubliceerd.

## SignPath Foundation-route

Na goedkeuring door SignPath gelden aanvullend:

1. Alleen een onveranderlijke `v*`-tag op de beschermde hoofdbranch mag de signingworkflow starten.
2. De volledige kandidaat wordt op een GitHub-hosted Windows-runner uit de openbare bron, het bevroren lockbestand en de vastgezette runtimebronnen gebouwd.
3. Het ongetekende artifact wordt eerst als GitHub Actions-artifact opgeslagen en daarna via de officiële, op commit vastgezette SignPath-action ingediend.
4. Origin verification, de ingestelde artifact configuration en handmatige releasegoedkeuring zijn verplicht.
5. SignPath-credentials zijn uitsluitend GitHub Actions-secrets en zijn nooit beschikbaar voor pull requests vanuit forks.
6. Het ontvangen artifact wordt opnieuw op Authenticode, publisher, tijdstempel, malware en werking gecontroleerd.

Zie `docs\SIGNPATH-ONBOARDING.md` voor de externe aanvraag- en configuratiestappen.

Definitieve ondertekende opdracht:

```powershell
$env:CSC_LINK='C:\beveiligd\localdeck.pfx'
$env:CSC_KEY_PASSWORD='...'
    .\Publish-LocalDeck.ps1 -Finalize -Publisher 'CN=LocalDeck B.V.' -DownloadBaseUrl 'https://localdeck.nl/downloads/releases/1.0.0'
```

Expliciet ongetekende interne of duidelijk gemarkeerde testbuild:

```powershell
.\Publish-LocalDeck.ps1 -Finalize -AllowUnsigned -DownloadBaseUrl 'https://localdeck.nl/downloads/releases/1.0.0'
```
