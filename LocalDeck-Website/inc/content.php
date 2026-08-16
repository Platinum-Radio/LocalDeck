<?php
declare(strict_types=1);

return [
    'start' => [
        'title' => ['Starten met LocalDeck', 'Getting started with LocalDeck'],
        'summary' => ['Van uitpakken tot je eerste veilige lokale website.', 'From extracting the folder to your first secure local website.'],
        'minutes' => 7,
        'sections' => [
            [
                'title' => ['1. Start LocalDeck', '1. Start LocalDeck'],
                'paragraphs' => [
                    ['Open de map LocalDeck-Startklaar en start LocalDeck.exe. De eerste keer kies je of services alleen met het programma meedraaien of als Windows-services actief blijven.', 'Open the LocalDeck-Startklaar folder and run LocalDeck.exe. On first launch, choose whether services run only with the app or remain active as Windows services.'],
                    ['Voor normaal lokaal werk is app-modus de veiligste standaard: stoppen van LocalDeck stopt dan ook de ontwikkelservices.', 'Application mode is the safest default for normal local work: closing LocalDeck also stops the development services.'],
                ],
                'steps' => [
                    ['Klik op Alles starten.', 'Click Start all.'],
                    ['Controleer of Apache, PHP, MySQL, phpMyAdmin, Mailpit en Redis groen zijn.', 'Check that Apache, PHP, MySQL, phpMyAdmin, Mailpit, and Redis are green.'],
                    ['Open Webbeheer of ga naar https://localhost.', 'Open Web Management or visit https://localhost.'],
                ],
            ],
            [
                'title' => ['2. Maak een project', '2. Create a project'],
                'paragraphs' => [
                    ['Maak een map onder websites of gebruik Nieuw project in het dashboard. LocalDeck ontdekt mappen met een index.php, index.html, composer.json of een public/index.php automatisch.', 'Create a folder under websites or use New project in the dashboard. LocalDeck automatically discovers folders containing index.php, index.html, composer.json, or public/index.php.'],
                    ['Een project krijgt een eigen .localhost-domein, poortpaar, PHP-profiel en — zodra HTTPS wordt ingeschakeld — een eigen lokaal certificaat.', 'Each project receives its own .localhost domain, port pair, PHP profile, and—once HTTPS is enabled—its own local certificate.'],
                ],
            ],
        ],
    ],
    'installatie' => [
        'title' => ['Installatie en portable gebruik', 'Installation and portable use'],
        'summary' => ['LocalDeck werkt als Windows-installatie of volledig vanuit één map.', 'LocalDeck works as a Windows installation or entirely from one folder.'],
        'minutes' => 8,
        'sections' => [
            [
                'title' => ['Portable map', 'Portable folder'],
                'paragraphs' => [
                    ['De startklare map bevat het dashboard, de offline runtimes, configuratie en de map websites. Verplaats de volledige map als één geheel; absolute projectpaden worden bij de volgende start opnieuw gekoppeld.', 'The ready-to-run folder contains the dashboard, offline runtimes, configuration, and the websites folder. Move the whole folder as one unit; absolute project paths are relinked on the next launch.'],
                    ['Kies de EXE voor de normale Windows-installatie of pak de ZIP uit in een map naar keuze en start LocalDeck.exe.', 'Choose the EXE for the regular Windows installation, or extract the ZIP to any folder and start LocalDeck.exe.'],
                ],
            ],
            [
                'title' => ['Windows-services', 'Windows services'],
                'paragraphs' => [
                    ['Servicemodus installeert de onderdelen als echte Windows-services en vraagt daarom eenmalig beheerderstoestemming. App-modus start processen verborgen en toont geen opdrachtvensters.', 'Service mode installs components as real Windows services and therefore requires administrator permission once. Application mode starts hidden processes and shows no command windows.'],
                ],
            ],
        ],
    ],
    'services' => [
        'title' => ['Services, poorten en herstel', 'Services, ports, and repair'],
        'summary' => ['Beheer ieder onderdeel apart en los poortconflicten gecontroleerd op.', 'Manage every component separately and resolve port conflicts safely.'],
        'minutes' => 9,
        'sections' => [
            [
                'title' => ['Afzonderlijk starten', 'Start individually'],
                'paragraphs' => [
                    ['Apache verzorgt HTTP en HTTPS, PHP draait via FastCGI, MySQL bewaart databases, phpMyAdmin beheert die databases, Mailpit vangt testmail op en Redis levert cache en queues.', 'Apache handles HTTP and HTTPS, PHP runs through FastCGI, MySQL stores databases, phpMyAdmin manages them, Mailpit captures test mail, and Redis provides cache and queues.'],
                    ['Gebruik de schakelaar bij één service om alleen dat onderdeel te starten of stoppen. Afhankelijkheden worden gecontroleerd voordat LocalDeck de actie uitvoert.', 'Use the switch beside a service to start or stop only that component. Dependencies are checked before LocalDeck performs the action.'],
                ],
            ],
            [
                'title' => ['Poort Autopilot', 'Port Autopilot'],
                'paragraphs' => [
                    ['Poorten controleren toont welk proces een bezette poort gebruikt. Poort Autopilot kiest een vrije poort, werkt de configuratie bij en herstart uitsluitend de betrokken onderdelen.', 'Check ports shows which process occupies a port. Port Autopilot chooses a free port, updates the configuration, and restarts only affected components.'],
                    ['Gebruik Herstellen wanneer configuratiebestanden ontbreken of niet meer overeenkomen met het dashboard. Projectbestanden worden daarbij niet overschreven.', 'Use Repair when configuration files are missing or no longer match the dashboard. Project files are never overwritten.'],
                ],
            ],
        ],
    ],
    'projecten-https' => [
        'title' => ['Projecten, domeinen en automatisch SSL', 'Projects, domains, and automatic SSL'],
        'summary' => ['Elk project krijgt een eigen domein, profiel en lokaal vertrouwd certificaat.', 'Every project gets its own domain, profile, and locally trusted certificate.'],
        'minutes' => 10,
        'sections' => [
            [
                'title' => ['.localhost of .test', '.localhost or .test'],
                'paragraphs' => [
                    ['Gebruik bij voorkeur .localhost: dit domein verwijst volgens browserconventies naar je eigen computer en vereist geen regel in het Windows hosts-bestand. .test is beschikbaar wanneer je productieachtige domeinen wilt nabootsen.', 'Prefer .localhost: by browser convention it points to your own computer and needs no Windows hosts-file entry. .test is available when you need production-like domains.'],
                    ['Bij het inschakelen van HTTPS maakt LocalDeck met de lokale certificaatautoriteit een afzonderlijk certificaat voor het project. De sleutel blijft in de LocalDeck-gegevensmap.', 'When HTTPS is enabled, LocalDeck uses its local certificate authority to create a separate certificate for the project. Its key remains in the LocalDeck data folder.'],
                ],
            ],
            [
                'title' => ['Veilig verwijderen', 'Safe removal'],
                'paragraphs' => [
                    ['De verwijderdialoog noemt afzonderlijk bestanden, databases, snapshots, branchomgevingen, certificaten en domeinconfiguratie. Verwijderen begint pas na expliciete bevestiging met de projectnaam.', 'The removal dialog lists files, databases, snapshots, branch environments, certificates, and domain configuration separately. Removal starts only after explicit confirmation with the project name.'],
                    ['Maak eerst een export of snapshot wanneer gegevens later nog nodig kunnen zijn. Verwijderde projectonderdelen zijn niet automatisch herstelbaar.', 'Create an export or snapshot first if data may be needed later. Removed project components are not automatically recoverable.'],
                ],
            ],
        ],
    ],
    'databases' => [
        'title' => ['Databases en phpMyAdmin', 'Databases and phpMyAdmin'],
        'summary' => ['Databases aanmaken, bekijken, exporteren en veilig koppelen.', 'Create, inspect, export, and safely connect databases.'],
        'minutes' => 8,
        'sections' => [
            [
                'title' => ['Databasebeheer', 'Database management'],
                'paragraphs' => [
                    ['Maak vanuit Databases een schema en optioneel een aparte gebruiker. Voor lokale projecten gebruikt LocalDeck standaard 127.0.0.1 en de ingestelde MySQL-poort.', 'Create a schema and optionally a dedicated user from Databases. LocalDeck uses 127.0.0.1 and the configured MySQL port for local projects by default.'],
                    ['phpMyAdmin opent vanuit het dashboard met de lokale beheersessie. Gebruik voor applicaties altijd een projectgebruiker met zo weinig mogelijk rechten.', 'phpMyAdmin opens from the dashboard with the local management session. Applications should always use a project user with the minimum required permissions.'],
                ],
            ],
            [
                'title' => ['Back-up en herstel', 'Backup and restore'],
                'paragraphs' => [
                    ['Automatische back-ups volgen het gekozen dagelijkse of wekelijkse schema. Maak vóór migraties en destructieve wijzigingen altijd een handmatige back-up of projectsnapshot.', 'Automatic backups follow the selected daily or weekly schedule. Always create a manual backup or project snapshot before migrations and destructive changes.'],
                ],
            ],
        ],
    ],
    'mail' => [
        'title' => ['Lokale e-mail en Mailbeheer', 'Local email and Mail Management'],
        'summary' => ['Test e-mail zonder per ongeluk berichten naar echte ontvangers te sturen.', 'Test email without accidentally sending messages to real recipients.'],
        'minutes' => 6,
        'sections' => [
            [
                'title' => ['Mail opvangen', 'Capture mail'],
                'paragraphs' => [
                    ['Gebruik SMTP-host 127.0.0.1 en poort 1025 in je project. Mailpit vangt de berichten lokaal op; Mailbeheer voegt adressen, retentie en een beheeroverzicht toe.', 'Use SMTP host 127.0.0.1 and port 1025 in your project. Mailpit captures messages locally; Mail Management adds addresses, retention, and an administrative overview.'],
                    ['De inbox is alleen bedoeld voor ontwikkeling. Gebruik voor openbare websites een echte mailprovider met geverifieerd domein, SPF, DKIM en DMARC.', 'The inbox is for development only. Public websites should use a real mail provider with a verified domain, SPF, DKIM, and DMARC.'],
                ],
            ],
        ],
    ],
    'updates' => [
        'title' => ['Updates, downloads en integriteit', 'Updates, downloads, and integrity'],
        'summary' => ['Hoe LocalDeck nieuwe versies ontdekt en controleert vóór installatie.', 'How LocalDeck discovers and verifies new versions before installation.'],
        'minutes' => 7,
        'sections' => [
            [
                'title' => ['Updatefeed', 'Update feed'],
                'paragraphs' => [
                    ['LocalDeck leest een klein HTTPS JSON-bestand met versienummer, downloadlink, release notes en SHA-256. Alleen een hogere versie op het gekozen kanaal activeert de updatepopup.', 'LocalDeck reads a small HTTPS JSON file with a version number, download link, release notes, and SHA-256. Only a higher version on the selected channel triggers the update popup.'],
                    ['De downloader controleert het bestand verplicht met SHA-256 voordat de installatie wordt gestart. Een release verschijnt pas in de downloadmap nadat de volledige releasecontrole is geslaagd.', 'The downloader verifies the file with SHA-256 before installation starts. A release appears in the download folder only after the full release validation has passed.'],
                ],
            ],
            [
                'title' => ['Downloadstatistieken', 'Download statistics'],
                'paragraphs' => [
                    ['Downloadlinks lopen via een teller die per versie en bestand één gestarte download registreert. De teller bewaart geen IP-adres, cookie of browserprofiel en telt ontbrekende bestanden niet.', 'Download links pass through a counter that records one initiated download per version and file. It stores no IP address, cookie, or browser profile and does not count missing files.'],
                    ['Een gestarte download is niet hetzelfde als een volledig afgeronde installatie; dat onderscheid wordt bewust zichtbaar gehouden.', 'An initiated download is not the same as a completed installation; that distinction is deliberately kept visible.'],
                ],
            ],
        ],
    ],
    'problemen' => [
        'title' => ['Problemen oplossen', 'Troubleshooting'],
        'summary' => ['Snelle diagnose voor services, domeinen, poorten en browsers.', 'Quick diagnosis for services, domains, ports, and browsers.'],
        'minutes' => 11,
        'sections' => [
            [
                'title' => ['Website opent niet', 'Website does not open'],
                'steps' => [
                    ['Controleer of Apache en PHP actief én gezond zijn.', 'Check that Apache and PHP are active and healthy.'],
                    ['Open Poorten controleren en los conflicten op met Autopilot.', 'Open Check ports and resolve conflicts with Autopilot.'],
                    ['Controleer of het projectdomein in het dashboard overeenkomt met de browser-URL.', 'Check that the project domain in the dashboard matches the browser URL.'],
                    ['Voer Herstellen uit om vhosts en de lokale startpagina opnieuw te genereren.', 'Run Repair to regenerate virtual hosts and the local start page.'],
                ],
            ],
            [
                'title' => ['Failed to fetch', 'Failed to fetch'],
                'paragraphs' => [
                    ['Deze melding betekent meestal dat Webbeheer zijn lokale API niet bereikt, dat Apache nog herstart, of dat een browsercertificaat niet wordt vertrouwd. Controleer de servicestatus en vernieuw daarna de pagina.', 'This message usually means Web Management cannot reach its local API, Apache is still restarting, or a browser certificate is not trusted. Check service status and then refresh the page.'],
                ],
            ],
        ],
    ],
];
