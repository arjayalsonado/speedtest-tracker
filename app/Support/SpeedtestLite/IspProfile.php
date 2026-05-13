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
