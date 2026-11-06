<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Application/Ports/Out/CachePort.php';

final class FileCacheAdapter implements CachePort
{
    private string $cacheDir;

    /** @var array<string, array{expires: int, value: mixed}> */
    private array $runtimeStore = array();

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? dirname(__DIR__, 3) . '/storage/cache';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }
    }

    public function get(string $key): mixed
    {
        if (isset($this->runtimeStore[$key])) {
            if ($this->runtimeStore[$key]['expires'] >= time()) {
                return $this->runtimeStore[$key]['value'];
            }
            unset($this->runtimeStore[$key]);
        }

        $file = $this->filePath($key);
        if (!is_file($file)) {
            return null;
        }

        $raw = @file_get_contents($file);
        if ($raw === false) {
            return null;
        }

        $decoded = @unserialize($raw);
        if (!is_array($decoded) || !isset($decoded['expires'], $decoded['key'])) {
            return null;
        }

        if ((int) $decoded['expires'] < time()) {
            @unlink($file);
            return null;
        }

        $this->runtimeStore[$key] = array(
            'expires' => (int) $decoded['expires'],
            'value' => $decoded['value'] ?? null,
        );

        return $decoded['value'] ?? null;
    }

    public function set(string $key, mixed $value, int $ttlSeconds = 60): void
    {
        $expires = time() + max(1, $ttlSeconds);
        $this->runtimeStore[$key] = array(
            'expires' => $expires,
            'value' => $value,
        );

        $payload = serialize(array(
            'key' => $key,
            'expires' => $expires,
            'value' => $value,
        ));

        @file_put_contents($this->filePath($key), $payload, LOCK_EX);
    }

    public function delete(string $key): void
    {
        unset($this->runtimeStore[$key]);
        $file = $this->filePath($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    public function clearPrefix(string $prefix): void
    {
        foreach (array_keys($this->runtimeStore) as $key) {
            if (str_starts_with($key, $prefix)) {
                unset($this->runtimeStore[$key]);
            }
        }

        $files = glob($this->cacheDir . '/*.cache');
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            $raw = @file_get_contents($file);
            if ($raw === false) {
                continue;
            }
            $decoded = @unserialize($raw);
            if (is_array($decoded) && isset($decoded['key']) && str_starts_with((string) $decoded['key'], $prefix)) {
                @unlink($file);
            }
        }
    }

    private function filePath(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
