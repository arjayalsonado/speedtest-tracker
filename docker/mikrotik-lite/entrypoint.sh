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

load_persistent_env() {
    declare -A runtime_env=()

    while IFS='=' read -r name value; do
        runtime_env["$name"]="$value"
    done < <(env)

    set -a
    # shellcheck disable=SC1091
    . "$CONFIG_DIR/.env" || true
    set +a

    # Docker/runtime-provided environment values should override persisted .env values.
    for name in "${!runtime_env[@]}"; do
        export "$name=${runtime_env[$name]}"
    done
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

# Load persistent env for shell-level validation variables while preserving Docker -e overrides.
load_persistent_env

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

# key:generate updates the .env file, but a blank APP_KEY from the template
# may still be exported in this shell. Reload without carrying that stale value.
unset APP_KEY
load_persistent_env

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
cat > /tmp/run-scheduler-once.sh <<'EOF'
#!/bin/sh
set -eu

APP_DIR="/var/www/html"
LOCK_DIR="/tmp/speedtest-lite-schedule-run.lock"
LOCK_PID_FILE="$LOCK_DIR/pid"

if ! mkdir "$LOCK_DIR" 2>/dev/null; then
    if [ -f "$LOCK_PID_FILE" ]; then
        old_pid="$(cat "$LOCK_PID_FILE" 2>/dev/null || true)"
        if [ -n "$old_pid" ] && kill -0 "$old_pid" 2>/dev/null; then
            if ps -o stat= -p "$old_pid" 2>/dev/null | grep -q Z; then
                echo "NOTICE: stale zombie schedule lock found for pid ${old_pid}; clearing it."
                rm -rf "$LOCK_DIR"
            else
                echo "NOTICE: previous schedule:run is still active for pid ${old_pid}; skipping this tick."
                exit 0
            fi
        else
            echo "NOTICE: stale schedule lock found; clearing it."
            rm -rf "$LOCK_DIR"
        fi
    else
        echo "NOTICE: schedule lock without pid found; clearing it."
        rm -rf "$LOCK_DIR"
    fi

    if ! mkdir "$LOCK_DIR" 2>/dev/null; then
        echo "NOTICE: previous schedule:run is still active; skipping this tick."
        exit 0
    fi
fi

echo "$$" > "$LOCK_PID_FILE"

cleanup() {
    rm -rf "$LOCK_DIR"
}
trap cleanup EXIT INT TERM

cd "$APP_DIR"
php artisan schedule:run --no-interaction
EOF
chmod +x /tmp/run-scheduler-once.sh

printf '%s /tmp/run-scheduler-once.sh >/proc/1/fd/1 2>/proc/1/fd/2
' "$MIKROTIK_CRON_SCHEDULE" > /tmp/crontab
crontab /tmp/crontab
crond -b -l 8

echo "==> Routing execution thread over to operational web engines..."
exec "$@"
