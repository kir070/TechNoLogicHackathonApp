<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'list';

    /* -------------------- LIST -------------------- */
    if ($method === 'GET' && $action === 'list') {
        $search = '%' . ($_GET['q'] ?? '') . '%';
        $college = $_GET['college'] ?? '';

        $params = [$search, $search];

        $sql = "SELECT p.*, c.name AS college_name 
                FROM professors p
                LEFT JOIN colleges c ON p.college_id = c.id
                WHERE p.is_active = 1 
                AND (p.first_name LIKE ? OR p.last_name LIKE ?)";

        if ($college) {
            $sql .= " AND p.college_id = ?";
            $params[] = $college;
        }

        $sql .= " ORDER BY p.last_name";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['professors' => $stmt->fetchAll()]);
        exit;
    }

    /* -------------------- SINGLE -------------------- */
    if ($method === 'GET' && $action === 'single') {
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("
            SELECT p.*, c.name AS college_name 
            FROM professors p
            LEFT JOIN colleges c ON p.college_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $prof = $stmt->fetch();

        if ($prof) {
            $exp = $db->prepare("
                SELECT sub.id, sub.code, sub.name 
                FROM professor_expertise pe
                JOIN subjects sub ON pe.subject_id = sub.id
                WHERE pe.professor_id = ?
            ");
            $exp->execute([$id]);
            $prof['expertise'] = $exp->fetchAll();
        }

        echo json_encode(['professor' => $prof]);
        exit;
    }

    /* -------------------- SAVE -------------------- */
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception('Invalid JSON input');
        }

        $id = (int)($input['id'] ?? 0);
        $fn = sanitize($input['first_name'] ?? '');
        $ln = sanitize($input['last_name'] ?? '');
        $email = sanitize($input['email'] ?? '');
        $phone = sanitize($input['phone'] ?? '');
        $college = (int)($input['college_id'] ?? 0);
        $spec = sanitize($input['specialization'] ?? '');
        $maxU = (int)($input['max_units'] ?? 21);
        $employment_type = sanitize($input['employment_type'] ?? '');

        if (!$fn || !$ln) {
            echo json_encode(['success' => false, 'error' => 'First and Last name are required']);
            exit;
        }

        // Start transaction to ensure both Professor and Expertise save correctly
        $db->beginTransaction();

        if ($id > 0) {
            /* -------------------- UPDATE -------------------- */
            $stmt = $db->prepare("
                UPDATE professors SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    college_id = ?,
                    specialization = ?,
                    max_units = ?,
                    employment_type = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $fn, $ln, $email, $phone, 
                $college, $spec, $maxU, 
                $employment_type, $id
            ]);
        } else {
            /* -------------------- INSERT -------------------- */
            $stmt = $db->prepare("
                INSERT INTO professors
                (first_name, last_name, email, phone, college_id, specialization, max_units, employment_type, is_active)
                VALUES (?,?,?,?,?,?,?,?, 1)
            ");

            $stmt->execute([
                $fn, $ln, $email, $phone, 
                $college, $spec, $maxU, 
                $employment_type
            ]);

            $id = $db->lastInsertId();
        }

        /* -------------------- EXPERTISE -------------------- */
        if (isset($input['expertise']) && is_array($input['expertise'])) {
            // Remove old expertise first
            $db->prepare("DELETE FROM professor_expertise WHERE professor_id = ?")
               ->execute([$id]);

            // Add new expertise
            $insExp = $db->prepare("
                INSERT IGNORE INTO professor_expertise (professor_id, subject_id)
                VALUES (?, ?)
            ");
            
            foreach ($input['expertise'] as $subId) {
                if ((int)$subId > 0) {
                    $insExp->execute([$id, (int)$subId]);
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    /* -------------------- DELETE -------------------- */
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        $db->prepare("UPDATE professors SET is_active = 0 WHERE id = ?")
           ->execute([$id]);

        echo json_encode(['success' => true]);
        exit;
    }

} catch (Exception $e) {
    // If something goes wrong, rollback changes and send the error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false, 
        'error' => 'Server Error: ' . $e->getMessage()
    ]);
}