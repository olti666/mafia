<?php

class Database {
    private static $db;

    public static function connect() {
        if (!self::$db) {
            $host = 'localhost';
            $username = 'root';
            $password = '';
            $database = 'mafia_game';

            self::$db = new mysqli($host, $username, $password, $database);

            if (self::$db->connect_error) {
                die("Connection failed: " . self::$db->connect_error);
            }
        }
        return self::$db;
    }

    public static function query($sql) {
        $db = self::connect();
        return $db->query($sql);
    }
}