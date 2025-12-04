<?php
header('Content-Type: text/plain');


$config = [
    'host' => 'mysql-ruu2',
    'user' => 'user',
    'pass' => 'pass',
    'db' => 'symfony_app'
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']}",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== USER TABLE DATA ===\n\n";

    $count = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
    echo "Total users: $count\n\n";
    

    $users = $pdo->query("SELECT * FROM user ORDER BY id DESC LIMIT 10")->fetchAll();
    
    if (empty($users)) {
        echo "No users found in database\n";
    } else {
        echo "Last 10 users:\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($users as $user) {
            echo "ID: {$user['id']}\n";
            echo "Email: {$user['email']}\n";
            echo "Name: " . ($user['name'] ?? 'NULL') . "\n";
            echo "Status: {$user['status']}\n";
            echo "Verified: " . ($user['is_verified'] ? 'YES' : 'NO') . "\n";
            echo "Last Login: " . ($user['last_login'] ?? 'Never') . "\n";
            echo "Roles: " . $user['roles'] . "\n";
            echo str_repeat("-", 80) . "\n";
        }
    }
    

    echo "\n=== STATISTICS ===\n";
    
    $stats = $pdo->query("
        SELECT 
            status,
            is_verified,
            COUNT(*) as count
        FROM user 
        GROUP BY status, is_verified
        ORDER BY status, is_verified
    ")->fetchAll();
    
    foreach ($stats as $stat) {
        echo "Status: {$stat['status']}, Verified: {$stat['is_verified']} -> {$stat['count']} users\n";
    }
    

    echo "\n=== INDEX USAGE ANALYSIS ===\n";
    

    $explain = $pdo->query("EXPLAIN SELECT * FROM user WHERE email = 'test@example.com'")->fetch();
    
    echo "Query: SELECT * FROM user WHERE email = 'test@example.com'\n";
    echo "Possible keys: " . $explain['possible_keys'] . "\n";
    echo "Key used: " . $explain['key'] . "\n";
    echo "Rows examined: " . $explain['rows'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
