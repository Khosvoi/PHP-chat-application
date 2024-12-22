<?php
class DBConnector {
    // Ensures foreign keys are on, sets up the connection with sqlite and sets up error handling

    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $defaultDbPath = self::getDbPath();

                if (!file_exists(dirname($defaultDbPath))) {
                    mkdir(dirname($defaultDbPath), 0777, true);
                }
                error_log('Using database file: ' . realpath($defaultDbPath));
                self::$pdo = new PDO('sqlite:' . $defaultDbPath);
                self::$pdo->exec('PRAGMA foreign_keys = ON;');
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    private static function getDbPath() {
        return '/var/lib/chat-db/messagingDB.sqlite';
    }
}