# Security policy

LocalDeck listens on `127.0.0.1` by default. Web Management uses a random local token stored in an HttpOnly cookie. External access, SMTP relay, and LAN sharing are disabled by default.

Official releases must pass the following controls:

- every runtime component has an exact version, size, and SHA-256 value in `offline-runtime.json`;
- the release workflow validates Windows artifacts against the configured publisher policy;
- the update client checks HTTPS, SHA-256, and—when enabled—the Authenticode publisher;
- support bundles exclude websites, databases, passwords, private keys, and certificate keys.

## Reporting a vulnerability

Please use [GitHub private vulnerability reporting](https://github.com/Platinum-Radio/LocalDeck/security/advisories/new). Do not disclose sensitive security details in a public issue, discussion, or community post.
