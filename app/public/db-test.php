<?php
header('Content-Type: text/plain');

echo "=== Connect with found credentials ===\n\n";

$host = 'mysql-ruu2';
$user = 'user';
$password = 'pass';
$database = 'symfony_app';

echo "Trying connection:\n";
echo "Host: $host\n";
echo "User: $user\n"; 
echo "Password: ***\n";
echo "Database: $database\n\n";

try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ CONNECTED SUCCESSFULLY!\n\n";
    
    // 1. Показать все базы данных
    echo "=== ALL DATABASES ===\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($databases as $db) {
        echo "- $db\n";
    }
    
    echo "\n=== TABLES IN $database ===\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "No tables found\n";
    } else {
        foreach ($tables as $table) {
            echo "\n--- Table: $table ---\n";
            
            // Показать индексы таблицы
            $indexes = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll();
            if (!empty($indexes)) {
                echo "Indexes:\n";
                foreach ($indexes as $index) {
                    echo "  - {$index['Key_name']} ({$index['Column_name']})";
                    if ($index['Non_unique'] == 0) echo " [UNIQUE]";
                    if ($index['Index_type'] == 'FULLTEXT') echo " [FULLTEXT]";
                    echo "\n";
                }
            } else {
                echo "No indexes\n";
            }
            
            // Показать структуру таблицы
            $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetchColumn(1);
            echo "\nStructure:\n";
            echo $create . "\n";
        }
    }
    
    // 3. Создать индекс (если нужно)
    echo "\n=== CREATE INDEX EXAMPLE ===\n";
    // Пример создания индекса
    foreach ($tables as $table) {
        // Сначала посмотрим колонки
        $columns = $pdo->query("DESCRIBE `$table`")->fetchAll();
        
        echo "Table: $table\n";
        foreach ($columns as $col) {
            echo "  Column: {$col['Field']} ({$col['Type']})\n";
        }
        
        // Предложим создать индекс
        if (!empty($columns)) {
            $firstCol = $columns[0]['Field'];
            echo "  To create index: CREATE INDEX idx_{$firstCol} ON `$table` (`$firstCol`);\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    
    // Попробуем root пользователя
    echo "\n=== TRYING ROOT USER ===\n";
    try {
        $pdo = new PDO("mysql:host=$host;dbname=mysql", 'root', 'root');
        echo "✅ Connected as root!\n";
        
        $stmt = $pdo->query("SHOW DATABASES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "- {$row[0]}\n";
        }
    } catch (Exception $e2) {
        echo "Root also failed: " . $e2->getMessage() . "\n";
    }
}
?>
