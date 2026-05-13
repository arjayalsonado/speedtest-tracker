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
