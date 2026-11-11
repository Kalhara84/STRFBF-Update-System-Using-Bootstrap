<?php
// Change database connection settings only here.
// Every caller, including AJAX functions, shares one connection per request.
function db_connection()
{
    static $connection = null;

    if ($connection === null) {
        $host = 'localhost';
        $port = 3306;
        $user = 'root';
        $pass = 'mypass';
        $database = 'ctecomco_strbf';

        // Preserve the application's existing false/or-die query error handling.
        mysqli_report(MYSQLI_REPORT_OFF);
        $connection = mysqli_connect($host, $user, $pass, $database, $port);
        if (!$connection) {
            error_log('Database connection failed: ' . mysqli_connect_error());
            die('Database connection failed. Please contact the administrator.');
        }
    }

    return $connection;
}

// Compatibility names used by the existing login and approval pages.
$conn = db_connection();
$connect = $conn;
$rConn = $conn;
