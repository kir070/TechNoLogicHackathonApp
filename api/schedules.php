<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'list';

    // ───────────────── 1. DELETE ─────────────────
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) throw new Exception('Invalid ID.');

        $stmt = $db->prepare("UPDATE schedules SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ───────────────── 2. LIST (GET) ─────────────────
    if ($method === 'GET' && $action === 'list') {
        $sql = "SELECT s.*, 
                       p.first_name, p.last_name, 
                       sub.name AS subject_name,
                       sub.code AS subject_code, 
                       sub.units,
                       r.name AS room_name
                FROM schedules s
                LEFT JOIN professors p ON s.professor_id = p.id
                LEFT JOIN subjects sub ON s.subject_id = sub.id
                LEFT JOIN rooms r ON s.room_id = r.id
                WHERE s.is_active = 1 
                ORDER BY s.day_of_week, s.start_time";

        $stmt = $db->query($sql);
        echo json_encode(['schedules' => $stmt->fetchAll()]);
        exit;
    }

    // ───────────────── 3. AUTO MATCH (GET) ─────────────────
    if ($method === 'GET' && $action === 'automatch') {
        $subjectId = (int)($_GET['subject_id'] ?? 0);
        $day       = $_GET['day'] ?? '';
        $startTime = $_GET['start_time'] ?? '';

        if (!$subjectId || !$day || !$startTime) throw new Exception('Missing parameters.');

        $sub = $db->prepare("SELECT units, room_type_required FROM subjects WHERE id = ?");
        $sub->execute([$subjectId]);
        $subject = $sub->fetch();

        $endTime = date('H:i:s', strtotime($startTime) + ($subject['units'] * 3600));

        $profStmt = $db->prepare("
            SELECT p.* FROM professors p
            JOIN professor_expertise pe ON p.id = pe.professor_id
            WHERE pe.subject_id = ? AND p.is_active = 1
            AND p.id NOT IN (
                SELECT professor_id FROM schedules 
                WHERE day_of_week = ? AND (start_time < ? AND end_time > ?) AND is_active = 1
            )
        ");
        $profStmt->execute([$subjectId, $day, $endTime, $startTime]);

        $roomSql = "SELECT * FROM rooms WHERE is_active = 1 AND id NOT IN (
            SELECT room_id FROM schedules 
            WHERE day_of_week = ? AND (start_time < ? AND end_time > ?) AND is_active = 1
        )";
        $roomParams = [$day, $endTime, $startTime];

        if ($subject['room_type_required'] !== 'any') {
            $roomSql .= " AND room_type = ?";
            $roomParams[] = $subject['room_type_required'];
        }

        $roomStmt = $db->prepare($roomSql);
        $roomStmt->execute($roomParams);

        echo json_encode([
            'end_time'   => $endTime,
            'professors' => $profStmt->fetchAll(),
            'rooms'      => $roomStmt->fetchAll()
        ]);
        exit;
    }

    // ───────────────── 4. SAVE (POST) ─────────────────
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception('No input provided.');

        if (($input['action'] ?? '') === 'auto_schedule') {
            $subjects = $db->query("SELECT * FROM subjects WHERE is_active=1")->fetchAll();
            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
            $timeSlots = $db->query("SELECT * FROM time_slots ORDER BY start_time")->fetchAll();

            foreach ($subjects as $subject) {
                foreach ($days as $day) {
                    foreach ($timeSlots as $slot) {
                        $start = $slot['start_time'];
                        $end = date('H:i:s', strtotime($start) + ($subject['units'] * 3600));

                        $check = $db->prepare("SELECT COUNT(*) FROM schedules WHERE day_of_week=? AND (start_time < ? AND end_time > ?) AND is_active=1");
                        $check->execute([$day, $end, $start]);
                        if ($check->fetchColumn() > 0) continue;

                        $prof = $db->prepare("SELECT p.id FROM professors p JOIN professor_expertise pe ON p.id = pe.professor_id WHERE pe.subject_id=? AND p.is_active=1 AND p.id NOT IN (SELECT professor_id FROM schedules WHERE day_of_week=? AND (start_time < ? AND end_time > ?) AND is_active=1) ORDER BY RAND() LIMIT 1");
                        $prof->execute([$subject['id'], $day, $end, $start]);
                        $professor = $prof->fetch();

                        $room = $db->prepare("SELECT id FROM rooms WHERE is_active=1 AND id NOT IN (SELECT room_id FROM schedules WHERE day_of_week=? AND (start_time < ? AND end_time > ?) AND is_active=1) LIMIT 1");
                        $room->execute([$day, $end, $start]);
                        $roomData = $room->fetch();

                        if ($professor && $roomData) {
                            $db->prepare("INSERT INTO schedules (subject_id, day_of_week, start_time, end_time, professor_id, room_id, is_active) VALUES (?,?,?,?,?,?,1)")
                               ->execute([$subject['id'], $day, $start, $end, $professor['id'], $roomData['id']]);
                            break 2; 
                        }
                    }
                }
            }
            echo json_encode(['success' => true]);
            exit;
        }

        $id = (int)($input['id'] ?? 0);
        $profId = (int)$input['professor_id'];
        $subId = (int)$input['subject_id'];
        $roomId = (int)$input['room_id'];
        $day = $input['day_of_week'];
        $start = $input['start_time'];

        $sub = $db->prepare("SELECT units FROM subjects WHERE id = ?");
        $sub->execute([$subId]);
        $units = $sub->fetchColumn();
        $end = date('H:i:s', strtotime($start) + ($units * 3600));

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE schedules SET professor_id=?, subject_id=?, room_id=?, day_of_week=?, start_time=?, end_time=?, is_active=1 WHERE id=?");
            $stmt->execute([$profId, $subId, $roomId, $day, $start, $end, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO schedules (professor_id, subject_id, room_id, day_of_week, start_time, end_time, is_active) VALUES (?,?,?,?,?,?,1)");
            $stmt->execute([$profId, $subId, $roomId, $day, $start, $end]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}