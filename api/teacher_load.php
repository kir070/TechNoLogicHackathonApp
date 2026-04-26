<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
$db = getDB();

$format = $_GET['format'] ?? 'json';

$professors = $db->query("
    SELECT p.*, c.name AS college_name
    FROM professors p
    LEFT JOIN colleges c ON p.college_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.last_name
")->fetchAll();

$result = [];
foreach ($professors as $prof) {
    // Removed sub.year_level and sub.section from the SELECT statement
    $subjects = $db->prepare("
        SELECT s.*, sub.code AS subject_code, sub.name AS subject_name,
               sub.units,
               r.name AS room_name
        FROM schedules s
        JOIN subjects sub ON s.subject_id = sub.id
        JOIN rooms r ON s.room_id = r.id
        WHERE s.professor_id = ? AND s.is_active = 1
        ORDER BY s.day_of_week, s.start_time
    ");
    $subjects->execute([$prof['id']]);
    $assigned = $subjects->fetchAll();

    $totalUnits = array_sum(array_column($assigned, 'units'));

    $prof['subjects']    = $assigned;
    $prof['total_units'] = $totalUnits;
    $result[] = $prof;
}

if ($format === 'csv') {
    header('Content-Type: text/csv');
    $out = fopen('php://output', 'w');
    // Removed 'Section' from the header array
    fputcsv($out, ['Professor', 'Employee ID', 'College', 'Total Units', 'Max Units', 'Subject Code', 'Subject Name', 'Room', 'Day', 'Start', 'End']);
    foreach ($result as $p) {
        if (empty($p['subjects'])) {
            // Adjusted empty subject row to match the new column count (11 columns)
            fputcsv($out, [$p['last_name'].', '.$p['first_name'], $p['employee_id'], $p['college_name'], $p['total_units'], $p['max_units'], '', '', '', '', '', '']);
        } else {
            foreach ($p['subjects'] as $s) {
                fputcsv($out, [
                    $p['last_name'].', '.$p['first_name'],
                    $p['employee_id'],
                    $p['college_name'],
                    $p['total_units'],
                    $p['max_units'],
                    $s['subject_code'],
                    $s['subject_name'],
                    // Removed the Section concatenation (Y-Section)
                    $s['room_name'],
                    $s['day_of_week'],
                    $s['start_time'],
                    $s['end_time']
                ]);
            }
        }
    }
    fclose($out);
    exit;
}

echo json_encode(['professors' => $result]);