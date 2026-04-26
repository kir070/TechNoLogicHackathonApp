<?php
// includes/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'scheduling_db');
define('APP_NAME', 'Scheduling');
define('SCHOOL_NAME', 'University Scheduling System');
define('CURRENT_SEMESTER', '1st Semester');
define('CURRENT_SY', '2025-2026');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sanitize($val) {
    return htmlspecialchars(trim($val ?? ''), ENT_QUOTES, 'UTF-8');
}

// Generate time slots for a subject based on units
function generateTimeSlots($startTime, $units) {
    $start = strtotime($startTime);
    $end = $start + ($units * 3600);
    return [
        'start' => date('H:i:s', $start),
        'end'   => date('H:i:s', $end)
    ];
}

// Section helper: get valid sections for year level
function getSectionsForYear($yearLevel) {
    $prefix = $yearLevel * 100;
    $sections = [];
    for ($i = 1; $i <= 5; $i++) {
        $sections[] = (string)($prefix + $i);
    }
    return $sections;
}

$yearLevelMap = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
$dayColors = [
    'Monday'    => '#4f7df9',
    'Tuesday'   => '#22c55e',
    'Wednesday' => '#f59e0b',
    'Thursday'  => '#ef4444',
    'Friday'    => '#8b5cf6',
    'Saturday'  => '#06b6d4'
];
