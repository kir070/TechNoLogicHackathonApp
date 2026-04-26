<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
$db = getDB();
$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'status_now') {
    $todayDay = date('l');
    $allowedDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    if (!in_array($todayDay, $allowedDays)) $todayDay = 'Monday';
    $nowTime = date('H:i:s');

    $rooms = $db->query("SELECT * FROM rooms WHERE is_active=1 ORDER BY name")->fetchAll();
    $result = [];
    foreach ($rooms as $room) {
        $stmt = $db->prepare("
            SELECT s.*, p.last_name, sub.name AS subject_name, sub.code AS subject_code
            FROM schedules s
            JOIN professors p ON s.professor_id=p.id
            JOIN subjects sub ON s.subject_id=sub.id
            WHERE s.room_id=? AND s.day_of_week=? AND s.start_time<=? AND s.end_time>? AND s.is_active=1
            LIMIT 1
        ");
        $stmt->execute([$room['id'], $todayDay, $nowTime, $nowTime]);
        $curr = $stmt->fetch();
        $room['occupied'] = !!$curr;
        $room['current_class'] = $curr ? "{$curr['subject_code']} — {$curr['last_name']}" : null;
        $result[] = $room;
    }
    echo json_encode(['rooms' => $result]);
    exit;
}

if ($action === 'availability') {
    $day   = $_GET['day']  ?? date('l');
    $rooms = $db->query("SELECT * FROM rooms WHERE is_active=1 ORDER BY name")->fetchAll();
    $slots = $db->query("SELECT * FROM time_slots ORDER BY start_time")->fetchAll();

    $grid = [];
    foreach ($rooms as $room) {
        $row = ['room' => $room, 'slots' => []];
        foreach ($slots as $slot) {
            $stmt = $db->prepare("
                SELECT s.id, s.start_time, s.end_time,
                       p.last_name, p.first_name,
                       sub.name AS sub_name, sub.code AS sub_code
                FROM schedules s
                JOIN professors p ON s.professor_id=p.id
                JOIN subjects sub ON s.subject_id=sub.id
                WHERE s.room_id=? AND s.day_of_week=?
                  AND s.start_time < ? AND s.end_time > ? AND s.is_active=1
                LIMIT 1
            ");
            $stmt->execute([$room['id'], $day, $slot['end_time'], $slot['start_time']]);
            $entry = $stmt->fetch();
            $row['slots'][] = [
                'slot'  => $slot,
                'entry' => $entry ?: null
            ];
        }
        $grid[] = $row;
    }
    echo json_encode(['grid' => $grid]);
    exit;
}

if ($action === 'list') {
    $rooms = $db->query("SELECT * FROM rooms WHERE is_active=1 ORDER BY name")->fetchAll();
    echo json_encode(['rooms' => $rooms]);
    exit;
}

if ($action === 'update' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $stmt = $db->prepare("UPDATE rooms SET has_projector=?, has_computers=?, has_ac=?, capacity=?, room_type=? WHERE id=?");
    $stmt->execute([
        (int)($input['has_projector'] ?? 0),
        (int)($input['has_computers'] ?? 0),
        (int)($input['has_ac'] ?? 0),
        (int)($input['capacity'] ?? 40),
        $input['room_type'] ?? 'lecture',
        $id
    ]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
