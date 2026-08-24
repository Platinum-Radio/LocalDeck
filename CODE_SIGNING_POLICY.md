# Code signing policy

This policy describes how LocalDeck release artifacts are built, reviewed, signed, verified, and published.

## Project identity

- Source repository: <https://github.com/Platinum-Radio/LocalDeck>
- Product website: <https://localdeck.nl/>
- License: [Apache License 2.0](LICENSE)
- Security policy: [SECURITY.md](SECURITY.md)
- Privacy policy: [PRIVACY.md](PRIVACY.md)
- Third-party software: [THIRD-PARTY-NOTICES.md](THIRD-PARTY-NOTICES.md)

## SignPath status

LocalDeck is being prepared for the SignPath Foundation open-source program. Existing public test releases are not signed by SignPath Foundation. They must not be represented as signed until an approved SignPath workflow has produced and verified the artifacts.

After acceptance, releases will carry the required attribution:

> Free code signing provided by SignPath.io, certificate by SignPath Foundation.

## Team roles

- Committer and reviewer: [Platinum-Radio](https://github.com/Platinum-Radio)
- Release and signing approver: [Platinum-Radio](https://github.com/Platinum-Radio)
- External contributors: contributors listed in the [repository history](https://github.com/Platinum-Radio/LocalDeck/graphs/contributors)

All maintainers with repository or SignPath access must use multi-factor authentication. Contributions from people without direct commit access require review before merge. A signing request requires a separate, explicit release approval; a successful build alone is not approval to sign or publish.

## Trusted build origin

Signed public artifacts must:

1. originate from the public LocalDeck repository;
2. be built from an immutable commit and version tag on a GitHub-hosted Windows runner;
3. use the committed `pnpm-lock.yaml` with frozen dependency resolution;
4. use runtime sources fixed by version, size, SHA-256, license, and upstream URL in `runtime-packages/runtime-sources.json`;
5. pass the source integrity workflow and all LocalDeck release checks;
6. be uploaded as a GitHub Actions artifact before submission to SignPath;
7. pass SignPath origin verification, malware scanning, configured artifact rules, and manual approval;
8. be Authenticode-verified after signing; and
9. receive release SHA-256 values only after signing is complete.

GitHub-hosted runners are mandatory for the SignPath open-source signing route. Fork pull requests never receive signing credentials and cannot submit release-signing requests.

## What may be signed

The LocalDeck project may sign only LocalDeck artifacts produced by its trusted workflow. It does not apply the LocalDeck or SignPath Foundation signature to unrelated upstream binaries. Upstream runtime files may be included under their original licenses and signatures when SignPath policy permits nested third-party components.

The intended signed outputs are:

- the LocalDeck application executables and project-owned helpers;
- the Windows NSIS installer; and
- the outer release package where the configured SignPath artifact format supports it.

Exact nested signing rules will be reviewed with SignPath during onboarding. A release fails closed if a required signature, timestamp, publisher, or verification result is missing.

## Privacy and user control

LocalDeck does not transfer project files, databases, captured messages, local certificates, private keys, or diagnostic contents to LocalDeck or SignPath. Network requests occur only for documented features that the user enables or invokes, apart from the configurable update check described in [PRIVACY.md](PRIVACY.md). Telemetry is not implemented.

The installer and application warn before privileged system changes. Windows-service registration, trusted local certificate installation, hosts or DNS changes, and complete project deletion require an explicit user action. LocalDeck includes an uninstall path and documents which project and runtime data may remain.

## Verification

Users can verify a signed release in PowerShell:

```powershell
Get-AuthenticodeSignature -LiteralPath '.\LocalDeck-Setup.exe' | Format-List Status, StatusMessage, SignerCertificate, TimeStamperCertificate
Get-FileHash -LiteralPath '.\LocalDeck-Setup.exe' -Algorithm SHA256
```

The Authenticode status must be `Valid`, the publisher must match the release metadata, and the SHA-256 value must match both GitHub Releases and LocalDeck.nl.

## Revocation and incidents

If a signing credential, workflow, release artifact, or maintainer account may be compromised, publishing stops immediately. Maintainers will preserve evidence, notify SignPath, revoke affected trust where required, remove affected downloads, publish a security notice, and issue a clean replacement from a reviewed commit.
