# Security policy

LocalDeck listens on `127.0.0.1` by default. Web Management uses a random local token stored in an HttpOnly cookie. External access, SMTP relay, and LAN sharing are disabled by default.

## Supported versions

Security fixes are applied to the newest published stable version and, while it is active, the newest public release candidate. Older test and archive releases receive no separate security updates. Users should reproduce an issue on the newest available version without sharing private project data.

Official releases must pass the following controls:

- every runtime component has an exact version, size, and SHA-256 value in `offline-runtime.json`;
- the release workflow validates Windows artifacts against the configured publisher policy;
- the update client checks HTTPS, SHA-256, and—when enabled—the Authenticode publisher;
- support bundles exclude websites, databases, passwords, private keys, and certificate keys.
- release inputs are pinned by version, byte length, SHA-256, upstream source, and license;
- official signed releases must follow [CODE_SIGNING_POLICY.md](CODE_SIGNING_POLICY.md).

## Reporting a vulnerability

Please use [GitHub private vulnerability reporting](https://github.com/Platinum-Radio/LocalDeck/security/advisories/new). Do not disclose sensitive security details in a public issue, discussion, or community post.

Include the affected LocalDeck version, Windows version, reproduction steps, impact, and any relevant sanitized logs. Do not attach project files, databases, credentials, certificates, or private keys. You will receive acknowledgement through GitHub; timelines depend on severity and the ability to reproduce the issue.

If private vulnerability reporting is temporarily unavailable, use the security contact route on <https://localdeck.nl/security.php?lang=en> and clearly mark the message as confidential.
