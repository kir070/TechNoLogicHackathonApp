<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$host  = $input['host'] ?? 'localhost';
$user  = $input['user'] ?? 'root';
$pass  = $input['pass'] ?? '';
$name  = $input['name'] ?? 'scheduling_db';

$steps = [];

try {
    // Connect without DB first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $steps[] = "Connected to MySQL server";

    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$name`");
    $steps[] = "Database '$name' created/selected";

    // Run SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    // Remove USE statement since we already selected
    $sql = preg_replace('/USE\s+scheduling_db\s*;/i', '', $sql);
    $sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);

    // Split and execute statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt) $pdo->exec($stmt);
    }
    $steps[] = "Tables created successfully";

    // Update config if different from defaults
    if ($host !== 'localhost' || $user !== 'root' || $pass !== '' || $name !== 'scheduling_db') {
        $config = file_get_contents(__DIR__ . '/includes/config.php');
        $config = preg_replace("/'localhost'/", "'$host'", $config);
        $config = preg_replace("/'root'/",      "'$user'", $config);
        $config = preg_replace("/''/",          "'$pass'", $config, 1);
        $config = preg_replace("/'scheduling_db'/", "'$name'", $config);
        file_put_contents(__DIR__ . '/includes/config.php', $config);
        $steps[] = "Configuration updated";
    }

    $steps[] = "Sample data inserted (rooms, time slots, colleges)";

    echo json_encode(['success' => true, 'steps' => $steps]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
