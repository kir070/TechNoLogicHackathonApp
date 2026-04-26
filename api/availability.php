<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

/* ================= GET AVAILABILITY ================= */
if ($method === 'GET') {

    $professor_id = (int)($_GET['professor_id'] ?? 0);

    $stmt = $db->prepare("
        SELECT * FROM professor_availability
        WHERE professor_id = ?
        ORDER BY FIELD(day_of_week,
            'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'
        ), start_time
    ");

    $stmt->execute([$professor_id]);

    echo json_encode([
        'availability' => $stmt->fetchAll()
    ]);
    exit;
}

/* ================= ADD AVAILABILITY ================= */
if ($method === 'POST') {

    $input = json_decode(file_get_contents('php://input'), true);

    /* ================= DELETE ================= */
    if (isset($input['action']) && $input['action'] === 'delete') {

        $stmt = $db->prepare("DELETE FROM professor_availability WHERE id = ?");
        $stmt->execute([$input['id']]);

        echo json_encode(['success' => true]);
        exit;
    }

    /* ================= UPDATE ================= */
    if (isset($input['action']) && $input['action'] === 'update') {

        $stmt = $db->prepare("
            UPDATE professor_availability
            SET day_of_week = ?, start_time = ?, end_time = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $input['day_of_week'],
            $input['start_time'],
            $input['end_time'],
            $input['id']
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    /* ================= ADD ================= */
    $professor_id = (int)($input['professor_id'] ?? 0);
    $day = $input['day_of_week'] ?? '';
    $start = $input['start_time'] ?? '';
    $end = $input['end_time'] ?? '';

    if (!$professor_id || !$day || !$start || !$end) {
        echo json_encode([
            'success' => false,
            'error' => 'Missing fields'
        ]);
        exit;
    }

    $stmt = $db->prepare("
        INSERT INTO professor_availability
        (professor_id, day_of_week, start_time, end_time)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$professor_id, $day, $start, $end]);

    echo json_encode(['success' => true]);
    exit;
}




echo json_encode(['success' => false]);