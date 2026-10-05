# Admin UI Baseline Audit Instructions

This directory holds the baseline measurements and comparison benchmarks for the AB-Socks Admin UI redesign.

## Establishing the Pre-Phase 1 Baseline

Before proceeding to **Phase 1** of `ADMIN-UI-PLAN.md`, the site owner should execute a full audit run against the live local development server to record the baseline state of all 32 admin pages:

```bash
cd tools/ui-test
npm install
npx playwright install chromium

# Run the complete audit against your local environment
AB_BASE_URL="http://localhost:8080" AB_ADMIN_USER="admin" AB_ADMIN_PASS="your_password" node audit.mjs
```

### Recording the Baseline Report

1. Locate the generated markdown report in:
   `tools/ui-test/output/<timestamp>/report.md`
2. Copy this file into this directory as `baseline-report.md`:
   ```bash
   cp output/<timestamp>/report.md baseline/baseline-report.md
   ```
3. Commit **only** `baseline-report.md` (do **not** commit the full-page `.png` screenshot files to preserve repository size).

This establishes a quantitative benchmark for overflow counts, console warnings, and tap-target violations to verify improvement across subsequent migration phases.
