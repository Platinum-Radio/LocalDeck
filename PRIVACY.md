# Privacy policy

Last updated: 18 August 2026

LocalDeck is an offline-first development tool. It requires no LocalDeck account, license key, online activation, advertising identifier, or telemetry service. **Telemetry is not implemented** in the current release, even though a disabled interface setting is reserved for possible future functionality.

## Data that remains on the computer

LocalDeck does not send the following data to LocalDeck, SignPath, or another analytics service:

- project files, source code, databases, and database credentials;
- captured email and virtual test addresses;
- local certificates and private keys;
- logs, snapshots, backups, diagnostic findings, and API test responses;
- local paths, project names, environment files, or secret values.

These items stay in the selected LocalDeck data and project directories. Workspace exports can contain project data and must be treated as confidential. Passwords and private keys are excluded from workspace exports. Support bundles are separate, sanitized, and created only when the user requests them; users are instructed to inspect a bundle before sharing it.

## Network connections

LocalDeck makes network requests only for documented functionality that is enabled or invoked by the user:

- **Update check:** disabled by default and available as an opt-in setting. When enabled, LocalDeck requests the configured HTTPS JSON update feed. The request does not contain a LocalDeck account, installation identifier, project information, or telemetry payload. As with any web request, the hosting provider can receive ordinary connection data such as the public IP address and request headers. A manual check is also available.
- **Update download:** starts only after user action and verifies the published SHA-256 value. Signed update enforcement can additionally verify Authenticode.
- **Git and package tools:** Git clone, Composer, npm, framework installers, and online templates contact the URL or registry chosen by the user and are governed by those services' privacy policies.
- **External developer tools:** optional downloads for tools such as Git, Node.js, or an editor occur only after user action.
- **SMTP relay:** LocalDeck sends mail outside the local Mailpit inbox only when the user configures and invokes an external SMTP server.
- **LAN sharing:** disabled by default. When enabled for a project, LocalDeck exposes a temporary tokenized link on the local network for the selected duration.
- **User-defined API requests:** API Studio and replay features accept only local destinations in the current release.

Local Web Management, Mail Management, phpMyAdmin, project domains, webhook capture, and the default DNS service listen locally. The default bind address is `127.0.0.1` unless the user explicitly enables a documented sharing feature.

## LocalDeck.nl

The website uses no advertising, analytics, or tracking cookies. Its functional language cookie stores only the selected language.

When an official download button is used, the website increments an aggregate counter for the version and edition. It does not store an IP address, browser profile, or unique user identifier in the download counter. Normal web-server access logs may still contain standard connection data according to the hosting provider's configuration and retention policy.

Support submissions contain the category, title, message, and optional name and email address entered by the sender. They are emailed to the LocalDeck administration inbox. A local delivery copy is retained for no more than 180 days and is pruned by the website. The application never submits a support bundle automatically.

## Changes

Any future telemetry or new external data transfer requires a documented code change, review, an update to this policy, and a clear opt-in before release. Enabling the currently reserved telemetry setting does not transmit data in this version.

Privacy questions can be submitted through <https://localdeck.nl/community.php?lang=en>. Security vulnerabilities must be reported privately as described in [SECURITY.md](SECURITY.md).
