# SignPath Foundation onboarding dossier

This document contains the information and remaining external steps for the LocalDeck SignPath Foundation application. It is not a claim of approval.

## Application details

| Field | Value |
|---|---|
| Project | LocalDeck |
| Repository | <https://github.com/Platinum-Radio/LocalDeck> |
| Website | <https://localdeck.nl/> |
| Downloads | <https://localdeck.nl/downloads.php?lang=en> |
| Documentation | <https://localdeck.nl/wiki.php?lang=en> |
| License | Apache-2.0 |
| Platform | Windows 10/11 x64 |
| Artifact types | NSIS installer EXE and extract-only ZIP |
| Build system | GitHub Actions, GitHub-hosted Windows runner |
| Maintainer | <https://github.com/Platinum-Radio> |
| Security contact | GitHub private vulnerability reporting |

## Project description

LocalDeck is an offline-first, open-source local PHP development environment for Windows. It provides one desktop control center for Apache, multiple PHP versions, MySQL, phpMyAdmin, Mailpit, Redis, local domains, automatic development HTTPS, project management, backups, diagnostics, and optional Windows service mode. It requires no LocalDeck account, license key, online activation, or component download during standard installation.

## Eligibility evidence

- The complete LocalDeck source and build scripts are public.
- LocalDeck is licensed under an OSI-approved license.
- Third-party runtime components, versions, sources, hashes, and licenses are documented.
- The Microsoft Visual C++ Redistributable is treated as a required Windows System Library and remains under Microsoft's license.
- The project has privacy, security, contribution, support, release, and signing policies.
- Normal builds and pull requests cannot publish releases.
- Release signing is designed to fail closed and requires manual approval.
- The application provides warnings for privileged changes and a supported uninstall path.
- LocalDeck is not a penetration-testing or hacking tool. Its port checks only inspect local bind availability and local process ownership, and its security checks are limited to LocalDeck's own configuration and files.

## External steps after this repository state is published

1. Enable multi-factor authentication for every repository and signing maintainer.
2. Protect `main`: require pull requests, the source integrity check, review of stale approvals after new commits, and conversation resolution.
3. Apply at <https://signpath.org/apply> with this dossier.
4. Install the official SignPath GitHub App only after SignPath instructs the project to do so.
5. Configure the SignPath project, trusted GitHub build system, artifact configurations, `release-signing` policy, and manual approver.
6. Add the SignPath API token only as an Actions secret. Store organization, project, policy, and artifact-configuration identifiers as repository variables unless SignPath classifies them as secrets.
7. Add the approved `signpath/github-action-submit-signing-request@v2` step to the tag-only Windows release workflow, pinned to a reviewed commit SHA.
8. Perform a signed release-candidate run, verify nested and outer Authenticode signatures, and repeat the Smart App Control test on a clean Windows 11 machine.
9. Only then change the signing status on the website from pending to active.

## Proposed SignPath configuration

- Project slug: `localdeck`
- Signing policy: `release-signing`
- Trusted repository: `https://github.com/Platinum-Radio/LocalDeck`
- Allowed source: immutable version tags matching `v*`
- Origin verification: required
- Trusted build system: GitHub.com, GitHub-hosted runner only
- Repository policy: `.signpath/policies/localdeck/release-signing.yml` requires GitHub-hosted runners and rejects reruns of signing builds
- Approval: manual, one project approver
- Candidate artifact configuration: full LocalDeck Windows release package with nested PE inspection

Artifact configuration names and nesting rules must be confirmed by SignPath. They are deliberately not hard-coded into a workflow before approval.
