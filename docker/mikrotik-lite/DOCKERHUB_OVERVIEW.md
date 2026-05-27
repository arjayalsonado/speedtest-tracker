# Speedtest Tracker MikroTik Lite / ARM64 Multi-Interface Build

Experimental ARM64 container image based on Speedtest Tracker, tailored for low-resource ARM64 container environments such as RouterOS/MikroTik.

This variant is designed for source-bound speed tests using multiple local source IPs or interfaces. In the validated setup, the container runs on a MikroTik hAP ax3 used as core access/switch/AP, while WAN routing is handled by a TP-Link ER605 main router/firewall. ISP1/ISP2 are connected to the ER605, and static routes or routing policy on the main router direct traffic based on the container source IP.

## Upstream

This image is an experimental ARM64/MikroTik-focused variant of Speedtest Tracker.

Upstream project:

https://github.com/alexjustesen/speedtest-tracker

This image is maintained independently and is not an official image from the upstream Speedtest Tracker project.

## Development Disclosure

Development notes and validation history are tracked in the project repository. Some implementation and documentation work was AI-assisted, with manual testing performed on RouterOS hardware.

## Current Recommended Tags

- `rvncore/speedtest-tracker:latest`
- `rvncore/speedtest-tracker:multi-isp-exp-arm64`
- `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.1.1-b1.6-mikrotik-lite-multi-isp-arm64`

For repeatable deployments, prefer the pinned build tag:

`rvncore/speedtest-tracker:0.1.1-b1.6-mikrotik-lite-multi-isp-arm64`

## Key Features

- ARM64 Alpine-based image
- Low-resource MikroTik Lite runtime
- SQLite storage
- Nginx + PHP-FPM
- Ookla Speedtest CLI
- Multi-profile scheduling with configurable cron per profile
- Source IP binding with Ookla `--ip`
- RouterOS/container interface source-IP validation
- Supports multiple source profiles; validated with two ISP paths
- 20-minute staggered schedules used in the validated setup, configurable via env
- Tested on MikroTik hAP ax3 RouterOS containers

## Configurable Runtime Values

The runtime is configured through Docker/RouterOS environment values or the persistent `/config/.env` file.

Common adjustable values:

- `SPEEDTEST_LITE_MODE`
  - Enables the MikroTik Lite multi-profile runtime mode.

- `SPEEDTEST_LITE_BIND_OPTION`
  - Controls the Ookla bind option.
  - Common value: `--ip`

- `SPEEDTEST_LITE_BIND_INTERFACE`
  - Interface name visible inside the container, used for source-IP validation.
  - Example: `veth-app-speed`

- `SPEEDTEST_LITE_ISP_PROFILES`
  - Comma-separated list of profile keys.
  - Example: `isp1,isp2`

Per-profile examples:

- `SPEEDTEST_LITE_ISP1_ENABLED=true`
- `SPEEDTEST_LITE_ISP1_NAME=CNVG`
- `SPEEDTEST_LITE_ISP1_SOURCE_IP=192.168.99.250`
- `SPEEDTEST_LITE_ISP1_CRON=0,20,40 * * * *`

- `SPEEDTEST_LITE_ISP2_ENABLED=true`
- `SPEEDTEST_LITE_ISP2_NAME=PLDT`
- `SPEEDTEST_LITE_ISP2_SOURCE_IP=192.168.99.251`
- `SPEEDTEST_LITE_ISP2_CRON=10,30,50 * * * *`

Additional profiles can be added by extending `SPEEDTEST_LITE_ISP_PROFILES` and defining matching profile variables.

Timezone/display values:

- `TZ=Asia/Manila`
- `APP_TIMEZONE=UTC`
- `DISPLAY_TIMEZONE=Asia/Manila`

Recommended behavior:

- Keep `APP_TIMEZONE=UTC` for stable stored timestamps.
- Set `DISPLAY_TIMEZONE` to the local display timezone.
- Set `TZ` to the local runtime timezone.

## Build Line Summary

### `0.1.1-b1.4` - Validated MikroTik Lite baseline

Previous known-good rollback build.

Includes:

- ARM64 Alpine-based MikroTik Lite image
- SQLite runtime storage
- Nginx + PHP-FPM
- Ookla Speedtest CLI
- Multi-profile ISP/source-IP scheduling
- Source IP binding with Ookla `--ip`
- RouterOS VETH source IP validation
- 20-minute staggered profile schedules
- Scheduler overlap guard and stale-lock recovery
- Two PHP-FPM workers for better admin responsiveness

### `0.1.1-b1.6` - Current corrected baseline

Current promoted build.

Adds/fixes:

- Upstream Speedtest Tracker v1.14.2 CVE baseline merge
- LF normalization for Linux runtime files
- Fixed RouterOS startup failure caused by CRLF entrypoint/env files
- Fixed clean first-boot `APP_KEY` caching issue
- Confirmed clean RouterOS overnight validation

## Planned Work

Runtime stabilization:

- Add a scheduler startup grace window to avoid speed tests during container warmup.
- Remove or relocate the OPcache CLI setting that causes startup warnings.
- Disable or redirect GitHub latest-version checks for MikroTik Lite.
- Continue using configurable staggered profile schedules by default.

Future feature line:

- Add profile-aware dashboard and results UI so multi-profile data can be viewed and compared directly in the web interface.

## Typical RouterOS Use Case

- One container
- One VETH interface
- Multiple source IPs on that VETH
- One profile per source IP, interface, or routed WAN path
- Main router/firewall handles source-based policy routing
- Container binds each speed test to the configured source IP

## Tested Hardware

Validated on:

- Device: MikroTik hAP ax3
- Model: C53UiG+5HPaxD2HPaxD
- Architecture: ARM64
- RouterOS: 7.22.1 stable
- Container runtime: RouterOS Containers
- Storage: USB-attached storage mounted as usb1-part1
- Validated RouterOS memory-high: 192M
- Recommended RouterOS memory-high: 192M minimum, 256M preferred where memory allows

Primary tested topology:

- TP-Link ER605 main router/firewall
- ISP1 and ISP2 connected to ER605
- Static routes or routing policy handled by ER605
- MikroTik hAP ax3 as core access/switch/AP
- Speedtest Tracker Lite container running on hAP ax3
- One RouterOS VETH with multiple IPv4 source addresses
- SQLite database on persistent `/config` mount

## Tag Guidance

Known good:

- `0.1.1-b1.6-mikrotik-lite-multi-isp-arm64` - Current validated build
- `0.1.1-b1.4-mikrotik-lite-multi-isp-arm64` - Previous rollback build

Avoid older experimental tags for new deployments.

Use pinned version tags for production-like deployments. Use `latest` only if you intentionally want the current promoted build.

## Status

Experimental but field-tested on MikroTik hAP ax3 model `C53UiG+5HPaxD2HPaxD` running RouterOS 7.22.1 stable.
