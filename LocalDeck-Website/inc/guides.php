<?php
declare(strict_types=1);

return [
    'xampp-alternative' => [
        'file' => 'xampp-alternative.php',
        'title' => ['Een modern XAMPP-alternatief voor Windows', 'A modern XAMPP alternative for Windows'],
        'summary' => ['Wanneer je meer nodig hebt dan alleen een webserver en database.', 'When you need more than just a web server and database.'],
        'intro' => ['LocalDeck combineert een vertrouwde Apache/PHP-stack met projectprofielen, automatisch HTTPS, herstelpunten en afzonderlijk servicebeheer.', 'LocalDeck combines a familiar Apache/PHP stack with project profiles, automatic HTTPS, restore points, and individual service control.'],
        'sections' => [
            [['Waarom overstappen?', 'Why switch?'], ['XAMPP blijft bruikbaar voor een eenvoudige lokale stack. LocalDeck richt zich daarnaast op meerdere projecten, verschillende PHP-versies, veilige poortwijzigingen en reproduceerbaar herstel.', 'XAMPP remains useful for a straightforward local stack. LocalDeck also focuses on multiple projects, different PHP versions, safe port changes, and reproducible repair.']],
            [['Wat blijft herkenbaar?', 'What stays familiar?'], ['Apache, PHP, MySQL en phpMyAdmin blijven beschikbaar. Je kunt services nog steeds samen of afzonderlijk starten en localhost rechtstreeks openen.', 'Apache, PHP, MySQL, and phpMyAdmin remain available. You can still start services together or individually and open localhost directly.']],
        ],
    ],
    'php-85-windows' => [
        'file' => 'php-85-windows.php',
        'title' => ['PHP 8.5 lokaal gebruiken op Windows', 'Use PHP 8.5 locally on Windows'],
        'summary' => ['Start PHP 8.5 zonder losse runtime-installatie of systeemwijzigingen.', 'Start PHP 8.5 without a separate runtime installation or system-wide changes.'],
        'intro' => ['LocalDeck bevat PHP 8.2 tot en met 8.5 en routeert projecten via afzonderlijke FastCGI-profielen.', 'LocalDeck includes PHP 8.2 through 8.5 and routes projects through separate FastCGI profiles.'],
        'sections' => [
            [['PHP-versie kiezen', 'Choose a PHP version'], ['Kies bij het project PHP 8.5 en laat LocalDeck de bijbehorende handler, poort en configuratie activeren. Andere projecten kunnen hun eigen versie behouden.', 'Select PHP 8.5 on the project and let LocalDeck activate the matching handler, port, and configuration. Other projects can keep their own version.']],
            [['Compatibiliteit controleren', 'Check compatibility'], ['Voer Composer-controle, applicatietests en de LocalDeck-diagnose uit voordat een bestaand project definitief naar een nieuwere PHP-versie wordt omgezet.', 'Run Composer checks, application tests, and LocalDeck diagnostics before permanently moving an existing project to a newer PHP version.']],
        ],
    ],
    'local-https' => [
        'file' => 'local-https.php',
        'title' => ['Automatisch HTTPS voor lokale projecten', 'Automatic HTTPS for local projects'],
        'summary' => ['Geef iedere lokale website een eigen vertrouwd certificaat.', 'Give every local website its own trusted certificate.'],
        'intro' => ['LocalDeck beheert een lokale certificaatautoriteit en maakt per project een afzonderlijk certificaat.', 'LocalDeck manages a local certificate authority and creates a separate certificate for each project.'],
        'sections' => [
            [['Waarom lokaal HTTPS?', 'Why local HTTPS?'], ['Cookies met Secure, OAuth-callbacks, service workers en browser-API’s gedragen zich zo dichter bij productie.', 'Secure cookies, OAuth callbacks, service workers, and browser APIs behave more like production.']],
            [['Automatisch herstel', 'Automatic repair'], ['Wanneer een domein of certificaat ontbreekt kan Domeinen & HTTPS herstellen de configuratie opnieuw opbouwen zonder projectbestanden te vervangen.', 'When a domain or certificate is missing, Repair domains & HTTPS can rebuild configuration without replacing project files.']],
        ],
    ],
    'migrate-xampp' => [
        'file' => 'migrate-xampp.php',
        'title' => ['Een XAMPP-project migreren naar LocalDeck', 'Migrate an XAMPP project to LocalDeck'],
        'summary' => ['Verplaats code en databases gecontroleerd naar een eigen projectprofiel.', 'Move code and databases safely into a dedicated project profile.'],
        'intro' => ['De migratiewizard inventariseert de bronmap, documentroot, database en PHP-vereisten voordat iets wordt gekopieerd.', 'The migration wizard inventories the source folder, document root, database, and PHP requirements before copying anything.'],
        'sections' => [
            [['Voorbereiden', 'Prepare'], ['Maak eerst een export van de XAMPP-database en stop schrijvende processen. Bewaar de oorspronkelijke htdocs-map totdat de nieuwe omgeving volledig is gecontroleerd.', 'First export the XAMPP database and stop processes that write data. Keep the original htdocs folder until the new environment is fully verified.']],
            [['Controleren', 'Verify'], ['Open het nieuwe projectdomein, controleer PHP-extensies, voer database-migraties uit en test e-mail via Mailpit voordat de oude omgeving wordt verwijderd.', 'Open the new project domain, check PHP extensions, run database migrations, and test email through Mailpit before removing the old environment.']],
        ],
    ],
    'local-wordpress' => [
        'file' => 'local-wordpress.php',
        'title' => ['WordPress lokaal ontwikkelen op Windows', 'Develop WordPress locally on Windows'],
        'summary' => ['Een lokaal WordPress-project met database, HTTPS en testmail.', 'A local WordPress project with database, HTTPS, and test mail.'],
        'intro' => ['Maak een WordPress-project vanuit een lokaal sjabloon of importeer een bestaande installatie inclusief database-export.', 'Create a WordPress project from a local template or import an existing installation including a database export.'],
        'sections' => [
            [['Eigen projectomgeving', 'Dedicated project environment'], ['Gebruik een aparte databasegebruiker, een herkenbaar .localhost-domein en HTTPS. Test uitgaande WordPress-mail via de lokale SMTP-poort zonder echte ontvangers te bereiken.', 'Use a dedicated database user, a recognizable .localhost domain, and HTTPS. Test outgoing WordPress mail through the local SMTP port without reaching real recipients.']],
            [['Veilig experimenteren', 'Experiment safely'], ['Maak vóór plug-inupdates, zoek-en-vervangacties of PHP-wijzigingen een projectsnapshot met bestanden en database.', 'Create a project snapshot containing files and database before plugin updates, search-and-replace operations, or PHP changes.']],
        ],
    ],
    'php-per-project' => [
        'file' => 'php-per-project.php',
        'title' => ['Een eigen PHP-versie per project', 'A dedicated PHP version per project'],
        'summary' => ['Draai oude en nieuwe projecten tegelijk zonder globale PHP-wissel.', 'Run old and new projects simultaneously without a global PHP switch.'],
        'intro' => ['LocalDeck koppelt ieder project aan een eigen FastCGI-handler en bewaart die keuze in het projectprofiel.', 'LocalDeck links each project to its own FastCGI handler and stores that choice in the project profile.'],
        'sections' => [
            [['Gelijktijdig ontwikkelen', 'Develop simultaneously'], ['Een onderhoudsproject kan PHP 8.2 gebruiken terwijl een nieuw project op PHP 8.5 draait. Apache routeert ieder domein naar de juiste lokale handler.', 'A maintenance project can use PHP 8.2 while a new project runs PHP 8.5. Apache routes each domain to the correct local handler.']],
            [['Reproduceerbaar delen', 'Share reproducibly'], ['De Project Capsule legt onder andere PHP-versie, documentroot, services en poorten vast, zonder lokale wachtwoorden of persoonlijke paden te publiceren.', 'The Project Capsule records the PHP version, document root, services, and ports, without publishing local passwords or personal paths.']],
        ],
    ],
];
