<?php

namespace Esmat\MultiTenantPermission\Contracts;

interface Tenant
{
    public function configure(): self;
    public function use(): self;
    public static function current(): ?self;
    public static function identifyById(int $id): ?self;
    public static function identifyByDomain(string $domain): ?self;
    public static function createWithDatabase(array $attributes): self;
    public function users();
    public function getSetting(string $key, $default = null);
    public function setSetting(string $key, $value): void;
    public function clearCache(): void;
}
