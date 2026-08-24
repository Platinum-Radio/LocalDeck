# LocalDeck-website

Dit is de afzonderlijke canonieke bron voor LocalDeck.nl. De map staat bewust naast `LocalDeck-Startklaar` en wordt nooit door een reset of herbouw van de desktopapp verwijderd.

Dit is het lokale websiteproject voor LocalDeck. Het draait zonder Composer- of npm-installatie op de meegeleverde Apache/PHP-runtime.

## Waarom geen WordPress

De productsite, wiki en updatefeed horen direct bij de LocalDeck-versie. Een zelfstandige site houdt deze inhoud in versiebeheer, beperkt plug-inonderhoud en maakt de JSON-updateketen eenvoudig controleerbaar. WordPress met bbPress is bruikbaar wanneer niet-technische redacteuren alles via één CMS moeten beheren, maar voegt voor deze productsite een extra kern, thema en plug-ins toe die doorlopend bijgewerkt en geback-upt moeten worden.

Voor een eventueel openbaar forum blijft een afzonderlijke Flarum-installatie op bijvoorbeeld `community.localdeck.nl` een logische vervolgstap. De huidige supportpagina is een direct contactformulier en pretendeert geen volledig publiek forum te zijn.

## Onderdelen

- `index.php` — productwebsite.
- `wiki.php` en `inc/content.php` — tweetalige wiki.
- `guides.php` en de afzonderlijke gidsroutes — praktische, tweetalige zoekmachinevriendelijke handleidingen.
- `compare.php` — transparante productvergelijking met verwijzingen naar officiële bronnen.
- `community.php` — beveiligd supportformulier voor vragen, fouten, ideeën en documentatie; e-mailt naar de vaste beheerinbox en houdt maximaal 180 dagen een lokale afleverkopie bij.
- `downloads.php` — de nieuwste release bovenaan en een downloadbaar archief van oudere versies, beide met aantallen per versie en uitvoering.
- `download.php` — gevalideerde downloadroute en privacyvriendelijke teller.
- `security.php` — veiligheidsmodel en kwetsbaarheden melden.
- `code-signing.php` — openbare status, SignPath-bronvermelding en verificatie-instructies.
- `status.php` en `api/status.php` — live componentcontrole in HTML en JSON.
- `sitemap.php` — tweetalige XML-sitemap, publiek beschikbaar als `sitemap.xml`.
- `downloads/windows.json` — updatefeed voor de desktopapp.
- `downloads/releases.json` — releasecatalogus voor website en teller.
- `private/download-stats.json` — aantallen; via Apache niet publiek leesbaar.
- `tools/Publish-WebsiteRelease.ps1` — publiceert uitsluitend reeds ondertekende definitieve bestanden in de websitebron.

## Downloadstatistieken

Een download wordt geteld nadat versie, uitvoering, publicatiestatus en bestandsbestaan zijn gecontroleerd. Daarna volgt een redirect naar het bestand. Het getal staat dus voor gestarte downloads, niet voor bevestigde installaties. Er worden geen IP-adressen, user agents of unieke identifiers opgeslagen.

De releasecatalogus wordt op semantisch versienummer aflopend gesorteerd; de volgorde in `releases.json` is daarom niet bepalend. De hoogste versie staat in het hoofdblok. Alle overige catalogusversies verschijnen automatisch in het uitklapbare versiearchief. Alleen gepubliceerde bestanden die werkelijk binnen `downloads/releases` bestaan krijgen een actieve downloadknop.

De JSON-opslag gebruikt een exclusieve file lock en is geschikt voor de release-candidatefase en een bescheiden downloadsite. Bij veel gelijktijdig verkeer kan dezelfde functie zonder wijziging aan de publieke URL naar een SQL-tabel worden verplaatst.

## Productiebeheer

Voor publicatie zijn minimaal nodig:

1. Behoud HTTPS-hosting met PHP 8.2+ en controleer `status.php` na iedere uitrol.
2. Houd de `private`-map schrijfbaar voor PHP, maar geblokkeerd via HTTP.
3. Bewaak servermail voor `website@localdeck.nl`, inclusief SPF/DKIM; test periodiek de aflevering naar de vaste beheerinbox.
4. Controleer privacytekst en bewaartermijnen wanneer de support- of statistiekopslag verandert.
5. Voeg bij een toekomstige ondertekende release de exacte vertrouwde Authenticode-uitgever toe aan LocalDeck.
6. Alleen gevalideerde output uit `Publish-LocalDeck.ps1 -Finalize` naar `Publish-WebsiteRelease.ps1` doorgeven.
7. De publieke updatefeed-URL in LocalDeck instellen en een volledige updateproef uitvoeren.

Tijdens normaal ontwikkelwerk worden geen nieuwe installer-, ZIP- of andere distributiebestanden gemaakt.
