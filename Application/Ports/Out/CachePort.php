<?php

declare(strict_types=1);

interface CachePort
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttlSeconds = 60): void;

    public function delete(string $key): void;

    public function clearPrefix(string $prefix): void;
}
