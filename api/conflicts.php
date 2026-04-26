<?php
require_once '../includes/config.php';

// Set headers early to ensure any output is treated as JSON
header('Content-Type: application/json');

// Disable error reporting from displaying on the page (prevents breaking JSON)
error_reporting(0);
ini_set('display_errors', 0);

try {
    $db = getDB();
    if (!$db) {
        throw new Exception("Database connection failed.");
    }

    // --- COUNT MODE ---
    if (isset($_GET['count'])) {
        $count = 0;

        // Room double-bookings count
        $roomConflicts = $db->query("
            SELECT COUNT(*) FROM (
                SELECT s1.id
                FROM schedules s1
                JOIN schedules s2 ON s1.room_id = s2.room_id 
                  AND s1.day_of_week = s2.day_of_week
                  AND s1.id < s2.id 
                  AND s1.start_time < s2.end_time 
                  AND s1.end_time > s2.start_time
                WHERE s1.is_active = 1 AND s2.is_active = 1
            ) t
        ")->fetchColumn();
        
        // Teacher double-bookings count
        $profConflicts = $db->query("
            SELECT COUNT(*) FROM (
                SELECT s1.id
                FROM schedules s1
                JOIN schedules s2 ON s1.professor_id = s2.professor_id 
                  AND s1.day_of_week = s2.day_of_week
                  AND s1.id < s2.id 
                  AND s1.start_time < s2.end_time 
                  AND s1.end_time > s2.start_time
                WHERE s1.is_active = 1 AND s2.is_active = 1
            ) t
        ")->fetchColumn();

        echo json_encode(['count' => (int)$roomConflicts + (int)$profConflicts]);
        exit;
    }

    // --- FULL LIST MODE ---
    $conflicts = [];

    // Room conflicts
    $stmt = $db->query("
        SELECT s1.id AS s1_id, s2.id AS s2_id,
               r.name AS room_name,
               s1.day_of_week, s1.start_time AS s1_start, s1.end_time AS s1_end,
               p1.last_name AS prof1, p2.last_name AS prof2,
               sub1.code AS sub1, sub2.code AS sub2
        FROM schedules s1
        JOIN schedules s2 ON s1.room_id = s2.room_id AND s1.day_of_week = s2.day_of_week
          AND s1.id < s2.id AND s1.start_time < s2.end_time AND s1.end_time > s2.start_time
        JOIN rooms r ON s1.room_id = r.id
        JOIN professors p1 ON s1.professor_id = p1.id
        JOIN professors p2 ON s2.professor_id = p2.id
        JOIN subjects sub1 ON s1.subject_id = sub1.id
        JOIN subjects sub2 ON s2.subject_id = sub2.id
        WHERE s1.is_active = 1 AND s2.is_active = 1
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $conflicts[] = [
            'type'  => 'room_double_book',
            'label' => 'Room Double-Booked',
            'desc'  => "Room {$row['room_name']} on {$row['day_of_week']} is double-booked: {$row['sub1']} ({$row['prof1']}) and {$row['sub2']} ({$row['prof2']})",
            'day'   => $row['day_of_week'],
            'time'  => $row['s1_start'] . '–' . $row['s1_end'],
            's1_id' => $row['s1_id'],
            's2_id' => $row['s2_id']
        ];
    }

    // Teacher conflicts
    $stmt2 = $db->query("
        SELECT s1.id AS s1_id, s2.id AS s2_id,
               p.last_name,
               s1.day_of_week, s1.start_time, s1.end_time,
               sub1.code AS sub1, sub2.code AS sub2,
               r1.name AS room1, r2.name AS room2
        FROM schedules s1
        JOIN schedules s2 ON s1.professor_id = s2.professor_id AND s1.day_of_week = s2.day_of_week
          AND s1.id < s2.id AND s1.start_time < s2.end_time AND s1.end_time > s2.start_time
        JOIN professors p ON s1.professor_id = p.id
        JOIN subjects sub1 ON s1.subject_id = sub1.id
        JOIN subjects sub2 ON s2.subject_id = sub2.id
        JOIN rooms r1 ON s1.room_id = r1.id
        JOIN rooms r2 ON s2.room_id = r2.id
        WHERE s1.is_active = 1 AND s2.is_active = 1
    ");

    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $conflicts[] = [
            'type'  => 'teacher_double_book',
            'label' => 'Teacher Double-Booked',
            'desc'  => "Prof. {$row['last_name']} scheduled for {$row['sub1']} ({$row['room1']}) and {$row['sub2']} ({$row['room2']}) simultaneously on {$row['day_of_week']}",
            'day'   => $row['day_of_week'],
            'time'  => $row['start_time'] . '–' . $row['end_time'],
            's1_id' => $row['s1_id'],
            's2_id' => $row['s2_id']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'conflicts' => $conflicts, 
        'count' => count($conflicts)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}