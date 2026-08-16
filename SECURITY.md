# Beveiliging

LocalDeck luistert standaard uitsluitend op `127.0.0.1`. Webbeheer gebruikt een lokaal token in een HttpOnly-cookie. Externe toegang, SMTP-relay en LAN-delen staan standaard uit.

Officiële releases moeten aan drie controles voldoen:

- ieder runtimepakket heeft een exacte versie, grootte en SHA-256 in `offline-runtime.json`;
- de publicatiestraat accepteert alleen geldig door de ingestelde uitgever ondertekende Windows-bestanden;
- de updateclient controleert HTTPS, SHA-256 en — zodra ingeschakeld — de Authenticode-uitgever.

Een supportbundel bevat geen websites, databases, wachtwoorden, privésleutels of certificaatsleutels. Meld een kwetsbaarheid privé bij het toekomstige officiële beveiligingsadres; publiceer gevoelige details niet in een openbaar issue.

