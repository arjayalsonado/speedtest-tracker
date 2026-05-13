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
