<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

if ($method === 'GET' && $action === 'list') {
    $search   = '%' . ($_GET['q'] ?? '') . '%';
    $year     = $_GET['year'] ?? '';
    $college  = $_GET['college'] ?? '';
    $params   = [$search, $search];
    $sql = "SELECT s.*, c.name AS college_name FROM subjects s
            LEFT JOIN colleges c ON s.college_id=c.id
            WHERE s.is_active=1 AND (s.name LIKE ? OR s.code LIKE ?)";
    if ($year)    { $sql .= " AND s.year_level=?"; $params[] = $year; }
    if ($college) { $sql .= " AND s.college_id=?"; $params[] = $college; }
    
    // Removed s.section from ordering
    $sql .= " ORDER BY s.year_level, s.code"; 
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['subjects' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id    = (int)($input['id'] ?? 0);
    $code  = sanitize($input['code'] ?? '');
    $name  = sanitize($input['name'] ?? '');
    $units = (int)($input['units'] ?? 3);
    $year  = (int)($input['year_level'] ?? 1);
    // Section variable removed
    $college = (int)($input['college_id'] ?? 0);
    $roomType = $input['room_type_required'] ?? 'any';
    $reqProj  = (int)($input['requires_projector'] ?? 0);
    $reqComp  = (int)($input['requires_computers'] ?? 0);
    $reqAC    = (int)($input['requires_ac'] ?? 0);
    $desc     = sanitize($input['description'] ?? '');

    if (!$code || !$name) { echo json_encode(['error' => 'Code and Name required']); exit; }
    if ($units < 1 || $units > 9) { echo json_encode(['error' => 'Units must be 1-9']); exit; }

    // Validation for sections removed

    if ($id) {
        // Removed section=? from UPDATE
        $stmt = $db->prepare("UPDATE subjects SET code=?,name=?,units=?,year_level=?,college_id=?,room_type_required=?,requires_projector=?,requires_computers=?,requires_ac=?,description=? WHERE id=?");
        $stmt->execute([$code,$name,$units,$year,$college,$roomType,$reqProj,$reqComp,$reqAC,$desc,$id]);
    } else {
        // Check unique code
        $ck = $db->prepare("SELECT id FROM subjects WHERE code=? AND is_active=1");
        $ck->execute([$code]);
        if ($ck->fetch()) { echo json_encode(['error' => "Subject code '{$code}' already exists"]); exit; }
        
        // Removed section from INSERT columns and values
        $stmt = $db->prepare("INSERT INTO subjects (code,name,units,year_level,college_id,room_type_required,requires_projector,requires_computers,requires_ac,description) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$code,$name,$units,$year,$college,$roomType,$reqProj,$reqComp,$reqAC,$desc]);
        $id = $db->lastInsertId();
    }
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    $db->prepare("UPDATE subjects SET is_active=0 WHERE id=?")->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['subjects' => []]);