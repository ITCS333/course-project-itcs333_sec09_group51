<?php
session_start();  // تمت الإضافة هنا

/**
* Assignment Management API
*
* RESTful API for CRUD on assignments + comments.
* Uses the SAME DB connection as the login system (auth/db.php).
*/
 
// ============================================================================
// HEADERS + CORS
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
 
require_once __DIR__ . '/../../auth/db.php';
 
try {
    $db = getDBConnection(); // PDO
} catch (PDOException $e) {
    sendResponse([
        'success' => false,
        'message' => 'DB connection failed: ' . $e->getMessage(),
    ], 500);
}
 
$method   = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$resource = $_GET['resource'] ?? null;
 

$rawBody = file_get_contents('php://input');
$data    = [];
 
if ($rawBody && ($method === 'POST' || $method === 'PUT' || $method === 'DELETE')) {
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
// ============================================================================
// ASSIGNMENT CRUD FUNCTIONS
// ============================================================================
 
function getAllAssignments(PDO $db)
{
    $sql    = "SELECT id, title, description, due_date, files, created_at, updated_at FROM assignments";
    $params = [];
 
    // search by title / description
    if (!empty($_GET['search'])) {
        $sql              .= " WHERE title LIKE :term OR description LIKE :term";
        $params[':term']   = '%' . $_GET['search'] . '%';
    }
 
    // sorting
    $allowedSort = ['title', 'due_date', 'created_at'];
    $sort        = $_GET['sort'] ?? null;
    $order       = strtolower($_GET['order'] ?? 'asc');
 
    if ($sort && validateAllowedValue($sort, $allowedSort)) {
        $sql .= " ORDER BY {$sort} " . ($order === 'desc' ? 'DESC' : 'ASC');
    }
 
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
 
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    foreach ($rows as &$row) {
        $row['files'] = $row['files'] ? json_decode($row['files'], true) : [];
        $row['dueDate'] = $row['due_date'];
    }
 
    sendResponse([
        'success' => true,
        'data'    => $rows,
    ]);
}
 
function getAssignmentById(PDO $db, $assignmentId)
{
    if (!$assignmentId) {
        sendResponse([
            'success' => false,
            'message' => 'Assignment ID is required.',
        ], 400);
    }
 
    $stmt = $db->prepare("
        SELECT id, title, description, due_date, files, created_at, updated_at
        FROM assignments
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $assignmentId]);
 
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if (!$row) {
        sendResponse([
            'success' => false,
            'message' => 'Assignment not found.',
        ], 404);
    }
 
    $row['files'] = $row['files'] ? json_decode($row['files'], true) : [];
 
    sendResponse([
        'success' => true,
        'data'    => $row,
    ]);
}
 
function createAssignment(PDO $db, array $data)
{
    if (empty($data['title']) || empty($data['description']) || empty($data['due_date'])) {
        sendResponse([
            'success' => false,
            'message' => 'title, description, and due_date are required.',
        ], 400);
    }
 
    $title       = sanitizeInput($data['title']);
    $description = sanitizeInput($data['description']);
    $dueDate     = sanitizeInput($data['due_date']);
 
    if (!validateDate($dueDate)) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid date format. Use YYYY-MM-DD.',
        ], 400);
    }
 
    $files = [];
    if (isset($data['files']) && is_array($data['files'])) {
        $files = $data['files'];
    }
    $filesJson = json_encode($files);
 
    $sql = "
        INSERT INTO assignments (title, description, due_date, files, created_at, updated_at)
        VALUES (:title, :description, :due_date, :files, NOW(), NOW())
    ";
 
    $stmt = $db->prepare($sql);
    $ok   = $stmt->execute([
        ':title'       => $title,
        ':description' => $description,
        ':due_date'    => $dueDate,
        ':files'       => $filesJson,
    ]);
 
    if (!$ok) {
        sendResponse([
            'success' => false,
            'message' => 'Failed to create assignment.',
        ], 500);
    }
 
    $id = $db->lastInsertId();
 
    getAssignmentById($db, $id); 
}
 
function updateAssignment(PDO $db, array $data)
{
    if (empty($data['id'])) {
        sendResponse([
            'success' => false,
            'message' => 'Assignment ID is required for update.',
        ], 400);
    }
 
    $id = (int) $data['id'];
 
    $check = $db->prepare("SELECT id FROM assignments WHERE id = :id LIMIT 1");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        sendResponse([
            'success' => false,
            'message' => 'Assignment not found.',
        ], 404);
    }
 
    $fields = [];
    $params = [':id' => $id];
 
    if (isset($data['title'])) {
        $fields[]       = "title = :title";
        $params[':title'] = sanitizeInput($data['title']);
    }
 
    if (isset($data['description'])) {
        $fields[]            = "description = :description";
        $params[':description'] = sanitizeInput($data['description']);
    }
 
    if (isset($data['due_date'])) {
        $dueDate = sanitizeInput($data['due_date']);
        if (!validateDate($dueDate)) {
            sendResponse([
                'success' => false,
                'message' => 'Invalid date format. Use YYYY-MM-DD.',
            ], 400);
        }
        $fields[]          = "due_date = :due_date";
        $params[':due_date'] = $dueDate;
    }
 
    if (isset($data['files']) && is_array($data['files'])) {
        $fields[]        = "files = :files";
        $params[':files'] = json_encode($data['files']);
    }
 
    if (empty($fields)) {
        sendResponse([
            'success' => false,
            'message' => 'No fields to update.',
        ], 400);
    }
 
    $sql  = "UPDATE assignments SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
 
    if ($stmt->rowCount() === 0) {
        sendResponse([
            'success' => false,
            'message' => 'No changes applied.',
        ], 200);
    } else {
        sendResponse([
            'success' => true,
            'message' => 'Assignment updated successfully.',
        ]);
    }
}
 
function deleteAssignment(PDO $db, $assignmentId)
{
    if (!$assignmentId) {
        sendResponse([
            'success' => false,
            'message' => 'Assignment ID is required.',
        ], 400);
    }
    $stmtComments = $db->prepare("DELETE FROM comments WHERE assignment_id = :id");
    $stmtComments->execute([':id' => $assignmentId]);
 
    $stmt = $db->prepare("DELETE FROM assignments WHERE id = :id");
    $stmt->execute([':id' => $assignmentId]);
 
    if ($stmt->rowCount() === 0) {
        sendResponse([
            'success' => false,
            'message' => 'Assignment not found or already deleted.',
        ], 404);
    }
 
    sendResponse([
        'success' => true,
        'message' => 'Assignment deleted successfully.',
    ]);
}
 
function getCommentsByAssignment(PDO $db, $assignmentId)
{
    if (!$assignmentId) {
        sendResponse([
            'success' => false,
            'message' => 'assignment_id is required.',
        ], 400);
    }
 
    $stmt = $db->prepare("
        SELECT id, assignment_id, author, text, created_at
        FROM comments
        WHERE assignment_id = :assignment_id
        ORDER BY created_at ASC
    ");
    $stmt->execute([':assignment_id' => $assignmentId]);
 
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    sendResponse([
        'success' => true,
        'data'    => $rows,
    ]);
}
 
function createComment(PDO $db, array $data)
{
    if (empty($data['assignment_id']) || empty($data['author']) || empty($data['text'])) {
        sendResponse([
            'success' => false,
            'message' => 'assignment_id, author and text are required.',
        ], 400);
    }
 
    $assignmentId = sanitizeInput($data['assignment_id']);
    $author       = sanitizeInput($data['author']);
    $text         = sanitizeInput($data['text']);
 
    if ($text === '') {
        sendResponse([
            'success' => false,
            'message' => 'Comment text cannot be empty.',
        ], 400);
    }
 
    $check = $db->prepare("SELECT id FROM assignments WHERE id = :id LIMIT 1");
    $check->execute([':id' => $assignmentId]);
    if (!$check->fetch()) {
        sendResponse([
            'success' => false,
            'message' => 'Assignment not found for this comment.',
        ], 404);
    }
 
    $sql = "
        INSERT INTO comments (assignment_id, author, text, created_at)
        VALUES (:assignment_id, :author, :text, NOW())
    ";
 
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':assignment_id' => $assignmentId,
        ':author'        => $author,
        ':text'          => $text,
    ]);
 
    $id = $db->lastInsertId();
 
    sendResponse([
        'success' => true,
        'data'    => [
            'id'            => $id,
            'assignment_id' => $assignmentId,
            'author'        => $author,
            'text'          => $text,
        ],
    ], 201);
}
 
function deleteComment(PDO $db, $commentId)
{
    if (!$commentId) {
        sendResponse([
            'success' => false,
            'message' => 'Comment ID is required.',
        ], 400);
    }
 
    $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
    $stmt->execute([':id' => $commentId]);
 
    if ($stmt->rowCount() === 0) {
        sendResponse([
            'success' => false,
            'message' => 'Comment not found or already deleted.',
        ], 404);
    }
 
    sendResponse([
        'success' => true,
        'message' => 'Comment deleted successfully.',
    ]);
}
 
// ============================================================================
// MAIN ROUTER
// ============================================================================
 
try {
    if (!$resource) {
        sendResponse([
            'success' => false,
            'message' => 'Resource parameter is required (assignments or comments).',
        ], 400);
    }
 
    if ($method === 'GET') {
        if ($resource === 'assignments') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                getAssignmentById($db, $id);
            } else {
                getAllAssignments($db);
            }
        } elseif ($resource === 'comments') {
            $assignmentId = $_GET['assignment_id'] ?? null;
            getCommentsByAssignment($db, $assignmentId);
        } else {
            sendResponse([
                'success' => false,
                'message' => 'Invalid resource for GET.',
            ], 400);
        }
    } elseif ($method === 'POST') {
        if ($resource === 'assignments') {
            createAssignment($db, $data);
        } elseif ($resource === 'comments') {
            createComment($db, $data);
        } else {
            sendResponse([
                'success' => false,
                'message' => 'Invalid resource for POST.',
            ], 400);
        }
    } elseif ($method === 'PUT') {
        if ($resource === 'assignments') {
            updateAssignment($db, $data);
        } else {
            sendResponse([
                'success' => false,
                'message' => 'PUT not supported for this resource.',
            ], 405);
        }
    } elseif ($method === 'DELETE') {
        if ($resource === 'assignments') {
            $id = $_GET['id'] ?? ($data['id'] ?? null);
            deleteAssignment($db, $id);
        } elseif ($resource === 'comments') {
            $commentId = $_GET['id'] ?? ($data['id'] ?? null);
            deleteComment($db, $commentId);
        } else {
            sendResponse([
                'success' => false,
                'message' => 'Invalid resource for DELETE.',
            ], 400);
        }
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Method not allowed.',
        ], 405);
    }
} catch (PDOException $e) {
    sendResponse([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ], 500);
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
    ], 500);
}
 
// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
 
function sendResponse($data, int $statusCode = 200)
{
    http_response_code($statusCode);
 
    if (!is_array($data)) {
        $data = ['data' => $data];
    }
 
    echo json_encode($data);
    exit;
}
 
function sanitizeInput($data): string
{
    $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return $data;
}
 
function validateDate($date): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt && $dt->format('Y-m-d') === $date;
}
 
function validateAllowedValue($value, array $allowedValues): bool
{
    return in_array($value, $allowedValues, true);
}
