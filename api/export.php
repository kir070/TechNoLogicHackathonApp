<?php
require_once '../includes/config.php';

$type   = $_GET['type']   ?? 'schedule';   // schedule | room | teacher
$format = $_GET['format'] ?? 'csv';         // csv | pdf
$filterId = (int)($_GET['id'] ?? 0);

$db = getDB();

function formatTimeFn($t) {
    if (!$t) return '';
    [$h, $m] = explode(':', $t);
    $hr = (int)$h;
    return ($hr % 12 ?: 12) . ':' . $m . ' ' . ($hr >= 12 ? 'PM' : 'AM');
}

// ─── SCHEDULE EXPORT ─────────────────────────
if ($type === 'schedule') {
    $params = [];
    $sql = "SELECT s.*, p.first_name, p.last_name, p.employee_id,
                   sub.code AS subject_code, sub.name AS subject_name,
                   sub.units, sub.year_level, sub.section,
                   r.name AS room_name, r.room_type, c.name AS college_name
            FROM schedules s
            JOIN professors p ON s.professor_id = p.id
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN rooms r ON s.room_id = r.id
            LEFT JOIN colleges c ON p.college_id = c.id
            WHERE s.is_active = 1";
    if ($filterId) { $sql .= " AND s.professor_id = ?"; $params[] = $filterId; }
    $sql .= " ORDER BY s.day_of_week, s.start_time";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="schedule_export_'.date('Ymd').'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Day','Start Time','End Time','Subject Code','Subject Name','Units','Year','Section','Professor','Employee ID','Room','Room Type','College']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['day_of_week'],
                formatTimeFn($r['start_time']),
                formatTimeFn($r['end_time']),
                $r['subject_code'],
                $r['subject_name'],
                $r['units'],
                'Year '.$r['year_level'],
                $r['section'],
                $r['last_name'].', '.$r['first_name'],
                $r['employee_id'],
                $r['room_name'],
                $r['room_type'],
                $r['college_name']
            ]);
        }
        fclose($out);
        exit;
    }

    // PDF via HTML print
    if ($format === 'pdf') {
        header('Content-Type: text/html');
        $title = $filterId ? 'Professor Schedule' : 'Full Schedule Export';
        echo generatePDFHtml($title, $rows, 'schedule');
        exit;
    }
}

// ─── ROOM EXPORT ──────────────────────────────
if ($type === 'room') {
    $params = [];
    $sql = "SELECT s.*, r.name AS room_name, r.room_type,
                   p.first_name, p.last_name,
                   sub.code AS subject_code, sub.name AS subject_name, sub.units
            FROM schedules s
            JOIN rooms r ON s.room_id = r.id
            JOIN professors p ON s.professor_id = p.id
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE s.is_active = 1";
    if ($filterId) { $sql .= " AND s.room_id = ?"; $params[] = $filterId; }
    $sql .= " ORDER BY r.name, s.day_of_week, s.start_time";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="room_schedule_'.date('Ymd').'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Room','Type','Day','Start','End','Subject Code','Subject Name','Units','Professor']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['room_name'], $r['room_type'],
                $r['day_of_week'],
                formatTimeFn($r['start_time']), formatTimeFn($r['end_time']),
                $r['subject_code'], $r['subject_name'], $r['units'],
                $r['last_name'].', '.$r['first_name']
            ]);
        }
        fclose($out);
        exit;
    }

    if ($format === 'pdf') {
        header('Content-Type: text/html');
        echo generatePDFHtml('Room Schedule Export', $rows, 'room');
        exit;
    }
}

// ─── PDF HTML GENERATOR ───────────────────────
function generatePDFHtml($title, $rows, $type) {
    $dayOrder = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6];
    usort($rows, fn($a,$b) => ($dayOrder[$a['day_of_week']]??9) - ($dayOrder[$b['day_of_week']]??9) ?: strcmp($a['start_time'],$b['start_time']));

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <title>'.$title.'</title>
    <style>
        body{font-family:Arial,sans-serif;font-size:12px;color:#1a1a2e;margin:20px;}
        h1{font-size:18px;margin-bottom:4px;color:#1e3a5f;}
        .meta{color:#666;font-size:11px;margin-bottom:16px;}
        table{width:100%;border-collapse:collapse;margin-bottom:20px;}
        th{background:#1e3a5f;color:white;padding:8px 10px;text-align:left;font-size:11px;text-transform:uppercase;}
        td{padding:7px 10px;border-bottom:1px solid #e0e0e0;font-size:11px;}
        tr:nth-child(even){background:#f5f8ff;}
        .day-header{background:#e8f0fe;font-weight:bold;color:#1e3a5f;padding:6px 10px;}
        @media print{body{margin:0;}button{display:none;}}
    </style>
    </head><body>
    <h1>'.$title.'</h1>
    <div class="meta">Generated: '.date('F j, Y g:i A').' &nbsp;|&nbsp; Semester: '.CURRENT_SEMESTER.' &nbsp;|&nbsp; SY: '.CURRENT_SY.'</div>
    <button onclick="window.print()" style="background:#1e3a5f;color:white;padding:8px 18px;border:none;border-radius:4px;cursor:pointer;margin-bottom:16px;">🖨 Print / Save PDF</button>
    <table><thead><tr>';

    if ($type === 'schedule') {
        $html .= '<th>Day</th><th>Time</th><th>Subject</th><th>Units</th><th>Section</th><th>Professor</th><th>Room</th>';
    } else {
        $html .= '<th>Room</th><th>Day</th><th>Time</th><th>Subject</th><th>Units</th><th>Professor</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($rows as $r) {
        $html .= '<tr>';
        if ($type === 'schedule') {
            $html .= '<td>'.$r['day_of_week'].'</td>
                      <td>'.formatTimeFn($r['start_time']).'–'.formatTimeFn($r['end_time']).'</td>
                      <td><strong>'.$r['subject_code'].'</strong><br>'.$r['subject_name'].'</td>
                      <td>'.$r['units'].'</td>
                      <td>Y'.$r['year_level'].'-'.$r['section'].'</td>
                      <td>'.$r['last_name'].', '.$r['first_name'].'</td>
                      <td>'.$r['room_name'].'</td>';
        } else {
            $html .= '<td><strong>'.$r['room_name'].'</strong> ('.$r['room_type'].')</td>
                      <td>'.$r['day_of_week'].'</td>
                      <td>'.formatTimeFn($r['start_time']).'–'.formatTimeFn($r['end_time']).'</td>
                      <td>'.$r['subject_code'].' — '.$r['subject_name'].'</td>
                      <td>'.$r['units'].'</td>
                      <td>'.$r['last_name'].', '.$r['first_name'].'</td>';
        }
        $html .= '</tr>';
    }

    $html .= '</tbody></table></body></html>';
    return $html;
}

echo json_encode(['error' => 'Invalid parameters']);
