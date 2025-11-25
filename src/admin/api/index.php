<?php
/**
 * Student Management API
 * 
 * This is a RESTful API that handles all CRUD operations for student management.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structure (for reference):
 * Table: students
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - student_id (VARCHAR(50), UNIQUE) - The student's university ID
 *   - name (VARCHAR(100))
 *   - email (VARCHAR(100), UNIQUE)
 *   - password (VARCHAR(255)) - Hashed password
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve student(s)
 *   - POST: Create a new student OR change password
 *   - PUT: Update an existing student
 *   - DELETE: Delete a student
 * 
 * Response Format: JSON
 */

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
// Allow cross-origin requests (CORS) if needed
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
// Allow specific headers (Content-Type, Authorization)
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit
if($_SERVER["REQUEST_METHOD"]=="OPTIONS"){
    http_response_code(200);
    exit;
}

// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance
$dataFile=__DIR__."/students.json";

// TODO: Get the PDO database connection
function loadStudents($filePath) {
    if (!file_exists($filePath)) {
        return []; 
    }
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveStudents($filePath, $data) {
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}
$students = loadStudents($dataFile);

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']
$method= $_SERVER['REQUEST_METHOD'];

// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()
$rawBody= file_get_contents("php://input");
$bodyData= json_decode($rawBody, true);
if(!is_array($bodyData)){
    $bodyData=[];
}

// TODO: Parse query parameters for filtering and searching
$queryParams = $_GET;

/**
 * Function: Get all students or search for specific students
 * Method: GET
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by name, student_id, or email
 *   - sort: Optional field to sort by (name, student_id, email)
 *   - order: Optional sort order (asc or desc)
 */
function getStudents($db) {
    global $dataFile;
    $students = loadStudents($dataFile);

    // TODO: Check if search parameter exists
    // If yes, prepare SQL query with WHERE clause using LIKE
    // Search should work on name, student_id, and email fields
    $search= isset($_GET["search"]) ? strtolower(trim($_GET["search"])) : null;
     if (!empty($search)) {
        $students = array_filter($students, function ($student) use ($search) {
            return strpos(strtolower($student["name"]), $search) !== false ||
                   strpos(strtolower($student["id"]), $search) !== false ||
                   strpos(strtolower($student["email"]), $search) !== false;
        });
                $students = array_values($students);
    }


    // TODO: Check if sort and order parameters exist
    // If yes, add ORDER BY clause to the query
    // Validate sort field to prevent SQL injection (only allow: name, student_id, email)
    // Validate order to prevent SQL injection (only allow: asc, desc)
    $allowedSort= ["name", "student_id", "email"];
    $sort= $_GET["sort"] ?? "name";
    $order = strtolower($_GET["order"] ?? "asc");
    // TODO: Prepare the SQL query using PDO
    // Note: Do NOT select the password field
     if ($sort === "student_id") {
        $sort = "id";
    }

    if (!in_array($sort, ["name", "email", "id"])) {
        $sort = "name";
    }

    if (!in_array($order, ["asc", "desc"])) {
        $order = "asc";
    }
    usort($students, function ($a, $b) use ($sort, $order) {
        $valA = strtolower($a[$sort]);
        $valB = strtolower($b[$sort]);

        if ($order === "asc") {
            return $valA <=> $valB;
        } else {
            return $valB <=> $valA;
        }
    });

    // TODO: Bind parameters if using search
    
    // TODO: Execute the query
    
    // TODO: Fetch all results as an associative array
    
    // TODO: Return JSON response with success status and data
    sendResponse([
        "success" => true,
        "data"    => $students
    ]);
}


/**
 * Function: Get a single student by student_id
 * Method: GET
 * 
 * Query Parameters:
 *   - student_id: The student's university ID
 */
function getStudentById($db, $studentId) {
    global $dataFile;
    $students = loadStudents($dataFile);
    // TODO: Prepare SQL query to select student by student_id
    
    // TODO: Bind the student_id parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch the result
   
    // TODO: Check if student exists
   foreach ($students as $student) {
        if ($student["id"] === $studentId) {

            // TODO: Check if student exists
            // If yes, return success response with student data
            sendResponse([
                "success" => true,
                "data"    => $student
            ], 200);
        }
    }
    // If yes, return success response with student data
    sendResponse([
        "success" => false,
        "message" => "Student not found"
    ], 404);
    // If no, return error response with 404 status
}


/**
 * Function: Create a new student
 * Method: POST
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (must be unique)
 *   - name: Student's full name
 *   - email: Student's email (must be unique)
 *   - password: Default password (will be hashed)
 */
function createStudent($db, $data) {
    global $dataFile;
    $students= loadStudents($dataFile);
    // TODO: Validate required fields
    // Check if student_id, name, email, and password are provided
    // If any field is missing, return error response with 400 status
  if (
        empty($data["student_id"]) || 
        empty($data["name"]) || 
        empty($data["email"]) ||
        empty($data["password"])
    ) {
        sendResponse([
            "success" => false,
            "message" => "student_id, name, email, and password are required."
        ], 400);
    }

    // TODO: Sanitize input data
    // Trim whitespace from all fields
    // Validate email format using filter_var()
    $studentId = trim($data["student_id"]);
    $name      = trim($data["name"]);
    $email     = trim($data["email"]);
    $password  = $data["password"];

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        sendResponse([
            "success"=> false,
            "message"=> "Invalid email format."
        ],400);
    }

    // TODO: Check if student_id or email already exists
    // Prepare and execute a SELECT query to check for duplicates
    // If duplicate found, return error response with 409 status (Conflict)
     foreach ($students as $student) {
        if ($student["id"] === $studentId) {
            sendResponse([
                "success" => false,
                "message" => "A student with this student_id already exists."
            ], 409);
        }
        if (strcasecmp($student["email"], $email) === 0) {
            sendResponse([
                "success" => false,
                "message" => "A student with this email already exists."
            ], 409);
        }
    }
    // TODO: Hash the password
    // Use password_hash() with PASSWORD_DEFAULT
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // TODO: Prepare INSERT query
    
    // TODO: Bind parameters
    // Bind student_id, name, email, and hashed password
    $newStudent=[
        "id" => $studentId,
        "name" => $name,
        "email" => $email,
        "password" => $hashedPassword,
        "created_at" => date("c")
    ];
    $students[]= $newStudent;
    // TODO: Execute the query
    $saved = saveStudents($dataFile, $students);

    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created)
    // If no, return error response with 500 status
     if ($saved) {
        sendResponse([
            "success" => true,
            "message" => "Student created successfully.",
            "data"    => $newStudent
        ], 201);
    }

    // If no, return error response with 500 status
    sendResponse([
        "success" => false,
        "message" => "Failed to save new student."
    ], 500);
}


/**
 * Function: Update an existing student
 * Method: PUT
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (to identify which student to update)
 *   - name: Updated student name (optional)
 *   - email: Updated student email (optional)
 */
function updateStudent($db, $data) {
    global $dataFile;
    $students = loadStudents($dataFile);

    // Validate student_id
    if (empty($data["student_id"])) {
        sendResponse([
            "success" => false,
            "message" => "student_id is required"
        ], 400);
    }

    $studentId = trim($data["student_id"]);

    // Find student
    $index = -1;
    foreach ($students as $i => $student) {
        if ($student["id"] === $studentId) {
            $index = $i;
            break;
        }
    }

    if ($index === -1) {
        sendResponse([
            "success" => false,
            "message" => "Student not found."
        ], 404);
    }

    // --- Update NAME ---
    if (!empty($data["name"])) {
        $students[$index]["name"] = trim($data["name"]);
    }

    // --- Update EMAIL ---
    if (!empty($data["email"])) {
        $newEmail = trim($data["email"]);

        // Validate email format
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            sendResponse([
                "success" => false,
                "message" => "Invalid email format."
            ], 400);
        }

        // Check for duplicate email
        foreach ($students as $i => $student) {
            if ($i !== $index && strcasecmp($student["email"], $newEmail) === 0) {
                sendResponse([
                    "success" => false,
                    "message" => "This email is already used by another student."
                ], 409);
            }
        }

        // Apply the email update ONLY after all checks
        $students[$index]["email"] = $newEmail;
    }

    // Save updated student list
    $saved = saveStudents($dataFile, $students);

    if ($saved) {
        sendResponse([
            "success" => true,
            "message" => "Student updated successfully",
            "data"    => $students[$index]
        ], 200);
    }

    sendResponse([
        "success" => false,
        "message" => "Failed to update student."
    ], 500);
}



/**
 * Function: Delete a student
 * Method: DELETE
 * 
 * Query Parameters or JSON Body:
 *   - student_id: The student's university ID
 */
function deleteStudent($db, $studentId) {
     global $dataFile;
     $students = loadStudents($dataFile);
    // TODO: Validate that student_id is provided
    // If not, return error response with 400 status
    if (empty($studentId)) {
            sendResponse([
                "success" => false,
                "message" => "student_id is required."
            ], 400);
        }  
     $studentId = trim($studentId);
    // TODO: Check if student exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status

     $index = -1;
        foreach ($students as $i => $student) {
            if ($student["id"] === $studentId) {
                $index = $i;
                break;
            }
        }
    
        if ($index === -1) {
            sendResponse([
                "success" => false,
                "message" => "Student not found."
            ], 404);
        }
    
    // TODO: Prepare DELETE query
     array_splice($students, $index, 1);
    
    // TODO: Bind the student_id parameter
    
    // TODO: Execute the query
    $saved = saveStudents($dataFile, $students);
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error response with 500 status
    if ($saved) {
            sendResponse([
                "success" => true,
                "message" => "Student deleted successfully."
            ], 200);
        }
    
        sendResponse([
            "success" => false,
            "message" => "Failed to delete student."
        ], 500);
}


/**
 * Function: Change password
 * Method: POST with action=change_password
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (identifies whose password to change)
 *   - current_password: The student's current password
 *   - new_password: The new password to set
 */
function changePassword($db, $data) {
    global $dataFile;
    $students = loadStudents($dataFile);
    // TODO: Validate required fields
    // Check if student_id, current_password, and new_password are provided
    // If any field is missing, return error response with 400 status
    
    if(empty($data["student_id"])||
      empty($data["current_password"])||
      empty($data["new_password"])){
       sendResponse([
        "success"=> false,
        "message"=> "student_id, current_password, and new_password are required."
       ],400);
      }
      $studentId= trim($data["student_id"]);
      $currentPassword= $data["current_password"];
      $newPassword= $data["new_password"];

    // TODO: Validate new password strength
    // Check minimum length (at least 8 characters)
    // If validation fails, return error response with 400 status
    
    if(strlen($newPassword) < 8) {
            sendResponse([
                "success" => false,
                "message" => "New password must be at least 8 characters long."
            ], 400);
        }
    // TODO: Retrieve current password hash from database
    // Prepare and execute SELECT query to get password
    $index = -1;
        foreach ($students as $i => $student) {
            if ($student["id"] === $studentId) {
                $index = $i;
                break;
            }
        }
    
        if ($index === -1) {
            sendResponse([
                "success" => false,
                "message" => "Student not found."
            ], 404);
        }
    // TODO: Verify current password
    // Use password_verify() to check if current_password matches the hash
    // If verification fails, return error response with 401 status (Unauthorized)
    if (!password_verify($currentPassword, $students[$index]["password"])) {
            sendResponse([
                "success" => false,
                "message" => "Current password is incorrect."
            ], 401); // Unauthorized
        }
        
    // TODO: Hash the new password
    // Use password_hash() with PASSWORD_DEFAULT
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // TODO: Update password in database
    // Prepare UPDATE query
     $students[$index]["password"] = $hashedNewPassword;

    // TODO: Bind parameters and execute
    
    // TODO: Check if update was successful
    // If yes, return success response
    // If no, return error response with 500 status
$saved = saveStudents($dataFile, $students);

    if ($saved) {
        sendResponse([
            "success" => true,
            "message" => "Password changed successfully."
        ], 200);
    }

    sendResponse([
        "success" => false,
        "message" => "Failed to update password."
    ], 500);
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Route the request based on HTTP method
    
    if ($method === 'GET') {
        // TODO: Check if student_id is provided in query parameters
        // If yes, call getStudentById()
        // If no, call getStudents() to get all students (with optional search/sort)
        
        if(!empty($queryParams["student_id"])){
          getStudentById($db, $queryParams["student_id"]);
        }else{
            getStudents($db);
        }

    } elseif ($method === 'POST') {
        // TODO: Check if this is a change password request
        // Look for action=change_password in query parameters
        // If yes, call changePassword()
        // If no, call createStudent()

        if(isset($queryParams["action"]) && $queryParams["action"] === "change_password"){
           changePassword($db, $requestBody);
        }else{
            createStudent($db, $requestBody);
        }
        
    } elseif ($method === 'PUT') {
        // TODO: Call updateStudent()
        updateStudent($db, $requestBody);
        
    } elseif ($method === 'DELETE') {
        // TODO: Get student_id from query parameter or request body
        // Call deleteStudent()

        $studentId= $queryParams["student_id"] ??
         $requestBody["student_id"] ?? 
         null;

         deleteStudent($db, $studentId);
    } else {
        // TODO: Return error for unsupported methods
        // Set HTTP status to 405 (Method Not Allowed)
        // Return JSON error message

        sendResponse([
            "success"=> false,
            "message"=> "Method not allowed."
        ],405);
    }
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    // Log the error message (optional)
    // Return generic error response with 500 status
    sendResponse([
            "success" => false,
            "message" => "Database error."
        ], 500);

} catch (Exception $e) {
    // TODO: Handle general errors
    // Return error response with 500 status
sendResponse([
        "success" => false,
        "message" => "Server error."
    ], 500);
}


// ============================================================================
// HELPER FUNCTIONS (Optional but Recommended)
// ============================================================================

/**
 * Helper function to send JSON response
 * 
 * @param mixed $data - Data to send
 * @param int $statusCode - HTTP status code
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
     http_response_code($statusCode);
    // TODO: Echo JSON encoded data
    echo json_encode($data, JSON_PRETTY_PRINT);
    // TODO: Exit to prevent further execution
    exit;
}


/**
 * Helper function to validate email format
 * 
 * @param string $email - Email address to validate
 * @return bool - True if valid, false otherwise
 */
function validateEmail($email) {
    // TODO: Use filter_var with FILTER_VALIDATE_EMAIL
    // Return true if valid, false otherwise
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace
    // TODO: Strip HTML tags using strip_tags()
    // TODO: Convert special characters using htmlspecialchars()
    // Return sanitized data
 $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

?>
