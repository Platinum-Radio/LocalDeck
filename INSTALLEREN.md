# LocalDeck-runtime installeren

Gebruik bij voorkeur `installer\Install-LocalDeck-Silent.vbs`. Dit opent alleen de normale Windows UAC-bevestiging; het PowerShell/DOS-venster blijft verborgen. Beheerrechten zijn alleen nodig om de actuele Microsoft Visual C++ Runtime te installeren; LocalDeck zelf en de databases blijven in je gebruikersprofiel.

Handmatig via een zichtbaar PowerShell-venster kan ook:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\scripts\install-runtime.ps1
```

De installatie komt uitsluitend in `%APPDATA%\LocalDeck\runtime`. Een bestaande XAMPP-, Apache- of MySQL-installatie wordt niet aangepast. Alle 13 runtimepakketten zijn ingebouwd; tijdens de runtime-installatie wordt niets gedownload. Daarom zijn de distributiebestanden groter dan eerdere previews.

## Installeren of uitpakken

- `LocalDeck-1.0.0-Windows-Setup-x64.exe` installeert LocalDeck via de normale Windows-installatiewizard.
- `LocalDeck-1.0.0-Windows-x64.zip` kun je uitpakken in iedere map waarop je schrijfrechten hebt. Start daarna `LocalDeck.exe` in die map; het dashboard en de runtimegegevens blijven gescheiden.

Dit zijn de enige twee openbare downloadbestanden: één EXE om te installeren en één ZIP om alleen uit te pakken. Ze worden pas aangemaakt nadat de interne startklare map op Windows is goedgekeurd. Voor tussentijdse controle gebruik je `LocalDeck-Startklaar\LocalDeck.exe`.

De uitpakbare ZIP hoeft niet in `Program Files` te staan. Windows-services en de lokale DNS-modus vragen, net als bij de geïnstalleerde versie, eenmalig UAC-toestemming.

Je kunt de installatie ook volledig vanuit LocalDeck starten via **Installatie → Nu installeren**. Achtergrondprocessen starten zonder CMD- of PowerShell-vensters.

Na installatie kun je op het dashboard **Open localhost** of **phpMyAdmin** kiezen. LocalDeck start de vereiste services stil op de achtergrond. De startpagina is bereikbaar via zowel `http://localhost` als `http://127.0.0.1`; bij een aangepaste Apache-poort gebruikt de dashboardknop automatisch het juiste adres.

## Uitvoermodus

Bij de eerste start kiest de gebruiker:

- **Zoals XAMPP:** processen draaien alleen wanneer LocalDeck actief is en stoppen bij volledig afsluiten.
- **Windows-services:** de zes runtimeonderdelen worden via WinSW geregistreerd en starten zelfstandig met Windows. Hiervoor verschijnt eenmalig een normale UAC-bevestiging.

Beide modi verbergen consolevensters. Omschakelen kan later via **Services** of **Instellingen**.

## Webbeheer en testmail

**Webbeheer** opent standaard op `http://127.0.0.1:7331`. Toegang verloopt via een willekeurig lokaal token dat de desktopapp als beveiligde cookie instelt. De server luistert nooit op het lokale netwerk. Je beheert er services, projecten, databases, testmail, resource-instellingen en inspectorgebeurtenissen. Mailpit accepteert alle lokale ontvangers; de pagina **E-mail** beheert een overzichtelijk testadresboek.

LocalDeck vereist geen account, aanmelding of online activatie. De basisruntime werkt volledig offline. Alleen bewuste vervolgacties zoals Git-clones, Composer/projecttemplates en updatecontroles gebruiken internet. De tijdelijke deeloptie is beperkt tot het lokale netwerk.

## Updates

LocalDeck gebruikt standaard `https://localdeck.nl/downloads/windows.json` en versie 1.0.0 volgt het stabiele kanaal. Zodra de feed een hogere versie meldt, verschijnt automatisch de LocalDeck-updatepopup. De URL en het kanaal zijn desgewenst onder **Instellingen → Updates en back-ups** aan te passen. Iedere download wordt verplicht met SHA-256 gecontroleerd. Versie 1.0.0 is nog niet Authenticode-ondertekend en kan daarom door Windows SmartScreen of Smart App Control worden gewaarschuwd of geblokkeerd; dit verdwijnt pas betrouwbaar met een vertrouwd uitgeverscertificaat en opgebouwde reputatie.

Verwijderen, inclusief lokale databases:

```powershell
.\scripts\uninstall-runtime.ps1 -WhatIf
.\scripts\uninstall-runtime.ps1
```

Gebruik eerst `-WhatIf` om het doel te controleren. Het verwijderen van de runtime verwijdert ook alle LocalDeck-databases.
