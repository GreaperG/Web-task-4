<?php
header('Content-Type: text/plain');

echo "=== Render MySQL Connection Test ===\n\n";

// Варианты хостов для тестирования
$possibleHosts = [
    // Попробуйте эти варианты
    'mysql-ruu2.internal',
    'mysql-ruu2',
    'localhost',
    '127.0.0.1',
    
    // Если MySQL на отдельном сервисе
    'dpg-*.internal', // замените * на реальный ID
];

// Проверка всех хостов
foreach ($possibleHosts as $host) {
    echo "Testing: $host:3306... ";
    
    $connection = @fsockopen($host, 3306, $errno, $errstr, 2);
    if (is_resource($connection)) {
        echo "✓ REACHABLE\n";
        fclose($connection);
        $workingHost = $host;
        break;
    } else {
        echo "✗ $errstr\n";
    }
}

if (!isset($workingHost)) {
    echo "\n❌ No host reachable!\n\n";
    
    echo "=== How to find correct hostname ===\n";
    echo "1. Go to Render Dashboard\n";
    echo "2. Click your MySQL service\n"; 
    echo "3. Look for 'Internal Hostname' or 'Connection' info\n";
    echo "4. It should look like: dpg-cn4tlvq1hbls73e7i4jg-a.internal\n";
    
    exit;
}

echo "\n✅ Found working host: $workingHost\n\n";

// Теперь попробуем подключиться с credentials
echo "=== Testing MySQL credentials ===\n";

// Попробуем разные комбинации credentials
$credentials = [
    [
        'user' => $_ENV['MYSQLUSER'] ?? $_SERVER['MYSQLUSER'] ?? 'user',
        'pass' => $_ENV['MYSQLPASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? '',
        'db' => $_ENV['MYSQLDATABASE'] ?? $_SERVER['MYSQLDATABASE'] ?? 'defaultdb'
    ],
    [
        'user' => 'root',
        'pass' => '',
        'db' => 'mysql'
    ],
    [
        'user' => 'admin',
        'pass' => '',
        'db' => 'mysql'
    ]
];

foreach ($credentials as $creds) {
    echo "Trying: {$creds['user']}/{$creds['db']}... ";
    
    try {
        $dsn = "mysql:host=$workingHost;port=3306;dbname={$creds['db']}";
        $pdo = new PDO($dsn, $creds['user'], $creds['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3
        ]);
        
        echo "✓ SUCCESS\n";
        
        // Покажем базы данных
        $stmt = $pdo->query("SHOW DATABASES");
        echo "\nAvailable databases:\n";
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "- {$row[0]}\n";
        }
        
        break;
    } catch (PDOException $e) {
        echo "✗ {$e->getMessage()}\n";
    }
}
?>
