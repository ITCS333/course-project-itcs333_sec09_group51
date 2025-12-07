<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


$host = "localhost";
$dbname = "assignment_db";
$user = "root";
$pass = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit();
}


$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);
$resource = isset($_GET['resource']) ? $_GET['resource'] : '';


function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validateDate($date) {
    $d = DateTime::createFromFormat("Y-m-d", $date);
    return $d && $d->format("Y-m-d") === $date;
}



function getAllAssignments($db) {
    $stmt = $db->prepare("SELECT * FROM assignments ORDER BY due_date ASC");
    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($assignments as &$a) {
        $a['files'] = json_decode($a['files'], true);
    }

    sendResponse($assignments);
}

function getAssignmentById($db, $id) {
    $stmt = $db->prepare("SELECT * FROM assignments WHERE id=:id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) sendResponse(["error"=>"Assignment not found"], 404);

    $assignment['files'] = json_decode($assignment['files'], true);
    sendResponse($assignment);
}

function createAssignment($db, $data) {
    if (empty($data['title']) || empty($data['description']) || empty($data['due_date'])) {
        sendResponse(["error"=>"Missing required fields"], 400);
    }

    $title = sanitizeInput($data['title']);
    $desc = sanitizeInput($data['description']);
    $due = sanitizeInput($data['due_date']);

    if (!validateDate($due)) sendResponse(["error"=>"Invalid date format"], 400);

    $files = isset($data['files']) ? json_encode($data['files']) : json_encode([]);

    $stmt = $db->prepare("INSERT INTO assignments (title, description, due_date, files, created_at, updated_at) VALUES (:title, :desc, :due, :files, NOW(), NOW())");
    $stmt->bindParam(":title",$title);
    $stmt->bindParam(":desc",$desc);
    $stmt->bindParam(":due",$due);
    $stmt->bindParam(":files",$files);
    $stmt->execute();

    $id = $db->lastInsertId();
    getAssignmentById($db,$id);
}

function updateAssignment($db, $data) {
    if (empty($data['id'])) sendResponse(["error"=>"Assignment ID required"],400);
    $id = $data['id'];

    $stmtCheck = $db->prepare("SELECT * FROM assignments WHERE id=:id");
    $stmtCheck->bindParam(":id",$id);
    $stmtCheck->execute();
    if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) sendResponse(["error"=>"Assignment not found"],404);

    $fields = [];
    if (isset($data['title'])) $fields['title'] = sanitizeInput($data['title']);
    if (isset($data['description'])) $fields['description'] = sanitizeInput($data['description']);
    if (isset($data['due_date'])) {
        if (!validateDate($data['due_date'])) sendResponse(["error"=>"Invalid date format"],400);
        $fields['due_date'] = sanitizeInput($data['due_date']);
    }
    if (isset($data['files'])) $fields['files'] = json_encode($data['files']);

    if(empty($fields)) sendResponse(["error"=>"No fields to update"],400);

    $setParts = [];
    foreach($fields as $k=>$v) $setParts[] = "$k=:$k";
    $setParts[] = "updated_at=NOW()";

    $sql = "UPDATE assignments SET ".implode(",",$setParts)." WHERE id=:id";
    $stmt = $db->prepare($sql);
    foreach($fields as $k=>$v) $stmt->bindValue(":$k",$v);
    $stmt->bindValue(":id",$id);
    $stmt->execute();

    getAssignmentById($db,$id);
}

function deleteAssignment($db,$id) {
    $stmtCheck = $db->prepare("SELECT * FROM assignments WHERE id=:id");
    $stmtCheck->bindParam(":id",$id);
    $stmtCheck->execute();
    if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) sendResponse(["error"=>"Assignment not found"],404);

    // Delete associated comments first
    $stmtC = $db->prepare("DELETE FROM comments WHERE assignment_id=:id");
    $stmtC->bindParam(":id",$id);
    $stmtC->execute();

    // Delete assignment
    $stmt = $db->prepare("DELETE FROM assignments WHERE id=:id");
    $stmt->bindParam(":id",$id);
    $stmt->execute();

    sendResponse(["success"=>"Assignment deleted"]);
}



function getCommentsByAssignment($db, $assignment_id) {
    $stmt = $db->prepare("SELECT * FROM comments WHERE assignment_id=:assignment_id ORDER BY created_at ASC");
    $stmt->bindParam(":assignment_id",$assignment_id);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sendResponse($comments);
}

function createComment($db, $data) {
    if (empty($data['assignment_id']) || empty($data['author']) || empty($data['text'])) {
        sendResponse(["error"=>"Missing required fields"], 400);
    }

    $assignment_id = sanitizeInput($data['assignment_id']);
    $author = sanitizeInput($data['author']);
    $text = sanitizeInput($data['text']);

    // verify assignment exists
    $stmtCheck = $db->prepare("SELECT * FROM assignments WHERE id=:id");
    $stmtCheck->bindParam(":id",$assignment_id);
    $stmtCheck->execute();
    if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) sendResponse(["error"=>"Assignment not found"],404);

    $stmt = $db->prepare("INSERT INTO comments (assignment_id, author, text, created_at) VALUES (:aid,:author,:text,NOW())");
    $stmt->bindParam(":aid",$assignment_id);
    $stmt->bindParam(":author",$author);
    $stmt->bindParam(":text",$text);
    $stmt->execute();

    $id = $db->lastInsertId();
    sendResponse([
        "id"=>$id,
        "assignment_id"=>$assignment_id,
        "author"=>$author,
        "text"=>$text
    ]);
}

function deleteComment($db,$id){
    $stmtCheck = $db->prepare("SELECT * FROM comments WHERE id=:id");
    $stmtCheck->bindParam(":id",$id);
    $stmtCheck->execute();
    if(!$stmtCheck->fetch(PDO::FETCH_ASSOC)) sendResponse(["error"=>"Comment not found"],404);

    $stmt = $db->prepare("DELETE FROM comments WHERE id=:id");
    $stmt->bindParam(":id",$id);
    $stmt->execute();
    sendResponse(["success"=>"Comment deleted"]);
}


try {
    if($method==="GET"){
        if($resource==="assignments"){
            if(isset($_GET['id'])) getAssignmentById($db,$_GET['id']);
            else getAllAssignments($db);
        } elseif($resource==="comments"){
            if(isset($_GET['assignment_id'])) getCommentsByAssignment($db,$_GET['assignment_id']);
            else sendResponse([]);
        } else sendResponse(["error"=>"Invalid resource"],400);

    } elseif($method==="POST"){
        if($resource==="assignments") createAssignment($db,$data);
        elseif($resource==="comments") createComment($db,$data);
        else sendResponse(["error"=>"Invalid resource"],400);

    } elseif($method==="PUT"){
        if($resource==="assignments") updateAssignment($db,$data);
        else sendResponse(["error"=>"PUT not supported"],400);

    } elseif($method==="DELETE"){
        if($resource==="assignments" && isset($_GET['id'])) deleteAssignment($db,$_GET['id']);
        elseif($resource==="comments" && isset($_GET['id'])) deleteComment($db,$_GET['id']);
        else sendResponse(["error"=>"ID required for delete"],400);

    } else sendResponse(["error"=>"Method not supported"],405);

} catch(PDOException $e){
    sendResponse(["error"=>$e->getMessage()],500);
} catch(Exception $e){
    sendResponse(["error"=>$e->getMessage()],500);
}
?>
