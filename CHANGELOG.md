# Changelog

## Unreleased

- LocalDeck-broncode is nu onder Apache-2.0 gepubliceerd met een NOTICE, bijdragebeleid en expliciete licentiemetadata.
- Openbaar code-signingbeleid, maintainerrollen en een SignPath Foundation-onboardingdossier toegevoegd.
- Alle 13 offline runtimebronnen zijn vastgezet op exacte versies, HTTPS-adressen, bestandsgroottes, SHA-256-waarden, licenties en broncodelinks.
- Dynamische `latest`-downloadlinks zijn uit het runtimevoorbereidingsproces verwijderd.
- Nieuwe Windows GitHub Actions-controle gebruikt een bevroren pnpm-lock, minimale rechten en op commit vastgezette Actions.
- CODEOWNERS, pull-requestcontrole, Dependabot en een automatische SignPath-readinesscontrole toegevoegd.
- Het versiebeheer bevat een door CODEOWNERS beschermd SignPath-bronbeleid dat alleen GitHub-hosted runners toestaat en herstarts van signing-builds weigert.
- De privacy- en third-partyteksten beschrijven nu precies welke netwerkacties bestaan en onder welke licenties de gebundelde onderdelen worden verspreid.
- Automatische updatecontrole staat voor nieuwe installaties standaard uit en kan bewust in Instellingen worden aangezet.
- De afzonderlijke LocalDeck-websitebron bevat een tweetalige code-signingpagina en duidelijke, nog niet als actief gepresenteerde SignPath-status.

## 1.1.0-test.1 — Publieke Windows-testversie

- Nieuwe eenvoudige en geavanceerde interfacemodus.
- Projectwerkplek voor website, database, mail, snapshots, debug en herstel.
- Slimme wizard voor nieuwe websites, bestaande mappen en Git-projecten.
- LocalDeck Fix en een samengevoegde Debug Inbox.
- Automatische projectinrichting met PHP, MySQL, HTTPS, Project Capsule en IDE-configuratie.
- Testkopieën, tijdelijke LAN-deellinks en verbeterde Nederlands/Engelse teksten.
- Responsieve vensterindeling voor gangbare Windows-schermresoluties.
- Windows-pakketten bevatten geen Vite-, esbuild- of Rollup-buildtools meer; alleen de benodigde Nederlandse en Engelse Electron-talen worden opgenomen.
- MySQL-, Redis- en Apache-archieven gebruiken een gecontroleerd runtime-only profiel zonder symbolen en SDK-bestanden; phpMyAdmin blijft ongewijzigd voor maximale compatibiliteit.
- De compacte offline runtime blijft volledig ingebouwd en wordt bij iedere validatie op vereiste bestanden, SHA-256 en uitgesloten ballast gecontroleerd.
- De testversie gebruikt een apart bèta-updatekanaal, zodat de stabiele 1.0-feed ongewijzigd blijft.
- Een nieuwe tweetalige updatepagina op LocalDeck.nl en `UPDATES.md` op GitHub tonen de belangrijkste wijzigingen per versie.

## 1.0.0 — Eerste publieke Windows-release

- Installer, portable EXE, uitpak-ZIP en broncode worden als vaste versie gepubliceerd met een los SHA-256-controlebestand.
- Deze eerste publieke release is bewust niet digitaal ondertekend; de downloadpagina en updatefeed tonen daarom zichtbaar dat Windows SmartScreen of Smart App Control kan waarschuwen of blokkeren.

- De instellingenpagina gebruikt nu vier compacte tabbladen voor algemeen gedrag, netwerk en domeinen, updates en back-ups, en privacy en beveiliging.
- `LocalDeck.nl` is rechtstreeks bereikbaar vanuit de zijbalk, instellingenpagina en opdrachtenzoeker; de desktopapp opent uitsluitend de vaste HTTPS-website in de standaardbrowser.
- De systeemvakinstelling vermeldt duidelijk dat het rode kruis LocalDeck bij de verborgen Windows-pictogrammen actief houdt en hoe het venster opnieuw wordt geopend.
- Ieder nieuw, bestaand of geïmporteerd project krijgt automatisch HTTPS met een eigen mkcert-certificaat en privésleutel; er worden geen certificaten gedownload.
- Apache gebruikt uitsluitend het certificaat van het betreffende project en schakelt de HTTPS-vhost pas in wanneer beide bestanden aanwezig zijn.
- Product Doctor en het projectoverzicht tonen de actuele projectspecifieke SSL-status.
- Verwijderen is nu een permanente opruimactie voor de projectmap, branchmappen, gekoppelde databases en back-ups, certificaten, vhosts, snapshots, mailadressen, workers, rapporten, kluiswaarden en overige projectmetadata.
- Een duidelijke waarschuwing somt de gevolgen op en vereist dat de projectnaam exact wordt overgetypt; dezelfde controle wordt opnieuw in het hoofdproces uitgevoerd.
- Veiligheidscontroles blokkeren te brede mappaden, geneste niet-gerelateerde projecten en databases die nog door een ander project worden gebruikt.
- Tijdelijk gestarte MySQL en eerder actieve Apache worden na de verwijderactie naar hun oorspronkelijke status teruggebracht.

## 0.9.0 — Nederlands en Engels (in ontwikkeling)

- De interface kiest bij de eerste start automatisch Nederlands op een Nederlandstalige Windows-installatie en anders Engels.
- De taalkeuze blijft lokaal bewaard en kan zonder herstart via Instellingen worden gewijzigd.
- Dashboard, Action Center, Quality Lab, API Studio, pop-ups, placeholders en bevestigingsvensters gebruiken één centrale vertaalmodule met Engels als fallback.
- Webbeheer en Mailbeheer volgen dezelfde taalinstelling; Webbeheer kan de taal ook rechtstreeks wijzigen.
- Nieuwe lokale startpagina's volgen de LocalDeck-taal en hebben daarnaast een lokale NL/EN-wisselknop.
- Taalmetadata wordt samen met de poorten naar de lokale startpagina geschreven en blijft werken in app- en Windows-servicemodus.

## 0.8.0 — Safe Change, automatisering en release-labs (in ontwikkeling)

- Safe Change Engine legt vóór poortwijzigingen de vorige configuratie en servicestatus vast, controleert vooraf, past atomisch toe, voert een gezondheidscontrole uit en kan vanuit het Action Center terugdraaien.
- Het duurzame Action Center bewaart stappen, voortgang, foutresultaten en herstelpunten over een herstart heen en ondersteunt annuleren, opnieuw uitvoeren en opruimen.
- Port Autopilot 2.0 inventariseert primaire en secundaire TCP-/UDP-bindingen op IPv4 en IPv6, inclusief projectpoorten, SMTP, webbeheer, DNS, reserveringen en Windows-proceseigenaren.
- Expliciete service-afhankelijkheden en startprofielen starten vereisten in de juiste volgorde en stoppen afhankelijke services veilig.
- Queue- en schedulerworkers draaien als verborgen processen met begrensde logs, processtatus en optioneel automatisch herstel.
- Rehearsal Mode gebruikt een tijdelijke projectkopie; Release Gate combineert preflight, Dependency Guard, drift, SBOM, performance en browsercontroles.
- CycloneDX 1.5-SBOM, licentie-inventaris, performancebaselines, Chromium-browsermatrix, Migration Lab, Team Drift Detector en Mail Quality Lab zijn geïntegreerd.
- API Studio importeert OpenAPI JSON/YAML, genereert lokale testdata en verstuurt uitsluitend naar lokale adressen.
- De accountloze Webhook Catcher luistert alleen op `127.0.0.1`; LocalDeck Replay verwijdert autorisatieheaders en cookies uit opnames.
- Xdebug Workbench ondersteunt debug, profiling, trace en coverage met triggerstart en een lokale uitvoermap.
- Componentpacks kunnen zonder uitpakken worden geïnspecteerd; Ctrl+K en privacy-/presentatiemodus versnellen dagelijks en demonstratiegebruik.
- Alle nieuwe processtarts gebruiken `windowsHide` en geen shell, zodat er geen DOS-vensters openen.

## 0.7.0 — Project Capsule en lokale kwaliteitsstraat (in ontwikkeling)

- Projectprofielen zijn opgewaardeerd naar echte YAML Project Capsules met schema 3, een kloppende SHA-256-lockfile en een afzonderlijk productieprofiel.
- Productiepariteit vergelijkt PHP, database-engine, webserver, vereiste omgevingssleutels en dependency-lockfiles zonder geheime waarden te tonen.
- Windows Secret Vault versleutelt globale en projectsleutels met DPAPI; taken ontvangen waarden alleen in hun procesomgeving.
- Database Branching maakt naast een Git-worktree ook een echte databasekopie en kan code en database gecontroleerd opruimen.
- Veilige productie-import maakt eerst een SQL-back-up en anonimiseert herkenbare namen, adressen, telefoons, e-mailadressen, postcodes en tokens direct na import.
- Port Autopilot herkent de eigenaar van een conflict, stelt een vrije poort voor en wijzigt alleen de LocalDeck-configuratie.
- Time Machine neemt projectbestanden, gekoppelde database, capsule/configuratie en de lokale Mailpit-inbox mee.
- Lokale CI-preflight, productiepariteit en Browser Testlab zijn beschikbaar in desktop- én Webbeheer.
- Accountloze LAN-links krijgen een lokaal gegenereerde QR-code.
- Windows Dev Drive-detectie adviseert zonder stil Defender-uitzonderingen aan te brengen.
- Crashherstel onthoudt de gewenste servicestatus; atomische updates worden apart klaargezet en de laatst gezonde versie wordt geregistreerd.
- Ondertekende offline `.ldpack`-componentpakketten vereisen een vertrouwde certificaatuitgever en geldige hashes voor elk bestand.
- CLI en lokale MCP ondersteunen Doctor, productiepariteit, preflight en logs; per project kunnen VS Code- en PhpStorm-configuraties worden gemaakt.
- Alle nieuwe PowerShell- en runtimeprocessen blijven verborgen; LocalDeck opent geen DOS-vensters.

## 0.6.2 — draagbare werkmap (in ontwikkeling)

- Nieuwe mapmodus houdt de app, runtime, databases, instellingen en websites samen in één verplaatsbare Windows-map.
- `websites` is de lokale document root voor `localhost` en bevat een direct uitvoerbare PHP-voorbeeldwebsite.
- Nieuwe PHP-websites kunnen volledig offline vanuit het dashboard worden aangemaakt.
- Websites in submappen worden automatisch ontdekt; opgeslagen paden worden hersteld nadat de complete map is verplaatst.
- Een vaste knop opent de websites-map direct vanuit het dashboard.
- `Maak-Startklare-Map.ps1` bouwt en test een interne startklare map zonder installer, portable EXE of ZIP te publiceren.
- phpMyAdmin meldt lokaal automatisch aan met het beveiligd beheerde MySQL-account; het wachtwoord staat niet in de webconfiguratie.
- phpMyAdmin- en Mailpit-beheer staan rechtstreeks in het desktop- en weboverzicht en starten hun vereiste services indien nodig.
- Webbeheer meet bij iedere statusvernieuwing de werkelijk bereikbare services, inclusief Apache op Windows-wildcardadressen, zodat “Alles starten” direct correct wordt weergegeven.
- Procesnummers van app-services blijven bij statusverversing behouden, zodat Webbeheer ook het juiste aantal actieve processen en hun geheugengebruik toont.
- Een tweede start van dezelfde LocalDeck-map activeert voortaan het bestaande venster; dubbele dashboards kunnen services en Webbeheer niet meer uit synchronisatie brengen.
- De interne startklare map gebruikt een ongewijzigde Electron-host die op het testsysteem door Windows Code Integrity wordt toegestaan; publieke releases vereisen later nog een eigen vertrouwde codehandtekening.
- Nieuw LocalDeck Mailbeheer bovenop de ingebouwde Mailpit-engine, met virtuele inboxen per ontvanger, zoeken en filters, testmail opstellen, HTML-/tekst-/header-/bronweergave, bijlagen, gelezen-status en veilig bulkbeheer.
- Mailbeheer gebruikt dezelfde lokale sessiebeveiliging als Webbeheer en benadert Mailpit uitsluitend via een begrensde proxy op `127.0.0.1`; externe afbeeldingen blijven standaard geblokkeerd.
- `.test`-domeinen worden bij het toevoegen en openen van een project automatisch met het Windows-hostsbestand gesynchroniseerd als dat nog nodig is, zodat onder andere `voorbeeld-website.test` direct werkt.
- Nieuwe projecten gebruiken standaard het gereserveerde `.localhost`, zonder hosts-bestand, DNS-service of UAC; `.test` blijft als compatibiliteitskeuze beschikbaar.
- Product Doctor beoordeelt per project paden, entrypoint, PHP, vhost, domein, lockfiles, database, HTTPS en schrijfrechten en kan veilige configuratie opnieuw opbouwen.
- Werkruimte-export neemt websites, databaseback-ups, snapshots, testmail en geschoonde instellingen mee; herstel kan bewust alleen naar een lege map.
- Databasebeheer ondersteunt lokale gebruikers met rechten op één database, naast querytiming, schema-analyse, testdata en phpMyAdmin.
- Mailbeheer Pro voegt tags, bulk-tagging, `.eml`-export, bewaarlimieten en HTML-, link- en SpamAssassin-controles toe.
- Alle 13 runtimepakketten hebben nu een exacte patchversie en opvraagbare SHA-256-status in het dashboard.
- De releasefabriek weigert standaard ongetekende publieke builds; alleen de expliciete `-AllowUnsigned`-route maakt een duidelijk gemarkeerde release met verplichte SHA-256-controle.
- Veilige supportbundels, componentpakketbeleid, projectrecepten en een Windows 10/11-testmatrix zijn toegevoegd.

## 0.6.1 — volledig ingebouwde Windows-runtime

- Apache, PHP 8.2–8.5, MySQL, phpMyAdmin, Mailpit, Redis, Composer, WinSW, mkcert en de Microsoft VC++ Runtime zitten voortaan in de installer, portable EXE en uitpakbare ZIP.
- De runtime-installatie gebruikt geen `winget`, download-API of internetverbinding meer en controleert alle 13 pakketten vooraf met SHA-256.
- `TypeNotFound` is opgelost in zowel de installatie als het DPAPI-script waarmee LocalDeck het versleutelde MySQL-wachtwoord leest.
- De MySQL-bootstrap gebruikt een tijdelijke vrije poort en kan daardoor nooit per ongeluk een bestaande MySQL-installatie op poort 3306 benaderen.
- Afsluiten in app-modus beëindigt nu ook Windows-procesbomen betrouwbaar en voorkomt ongewenste automatische herstarts.
- Echte end-to-endcontrole toegevoegd voor alle zes services, localhost, phpMyAdmin, Mailpit en databasebeheer.

## 0.6.0 — Project Studio en Developer Inspector

- Project Studio met Git-clone, frameworkdetectie, reproduceerbare `.localdeck.yml`-blueprints en SHA-256-lockfiles.
- Geïsoleerde Git-worktree-omgevingen per branch met eigen `.test`-domein, poorten en databaseprofiel.
- PHP 8.2–8.5 draait per gebruikte projectversie in een eigen FastCGI-pool; PHP 8.5 is standaard en Composer/frameworktaken gebruiken dezelfde projectruntime.
- Developer Inspector combineert Apache-requestlogs, PHP-/taakmeldingen en lokaal procesgeheugen.
- Database Lab vergelijkt schema's, maakt begrensde testdata en anonimiseert herkenbare persoonsgegevens na een automatische SQL-back-up.
- Xdebug is per project uit, debug of profiling en gebruikt een trigger om normale requests snel te houden.
- Tijdgebonden LAN-deellinks gebruiken een willekeurig token, blijven accountloos en verlopen automatisch.
- Lokale CLI en optionele MCP-server bieden een beperkte set expliciete beheeracties zonder cloudaccount.
- Eco-modus stopt app-services na instelbare inactiviteit.
- Webbeheer omvat nu projecten, databases, mail, resource-instellingen en de inspectortijdlijn.
- Striktere domein-, poort-, origin- en configuratievalidatie en automatische HTTP-fallback wanneer nog geen lokaal certificaat bestaat.
- Windows-services ondersteunen de PHP-versiepools naast elkaar en alle processtarts blijven verborgen.

## 0.5.1 — Windows-runtime hotfix

- Een reeds aanwezige Microsoft Visual C++ Runtime wordt via het Windows-register herkend en niet opnieuw via winget geïnstalleerd.
- De officiële, digitaal ondertekende Microsoft-download wordt als veilige fallback gebruikt wanneer VC++ werkelijk ontbreekt.
- Exitcodes voor "al geïnstalleerd" en "herstart vereist" worden correct afgehandeld.
- Installatielogs worden bewaard in `runtime\logs` en foutmeldingen behouden hun Nederlandse tekens onder Windows PowerShell 5.1.

## 0.5.0 — uitgebreide Windows-preview

- Uitpakbare Windows ZIP naast de NSIS-installer en portable EXE.
- Projectprofielen met unieke HTTP/HTTPS-poorten, developerpagina en `.localdeck.json`.
- Project- en databasesnapshots met veilig herstel.
- Ingebouwde taakrunner voor Composer, npm, PHPUnit, Artisan en eigen opdrachten.
- Database-inspector met tabeloverzicht en begrensde, alleen-lezen SQL.
- Lokale `.test`-DNS als verborgen app-proces of echte Windows-service.
- Beveiligingscentrum, automatisch herstel en Authenticode-controle voor updates.
- Feedgestuurde rollback naar een vorige installer, opnieuw gecontroleerd met SHA-256 en optioneel Authenticode.
- Offline-runtimepakket met 13 benodigde Windows-onderdelen.
- XAMPP-, WampServer- en Laragon-migratie; WordPress-, Laravel-, Symfony- en Drupal-templates.
- Plug-incatalogus, gecombineerde servicelogs en configureerbare Mailpit/SMTP-route.

## 0.4.1 — runtime-installatiefix

- De eerste-startwizard installeert nu daadwerkelijk Apache, PHP, MySQL, phpMyAdmin, Mailpit en Redis voordat hij wordt afgesloten
- Ontbrekende of onvolledige runtimes worden op schijf gecontroleerd en niet langer door oude instellingen overschreven
- Serviceknoppen blijven uitgeschakeld totdat de echte Windows-runtime gereed is
- Dashboard en Services bieden direct een knop om de ontbrekende runtime te installeren
- Installatievoortgang en de laatste relevante foutmelding blijven zichtbaar in de wizard

## 0.4.0 — Windows release candidate

- Keuze tussen XAMPP-achtige app-modus en blijvende Windows-services via WinSW
- Beveiligd lokaal webbeheer op `127.0.0.1` met een willekeurig toegangstoken
- Volledig vernieuwd desktopdashboard met directe instellingen en herstelacties
- Mooie update-popup, stabiel/bèta-kanaal en configureerbare HTTPS-updatefeed
- Mailpit-adresboek voor lokale testadressen en directe inboxkoppeling
- Eén-klik projectinstallatie voor WordPress, Laravel en Symfony
- Veilige XAMPP-projectimport en projectexport als LocalDeck-zip
- Composer install/audit, VS Code, terminal en projectmap per project
- PHP-versies en extensies beheren; Xdebug wordt geactiveerd zodra een passende DLL aanwezig is
- Databaseback-ups, herstelpunten en verborgen dagelijkse of wekelijkse planning
- MySQL-wachtwoord versleuteld met Windows DPAPI en phpMyAdmin-cookieauthenticatie
- Automatisch vrije poort zoeken, configuratievalidatie en beperkte crashherstart
- Optionele installatie van Node.js LTS, Git en Visual Studio Code
- Privacyveilig diagnoserapport, licentieoverzicht en releasevoorbeeld voor updatefeeds

## 0.3.1 — localhost-dashboard

- Vaste LocalDeck-startpagina op zowel `localhost` als `127.0.0.1`
- Dashboardknoppen voor de startpagina en phpMyAdmin
- Benodigde services worden automatisch en stil gestart vóór het openen
- Standaard-vhost blijft actief nadat `.test`-projecten zijn toegevoegd
- Startpagina volgt aangepaste Apache-, PHP-, MySQL- en phpMyAdmin-poorten

## 0.3.0 — Windows native preview

- Geïntegreerde runtime-installatie met voortgang in de app
- Stille PowerShell-launcher en achtergrondprocessen zonder consolevensters
- Persistente projecten, poorten, databases en instellingen
- Windows-systeemvak en optioneel starten met Windows
- PHP 8.2, 8.3, 8.4 en 8.5 plus Composer
- Apache, MySQL 8.4 LTS, phpMyAdmin, Redis en Mailpit
- `.test`-domeinen, Apache virtual hosts, mkcert en lokaal HTTPS
- Poortdiagnose en service-healthchecks
- SQL import, export en automatische back-up vóór verwijderen
- Willekeurig MySQL-rootwachtwoord met beperkt Windows-bestandsrecht
- Nieuw LocalDeck-logo, favicon en Windows-icoon

## 0.2.0

- Eerste native installerpreview

## 0.1.x

- Simulatiemodus, dashboard, projecten, services en databaseprototype
