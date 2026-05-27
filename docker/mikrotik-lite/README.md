# MikroTik Lite Multi-Interface Build

This directory contains the experimental low-resource ARM64 container build for running Speedtest Tracker with multiple source IP profiles.

The primary validated target is MikroTik hAP ax3 running RouterOS containers, but the design is not limited to MikroTik. Any ARM64 container host that can expose multiple source IPs or interfaces to the container may use the same source-binding model.

## Current Build State

Current validated test build:

```text
rvncore/speedtest-tracker:0.1.1-b1.6-mikrotik-lite-multi-isp-arm64
```

Do not deploy:

```text
rvncore/speedtest-tracker:0.1.1-b1.0-mikrotik-lite-multi-isp-arm64
rvncore/speedtest-tracker:0.1.1-b1.1-mikrotik-lite-multi-isp-arm64
rvncore/speedtest-tracker:0.1.1-b1.5-mikrotik-lite-multi-isp-arm64
```

Build `b1.0` is known broken because it included an unsupported Laravel scheduler call:

```php
Schedule::command(...)->timeout(...)
```

The installed Laravel scheduler event API does not support that method. Build `b1.1` removes the scheduler-level timeout and keeps timeout protection in the speedtest job/process path, but it is still incomplete for web UI use because it does not include the Vite production manifest at `public/build/manifest.json`.

Build `b1.2` adds the missing frontend asset build step. Build `b1.3` adds a scheduler lock wrapper so a new cron tick does not start another `schedule:run` while the previous one is still active. Build `b1.4` adds stale-lock recovery and raises PHP-FPM to two workers for better admin responsiveness during Lite runtime testing.

Build `b1.5` was an immutable baseline test image after the upstream v1.14.2 merge, but it is known broken and should not be promoted. It was built from a Windows checkout where Linux runtime files were CRLF-normalized, causing the Linux entrypoint and env parsing to fail.

Build `b1.6` is the corrected baseline candidate. It enforces LF checkout for MikroTik Lite runtime files and reloads the generated Laravel `APP_KEY` before config caching.

Planned next patch build:

```text
0.1.1-b1.7-mikrotik-lite-multi-isp-arm64
```

Planned b1.7 cleanup items:

- Remove PHP-FPM pool setting `php_admin_value[opcache.enable_cli] = 1`.
- Keep `php_admin_value[opcache.enable] = 1`.
- Keep the 20-minute profile schedule defaults.
- Add a MikroTik Lite guard for GitHub latest-version checks so dashboard rendering does not block on GitHub timeouts.
- Add a 5-minute scheduler startup grace window so speed tests do not begin during container warmup.

## Runtime Model

- One container.
- One RouterOS VETH or host interface visible inside the container.
- Multiple source IPs assigned to that interface.
- Up to five configured ISP/source profiles.
- RouterOS or the host handles source-based routing.
- The container validates whether each configured source IP is visible.
- Speedtest execution binds to the selected source IP.
- SQLite is used for the low-resource runtime database.

## Important Environment Values

Use `/config/.env` or RouterOS envlist values to set the runtime configuration.

```text
SPEEDTEST_LITE_MODE=multi_isp_experimental
SPEEDTEST_LITE_BIND_OPTION=--ip
SPEEDTEST_LITE_BIND_INTERFACE=<in-container-interface-name>
SPEEDTEST_LITE_ISP_PROFILES=isp1,isp2

TZ=Asia/Manila
APP_TIMEZONE=UTC
DISPLAY_TIMEZONE=Asia/Manila

SPEEDTEST_LITE_ISP1_ENABLED=true
SPEEDTEST_LITE_ISP1_NAME=CNVG
SPEEDTEST_LITE_ISP1_SOURCE_IP=192.168.99.250
SPEEDTEST_LITE_ISP1_CRON=0,20,40 * * * *

SPEEDTEST_LITE_ISP2_ENABLED=true
SPEEDTEST_LITE_ISP2_NAME=PLDT
SPEEDTEST_LITE_ISP2_SOURCE_IP=192.168.99.251
SPEEDTEST_LITE_ISP2_CRON=10,30,50 * * * *
```

On RouterOS, long VETH names may appear truncated inside the container. For example, `veth-app-speedtest` appeared as `veth-app-speedt`. Set `SPEEDTEST_LITE_BIND_INTERFACE` to the name shown by:

```sh
ip a
```

Validated RouterOS naming convention after cleanup:

```text
container:  app-speed
interface:  veth-app-speed
envlist:    env-app-speed
mountlist:  mount-app-speed
```

The persistent storage path may still use the earlier `app-speedtest` directory name. Do not move `/config` casually; preserving the existing mount keeps SQLite history and app state intact.

## RouterOS Deployment Notes

Use a dedicated root directory and a persistent `/config` mount. A practical layout is:

```text
usb1-part1/apps/speedtest/app-speedtest/root
usb1-part1/apps/speedtest/app-speedtest/config
```

Known stable RouterOS memory setting from validation:

```text
memory-high=192M
```

`128M` allowed the container to boot and manual runs to work, but scheduled automatic runs left intermittent `waiting` rows. Raising the limit to `160M` resolved the first automatic-run issue during validation. RouterOS admin/web testing later showed better stability at `192M` while scheduled tests were active.

## Validation Commands

Run inside the container from `/var/www/html`:

```sh
php artisan speedtest-lite:validate-isp-profiles
php artisan schedule:list
php artisan speedtest-lite:run-isp-profile isp1
php artisan speedtest-lite:run-isp-profile isp2
```

Expected profile validation:

```text
[isp1] CNVG source_ip=192.168.99.250 visible=yes
[isp2] PLDT source_ip=192.168.99.251 visible=yes
```

Known-good b1.4 closeout checks from RouterOS validation:

```text
speedtest-lite:validate-isp-profiles
  [isp1] CNVG source_ip=192.168.99.250 visible=yes cron=0,20,40 * * * *
  [isp2] PLDT source_ip=192.168.99.251 visible=yes cron=10,30,50 * * * *

schedule:list
  profile schedules present on 20-minute stagger
  upstream maintenance schedules still present
  sqlite-vacuum still present

SQLite summary
  isp1 completed rows present
  isp1 failed rows preserved for troubleshooting
  isp2 completed rows present
  no untagged waiting bucket remains

process state
  nginx master/worker
  crond
  php-fpm master
  two php-fpm pool workers
  no stale schedule:run or speedtest process while idle

HTTP check
  wget -S -O - http://127.0.0.1 returns HTTP/1.1 200 OK
```

Use SQLite to confirm source routing and result tagging:

```sh
sqlite3 /config/database/database.sqlite "select id, isp_profile_key, isp_profile_name, source_ip, status, json_extract(data,'$.interface.externalIp') as external_ip, json_extract(data,'$.isp') as isp, json_extract(data,'$.server.id') as server_id, created_at from results where status='completed' order by id desc limit 10;"
```

Validated routing examples:

```text
isp1 | CNVG | 192.168.99.250 | external_ip=136.158.32.96 | isp=Converge
isp2 | PLDT | 192.168.99.251 | external_ip=112.208.182.118 | isp=PLDT
```

## Timeout Behavior

Do not use scheduler-level timeout chaining in `routes/console.php`.

Valid timeout hardening lives in `app/Jobs/Ookla/RunSpeedtestJob.php`:

```php
public $timeout = 300;
$process->setTimeout(300);
```

This gives slow RouterOS speedtest runs more time while avoiding unsupported Laravel scheduler APIs.

## Timezone Behavior

Keep stored application timestamps in UTC and use the display timezone for UI and schedule presentation:

```text
TZ=Asia/Manila
APP_TIMEZONE=UTC
DISPLAY_TIMEZONE=Asia/Manila
```

Do not set `APP_TIMEZONE=Asia/Manila` for an existing SQLite database that was created with UTC timestamps. Historical rows are stored as timezone-less SQLite datetime strings, so changing `APP_TIMEZONE` can make old UTC rows appear as if they were already Manila time. `DISPLAY_TIMEZONE` is the intended user-facing timezone control.

For RouterOS envlists, add or set all three values explicitly:

```rsc
/container/envs/add list=env-app-speed key=TZ value="Asia/Manila"
/container/envs/add list=env-app-speed key=APP_TIMEZONE value="UTC"
/container/envs/add list=env-app-speed key=DISPLAY_TIMEZONE value="Asia/Manila"
```

If a key already exists, use `set` instead of `add`:

```rsc
/container/envs/set [find list=env-app-speed key=TZ] value="Asia/Manila"
/container/envs/set [find list=env-app-speed key=APP_TIMEZONE] value="UTC"
/container/envs/set [find list=env-app-speed key=DISPLAY_TIMEZONE] value="Asia/Manila"
```

Restart the container after changing timezone values so Laravel rebuilds the cached config. Validate inside the container:

```sh
cd /var/www/html
php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo "APP_TIMEZONE=".config("app.timezone").PHP_EOL; echo "DISPLAY_TIMEZONE=".config("app.display_timezone").PHP_EOL; echo "now=".now()->toDateTimeString().PHP_EOL; echo "display_now=".now()->timezone(config("app.display_timezone"))->toDateTimeString().PHP_EOL;'
```

Expected output should show `APP_TIMEZONE=UTC` and `DISPLAY_TIMEZONE=Asia/Manila`.

## Admin Credentials

The upstream application seeds a default admin account for first login. Change it after the container is running and the database is initialized.

Run inside the container from `/var/www/html`:

```sh
php artisan app:user-reset-password
```

Follow the prompts to choose the user and set the new password. After changing it, sign out and sign back in through the web UI to verify the new credentials.

Do not store admin passwords in the image, README, CODELOAD history, RouterOS comments, or screenshots.

## Documentation and History

- Detailed implementation and review history: `.ai/CODELOAD_HISTORY.md`
- Repo-level changelog: `CHANGELOG.md`
- Build-specific changelog: `docker/mikrotik-lite/CHANGELOG.md`

`CODELOAD_HISTORY.md` is an AI handoff and engineering worklog. It is not a replacement for release notes or operator documentation.

## Pending UI Work

The current `0.1.1-b1.4` build stores ISP/profile metadata in SQLite, but the dashboard remains profile-unaware. Existing charts combine completed results from all profiles.

Pending feature work for the next feature line:

- Add profile/source columns and filters to the Results table.
- Add a dashboard profile selector.
- Filter dashboard chart queries by selected profile.
- Later, add per-profile chart series and latest-result summary cards.

Suggested feature version:

```text
0.2.0-b1.0-mikrotik-lite-multi-isp-arm64
```
