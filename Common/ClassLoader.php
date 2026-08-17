<?php

declare(strict_types=1);

final class ClassLoader
{
    private static bool $registered = false;

    /** @var array<string, string>|null */
    private static ?array $classMap = null;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        spl_autoload_register(array(self::class, 'autoload'));
        self::$registered = true;
    }

    public static function autoload(string $className): void
    {
        if (self::$classMap === null) {
            self::buildClassMap();
        }

        $shortName = basename(str_replace('\\', '/', $className));
        if (isset(self::$classMap[$shortName]) && is_file(self::$classMap[$shortName])) {
            require_once self::$classMap[$shortName];
        }
    }

    private static function buildClassMap(): void
    {
        self::$classMap = array();
        $rootDir = dirname(__DIR__);
        $directories = array(
            $rootDir . '/Domain',
            $rootDir . '/Application',
            $rootDir . '/Common',
            $rootDir . '/Infrastructure',
        );

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                    $classKey = $file->getBasename('.php');
                    self::$classMap[$classKey] = $file->getRealPath();
                }
            }
        }
    }
}
