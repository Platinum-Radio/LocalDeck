# Contributing to LocalDeck

Thank you for helping improve LocalDeck. The project is Windows-first and accepts bug fixes, tests, documentation, translations, security improvements, and focused feature proposals.

## Before contributing

- Use an account protected by multi-factor authentication.
- Never commit passwords, API tokens, certificates, private keys, databases, captured email, user projects, or generated release files.
- Open an issue before a large architectural change so the scope and migration impact can be discussed.
- Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md).

## Development workflow

1. Fork the repository and create a focused branch.
2. Install the locked dependencies with `pnpm install --frozen-lockfile`.
3. Make the smallest cohesive change and add or update tests.
4. Run `pnpm check` and `pnpm build`.
5. Maintainers with the offline runtime workspace must also run `Validate-LocalDeck.ps1`.
6. Submit a pull request using the repository template.

Changes from non-committers require review before merge. Changes to build scripts, dependency locks, security controls, update handling, packaging, and signing policy require explicit maintainer review.

## Licensing contributions

By intentionally submitting a contribution for inclusion in LocalDeck, you agree that it is provided under the [Apache License 2.0](LICENSE), as described in section 5 of that license. Only submit work that you have the right to contribute.

Third-party code may not be copied into the repository unless its license is OSI-approved, compatible with distribution, documented in `THIRD-PARTY-NOTICES.md`, and accepted during review.

## Release artifacts

Pull requests and ordinary development builds must not publish installers or ZIP distributions. Official artifacts are produced only from an approved release commit through the documented release and code-signing process.
