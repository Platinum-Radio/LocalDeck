# LocalDeck releasebron

Deze map heeft twee doelen:

1. `windows.json` is de stabiele machineleesbare updatefeed voor de LocalDeck-desktopapp.
2. `beta.json` is het afzonderlijke kanaal voor test- en prereleaseversies.
3. `releases.json` voedt de downloadpagina, bestandscontrole en statistieken per versie.

Plaats nooit handmatig een ongetest bestand in een gepubliceerde release. De normale volgorde is:

1. Rond de versie af in de canonieke `LocalDeck-Master`-map.
2. Voer `Validate-LocalDeck.ps1` uit.
3. Maak uitsluitend na expliciete goedkeuring de definitieve release met `Publish-LocalDeck.ps1 -Finalize`.
4. Onderteken de Windows-bestanden met de officiële Authenticode-uitgever zodra het certificaat beschikbaar is. Een bewust ongetekende testrelease vereist de expliciete `-AllowUnsigned`-route.
5. Gebruik daarna `tools/Publish-WebsiteRelease.ps1` om de gecontroleerde bestanden in deze map te plaatsen en de JSON-bestanden atomisch bij te werken.

De downloadlink loopt via `download.php`. Alleen bekende, gepubliceerde en werkelijk aanwezige bestanden worden geteld. De teller registreert een gestarte overdracht, niet een bevestigde installatie, en bewaart geen IP-adressen.

Beide feeds wijzen uitsluitend naar HTTPS-downloads op LocalDeck.nl. Het stabiele kanaal wordt nooit automatisch vervangen door een prerelease.
