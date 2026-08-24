# Third-party notices

LocalDeck source code is licensed under Apache-2.0. Official Windows distributions also aggregate unmodified or runtime-trimmed third-party components under their own licenses. Those components are not relicensed under the LocalDeck license.

The authoritative versions, upstream download URLs, source-code locations, byte lengths, SHA-256 values, and license links are recorded in [`runtime-packages/runtime-sources.json`](runtime-packages/runtime-sources.json). `offline-runtime.json` records the exact files included in a prepared distribution.

Full license texts for standalone binaries and bundled application libraries are retained in the [`licenses`](licenses) directory and included in LocalDeck distributions.

## Offline Windows runtime

| Component | Version | License | Upstream project/source | Packaging note |
|---|---:|---|---|---|
| Apache HTTP Server / Apache Lounge Windows build | 2.4.68 | Apache-2.0 and included notices | [Apache HTTP Server](https://httpd.apache.org/) | Development headers and link libraries are removed; `LICENSE.txt` and `NOTICE.txt` remain. |
| PHP | 8.2.33, 8.3.33, 8.4.24, 8.5.9 | PHP-3.01 and included notices | [PHP source](https://github.com/php/php-src) | Official Windows binary archives are included without source modification. |
| MySQL Community Server | 8.4.10 | GPL-2.0-only plus included MySQL notices | [MySQL Community](https://www.mysql.com/products/community/) | Development headers, link libraries, symbols, and debug plug-ins are removed; the license remains. |
| phpMyAdmin | 5.2.3 | GPL-2.0-only plus bundled dependency licenses | [phpMyAdmin source](https://github.com/phpmyadmin/phpmyadmin/tree/RELEASE_5_2_3) | The PHP source distribution and its vendor license files remain in the package. |
| Mailpit | 1.30.7 | MIT | [Mailpit source](https://github.com/axllent/mailpit/tree/v1.30.7) | Official Windows binary and license. |
| Redis for Windows | 5.0.14.1 | BSD-3-Clause | [Windows fork source](https://github.com/tporadowski/redis/tree/v5.0.14.1) | Debug symbols are removed; copyright and license attribution is reproduced here and in the source link. |
| WinSW | 2.12.0 | MIT | [WinSW source](https://github.com/winsw/winsw/tree/v2.12.0) | Official .NET 4 service-wrapper binary. |
| Composer | 2.10.2 | MIT | [Composer source](https://github.com/composer/composer/tree/2.10.2) | Official PHAR distribution. |
| mkcert | 1.4.4 | BSD-3-Clause | [mkcert source](https://github.com/FiloSottile/mkcert/tree/v1.4.4) | Official Windows binary. |
| Microsoft Visual C++ Redistributable | 14.51.36247.0 | Microsoft Visual C++ Runtime license | [Microsoft runtime documentation](https://learn.microsoft.com/cpp/windows/latest-supported-vc-redist) | Proprietary Windows System Library required by upstream Windows binaries; not part of the LocalDeck open-source work. |

Apache Lounge, MySQL, phpMyAdmin, PHP, Electron, and Chromium include additional third-party notices inside their upstream distributions. LocalDeck's packaging rules retain required license and notice files. The exact upstream licenses control if this summary differs from them.

## Desktop application dependencies

| Component | Version in lock | License | Project |
|---|---:|---|---|
| Electron | 33.4.11 | MIT; Chromium and bundled notices also apply | [electron/electron](https://github.com/electron/electron) |
| React / React DOM | 18.3.1 | MIT | [facebook/react](https://github.com/facebook/react) |
| Lucide React | 0.468.0 | ISC | [lucide-icons/lucide](https://github.com/lucide-icons/lucide) |
| node-qrcode | 1.5.4 | MIT | [soldair/node-qrcode](https://github.com/soldair/node-qrcode) |
| yaml | 2.9.0 | ISC | [eemeli/yaml](https://github.com/eemeli/yaml) |

Vite, TypeScript, Vitest, electron-builder, and the remaining packages in `pnpm-lock.yaml` are build or test dependencies and are not shipped as standalone LocalDeck runtime tools. Code bundled from a package retains that package's license. Electron distributions include `LICENSE` and `LICENSES.chromium.html` for Electron, Chromium, Node.js, and their incorporated libraries.

## Corresponding source

LocalDeck does not modify the source of the upstream runtime programs. Runtime-only optimization removes development files from some binary archives but does not alter executable code. Exact source tags or official source archive locations are recorded in `runtime-sources.json`.

For GPL-covered binary components distributed by LocalDeck, corresponding source is available through the exact upstream source locations listed above. If a referenced source becomes unavailable, request the corresponding source through <https://localdeck.nl/community.php?lang=en>. For at least three years after the related LocalDeck binary distribution, the project will provide a copy for no more than the reasonable physical cost of transferring it.

## Trademarks and affiliation

Product and project names belong to their respective owners. Inclusion is not an endorsement. LocalDeck is not affiliated with the upstream projects or their maintainers.

## Nederlands

LocalDeck bundelt deze onderdelen uitsluitend om de Windows-ontwikkelomgeving offline bruikbaar te maken. Alle onderdelen behouden hun eigen licentie. De exacte versies en controlesommen staan in de twee runtime-manifesten; de originele licentie- en noticebestanden blijven waar vereist in de distributie aanwezig.
