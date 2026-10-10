<?php
/** Koneksi PDO dengan pola Singleton. */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    private function __clone() {}
    public function __wakeup() { throw new Exception('Singleton tidak boleh di-unserialize.'); }

    public static function getInstance(): Database
    {
        return self::$instance ??= new Database();
    }

    public function pdo(): PDO { return $this->pdo; }
}
