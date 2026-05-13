# Speedtest Tracker MikroTik Lite — v1.2 Multi-ISP Experimental Refactor

Status: **Corrected experimental branch design and code patch package**  
Previous archive: **v1.1 Multi-ISP Experimental Refactor**  
Baseline retained: **MikroTik Lite Standard** single-ISP build from v0.8/v0.9  
New branch: **`mikrotik-lite-multi-isp-exp`**  
Date: **2026-05-13**

---

## 0. v1.2 correction summary

This v1.2 archive supersedes the v1.1 multi-ISP markdown for code assembly.

The v1.1 architecture remains accepted:

```text
One container
One RouterOS VETH
Up to five generic ISP profiles
One source IP per ISP profile
One SQLite database
Profile-specific schedules
RouterOS policy routing by source IP
Container validates source IP visibility only
Speedtest execution binds to the selected profile/source IP
```

However, the v1.1 code package needed corrections after checking the current public `mikrotik-lite-multi-isp-exp` branch.

### Corrections accepted in v1.2

| Area | v1.1 issue | v1.2 correction |
|---|---|---|
| Upstream action namespace | Used `App\Actions\Speedtest\CheckForScheduledSpeedtests` | Use `App\Actions\CheckForScheduledSpeedtests` |
| Ookla binding option | Assumed setting `speedtest.interface` could support `--ip` without patching the job | Patch `RunSpeedtestJob.php` to use `SPEEDTEST_LITE_BIND_OPTION` and allow `--interface` or `--ip` |
| Dockerfile | v1.1 shortened runtime package list and risked dropping accepted v0.9 packages | Use v0.9 Dockerfile as baseline and add only `iproute2` |
| `routes/console.php` | v1.1 replacement could drop upstream prune/maintenance schedules | Preserve upstream schedules; replace only the scheduled speedtest block |
| RouterOS VETH command | Pasted reviewer used an exact multi-address `/interface/veth/set address=a,b` command | Treat exact CLI syntax as validation-required; document UI/address-list method first |
| Release wording | Reviewer called the architecture completely solid | Mark as accepted for code assembly / QA testing, not production-final |

### Current public branch observation

At the time of this archive update, `mikrotik-lite-multi-isp-exp` exists, but the custom MikroTik Lite / multi-ISP files have not yet been applied. The current upstream class location and scheduler/binding behavior were checked before preparing this v1.2 patch.

---

## 1. Branching / naming convention

Use this branch split:

```bash
git checkout mikrotik-lite

git branch mikrotik-lite-standard

git checkout -b mikrotik-lite-multi-isp-exp
```

Recommended image tags:

```text
yourdockerhubuser/speedtest-tracker:1.14.1-mikrotik-lite-arm64
yourdockerhubuser/speedtest-tracker:1.14.1-mikrotik-lite-multi-isp-exp-arm64
yourdockerhubuser/speedtest-tracker:lite-arm64
yourdockerhubuser/speedtest-tracker:multi-isp-exp-arm64
```

Interpretation:

- `mikrotik-lite-standard` = stable single-ISP/source-IP MikroTik Lite branch.
- `mikrotik-lite-multi-isp-exp` = experimental one-container, one-VETH, multi-source-IP branch.

---

## 2. Design summary

### Accepted design

```text
One container
One RouterOS VETH
Up to five IP addresses configured on the same VETH in RouterOS
One Laravel/Speedtest Tracker Lite application
One SQLite database
Profile-aware scheduled speed tests
RouterOS policy-routing by source IP
```

### Rejected as default

```text
Two containers for two ISPs
Two VETH interfaces on one RouterOS container
Container self-adding secondary IPs with `ip addr add`
Hardcoded provider names such as Converge/PLDT
Running all profile tests at the same minute
```

### Why this design

RouterOS owns the VETH IP assignment. The container should not mutate its network namespace by default. The application only validates that the configured source IPs are visible and then binds each speed test to the selected source identity.

---

## 3. Implementation boundary

This v1.2 patch is the **multi-ISP execution foundation**.

It does **not** include the full dashboard/UI refactor yet. Result rows gain profile metadata so a later v1.3 UI pass can add:

- dashboard filter: All / ISP 1 / ISP 2 / ISP 3 / ISP 4 / ISP 5
- profile labels in result tables
- profile-specific chart series
- profile-specific export filters

The implementation intentionally reuses Speedtest Tracker's existing execution flow where possible. The only upstream execution patch required for v1.2 is in `app/Jobs/Ookla/RunSpeedtestJob.php` so the binding flag can be selected dynamically.

---

## 4. Change summary for QA/reviewer

| Area | Change | Reason | Risk |
|---|---|---|---|
| Branching | Add `mikrotik-lite-multi-isp-exp` branch | Keeps standard MikroTik build stable | Low |
| Environment | Add generic ISP profile slots `isp1` to `isp5` | Avoid provider-specific hardcoding | Low |
| Dockerfile | Add `iproute2` to v0.9 package baseline | Allows runtime validation via `ip addr show` without dropping stable runtime packages | Low |
| Entrypoint | Validate configured source IPs only | RouterOS owns VETH IP assignment | Low |
| Laravel config | Add `config/speedtest-lite.php` | Central profile registry | Low |
| Scheduler | Replace only scheduled speedtest block with profile-aware scheduled commands | Allows staggered tests per ISP while preserving upstream prune/maintenance tasks | Medium |
| Command | Add `speedtest-lite:run-isp-profile {profile}` | Runs selected profile with source binding | Medium |
| Ookla job | Patch binding argument from hardcoded `--interface` to configurable `--interface` / `--ip` | Enables source-IP binding tests without rewriting parser | Medium |
| Results DB | Add profile metadata columns to `results` | Allows later UI filtering/graph split | Medium |
| UI | Not implemented in v1.2 | Keep source refactor minimal first | Known gap |

---

## 5. Files to add or change

## File 1 — `docker/mikrotik-lite/.env.mikrotik-lite`

Create or replace this file under `docker/mikrotik-lite/`.

```env
APP_NAME="Speedtest Tracker Lite"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stderr
LOG_LEVEL=warning

DB_CONNECTION=sqlite
DB_DATABASE=/config/database/database.sqlite
DB_FOREIGN_KEYS=true
DB_BUSY_TIMEOUT=5000
DB_JOURNAL_MODE=WAL
DB_SYNCHRONOUS=NORMAL

QUEUE_CONNECTION=sync
CACHE_STORE=array
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log

# Standard Speedtest Tracker fallback schedule.
# In multi-ISP mode, profile cron values below are used by routes/console.php.
SPEEDTEST_SCHEDULE="*/15 * * * *"
PRUNE_RESULTS_OLDER_THAN=30

PUBLIC_DASHBOARD=true
DEFAULT_CHART_RANGE=24h
CONTENT_WIDTH=full

THRESHOLD_ENABLED=false
INFLUXDB_V2_ENABLED=false
FILAMENT_CACHE_COMPONENTS=false

# ------------------------------------------------------------
# MikroTik Lite Multi-ISP Experimental Mode
# ------------------------------------------------------------
SPEEDTEST_LITE_MODE=multi_isp_experimental
SPEEDTEST_LITE_ISP_PROFILE_LIMIT=5
SPEEDTEST_LITE_ISP_PROFILES=isp1,isp2
SPEEDTEST_LITE_VALIDATE_SOURCE_IPS=true
SPEEDTEST_LITE_BIND_INTERFACE=eth0

# v1.2 correction:
# Current upstream app passes speedtest.interface as --interface by default.
# This experimental branch patches RunSpeedtestJob.php so this can be --interface or --ip.
# Start with --ip for source-IP binding. If the bundled Ookla CLI rejects it, test --interface.
SPEEDTEST_LITE_BIND_OPTION=--ip

SPEEDTEST_LITE_ISP1_ENABLED=true
SPEEDTEST_LITE_ISP1_NAME="ISP 1"
SPEEDTEST_LITE_ISP1_SOURCE_IP=192.168.99.250
SPEEDTEST_LITE_ISP1_CRON="*/15 * * * *"

SPEEDTEST_LITE_ISP2_ENABLED=true
SPEEDTEST_LITE_ISP2_NAME="ISP 2"
SPEEDTEST_LITE_ISP2_SOURCE_IP=192.168.99.251
SPEEDTEST_LITE_ISP2_CRON="7,22,37,52 * * * *"

SPEEDTEST_LITE_ISP3_ENABLED=false
SPEEDTEST_LITE_ISP3_NAME="ISP 3"
SPEEDTEST_LITE_ISP3_SOURCE_IP=
SPEEDTEST_LITE_ISP3_CRON=

SPEEDTEST_LITE_ISP4_ENABLED=false
SPEEDTEST_LITE_ISP4_NAME="ISP 4"
SPEEDTEST_LITE_ISP4_SOURCE_IP=
SPEEDTEST_LITE_ISP4_CRON=

SPEEDTEST_LITE_ISP5_ENABLED=false
SPEEDTEST_LITE_ISP5_NAME="ISP 5"
SPEEDTEST_LITE_ISP5_SOURCE_IP=
SPEEDTEST_LITE_ISP5_CRON=
```

---

## File 2 — `docker/mikrotik-lite/Dockerfile.mikrotik-lite`

Use the accepted v0.9 Dockerfile baseline and add only `iproute2` to the final runtime package list.

```dockerfile
# ==========================================
# STAGE 1: Compilation & Dependency Fetching
# ==========================================
FROM php:8.4-fpm-alpine3.22 AS builder

RUN apk add --no-cache \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    sqlite-dev \
    linux-headers

RUN docker-php-ext-install pdo pdo_sqlite mbstring exif intl pcntl sockets

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# ==========================================
# STAGE 2: Minimal Production Image Runtime
# ==========================================
FROM php:8.4-fpm-alpine3.22

RUN apk add --no-cache \
    nginx \
    sqlite \
    sqlite-libs \
    shadow \
    curl \
    bash \
    jq \
    icu-libs \
    libxml2 \
    oniguruma \
    busybox-suid \
    ca-certificates \
    tzdata \
    iproute2

RUN getent group www-data || groupadd -g 82 www-data; \
    getent passwd www-data || useradd -u 82 -g www-data -m -s /sbin/nologin www-data; \
    mkdir -p /run/nginx /var/lib/nginx/tmp /var/log/nginx /config; \
    chown -R www-data:www-data /run/nginx /var/lib/nginx /var/log/nginx /config

COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d
COPY --from=builder /app /var/www/html

COPY nginx.conf /etc/nginx/nginx.conf
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
COPY .env.mikrotik-lite /var/www/html/.env.mikrotik-lite

RUN chmod +x /usr/local/bin/entrypoint.sh && \
    mkdir -p /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html /config

WORKDIR /var/www/html
VOLUME ["/config"]
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["sh", "-c", "php-fpm -y /usr/local/etc/php-fpm.d/www.conf & exec nginx -g 'daemon off;'"]
```

### v1.2 Dockerfile rule

Do not replace this with the shorter v1.1 Dockerfile. The accepted v0.9 baseline keeps `sqlite-libs`, `ca-certificates`, and `tzdata`; v1.2 only adds `iproute2`.

---

## File 3 — `docker/mikrotik-lite/entrypoint.sh`

Patch the accepted v0.9 entrypoint by adding the multi-ISP validation block after `.env` is linked and before Artisan operations.

```bash
#!/bin/bash
set -e

APP_DIR="/var/www/html"
CONFIG_DIR="/config"

bool_true() {
    case "$(echo "${1:-}" | tr '[:upper:]' '[:lower:]')" in
        true|1|yes|y|on) return 0 ;;
        *) return 1 ;;
    esac
}

cd "$APP_DIR"

echo "==> Initializing MikroTik Lite Volume Layout..."

mkdir -p \
  "$CONFIG_DIR/database" \
  "$CONFIG_DIR/storage/app" \
  "$CONFIG_DIR/storage/framework/cache/data" \
  "$CONFIG_DIR/storage/framework/sessions" \
  "$CONFIG_DIR/storage/framework/views" \
  "$CONFIG_DIR/storage/logs" \
  "$APP_DIR/bootstrap/cache" \
  /run/nginx

if [ ! -f "$CONFIG_DIR/.env" ]; then
    echo "==> No persistent config found. Deploying template base..."
    if [ -f "$APP_DIR/.env.mikrotik-lite" ]; then
        cp "$APP_DIR/.env.mikrotik-lite" "$CONFIG_DIR/.env"
    else
        touch "$CONFIG_DIR/.env"
    fi
fi

ln -sfn "$CONFIG_DIR/.env" "$APP_DIR/.env"
rm -rf "$APP_DIR/storage"
ln -sfn "$CONFIG_DIR/storage" "$APP_DIR/storage"

# Load persistent env for shell-level validation variables.
set -a
# shellcheck disable=SC1091
. "$CONFIG_DIR/.env" || true
set +a

if [ ! -f "$CONFIG_DIR/database/database.sqlite" ]; then
    echo "==> Creating persistent SQLite database file..."
    touch "$CONFIG_DIR/database/database.sqlite"
fi

chown -R www-data:www-data "$CONFIG_DIR" "$APP_DIR/bootstrap/cache"

# Validate RouterOS-provided VETH source IPs.
# RouterOS owns IP assignment. This container only verifies presence.
if bool_true "${SPEEDTEST_LITE_VALIDATE_SOURCE_IPS:-true}"; then
    echo "==> Validating configured ISP source IPs on ${SPEEDTEST_LITE_BIND_INTERFACE:-eth0}..."

    IFS=',' read -ra PROFILES <<< "${SPEEDTEST_LITE_ISP_PROFILES:-}"
    for raw_profile in "${PROFILES[@]}"; do
        profile="$(echo "$raw_profile" | xargs | tr '[:lower:]' '[:upper:]' | tr '-' '_')"
        [ -z "$profile" ] && continue

        enabled_var="SPEEDTEST_LITE_${profile}_ENABLED"
        name_var="SPEEDTEST_LITE_${profile}_NAME"
        ip_var="SPEEDTEST_LITE_${profile}_SOURCE_IP"

        enabled="${!enabled_var:-false}"
        profile_name="${!name_var:-$profile}"
        source_ip="${!ip_var:-}"

        if bool_true "$enabled"; then
            if [ -z "$source_ip" ]; then
                echo "WARNING: ${profile_name} is enabled but has no source IP configured." >&2
                continue
            fi

            if ip -o addr show dev "${SPEEDTEST_LITE_BIND_INTERFACE:-eth0}" | grep -q "${source_ip}"; then
                echo "    - ${profile_name}: found ${source_ip}"
            else
                echo "WARNING: ${profile_name}: source IP ${source_ip} not visible on ${SPEEDTEST_LITE_BIND_INTERFACE:-eth0}." >&2
                echo "WARNING: Add it in RouterOS VETH address list before trusting this profile." >&2
            fi
        fi
    done
fi

if ! grep -q '^APP_KEY=base64:' "$CONFIG_DIR/.env"; then
    echo "==> Generating Laravel APP_KEY..."
    php artisan key:generate --force --no-interaction || true
fi

echo "==> Flushing old deployment state caches..."
php artisan package:discover --no-interaction || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "==> Executing schema migration routines..."
php artisan migrate --force --no-interaction

echo "==> Compiling optimized production cache manifests..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

chown -R www-data:www-data "$CONFIG_DIR" "$APP_DIR/bootstrap/cache" "$APP_DIR/storage"

echo "==> Validating Speedtest CLI binary availability..."
if command -v speedtest >/dev/null 2>&1; then
    speedtest --version | head -n 1 || true
else
    echo "WARNING: Ookla speedtest binary not discovered in system PATH." >&2
fi

echo "==> Initializing internal scheduler cron tasks..."
MIKROTIK_CRON_SCHEDULE="${MIKROTIK_CRON_SCHEDULE:-*/15 * * * *}"
printf '%s cd /var/www/html && php artisan schedule:run --no-interaction >/proc/1/fd/1 2>/proc/1/fd/2
' "$MIKROTIK_CRON_SCHEDULE" > /tmp/crontab
crontab /tmp/crontab
crond -b -l 8

echo "==> Routing execution thread over to operational web engines..."
exec "$@"
```

---

## File 4 — `config/speedtest-lite.php`

Create new file.

```php
<?php

$bool = static fn (string $key, bool $default = false): bool => filter_var(
    env($key, $default),
    FILTER_VALIDATE_BOOLEAN,
);

$profiles = [];

for ($i = 1; $i <= (int) env('SPEEDTEST_LITE_ISP_PROFILE_LIMIT', 5); $i++) {
    $key = "isp{$i}";
    $prefix = 'SPEEDTEST_LITE_ISP'.strtoupper((string) $i);

    $profiles[$key] = [
        'key' => $key,
        'enabled' => $bool("{$prefix}_ENABLED", false),
        'name' => env("{$prefix}_NAME", "ISP {$i}"),
        'source_ip' => env("{$prefix}_SOURCE_IP"),
        'cron' => env("{$prefix}_CRON"),
    ];
}

return [
    'mode' => env('SPEEDTEST_LITE_MODE', 'standard'),
    'profile_limit' => (int) env('SPEEDTEST_LITE_ISP_PROFILE_LIMIT', 5),
    'enabled_profile_keys' => array_filter(array_map(
        static fn (string $profile): string => trim($profile),
        explode(',', env('SPEEDTEST_LITE_ISP_PROFILES', '')),
    )),
    'validate_source_ips' => $bool('SPEEDTEST_LITE_VALIDATE_SOURCE_IPS', true),
    'bind_interface' => env('SPEEDTEST_LITE_BIND_INTERFACE', 'eth0'),
    'bind_option' => env('SPEEDTEST_LITE_BIND_OPTION', '--interface'),
    'profiles' => $profiles,
];
```

---

## File 5 — `app/Support/SpeedtestLite/IspProfile.php`

Create new file.

```php
<?php

namespace App\Support\SpeedtestLite;

use InvalidArgumentException;

final readonly class IspProfile
{
    public function __construct(
        public string $key,
        public string $name,
        public string $sourceIp,
        public ?string $cron,
        public bool $enabled = true,
    ) {}

    /**
     * @param  array{key?: string, name?: string|null, source_ip?: string|null, cron?: string|null, enabled?: bool}  $data
     */
    public static function fromArray(string $key, array $data): self
    {
        $sourceIp = trim((string) ($data['source_ip'] ?? ''));

        if ($sourceIp === '') {
            throw new InvalidArgumentException("ISP profile [{$key}] has no source IP configured.");
        }

        if (filter_var($sourceIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            throw new InvalidArgumentException("ISP profile [{$key}] source IP [{$sourceIp}] is not a valid IPv4 address.");
        }

        return new self(
            key: $key,
            name: trim((string) ($data['name'] ?? strtoupper($key))),
            sourceIp: $sourceIp,
            cron: $data['cron'] ?? null,
            enabled: (bool) ($data['enabled'] ?? false),
        );
    }
}
```

---

## File 6 — `app/Support/SpeedtestLite/IspProfiles.php`

Create new file.

```php
<?php

namespace App\Support\SpeedtestLite;

use Illuminate\Support\Collection;
use InvalidArgumentException;

final class IspProfiles
{
    /**
     * @return Collection<string, IspProfile>
     */
    public static function enabled(): Collection
    {
        $enabledKeys = collect(config('speedtest-lite.enabled_profile_keys', []))
            ->map(fn (string $key): string => strtolower(trim($key)))
            ->filter()
            ->unique();

        $profiles = collect(config('speedtest-lite.profiles', []));

        return $enabledKeys
            ->mapWithKeys(function (string $key) use ($profiles): array {
                $data = $profiles->get($key);

                if (! is_array($data)) {
                    return [];
                }

                if (! (bool) ($data['enabled'] ?? false)) {
                    return [];
                }

                return [$key => IspProfile::fromArray($key, $data)];
            });
    }

    public static function get(string $key): IspProfile
    {
        $key = strtolower(trim($key));

        $profile = self::enabled()->get($key);

        if (! $profile instanceof IspProfile) {
            throw new InvalidArgumentException("ISP profile [{$key}] is not enabled or does not exist.");
        }

        return $profile;
    }
}
```

---

## File 7 — `database/migrations/2026_05_13_000001_add_isp_profile_columns_to_results_table.php`

Create new migration.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            if (! Schema::hasColumn('results', 'isp_profile_key')) {
                $table->string('isp_profile_key')->nullable()->after('service')->index();
            }

            if (! Schema::hasColumn('results', 'isp_profile_name')) {
                $table->string('isp_profile_name')->nullable()->after('isp_profile_key');
            }

            if (! Schema::hasColumn('results', 'source_ip')) {
                $table->string('source_ip', 45)->nullable()->after('isp_profile_name')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            if (Schema::hasColumn('results', 'source_ip')) {
                $table->dropIndex(['source_ip']);
                $table->dropColumn('source_ip');
            }

            if (Schema::hasColumn('results', 'isp_profile_key')) {
                $table->dropIndex(['isp_profile_key']);
                $table->dropColumn('isp_profile_key');
            }

            if (Schema::hasColumn('results', 'isp_profile_name')) {
                $table->dropColumn('isp_profile_name');
            }
        });
    }
};
```

### QA note

This migration is additive for the experimental branch. If SQLite has trouble with rollback index dropping, rollback can be handled manually. Forward migration is the primary requirement.

---

## File 8 — `app/Console/Commands/ValidateIspProfiles.php`

Create new file.

```php
<?php

namespace App\Console\Commands;

use App\Support\SpeedtestLite\IspProfiles;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ValidateIspProfiles extends Command
{
    protected $signature = 'speedtest-lite:validate-isp-profiles';

    protected $description = 'Validate configured MikroTik Lite ISP profiles and source IP visibility.';

    public function handle(): int
    {
        $interface = config('speedtest-lite.bind_interface', 'eth0');
        $profiles = IspProfiles::enabled();

        if ($profiles->isEmpty()) {
            $this->warn('No enabled ISP profiles found.');

            return self::SUCCESS;
        }

        $process = new Process(['ip', '-o', 'addr', 'show', 'dev', $interface]);
        $process->run();

        $ipOutput = $process->isSuccessful() ? $process->getOutput() : '';

        foreach ($profiles as $profile) {
            $visible = str_contains($ipOutput, $profile->sourceIp);

            $this->line(sprintf(
                '[%s] %s source_ip=%s visible=%s cron=%s',
                $profile->key,
                $profile->name,
                $profile->sourceIp,
                $visible ? 'yes' : 'no',
                $profile->cron ?: 'not-set',
            ));

            if (! $visible) {
                $this->warn("Source IP [{$profile->sourceIp}] is not visible on [{$interface}]. Check RouterOS VETH address list.");
            }
        }

        return self::SUCCESS;
    }
}
```

---

## File 9 — `app/Console/Commands/RunIspProfileSpeedtest.php`

Create new file.

v1.2 correction: the current upstream branch uses `App\Actions\CheckForScheduledSpeedtests`, not `App\Actions\Speedtest\CheckForScheduledSpeedtests`.

```php
<?php

namespace App\Console\Commands;

use App\Actions\CheckForScheduledSpeedtests;
use App\Models\Result;
use App\Support\SpeedtestLite\IspProfiles;
use Illuminate\Console\Command;
use Throwable;

class RunIspProfileSpeedtest extends Command
{
    protected $signature = 'speedtest-lite:run-isp-profile {profile : ISP profile key, for example isp1}';

    protected $description = 'Run a scheduled Speedtest Tracker check bound to a configured ISP source IP.';

    public function handle(): int
    {
        $profile = IspProfiles::get((string) $this->argument('profile'));

        $this->info("Running speed test for {$profile->name} using source IP {$profile->sourceIp}...");

        $beforeId = Result::query()->max('id') ?? 0;

        config([
            'speedtest.interface' => $profile->sourceIp,
            'speedtest.schedule' => '* * * * *',
            'speedtest-lite.active_profile_key' => $profile->key,
            'speedtest-lite.active_profile_name' => $profile->name,
            'speedtest-lite.active_source_ip' => $profile->sourceIp,
        ]);

        putenv('SPEEDTEST_INTERFACE='.$profile->sourceIp);
        $_ENV['SPEEDTEST_INTERFACE'] = $profile->sourceIp;
        $_SERVER['SPEEDTEST_INTERFACE'] = $profile->sourceIp;

        try {
            CheckForScheduledSpeedtests::run();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            report($exception);

            return self::FAILURE;
        }

        // In the Lite build QUEUE_CONNECTION=sync, so the result should be created before return.
        $result = Result::query()
            ->where('id', '>', $beforeId)
            ->latest('id')
            ->first();

        if (! $result) {
            $this->warn('No new result was detected after running the profile. Check queue mode and upstream action behavior.');

            return self::SUCCESS;
        }

        $result->forceFill([
            'isp_profile_key' => $profile->key,
            'isp_profile_name' => $profile->name,
            'source_ip' => $profile->sourceIp,
        ])->save();

        $this->info("Tagged result #{$result->getKey()} as {$profile->name} ({$profile->sourceIp}).");

        return self::SUCCESS;
    }
}
```

### Risk note

This command still assumes the upstream action creates the result synchronously. That should hold only while the Lite build uses `QUEUE_CONNECTION=sync`. If upstream switches to true async behavior, this command may not find a newly-created result immediately.

---

## File 10 — `app/Jobs/Ookla/RunSpeedtestJob.php`

Patch the current upstream command builder.

### Existing upstream behavior

Current upstream code hardcodes the bind argument as:

```php
config('speedtest.interface') ? '--interface='.config('speedtest.interface') : null,
```

That means setting `speedtest.interface` to `192.168.99.250` currently produces:

```bash
--interface=192.168.99.250
```

For source-IP binding, the experimental branch needs to test/use:

```bash
--ip=192.168.99.250
```

### v1.2 patch

Inside `handle()`, before `$command = array_filter([...])`, add:

```php
$bindValue = config('speedtest.interface');
$bindOption = config('speedtest-lite.bind_option', '--interface');

if (! in_array($bindOption, ['--interface', '--ip'], true)) {
    $bindOption = '--interface';
}

$bindArgument = $bindValue ? "{$bindOption}={$bindValue}" : null;
```

Then replace the hardcoded bind line in `$command` with:

```php
$bindArgument,
```

### Corrected command builder section

```php
public function handle(): void
{
    $this->result->update([
        'status' => ResultStatus::Running,
    ]);

    SpeedtestRunning::dispatch($this->result);

    $bindValue = config('speedtest.interface');
    $bindOption = config('speedtest-lite.bind_option', '--interface');

    if (! in_array($bindOption, ['--interface', '--ip'], true)) {
        $bindOption = '--interface';
    }

    $bindArgument = $bindValue ? "{$bindOption}={$bindValue}" : null;

    $command = array_filter([
        'speedtest',
        '--accept-license',
        '--accept-gdpr',
        '--selection-details',
        '--format=json',
        $this->result->server_id ? '--server-id='.$this->result->server_id : null,
        $bindArgument,
    ]);

    $process = new Process($command);

    // Keep the rest of the upstream method unchanged.
}
```

### QA note

After build, verify the bundled Ookla CLI supports the selected bind flag:

```bash
speedtest --help | grep -E -- '--ip|--interface' || true
```

If `--ip` is unsupported in the bundled binary, change:

```env
SPEEDTEST_LITE_BIND_OPTION=--ip
```

to:

```env
SPEEDTEST_LITE_BIND_OPTION=--interface
```

and retest manually.

---

## File 11 — `routes/console.php`

Do **not** replace the whole file blindly.

v1.2 correction: preserve existing upstream scheduled maintenance tasks such as model pruning, queue pruning, and SQLite vacuum. Replace only the scheduled speedtest block.

### Existing block to replace

```php
/**
 * Check for scheduled speedtests.
 */
Schedule::everyMinute()
    ->group(function () {
        Schedule::call(fn () => CheckForScheduledSpeedtests::run());
    });
```

### Add this import near the existing imports

```php
use App\Support\SpeedtestLite\IspProfiles;
```

### Replacement block

```php
/**
 * Check for scheduled speedtests.
 *
 * Standard mode keeps the upstream every-minute scheduler check.
 * Multi-ISP mode schedules one command per enabled ISP profile.
 */
if (config('speedtest-lite.mode') === 'multi_isp_experimental') {
    foreach (IspProfiles::enabled() as $profile) {
        if (! $profile->cron) {
            continue;
        }

        Schedule::command("speedtest-lite:run-isp-profile {$profile->key}")
            ->cron($profile->cron)
            ->name("mikrotik-lite-speedtest-{$profile->key}")
            ->withoutOverlapping(20);
    }
} else {
    Schedule::everyMinute()
        ->group(function () {
            Schedule::call(fn () => CheckForScheduledSpeedtests::run());
        });
}
```

### Impact

- Multi-ISP mode gets profile-specific schedules.
- Standard mode keeps upstream scheduler behavior.
- Existing maintenance tasks remain untouched.

---

## 6. RouterOS VETH setup

### Safe statement

Configure multiple source IPs on the same RouterOS VETH using the RouterOS-supported VETH address list / interface UI method.

Example VETH address list:

```text
192.168.99.250/24
192.168.99.251/24
```

Gateway:

```text
192.168.99.254
```

### Do not lock this CLI syntax until tested

The pasted reviewer command below is not accepted as final until verified on your RouterOS version:

```routeros
/interface/veth/set [find name=veth-multi-isp] address=192.168.99.250/24,192.168.99.251/24 gateway=192.168.99.254
```

Use the UI/address-list method first if unsure, then validate inside the container:

```bash
ip -o addr show dev eth0
php artisan speedtest-lite:validate-isp-profiles
```

---

## 7. RouterOS / upstream policy routing concept

If routing is handled on MikroTik:

```routeros
/routing/table
add name=to-isp1 fib
add name=to-isp2 fib

/ip/route
add dst-address=0.0.0.0/0 gateway=<isp1-gateway> routing-table=to-isp1
add dst-address=0.0.0.0/0 gateway=<isp2-gateway> routing-table=to-isp2

/routing/rule
add src-address=192.168.99.250/32 action=lookup-only-in-table table=to-isp1
add src-address=192.168.99.251/32 action=lookup-only-in-table table=to-isp2
```

If routing is handled by TP-Link ER605 instead, create policy routing rules by source IP:

```text
192.168.99.250/32 -> ISP 1 WAN
192.168.99.251/32 -> ISP 2 WAN
```

---

## 8. Build command

```bash
docker buildx build \
  --platform linux/arm64 \
  -f Dockerfile.mikrotik-lite \
  --tag your_docker_username/speedtest-tracker:1.14.1-mikrotik-lite-multi-isp-exp-arm64 \
  --tag your_docker_username/speedtest-tracker:multi-isp-exp-arm64 \
  --push \
  .
```

---

## 9. Validation checklist

### Static repo checks

```bash
grep -R "namespace App\\Actions;" -n app/Actions/CheckForScheduledSpeedtests.php
grep -R "class CheckForScheduledSpeedtests" -n app
grep -R "class Result" -n app
grep -R "speedtest-lite.bind_option\|--interface=.*speedtest.interface\|--ip" -n app config
```

### Build/runtime checks

```bash
docker run --rm --platform linux/arm64 your_docker_username/speedtest-tracker:multi-isp-exp-arm64 php -m | grep -E 'intl|mbstring|pdo_sqlite|pcntl|sockets'
```

Inside the running container:

```bash
php artisan about
php artisan migrate:status
php artisan schedule:list
php artisan speedtest-lite:validate-isp-profiles
ip -o addr show dev eth0
speedtest --help | grep -E -- '--ip|--interface' || true
```

Manual test vectors:

```bash
php artisan speedtest-lite:run-isp-profile isp1
php artisan speedtest-lite:run-isp-profile isp2
```

SQLite validation:

```bash
sqlite3 /config/database/database.sqlite 'PRAGMA journal_mode;'
sqlite3 /config/database/database.sqlite 'PRAGMA synchronous;'
sqlite3 /config/database/database.sqlite 'select id, isp_profile_key, isp_profile_name, source_ip, status, created_at from results order by id desc limit 10;'
```

Expected SQLite values:

```text
wal
1
```

Expected profile metadata result:

```text
isp1 | ISP 1 | 192.168.99.250
isp2 | ISP 2 | 192.168.99.251
```

---

## 10. Known risks / QA focus

1. **Source binding must be proven on the bundled Ookla CLI.**  
   v1.2 patches the app so `--ip` or `--interface` can be selected. QA must confirm which flag works in the actual image.

2. **Result tagging assumes synchronous execution.**  
   The Lite build uses `QUEUE_CONNECTION=sync`. If upstream behavior changes to async execution, the command may return before a result is created.

3. **UI filtering is not included.**  
   The database metadata is prepared, but the dashboard still needs a UI pass.

4. **Do not run all ISP profiles simultaneously.**  
   On hAP ax³, stagger schedules by at least 5 to 7 minutes.

5. **96 MB remains a test target, not a guarantee.**  
   One container is more efficient than two, but Laravel + Filament + Nginx + PHP-FPM + cron + active Ookla test may still exceed 96 MB during execution.

6. **RouterOS multi-address VETH syntax must be verified.**  
   The design assumes RouterOS can expose multiple VETH IPs to the container. Validate with `ip -o addr show dev eth0` before trusting profile routing.

7. **Dockerfile must preserve v0.9 packages.**  
   Do not drop `sqlite-libs`, `ca-certificates`, or `tzdata` when adding `iproute2`.

---

## 11. v1.2 final summary for reviewer

The v1.2 refactor keeps the v1.1 architecture but corrects the code package against the actual current upstream branch. The critical namespace is `App\Actions\CheckForScheduledSpeedtests`. The existing Ookla job hardcodes `--interface`, so v1.2 adds a small configurable bind-option patch to support `--ip` source binding tests. The Dockerfile must remain based on the accepted v0.9 runtime package baseline with `iproute2` added. The scheduler patch should preserve existing upstream maintenance tasks and only replace the scheduled speedtest block.

Status: **accepted for code assembly and QA testing**.  
Status is **not production-final** until source binding, result tagging, RouterOS VETH visibility, and memory behavior are validated on the hAP ax³.

---

## 12. Iteration log

### v1.1

Initial multi-ISP experimental archive. Accepted architecture, but code package contained namespace, Dockerfile, scheduler replacement, and binding-option assumptions requiring correction.

### v1.2

Corrected code package after checking the current public branch:

- Changed action import to `App\Actions\CheckForScheduledSpeedtests`.
- Added required `RunSpeedtestJob.php` binding-option patch.
- Preserved v0.9 Dockerfile runtime package baseline and added only `iproute2`.
- Changed `routes/console.php` guidance to patch only the scheduled speedtest block.
- Downgraded final status wording from production-ready to QA/testing-ready.
- Marked exact RouterOS VETH multi-address CLI syntax as validation-required.
