<?php

class Database
{
    private static string $host = 'db'; 
    private static string $dbname = 'bibliotrack'; 
    private static string $user = 'root';
    private static string $password = 'root';

    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {

            $dsn = "mysql:host=" . self::$host .
                   ";dbname=" . self::$dbname .
                   ";charset=utf8mb4";

            try {

                self::$instance = new PDO(
                    $dsn,
                    self::$user,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );

            } catch (PDOException $e) {

                http_response_code(500);

                die(
                    '<h2>Error con la conexión a la base de datos.</h2>
                    <p>' . $e->getMessage() . '</p>'
                );
            }
        }

        return self::$instance;
    }
}