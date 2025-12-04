<?php
// db-test.php
header('Content-Type: text/plain');

echo "Testing MySQL connection from web service...\n\n";


$host = 'mysql-ruu2.internal'; 
$port = 3306;

$username = $_ENV['MYSQLUSER'] ?? 'default_user';
$password = $_ENV['MYSQLPASSWORD'] ?? '';
$database = $_ENV['MYSQLDATABASE'] ?? 'defaultdb';

echo "Connection details:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "User: $username\n";
echo "Database: $database\n\n";

try {

    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "✅ SUCCESS: Connected to MySQL!\n\n";
    

    echo "=== Available Databases ===\n";
    $stmt = $pdo->query("SHOW DATABASES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
    }
    
    echo "\n=== Current User ===\n";
    $stmt = $pdo->query("SELECT CURRENT_USER()");
    echo $stmt->fetchColumn() . "\n";
    
    echo "\n=== MySQL Version ===\n";
    $stmt = $pdo->query("SELECT VERSION()");
    echo $stmt->fetchColumn() . "\n";
    
    echo "\n=== Show Tables ===\n";
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) > 0) {
            foreach ($tables as $table) {
                echo "- $table\n";
            }
        } else {
            echo "No tables in database '$database'\n";
        }
    } catch (Exception $e) {
        echo "Could not show tables: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    
    echo "Troubleshooting steps:\n";
    echo "1. Check if MySQL service is running\n";
    echo "2. Check environment variables in Render dashboard\n";
    echo "3. Verify internal hostname: $host\n";
    

    echo "\n=== Environment Variables ===\n";
    foreach ($_ENV as $key => $value) {
        if (stripos($key, 'MYSQL') !== false) {
            $displayValue = (stripos($key, 'PASS') !== false) ? '***HIDDEN***' : $value;
            echo "$key: $displayValue\n";
        }
    }
}
?>
