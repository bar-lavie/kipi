<?php

namespace app;

include 'functions.php';

use PDO;

class DB
{
    private static $db_name = 'kipi';
    private static $db_host = 'localhost';
    private static $db_username = 'root';
    private static $db_password = '';

    private static $cont  = null;

    public function __construct()
    {
        die('Init function is not allowed');
    }
    public static function create_db()
    {
        $pdo = new PDO("mysql:host=" . self::$db_host, self::$db_username, self::$db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query("
        CREATE DATABASE IF NOT EXISTS `kipi` CHARACTER SET utf8 COLLATE utf8_general_ci;
        USE `kipi`;
        CREATE TABLE IF NOT EXISTS `kipi`.`user` ( `password` VARCHAR(56) NOT NULL ) ENGINE = InnoDB;
        CREATE TABLE IF NOT EXISTS `kipi`.`passwords` ( `order_id` INT NOT NULL , `username` VARCHAR(255) NOT NULL , `password` VARCHAR(255) NOT NULL , `alt` LONGTEXT NOT NULL ) ENGINE = InnoDB;
        ");
    }
    public static function db_exists()
    {
        $pdo = new PDO("mysql:host=" . self::$db_host, self::$db_username, self::$db_password);
        $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'kipi'");
        return (bool) $stmt->fetchColumn();
    }

    public static function user_exists()
    {
        return (bool) self::connect()->query("SELECT count(*) FROM user")->fetchColumn();
    }

    public static function connect()
    {
        if (!self::$cont) {
            try {
                self::$cont =  new PDO("mysql:host=" . self::$db_host . ";dbname=" . self::$db_name, self::$db_username, self::$db_password);
                self::$cont->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // PDO::ERRMODE_EXCEPTION
                // $connect->exec("SET CHARACTER SET utf8");
            } catch (\PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }
}
