# LocalDeck updates

This page highlights the most important user-facing changes. For the complete technical history, see the [changelog](CHANGELOG.md).

## 1.1.0-test.1 — August 18, 2026

This prerelease is ready for early Windows testing. It keeps the fully offline runtime of LocalDeck 1.0 while making the application easier to use, more responsive, and substantially more capable.

### Easier every day

- A new simple interface keeps essential controls in view; advanced mode exposes the full developer toolbox.
- The dashboard, navigation, settings, modals, and dense data views now adapt cleanly from 1280 × 720 upward.
- Settings are reorganized into focused sections with search instead of one long page.
- LocalDeck stays available in the Windows notification area when the main window is closed.

### Complete project lifecycle

- The project wizard can prepare a site folder, local domain, PHP runtime, database, and HTTPS certificate as one guided flow.
- Every project receives isolated HTTP and HTTPS ports plus its own trusted local certificate.
- Project removal has an explicit typed warning and cleans associated files, databases, backups, mail addresses, tasks, workers, domains, certificates, and metadata.
- Project Capsules, start profiles, branch environments, snapshots, imports, and migration helpers make repeatable environments easier.

### More reliable control

- Port inspection shows conflicts and process ownership; Port Autopilot can safely move a service to a free port.
- LocalDeck Fix, diagnostics, preflight checks, release gates, and the Debug Inbox turn failures into actionable repairs.
- Apache, PHP, MySQL, phpMyAdmin, Mailpit, and Redis can still run separately, together, in app mode, or as Windows services.
- Localhost, `127.0.0.1`, phpMyAdmin automatic sign-in, Web Management, and Mail Management are verified in the packaged application.

### Developer tooling

- Side-by-side PHP 8.2, 8.3, 8.4, and 8.5 with per-project selection.
- Database Lab, API workspaces, webhook capture, task runner, queue workers, Xdebug support, schema comparison, and anonymized test data.
- Dutch and English interfaces throughout the desktop app and browser-based management pages.

### Smaller offline package

- Build-only tooling and unused Electron locales are excluded from the Windows package.
- Runtime archives are safely recompressed while official runtime contents and integrity hashes remain intact.
- The complete runtime stays embedded: no LocalDeck account, activation, or component download is required.

### Validation

- Full automated validation passes.
- All six real bundled services pass the start-ready smoke test.
- Both the installable EXE and extract-only ZIP are verified before publication.
- SHA-256 hashes are published with every release asset.

[Download the test release](https://localdeck.nl/downloads.php?lang=en) · [Read the complete changelog](CHANGELOG.md) · [Report feedback](https://localdeck.nl/community.php?lang=en)

## 1.0.0 — August 16, 2026

The first public LocalDeck release introduced the all-in-one Windows stack: Apache, PHP 8.2–8.5, MySQL, phpMyAdmin, Mailpit, Redis, Composer, automatic local HTTPS, Dutch and English interfaces, EXE and ZIP distribution, and a fully offline runtime.

[Read the LocalDeck 1.0.0 changelog](CHANGELOG.md#100--eerste-publieke-windows-release)
