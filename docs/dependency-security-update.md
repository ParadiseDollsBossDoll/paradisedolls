# Frontend Dependency Security Update

Prepared 2026-10-20 on `codex/frontend-dependency-security`; not deployed.

## Changes

- PDF.js 5.7.284 -> 6.4.299; patched range starts at 6.2.108 according to
  https://github.com/advisories/GHSA-hq66-cqwq-w95j.
- Compatible locked updates include Axios 1.20.0, concurrently 9.2.5,
  shell-quote 1.12.0, PostCSS 8.5.31, Browserslist 4.29.4, and socket.io-parser 4.2.7.
- PDF rendering explicitly passes `canvas: null` so PDF.js 6 uses the supplied
  canvas context. Source checked: Mozilla PDF.js 6.4.299 `src/display/api.js`,
  `PDFPageProxy.render` and `RenderParameters`.
- CI checks the production dependency graph and loads/renders a generated PDF
  with the installed package. The native canvas is PDF.js's existing optional dependency.
- PDF.js 6 requires Node >=22.13.0; the pinned CI/VPS Node 22.22.2 satisfies it.

## Remaining Findings

At preparation time, full `npm audit` decreased from 16 findings (2 critical,
12 high, 2 moderate) to 7 (0 critical, 5 high, 2 moderate).
`npm audit --omit=dev` reports zero findings. This describes npm's dependency
classification, not proof that every browser-shipped module is a production dependency;
Axios is bundled despite being declared as a development dependency and was updated too.

The seven remaining findings are braces and postcss-selector-parser advisories,
plus affected parent chains through chokidar, micromatch, fast-glob,
postcss-nested, and Tailwind 3. No reachable production exploitation is established
by this count. They remain real, unremediated build-tool findings, not false positives.
The user explicitly chose to defer the Tailwind 4 migration and report them.
No blanket suppressions or `npm audit fix --force` were used.

## Release Boundary

This branch includes all earlier admin/notification candidate commits. The old VPS
staged build at commit 8421a63 does not contain these fixes and must not be reused
for this updated candidate. Rebuild from the reviewed commit with the production
public Reverb settings. Do not copy local build artifacts or local credentials to the VPS.
The older deployment approval is not approval to publish the new dependency scope.

Historical credential rotation is user-reported, not independently verified.
Current backups, live baseline/version marker, post-rotation video/chat tests,
and post-deploy browser checks are still release gates. This update changes no
database schema or stored content.
