# GitHub Copilot Instructions for AB-Socks

- Follow the architectural guidelines and critical rules defined in `AGENTS.md`.
- When the user asks to commit, release, or save changes, strictly execute the **Mandatory Commit & Release Workflow** from `AGENTS.md`:
  - Run quality gate `php tools/verify.php`.
  - Bump `APP_VERSION` in `app/bootstrap.php`.
  - Prepend release notes to `docs/CHANGELOG.md` in English.
  - Update `docs/architecture/<domain>.md` if system architecture changed.
  - Commit using Conventional Commits.
  - Create Git tag `git tag -a vX.Y.Z -m "Release vX.Y.Z"`.
  - Push with tags `git push origin main --follow-tags`.
  - Create GitHub Release via `gh release create`.
- Strictly adhere to layer boundaries: Controllers < 80-120 lines, Views 0 SQL queries, Services handle all mutations and transactions.
