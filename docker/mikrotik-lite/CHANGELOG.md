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
