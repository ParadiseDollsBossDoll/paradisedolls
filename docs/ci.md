# Automated Checks

`CI / Syntax, tests, and build` runs on pushes, pull requests, and manual CI runs.
It installs locked dependencies, checks PHP/JavaScript syntax, runs the full Laravel
test suite against in-memory SQLite, and builds frontend assets on Linux.
CI uses disposable test credentials, not production secrets. It cannot publish files
to the VPS. A green result covers those checks, not a live browser/chat test or a
security audit of the entire repository.

The first Linux run reproduced an existing course-access regression: enrolled
models bypassed verification. This branch also restores verification before the
enrollment allow rule. The existing unverified-member test is retained unchanged;
this application fix requires its own explicit deployment approval.

The Hostinger deployment workflow is manual-only and restricted to `main`.
Do not run that workflow to check a branch: it uploads files to production.
Deployment still requires explicit approval, release review, current site/database
backups, and a rollback command. CI passing does not grant deployment approval.

Before deployment, open GitHub Actions, select the CI run, and confirm its commit
matches `git rev-parse HEAD`. Require the `Syntax, tests, and build` check in the
repository's branch protection settings. Adding the workflow alone does not
automatically enable branch protection or enforce CI in the manual deploy workflow.

The current staged VPS frontend was built from `f68e6be` with new public Reverb
environment settings. CI on the CI-setup branch tests that code plus the workflow
changes; it does not retroactively certify the staged bundle. Rebuild/revalidate
the release after selecting the approved commit, before publishing the manifest.
