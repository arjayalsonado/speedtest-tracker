# Changelog

This file tracks consolidated repository changes for the MikroTik Lite experimental multi-ISP build and related repo patches.

## 2026-05-14 — `mikrotik-lite-multi-isp-exp`

### Summary

- Added support for multi-ISP profile execution in Speedtest Tracker Lite.
- Added experimental branch-specific Docker build files under `docker/mikrotik-lite/`.
- Added repo-level configuration and support classes for profile-driven source IP binding.
- Added schedule registration, validation, and result metadata tagging for ISP profiles.
- Added a root-level changelog for consolidated patch tracking, with a supplemental build-specific changelog in `docker/mikrotik-lite/CHANGELOG.md`.

### Added

- `docker/mikrotik-lite/Dockerfile.mikrotik-lite`
- `docker/mikrotik-lite/entrypoint.sh`
- `docker/mikrotik-lite/.env.mikrotik-lite`
- `docker/mikrotik-lite/nginx.conf`
- `docker/mikrotik-lite/php-fpm.conf`
- `config/speedtest-lite.php`
- `app/Support/SpeedtestLite/IspProfile.php`
- `app/Support/SpeedtestLite/IspProfiles.php`
- `app/Console/Commands/ValidateIspProfiles.php`
- `app/Console/Commands/RunIspProfileSpeedtest.php`
- `database/migrations/2026_05_14_000001_add_isp_profile_columns_to_results_table.php`
- `CHANGELOG.md`

### Changed

- `routes/console.php`
  - Added multi-ISP schedule registration for enabled ISP profiles.
  - Preserved existing upstream maintenance and pruning schedules.

- `app/Jobs/Ookla/RunSpeedtestJob.php`
  - Replaced hardcoded `--interface` binding with configurable bind option.
  - Added fallback validation for `--interface` / `--ip`.

### Notes

- `docker/8.4/` remains the repo's existing main Docker configuration.
- The experimental image is intentionally built from `docker/mikrotik-lite/Dockerfile.mikrotik-lite`.
- The branch-specific changelog in `docker/mikrotik-lite/CHANGELOG.md` remains useful for Docker/build-specific detail.
- The archive documentation file is now `speedtest-tracker-mikrotik-lite-change-archive-v1.2.1-multi-isp-experimental.md`.

## 2026-05-14T17:30Z — `REF-20260514-01`

### Summary

- Appended changelog tracking for new Docker config files without overwriting the existing entry above.
- Added a timestamped reference ID for this update.

### Added

- Changelog append-only update marker: `REF-20260514-01`

### Notes

- This entry preserves all prior content in the changelog.
- Future updates should follow this append-only pattern with a new timestamped reference ID.

## 2026-05-15 - RouterOS Runtime Validation and Image Versioning

Reference: `REF-CODELOAD-20260515-MIKROTIK-LITE-ROUTEROS-RUNTIME-VALIDATION`

### Summary

- Validated the MikroTik Lite multi-ISP experimental image on hAP ax3 RouterOS.
- Confirmed VETH multi-IP visibility, manual profile runs, and automatic scheduled profile runs.
- Added timeout hardening for low-resource speedtest execution.
- Defined Docker image version/build tagging convention.

### Validation

- Deployment image namespace: `rvncore/speedtest-tracker`.
- RouterOS-pulled tag tested: `rvncore/speedtest-tracker:multi-isp-exp-arm64`.
- In-container VETH interface name was `veth-app-speedt`; `SPEEDTEST_LITE_BIND_INTERFACE` was updated accordingly.
- CNVG `192.168.99.250` and PLDT `192.168.99.251` validated as visible.
- Manual `isp1` and `isp2` runs completed and tagged results.
- Automatic scheduler completed:
  - CNVG result `#7` at `2026-05-14 19:00:02`.
  - PLDT result `#8` at `2026-05-14 19:07:02`.
- Increasing RouterOS `memory-high` from `128M` to `160M` may have resolved early automatic scheduled runs that left `waiting` rows.

### Changed

- `routes/console.php`
  - Removed unsupported scheduler-level `->timeout(300)` after build `b1.0` failed on RouterOS with `Method Illuminate\Console\Scheduling\Event::timeout does not exist`.
- `app/Jobs/Ookla/RunSpeedtestJob.php`
  - Raised job timeout to `300` seconds.
  - Added Symfony process timeout of `300` seconds.
  - Added bind option/value logging.

### Image Tags

- Preferred immutable patch-build tag:
  - `rvncore/speedtest-tracker:0.1.1-b1.1-mikrotik-lite-multi-isp-arm64`
- Version convenience tag:
  - `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
- Moving experimental tag:
  - `rvncore/speedtest-tracker:multi-isp-exp-arm64`

### Remaining

- Confirm external/public IP or ISP differs per profile.
- Decide when to deploy the timeout-hardened `0.1.1` image.
- Clean or mark abandoned early `waiting` rows after validation is complete.

## 2026-05-19 - MikroTik Lite Web UI Asset Fix

Reference: `REF-CODELOAD-20260519-MIKROTIK-LITE-B1-2-VITE-MANIFEST-FIX`

### Summary

- Investigated RouterOS web UI `500 Internal Server Error` on `app-speedtest-v011`.
- Confirmed Laravel was throwing `Illuminate\Foundation\ViteManifestNotFoundException` because `/var/www/html/public/build/manifest.json` was missing.
- Updated the MikroTik Lite Docker builder stage to install Node/npm, run `npm ci`, run `npm run build`, and remove `node_modules` before the runtime image copy.
- Built corrected local image `0.1.1-b1.2-mikrotik-lite-multi-isp-arm64`.

### Image Status

- `0.1.1-b1.0`: broken, unsupported scheduler `Event::timeout` call.
- `0.1.1-b1.1`: boots and includes scheduler fix, but web UI is broken because the Vite manifest is missing.
- `0.1.1-b1.2`: then-current corrected local build with Vite manifest included.

### Validation

- Docker build completed for ARM64.
- Build log confirmed Vite generated:
  - `public/build/manifest.json`
  - production CSS assets under `public/build/assets`
- Local container smoke test confirmed `/var/www/html/public/build/manifest.json` exists.
- Local container smoke test confirmed `speedtest-lite` Artisan commands are still present.

## 2026-05-19 - MikroTik Lite Scheduler Lock

Reference: `REF-CODELOAD-20260519-MIKROTIK-LITE-B1-3-SCHEDULER-LOCK`

### Summary

- Added a lock wrapper around the internal cron-triggered `php artisan schedule:run --no-interaction`.
- Prevents a new scheduler tick from starting if the previous scheduler run is still active.
- Built local image `0.1.1-b1.3-mikrotik-lite-multi-isp-arm64`.

### Validation

- ARM64 Docker build completed.
- Local smoke test confirmed the scheduler wrapper exists in `entrypoint.sh`.
- Local smoke test confirmed the Vite manifest remains present.
- Local smoke test confirmed `speedtest-lite` Artisan commands remain present.

## 2026-05-19 - MikroTik Lite b1.4 Runtime Stabilization

Reference: `REF-CODELOAD-20260519-MIKROTIK-LITE-B1-4-RUNTIME-STABILITY`

### Summary

- Added stale scheduler lock recovery to the MikroTik Lite entrypoint wrapper.
- Increased PHP-FPM worker capacity from one to two workers for better admin responsiveness.
- Rebuilt local ARM64 image `0.1.1-b1.4-mikrotik-lite-multi-isp-arm64`.

### Image Status

- `0.1.1-b1.3`: scheduler overlap guard works, but an abnormal interruption can leave a stale lock.
- `0.1.1-b1.4`: current corrected local build with stale-lock recovery, Vite assets, scheduler overlap protection, and two PHP-FPM workers.

### Validation

- ARM64 Docker build completed.
- Current local image ID: `sha256:dd881fa2ce28e3f48c1426c2a055009fa0d97dc98360a7267e0a661003805d80`.
- Local smoke test confirmed:
  - `pm.max_children = 2`
  - stale-lock recovery strings exist in `/usr/local/bin/entrypoint.sh`
  - `/var/www/html/public/build/manifest.json` exists
  - `speedtest-lite` Artisan commands remain available

## 2026-05-24 - MikroTik Lite RouterOS Validation and Cleanup

Reference: `REF-CODELOAD-20260524-MIKROTIK-LITE-B1-4-VALIDATION-CLEANUP`

### Summary

- Confirmed b1.4 RouterOS runtime after naming cleanup.
- Updated the active RouterOS cadence to 20-minute profile spacing.
- Confirmed timezone display behavior.
- Cleaned abandoned untagged `waiting` rows from the live SQLite database.
- Identified b1.5 patch items for OPcache warning cleanup and GitHub latest-version timeout avoidance.

### Validation

- In-container interface is now `veth-app-speed`.
- Source IP validation passed:
  - `isp1` CNVG `192.168.99.250`
  - `isp2` PLDT `192.168.99.251`
- Schedule validation passed:
  - `isp1`: `0,20,40 * * * *`
  - `isp2`: `10,30,50 * * * *`
- Timezone validation passed:
  - `APP_TIMEZONE=UTC`
  - `DISPLAY_TIMEZONE=Asia/Manila`

### Data Cleanup

- Removed 136 abandoned untagged `waiting` rows from the RouterOS SQLite database.
- Tagged completed and failed profile rows were preserved.

### Planned b1.5

- Remove `php_admin_value[opcache.enable_cli] = 1`.
- Keep `php_admin_value[opcache.enable] = 1`.
- Add a MikroTik Lite GitHub version-check guard:
  - `SPEEDTEST_LITE_DISABLE_GITHUB_VERSION_CHECK=true`
  - `SPEEDTEST_LITE_GITHUB_REPOSITORY=rvncore/speedtest-tracker`
