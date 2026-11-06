<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../Common/EnvLoader.php';

final class Connection
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = EnvLoader::get('DB_HOST', '127.0.0.1') ?? '127.0.0.1';
        $port = EnvLoader::get('DB_PORT', '3306') ?? '3306';
        $database = EnvLoader::get('DB_DATABASE', 'crud_usuarios') ?? 'crud_usuarios';
        $username = EnvLoader::get('DB_USERNAME', 'Estudiante') ?? 'Estudiante';
        $password = EnvLoader::get('DB_PASSWORD', 'Developer') ?? 'Developer';
        $charset = EnvLoader::get('DB_CHARSET', 'utf8mb4') ?? 'utf8mb4';
        $sslCa = EnvLoader::get('DB_SSL_CA', '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        );

        if ($sslCa !== null && trim($sslCa) !== '' && defined('PDO::MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = trim($sslCa);
        }

        self::$instance = new PDO($dsn, $username, $password, $options);

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
