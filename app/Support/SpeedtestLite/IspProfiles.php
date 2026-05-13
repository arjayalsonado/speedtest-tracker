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
