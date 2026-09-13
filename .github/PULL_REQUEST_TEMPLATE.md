## What changed and why

<!-- Not just what the diff shows — why this approach, and what it depends on. -->

## Version / migration

- [ ] `APP_VERSION` bumped in `app/bootstrap.php` (or N/A — no version-worthy change)
- [ ] New migration added under `database/migrations/`, mirrored into `schema.sql` (or N/A — no schema change)

## Documentation

- [ ] `docs/CHANGELOG.md` **and** `.en.md` updated
- [ ] `docs/ARCHITECTURE.md` **and** `.en.md` updated (or N/A — no documented behavior changed)

## Verification

- [ ] `php -l` passes on every file touched (see `AGENTS.md` for the command)
- [ ] Reviewed against the checklist in `SECURITY.md` (or N/A — no auth/money/upload/customer-data code touched)
