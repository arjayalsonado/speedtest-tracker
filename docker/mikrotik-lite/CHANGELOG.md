# MikroTik Lite Experimental Build Changelog

Date: 2026-05-14
Branch: `mikrotik-lite-multi-isp-exp`

## Overview

This changelog tracks the v1.2.1 experimental MikroTik Lite multi-ISP build changes and patches that were implemented from the archive documentation.

## Summary

- Added multi-ISP profile support for Speedtest Tracker Lite.
- Added branch-specific Docker build files under `docker/mikrotik-lite/`.
- Added source-IP binding validation and profile-aware scheduling.
- Patched the Ookla speedtest job to support configurable bind option (`--ip` / `--interface`).
- Added result metadata columns for ISP profile tagging.

## Added

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

## Changed

- `routes/console.php`
  - Added multi-ISP schedule registration for enabled ISP profiles.
  - Preserved existing upstream maintenance and pruning schedules.

- `app/Jobs/Ookla/RunSpeedtestJob.php`
  - Replaced hardcoded `--interface` binding with configurable bind option.
  - Added fallback validation for `--interface` / `--ip`.

## Notes

- The experimental build is intentionally isolated from the existing `docker/8.4/` build configuration.
- Build the experimental image explicitly from `docker/mikrotik-lite/Dockerfile.mikrotik-lite` to avoid conflicts.
- This changelog is based on `speedtest-tracker-mikrotik-lite-change-archive-v1.2-multi-isp-experimental.md`.

## 2026-05-14T17:30Z — `REF-20260514-01`

### Summary

- Appended an update marker for added Docker config files, preserving the original changelog contents.
- Added a reference ID for tracking update history.

### Notes

- The archive documentation file was renamed after the original entry and is now `speedtest-tracker-mikrotik-lite-change-archive-v1.2.1-multi-isp-experimental.md`.
- Future updates should append new sections with unique timestamped reference IDs.

## 2026-05-15 - RouterOS Runtime Validation and Timeout Hardening

Reference: `REF-CODELOAD-20260515-MIKROTIK-LITE-ROUTEROS-RUNTIME-VALIDATION`

### Summary

- Validated the experimental image on MikroTik hAP ax3 RouterOS containers.
- Confirmed one container can see multiple VETH source IPs and run profile-specific speedtests.
- Automatic scheduled CNVG and PLDT profile runs completed after runtime stabilization and memory adjustment.
- Added timeout hardening for slower RouterOS speedtest execution.
- Established image version/build tagging convention.

### Runtime Findings

- Deployment namespace: `rvncore/speedtest-tracker`.
- Current RouterOS-pulled tag: `rvncore/speedtest-tracker:multi-isp-exp-arm64`.
- RouterOS VETH interface appeared inside the container as `veth-app-speedt`, not `eth0`.
- `SPEEDTEST_LITE_BIND_INTERFACE` must match the in-container interface name.
- CNVG profile `192.168.99.250` validated as visible.
- PLDT profile `192.168.99.251` validated as visible.
- Manual `isp1` and `isp2` profile runs completed and tagged results.
- Automatic scheduler completed:
  - CNVG result `#7` at `2026-05-14 19:00:02`.
  - PLDT result `#8` at `2026-05-14 19:07:02`.
- Increasing `memory-high` from `128M` to `160M` may have resolved early automatic-run failures that left `waiting` rows.

### Changed

- `routes/console.php`
  - Removed unsupported scheduler-level `->timeout(300)` after build `b1.0` failed on RouterOS with `Method Illuminate\Console\Scheduling\Event::timeout does not exist`.

- `app/Jobs/Ookla/RunSpeedtestJob.php`
  - Raised job timeout from `120` to `300` seconds.
  - Added Symfony process timeout of `300` seconds.
  - Added bind option/value logging for validation troubleshooting.

### Image Versioning

- Use SemVer-style image versions plus `bX.Y` build suffixes.
- Increment build major `X` for significant build/release-line changes; increment build minor `Y` for smaller rebuilds of the same line.
- Preferred immutable tag format:
  - `rvncore/speedtest-tracker:<version>-b<X.Y>-mikrotik-lite-multi-isp-arm64`
- Convenience tags:
  - `rvncore/speedtest-tracker:<version>-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:multi-isp-exp-arm64`
- Corrected timeout-hardened patch build should be tagged as:
  - `rvncore/speedtest-tracker:0.1.1-b1.1-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:multi-isp-exp-arm64`
  - `rvncore/speedtest-tracker:latest`

### Remaining

- Confirm external/public IP or ISP differs per profile.
- Decide when to deploy the timeout-hardened `0.1.1` image to RouterOS.
- Clean or mark abandoned early `waiting` rows after validation is complete.

## 2026-05-19 - Build b1.2 Vite Manifest Fix

Reference: `REF-CODELOAD-20260519-MIKROTIK-LITE-B1-2-VITE-MANIFEST-FIX`

### Summary

- Investigated web UI `500 Internal Server Error` after corrected scheduler build booted on RouterOS.
- RouterOS container logs showed `Illuminate\Foundation\ViteManifestNotFoundException`.
- Root cause was missing `/var/www/html/public/build/manifest.json` in the MikroTik Lite image.
- Added frontend asset compilation to the builder stage:
  - install `nodejs` and `npm`
  - run `npm ci --no-audit --no-fund`
  - run `npm run build`
  - remove `node_modules` before runtime copy

### Image Status

- `0.1.1-b1.0`: broken by unsupported scheduler `Event::timeout`.
- `0.1.1-b1.1`: scheduler boot fixed, but web UI broken because Vite production assets are missing.
- `0.1.1-b1.2`: then-current corrected local build with Vite production assets included.

### Validation

- ARM64 Docker build completed.
- Vite build output includes `public/build/manifest.json`.
- Local smoke test confirmed the manifest exists in the image.
- Local smoke test confirmed `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles` remain available.

## 2026-05-19 - Build b1.3 Scheduler Lock

Reference: `REF-CODELOAD-20260519-MIKROTIK-LITE-B1-3-SCHEDULER-LOCK`

### Summary

- Added `/tmp/run-scheduler-once.sh` generation inside the entrypoint.
- The wrapper uses a lock directory at `/tmp/speedtest-lite-schedule-run.lock`.
- If a previous scheduler pass is active, the next cron tick logs:
  - `NOTICE: previous schedule:run is still active; skipping this tick.`
- This is intended to reduce memory/process contention when Ookla runs take longer than one minute.

### Image Status

- `0.1.1-b1.3`: then-current corrected local build with scheduler overlap protection and Vite production assets.

### Validation

- ARM64 Docker build completed.
- Local smoke test confirmed the scheduler wrapper exists in `/usr/local/bin/entrypoint.sh`.
- Local smoke test confirmed `/var/www/html/public/build/manifest.json` exists.
- Local smoke test confirmed `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles` remain available.

## 2026-05-19 - Build b1.4 Runtime Stabilization

Reference: `REF-CODELOAD-20260519-MIKROTIK-LITE-B1-4-RUNTIME-STABILITY`

### Summary

- Added stale-lock recovery to `/tmp/run-scheduler-once.sh`.
- The wrapper now clears dead or zombie scheduler lock holders before starting a new scheduler pass.
- Increased PHP-FPM `pm.max_children` from `1` to `2` to reduce admin/dashboard stalls caused by a single blocked worker.

### Image Status

- `0.1.1-b1.4`: current corrected local build.

### Validation

- ARM64 Docker build completed.
- Current local image ID: `sha256:dd881fa2ce28e3f48c1426c2a055009fa0d97dc98360a7267e0a661003805d80`.
- Local smoke test confirmed:
  - `pm.max_children = 2`
  - stale-lock recovery is present in `/usr/local/bin/entrypoint.sh`
  - `/var/www/html/public/build/manifest.json` exists
  - `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles` remain available

## 2026-05-24 - b1.4 RouterOS Validation and Cleanup

Reference: `REF-CODELOAD-20260524-MIKROTIK-LITE-B1-4-VALIDATION-CLEANUP`

### Summary

- Confirmed b1.4 running on RouterOS after operational naming cleanup.
- Active RouterOS object names:
  - container: `app-speed`
  - interface: `veth-app-speed`
  - envlist: `env-app-speed`
  - mountlist: `mount-app-speed`
- Kept existing persistent `/config` mount to preserve SQLite history.

### Validation

- `ip a` showed `veth-app-speed` with:
  - `192.168.99.249`
  - `192.168.99.250`
  - `192.168.99.251`
- `speedtest-lite:validate-isp-profiles` confirmed both profile source IPs visible.
- `schedule:list` confirmed:
  - `isp1`: `0,20,40 * * * *`
  - `isp2`: `10,30,50 * * * *`
- Timezone config confirmed:
  - `APP_TIMEZONE=UTC`
  - `DISPLAY_TIMEZONE=Asia/Manila`

### SQLite Cleanup

- Removed 136 old untagged abandoned `waiting` rows.
- Preserved tagged profile rows:
  - `isp1` completed and failed rows
  - `isp2` completed rows

### Planned b1.5 Patch Items

- Remove PHP-FPM pool setting `php_admin_value[opcache.enable_cli] = 1`.
- Keep `php_admin_value[opcache.enable] = 1`.
- Add configurable GitHub latest-version behavior for MikroTik Lite:
  - disable by default
  - target `rvncore/speedtest-tracker` if enabled

## 2026-05-27 - b1.5 Failed Baseline and b1.6 Corrected Baseline

Reference: `REF-CODELOAD-20260527-MIKROTIK-LITE-B1-6-BASELINE`

### b1.5 Status

- Image tag: `rvncore/speedtest-tracker:0.1.1-b1.5-mikrotik-lite-multi-isp-arm64`
- Source baseline: upstream v1.14.2 merge commit `cd4d5db`
- Status: failed immutable test image; do not deploy or promote.
- Failure observed on RouterOS:
  - `execvpe /usr/local/bin/entrypoint.sh: No such file or directory`
- Cause:
  - Linux runtime files were copied into the image with CRLF line endings after a Windows checkout/merge workflow.
  - The entrypoint shebang effectively referenced `/bin/bash\r`.
  - `.env.mikrotik-lite` also required LF enforcement to avoid CRLF in env values.

### b1.6 Status

- Image tag: `rvncore/speedtest-tracker:0.1.1-b1.6-mikrotik-lite-multi-isp-arm64`
- Source commit: `6bedd64`
- Local image ID observed during validation:
  - `sha256:8f7983d6c3da29e9a5cd3c3c50c3c2dd65b962e0b6a4526e28eb3adac3e8c3d6`
- Status: corrected baseline candidate; RouterOS overnight validation clean.

### Fixes

- Added `.gitattributes` rules for LF-normalized MikroTik Lite runtime files.
- Normalized the working-tree runtime files to LF before rebuilding.
- Reloaded the generated Laravel `APP_KEY` before config caching so a blank template `APP_KEY=` export cannot persist into Laravel's cached config.

### Validation

- Local Docker validation:
  - default entrypoint started successfully
  - package discovery, migrations, cache generation completed
  - Vite manifest existed
  - schedule list rendered
  - HTTP returned `302` then `200 OK`
- RouterOS validation:
  - b1.6 ran cleanly overnight
  - no stale `schedule:run` or speedtest process observed after a run completed
  - nginx and PHP-FPM remained active
  - dashboard HTTP check returned `200 OK`
  - profile results continued completing

### Planned b1.7 Patch Items

- Add a 5-minute scheduler startup grace window.
- Remove or relocate the OPcache CLI setting that causes startup warnings.
- Disable or redirect GitHub latest-version checks for MikroTik Lite.
