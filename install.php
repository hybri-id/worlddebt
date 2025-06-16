<?php
/**
 * Installation script to:
 * - create database if it does not exist
 * - create `debts` table with proper structure
 * 
 * Customize the DB credentials below before running.
 */
require_once 'db.php';

// === Connect without selecting DB ===
try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Failed to connect to database server: " . htmlspecialchars($e->getMessage()));
}

// === Create database if not exists ===
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    die("Failed to create database `$db`: " . htmlspecialchars($e->getMessage()));
}

// === Connect to the created database ===
try {
    $pdo->exec("USE `$db`");
} catch (PDOException $e) {
    die("Failed to select database `$db`: " . htmlspecialchars($e->getMessage()));
}

// === Create debts table if not exists ===
$tableSql = <<<SQL
CREATE TABLE IF NOT EXISTS debts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country VARCHAR(100) NOT NULL UNIQUE,
    debt BIGINT UNSIGNED NOT NULL DEFAULT 0,
    interest_per_second DOUBLE NOT NULL DEFAULT 0,
    population BIGINT UNSIGNED NOT NULL DEFAULT 0,
    gdp BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_updated DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    $pdo->exec($tableSql);
    echo "<h2>Success!</h2>";
    echo "<p>Database <strong>" . htmlspecialchars($db) . "</strong> and table <strong>debts</strong> are ready.</p>";
    echo '<p><a href="import_csv.php">Go to CSV Importer</a></p>';
} catch (PDOException $e) {
    die("Failed to create `debts` table: " . htmlspecialchars($e->getMessage()));
}