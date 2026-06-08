# Changelog

This file tracks consolidated repository changes for the MikroTik Lite experimental multi-ISP build and related repo patches.

## 2026-06-08 - MikroTik Lite 0.2.0-b1.2 Unique Egress Validation

Reference: `REF-CODELOAD-20260608-MIKROTIK-LITE-0-2-0-B1-2-UNIQUE-EGRESS`

### Summary

- Added dynamic unique-egress validation for MikroTik Lite multi-ISP profiles.
- Marks a profile run failed when its external IP matches another enabled profile within the configured recent window.
- Added external IP visibility to the guest Last Results profile card.
- Shows unique-egress failures as downtime/failover indicators instead of silently displaying stale successful data.

### Planned Image Tag

- `rvncore/speedtest-tracker:0.2.0-b1.2-<source-sha>-mikrotik-lite-multi-isp-arm64`

## 2026-06-08 - MikroTik Lite 0.2.0-b1.1 Guest UI Feedback Patch

Reference: `REF-CODELOAD-20260608-MIKROTIK-LITE-0-2-0-B1-1-UI-FEEDBACK`

### Summary

- Incorporated first RouterOS guest-dashboard feedback from the `0.2.0-b1.0-b38ea06` test image.
- Restored a single `Last results` section heading and changed each profile result row to include an ISP Profile card.
- Replaced per-chart range dropdowns on the guest dashboard with one shared chart range selector.
- Preserved the selected chart range when changing the ISP/profile selector.

### Planned Image Tag

- `rvncore/speedtest-tracker:0.2.0-b1.1-<source-sha>-mikrotik-lite-multi-isp-arm64`

## 2026-06-08 - MikroTik Lite 0.2.0-b1.0 Guest Multi-ISP UI

Reference: `REF-CODELOAD-20260608-MIKROTIK-LITE-0-2-0-B1-0-UI`

### Summary

- Started the `0.2.0` feature line for profile-aware MikroTik Lite dashboard UI work.
- Added dynamic guest-dashboard latest results per ISP profile.
- Added a guest metrics ISP/profile selector that supports more than two profiles.
- Extended chart ranges to include 1 hour, 6 hours, 12 hours, 24 hours, week, month, and year.

### Planned Image Tag

- `rvncore/speedtest-tracker:0.2.0-b1.0-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.2.0-b1.0-<source-sha>-mikrotik-lite-multi-isp-arm64`

### Deferred

- Custom date range filtering.
- Logged-in dashboard parity where useful.
- Results table profile columns and filters.
- Lite-build-aware version indicator.

## 2026-05-29 - MikroTik Lite b1.9 Patch Candidate

Reference: `REF-CODELOAD-20260529-MIKROTIK-LITE-B1-9-PATCH`

### Summary

- Started the b1.9 patch line after accepting b1.8 as the upstream v1.14.3 baseline observation build.
- Changed the default MikroTik Lite example profile schedules to the calmer 30-minute-per-profile stagger already validated on RouterOS.
- Kept Docker Scout/APK remediation scoped to b1.9 analysis and testing, separate from b1.8.

### Changed

- Default ISP profile schedule examples:
  - `isp1`: `0,30 * * * *`
  - `isp2`: `15,45 * * * *`

### Notes

- Existing RouterOS envlist overrides can still set any valid per-profile cron values.
- Docker Scout remediation should be tested as a separate b1.9 candidate image using a SHA-specific tag before any moving tag promotion.

## 2026-06-08 - MikroTik Lite b1.9 Promotion Closeout

Reference: `REF-CODELOAD-20260608-MIKROTIK-LITE-B1-9-CLOSEOUT`

### Summary

- Accepted b1.9 as the current promoted MikroTik Lite build after extended RouterOS runtime validation.
- Confirmed the official RouterOS container is `app-speed` using the stable root directory.
- Confirmed Docker Hub convenience tags point to the validated b1.9 image.
- Removed public references to local `.ai` worklogs from operator documentation.
- Added a standalone repository publishing checklist.

### Current Tags

- `rvncore/speedtest-tracker:0.1.1-b1.9-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.1.1-b1.9-28d2629-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:multi-isp-exp-arm64`
- `rvncore/speedtest-tracker:latest`

### Notes

- Docker Hub immutable-tag protection uses:
  - `.*-b.*-mikrotik-lite-multi-isp-arm64`
- The retained b1.8 RouterOS rollback can be removed when local rollback is no longer desired.

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

## 2026-05-27 - MikroTik Lite b1.5/b1.6 Baseline Rebuild

Reference: `REF-CODELOAD-20260527-MIKROTIK-LITE-B1-6-BASELINE`

### Summary

- Merged upstream Speedtest Tracker v1.14.2 CVE release into the experimental MikroTik Lite branch.
- Built and pushed immutable test image `0.1.1-b1.5-mikrotik-lite-multi-isp-arm64`.
- Marked `b1.5` as failed after RouterOS startup reported `/usr/local/bin/entrypoint.sh` could not execute.
- Root cause was not an upstream application change. The upstream merge/rebuild workflow on Windows exposed missing LF enforcement for Linux runtime files, allowing CRLF to be copied into the image.
- Built and pushed corrected immutable test image `0.1.1-b1.6-mikrotik-lite-multi-isp-arm64`.
- RouterOS overnight validation reported clean b1.6 runtime behavior.

### b1.5 Failure

- Source baseline: upstream v1.14.2 merge commit `cd4d5db`.
- Local image ID observed during testing: `sha256:09e0e5e195d02075dbe23fcf8803ae05dc3c1b18b353997a29bcb33d9880cf4a`.
- Failure mode:
  - `execvpe /usr/local/bin/entrypoint.sh: No such file or directory`
- Technical cause:
  - `entrypoint.sh` had CRLF line endings, so Linux attempted to execute `/bin/bash\r`.
  - `.env.mikrotik-lite` also needed LF enforcement to avoid values such as `UTC\r`.
- Status:
  - Do not deploy.
  - Do not promote to moving tags.

### b1.6 Correction

- Source commit: `6bedd64`.
- Local image ID observed during validation: `sha256:8f7983d6c3da29e9a5cd3c3c50c3c2dd65b962e0b6a4526e28eb3adac3e8c3d6`.
- Corrections:
  - Enforced LF for `.gitattributes`.
  - Enforced LF for MikroTik Lite shell/env/config runtime files.
  - Reloaded the generated Laravel `APP_KEY` before config caching so a blank exported template value cannot poison `bootstrap/cache/config.php`.
- Local validation:
  - default entrypoint executed successfully
  - migrations completed
  - Vite manifest present
  - schedule list valid
  - HTTP returned `302` to setup route and then `200 OK`
- RouterOS validation:
  - b1.6 ran cleanly overnight
  - no stale scheduled processes observed after runs completed
  - web UI returned `200 OK`
  - profile result rows continued completing

### Planned b1.7

- Add a 5-minute scheduler startup grace window to avoid first-boot speedtest contention.
- Remove or relocate the OPcache CLI setting that causes the PHP startup warning.
- Keep upstream Speedtest Tracker version checks unchanged.
- Track Lite-build-aware version checking as future feature work.

## 2026-05-27 - MikroTik Lite b1.7 Runtime Stabilization

Reference: `REF-CODELOAD-20260527-MIKROTIK-LITE-B1-7-RUNTIME-STABILIZATION`

### Summary

- Added a scheduler startup grace window for MikroTik Lite containers.
- Removed the PHP-FPM pool-level `opcache.enable_cli` setting that caused startup warnings.
- Left upstream Speedtest Tracker version checks unchanged.

### Changes

- Added `MIKROTIK_SCHEDULER_STARTUP_GRACE_SECONDS`, defaulting to `300`.
- The generated scheduler wrapper now skips `schedule:run` while the startup grace window is active.
- Startup skip log example:
  - `NOTICE: scheduler startup grace window active; skipping this tick (...s remaining).`
- Removed PHP-FPM pool-level OPcache enablement settings that caused startup warnings.

### Deferred

- Lite-build-aware version checking remains future feature work.
