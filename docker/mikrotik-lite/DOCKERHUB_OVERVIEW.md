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

## Recommended Tags

- `rvncore/speedtest-tracker:0.1.1-b1.7-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.1.1-b1.7-c7ab9c6-mikrotik-lite-multi-isp-arm64`

For repeatable deployments, prefer the pinned build tag:

`rvncore/speedtest-tracker:0.1.1-b1.7-mikrotik-lite-multi-isp-arm64`

For exact build traceability, use the source-SHA tag:

`rvncore/speedtest-tracker:0.1.1-b1.7-c7ab9c6-mikrotik-lite-multi-isp-arm64`

Moving tags such as `latest`, `multi-isp-exp-arm64`, and `0.1.1-mikrotik-lite-multi-isp-arm64` should be used only after confirming they have been promoted to the intended validated build.

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
- 30-minute per-profile staggered schedules used in the validated setup, configurable via env
- Tested on MikroTik hAP ax3 RouterOS containers

## Configurable Runtime Values

The runtime is configured through Docker/RouterOS environment values or the persistent `/config/.env` file.

### Common Adjustable Values

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

### Per-Profile Examples

- `SPEEDTEST_LITE_ISP1_ENABLED=true`
- `SPEEDTEST_LITE_ISP1_NAME=CNVG`
- `SPEEDTEST_LITE_ISP1_SOURCE_IP=192.168.99.250`
- `SPEEDTEST_LITE_ISP1_CRON=0,30 * * * *`

- `SPEEDTEST_LITE_ISP2_ENABLED=true`
- `SPEEDTEST_LITE_ISP2_NAME=PLDT`
- `SPEEDTEST_LITE_ISP2_SOURCE_IP=192.168.99.251`
- `SPEEDTEST_LITE_ISP2_CRON=15,45 * * * *`

Additional profiles can be added by extending `SPEEDTEST_LITE_ISP_PROFILES` and defining matching profile variables.

### Timezone / Display Values

- `TZ=Asia/Manila`
- `APP_TIMEZONE=UTC`
- `DISPLAY_TIMEZONE=Asia/Manila`

### Timezone Recommendation

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

### `0.1.1-b1.6` - Corrected upstream baseline

Previous corrected baseline.

Adds/fixes:

- Upstream Speedtest Tracker v1.14.2 CVE baseline merge
- LF normalization for Linux runtime files
- Fixed RouterOS startup failure caused by CRLF entrypoint/env files
- Fixed clean first-boot `APP_KEY` caching issue
- Confirmed clean RouterOS overnight validation

### `0.1.1-b1.7` - Validated runtime stabilization build

Validated on RouterOS using the source-SHA image tag. Promote moving tags only after confirming the target Docker Hub tags point to this build.

Adds/fixes:

- Scheduler startup grace window to avoid speed tests during container warmup
- Removed PHP-FPM pool-level OPcache enablement settings that caused startup warnings
- Confirmed RouterOS automatic scheduled-run validation
- Used as the active observation build before the next default-schedule patch

## Planned Work

### Runtime Stabilization

- Change default example schedules to a calmer 30-minute-per-profile stagger.
- Continue using configurable staggered profile schedules by default.
- Review Docker Scout APK package vulnerability findings and rebuild when patched packages or base images are available.

### Future Feature Line

- Add profile-aware dashboard and results UI so multi-profile data can be viewed and compared directly in the web interface.
- Add a Lite-build-aware version check alongside the upstream Speedtest Tracker version check.

## Typical RouterOS Use Case

- One container
- One VETH interface
- Multiple source IPs on that VETH
- One profile per source IP, interface, or routed WAN path
- Main router/firewall handles source-based policy routing
- Container binds each speed test to the configured source IP

## RouterOS Example Deployment

This example matches the validated style but is meant to be adjusted for your network. The storage name, bridge name, IPs, profile names, cron values, and memory cap are all configurable.

### Example Variables

- Storage: `usb1-part1`
- App path: `apps/speedtest/app-speedtest`
- Bridge: `bridge.rvencore.lan`
- Container management IP: `192.168.99.249/24`
- Profile source IPs: `192.168.99.250/24`, `192.168.99.251/24`
- Gateway: `192.168.99.254`
- Interface name: `veth-app-speed`
- Env list: `env-app-speed`
- Mount list: `mount-app-speed`
- Container name: `app-speed`

### Create the VETH Interface

Create one VETH interface and add the management/source IPs used by the profiles.

```
/interface/veth/add name=veth-app-speed address=192.168.99.249/24 gateway=192.168.99.254
/ip/address/add interface=veth-app-speed address=192.168.99.250/24
/ip/address/add interface=veth-app-speed address=192.168.99.251/24
/interface/bridge/port/add bridge=bridge.rvencore.lan interface=veth-app-speed pvid=1
```

### Create Storage Directories

Create or verify persistent directories on the RouterOS storage device.

```
/file/make-directory usb1-part1/apps
/file/make-directory usb1-part1/apps/speedtest
/file/make-directory usb1-part1/apps/speedtest/app-speedtest
/file/make-directory usb1-part1/apps/speedtest/app-speedtest/config
```

### Create the Config Mount

Create the persistent `/config` mount.

```
/container/mounts/add name=mount-speedtest-config src=usb1-part1/apps/speedtest/app-speedtest/config dst=/config
/container/mounts/add name=mount-app-speed mounts=mount-speedtest-config
```

### Create the Environment List

Create the environment list. Adjust profile names, source IPs, and cron values for your own WAN paths.

```
/container/envs/add list=env-app-speed key=TZ value="Asia/Manila"
/container/envs/add list=env-app-speed key=APP_TIMEZONE value="UTC"
/container/envs/add list=env-app-speed key=DISPLAY_TIMEZONE value="Asia/Manila"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_MODE value="multi_isp_experimental"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_BIND_OPTION value="--ip"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_BIND_INTERFACE value="veth-app-speed"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP_PROFILES value="isp1,isp2"
/container/envs/add list=env-app-speed key=MIKROTIK_SCHEDULER_STARTUP_GRACE_SECONDS value="300"

/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP1_ENABLED value="true"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP1_NAME value="CNVG"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP1_SOURCE_IP value="192.168.99.250"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP1_CRON value="0,30 * * * *"

/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP2_ENABLED value="true"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP2_NAME value="PLDT"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP2_SOURCE_IP value="192.168.99.251"
/container/envs/add list=env-app-speed key=SPEEDTEST_LITE_ISP2_CRON value="15,45 * * * *"
```

### Create and Start the Container

For first validation, prefer the exact build tag. After promotion, `latest` can be used if you intentionally want the current promoted build.

```
/container/add remote-image=rvncore/speedtest-tracker:0.1.1-b1.7-c7ab9c6-mikrotik-lite-multi-isp-arm64 interface=veth-app-speed root-dir=usb1-part1/apps/speedtest/app-speedtest/root mountlists=mount-app-speed envlist=env-app-speed memory-high=192M logging=yes start-on-boot=no name=app-speed
/container/start app-speed
```

### Validate Inside the Container

```sh
cd /var/www/html
ip a
php artisan speedtest-lite:validate-isp-profiles
php artisan schedule:list
test -f public/build/manifest.json && echo "vite manifest ok"
wget -S -O /tmp/http-test.html http://127.0.0.1
```

The two source IPs must be visible inside the container before profile-bound runs can be trusted. Routing to each WAN/ISP path is handled outside the container by your router/firewall using static routes or routing policy.

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

### Primary Tested Topology

- TP-Link ER605 main router/firewall
- ISP1 and ISP2 connected to ER605
- Static routes or routing policy handled by ER605
- MikroTik hAP ax3 as core access/switch/AP
- Speedtest Tracker Lite container running on hAP ax3
- One RouterOS VETH with multiple IPv4 source addresses
- SQLite database on persistent `/config` mount

## Tag Guidance

### Known Good Tags

- `0.1.1-b1.7-c7ab9c6-mikrotik-lite-multi-isp-arm64` - Exact RouterOS-tested b1.7 build
- `0.1.1-b1.7-mikrotik-lite-multi-isp-arm64` - Validated b1.7 build tag, once pushed/promoted
- `0.1.1-b1.6-mikrotik-lite-multi-isp-arm64` - Previous corrected baseline
- `0.1.1-b1.4-mikrotik-lite-multi-isp-arm64` - Previous rollback build

Avoid older experimental tags for new deployments.

Use pinned version tags for production-like deployments. Use `latest` only if you intentionally want the current promoted build and have confirmed where it points.

## Status

Experimental but field-tested on MikroTik hAP ax3 model `C53UiG+5HPaxD2HPaxD` running RouterOS 7.22.1 stable.
