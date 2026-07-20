<?php
class Database
{
    private static ?PDO $instance = null;

    private static function getConfig(): array
    {
        $host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: '127.0.0.1';
        $dbname = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'siakad_mini';
        $user = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: '';

        return [
            'host' => $host,
            'dbname' => $dbname,
            'user' => $user,
            'pass' => $pass,
        ];
    }

    private static function getOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    private static function buildDsn(array $config, bool $withDatabase = true): string
    {
        $dsn = 'mysql:host=' . $config['host'] . ';charset=utf8mb4';
        if ($withDatabase) {
            $dsn .= ';dbname=' . $config['dbname'];
        }

        return $dsn;
    }

    private static function createDatabaseIfNeeded(array $config): void
    {
        $pdo = new PDO(self::buildDsn($config, false), $config['user'], $config['pass'], self::getOptions());
        $escapedName = str_replace('`', '``', $config['dbname']);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $escapedName . '`');
    }

    private static function ensureSchema(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->fetch()) {
            return;
        }

        $seedCandidates = [
            dirname(__DIR__) . '/database/seed.sql',
            dirname(__DIR__) . '/seed.sql',
        ];

        $seedPath = null;
        foreach ($seedCandidates as $candidate) {
            if (is_file($candidate)) {
                $seedPath = $candidate;
                break;
            }
        }

        if ($seedPath === null) {
            return;
        }

        $sql = file_get_contents($seedPath);
        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            if (preg_match('/^\s*(CREATE\s+DATABASE|USE)\b/i', $statement)) {
                continue;
            }

            $pdo->exec($statement);
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = self::getConfig();

            try {
                self::createDatabaseIfNeeded($config);
                self::$instance = new PDO(self::buildDsn($config, true), $config['user'], $config['pass'], self::getOptions());
                self::ensureSchema(self::$instance);
            } catch (PDOException $e) {
                error_log($e->getMessage());
                throw new RuntimeException(
                    'Koneksi database gagal. Periksa server MySQL, host, user, password, dan nama database. Detail: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        return self::$instance;
    }
}
