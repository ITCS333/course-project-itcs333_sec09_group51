<?php
/**
 * Course Resources API
 * 
 * This is a RESTful API that handles all CRUD operations for course resources 
 * and their associated comments/discussions.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: resources
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - title (VARCHAR(255))
 *   - description (TEXT)
 *   - link (VARCHAR(500))
 *   - created_at (TIMESTAMP)
 * 
 * Table: comments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - resource_id (INT, FOREIGN KEY references resources.id)
 *   - author (VARCHAR(100))
 *   - text (TEXT)
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve resource(s) or comment(s)
 *   - POST: Create a new resource or comment
 *   - PUT: Update an existing resource
 *   - DELETE: Delete a resource or comment
 * 
 * Response Format: JSON
 * 
 * API Endpoints:
 *   Resources:
 *     GET    /api/resources.php                    - Get all resources
 *     GET    /api/resources.php?id={id}           - Get single resource by ID
 *     POST   /api/resources.php                    - Create new resource
 *     PUT    /api/resources.php                    - Update resource
 *     DELETE /api/resources.php?id={id}           - Delete resource
 * 
 *   Comments:
 *     GET    /api/resources.php?resource_id={id}&action=comments  - Get comments for resource
 *     POST   /api/resources.php?action=comment                    - Create new comment
 *     DELETE /api/resources.php?comment_id={id}&action=delete_comment - Delete comment
 */

// ============================================================================
// HEADERS AND INITIALIZATION
// ============================================================================

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
header('Content-Type: application/json');
// Allow cross-origin requests (CORS) if needed
header('Access-Control-Allow-Origin: *');
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
// Allow specific headers (Content-Type, Authorization)
header('Access-Control-Allow-Headers: Content-Type, Authorization');


// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();}

// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance
// Example: require_once '../config/Database.php';
include '../config/Database.php';
$host = "localhost";

// TODO: Get the PDO database connection
// Example: $database = new Database();
// Example: $db = $database->getConnection();
$database = new Database();
$db = $database->getConnection();

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']
$method =  $_SERVER['REQUEST_METHOD'];

// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode() with associative array parameter
if ($method === 'POST' || $method === 'PUT') {
        $input =file_get_contents('php://input');
        $inputData = json_decode($input , true);
}

// TODO: Parse query parameters
// Get 'action', 'id', 'resource_id', 'comment_id' from $_GET
$action = $_REQUEST['action'] ?? null ;
$id     = $_REQUEST['id'] ?? null;
$resource_id = $_REQUEST['resource_id'] ?? null;
$comment_id  = $_REQUEST['comment_id'] ?? null;


// ============================================================================
// RESOURCE FUNCTIONS
// ============================================================================

/**
 * Function: Get all resources
 * Method: GET
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by title or description
 *   - sort: Optional field to sort by (title, created_at)
 *   - order: Optional sort order (asc or desc, default: desc)
 * 
 * Response:
 *   - success: true/false
 *   - data: Array of resource objects
 */
function getAllResources($db) {
    // TODO: Initialize the base SQL query
    // SELECT id, title, description, link, created_at FROM resources
    $sql= 'SELECT id, title, description, link, created_at FROM resources';
    // TODO: Check if search parameter exists
    // If yes, add WHERE clause using LIKE to search title and description
    // Use OR to search both fields
    $search = $_GET['search'] ?? null;
    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE title LIKE :search OR description LIKE :search";
        $params[':search'] = "%$search%";
    }
    // TODO: Check if sort parameter exists and validate it
    // Only allow: title, created_at
    // Default to created_at if not provided or invalid
    $sort = $_GET['sort'] ?? 'created_at';
    $allowedSorts = ['title', 'created_at'];
    if (!in_array($sort, $allowedSorts)) 
        $sort = 'created_at';
    // TODO: Check if order parameter exists and validate it
    // Only allow: asc, desc
    // Default to desc if not provided or invalid
    $order = $_GET['order'] ?? 'desc';
    $allowedOrders = ['asc', 'desc'];
    if (!in_array(strtolower($order), $allowedOrders)) 
        $order = 'desc';
    // TODO: Add ORDER BY clause to query   
    $sql .= " ORDER BY $sort $order";
    
    try {
    // TODO: Prepare the SQL query using PDO
    $stmt = $db->prepare($sql);

    // TODO: If search parameter was used, bind the search parameter
    // Use % wildcards for LIKE search
    if (!empty($search)) 
            $stmt->bindValue(':search', '%$search%', PDO::PARAM_STR);
    // TODO: Execute the query
    $stmt->execute();

    // TODO: Fetch all results as an associative array
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Return JSON response with success status and data
    // Use the helper function sendResponse()
    // sendResponse(true, 'Resources retrieved successfully', $resources);
    sendResponse( $resources);

    }catch(PDOException $e){
        //  sendResponse(false, 'Failed to retrieve resources: ' . $e->getMessage(), [], 500);
        sendResponse([], 500);
    } 
}


/**
 * Function: Get a single resource by ID
 * Method: GET
 * 
 * Parameters:
 *   - $resourceId: The resource's database ID
 * 
 * Response:
 *   - success: true/false
 *   - data: Resource object or error message
 */
function getResourceById($db, $resourceId) {
    // TODO: Validate that resource ID is provided and is numeric
    // If not, return error response with 400 status
    if (empty($resourceId) || !is_numeric($resourceId)) {
        // sendResponse(false, 'Invalid or missing resource ID', [], 400);
        sendResponse([], 400);
    }
    // TODO: Prepare SQL query to select resource by id
    // SELECT id, title, description, link, created_at FROM resources WHERE id = ?
    $sql = 'SELECT id, title, description, link, created_at FROM resources WHERE id = ?';
    $stmt = $db->prepare($sql);
    // TODO: Bind the resource_id parameter
    $stmt->bindParam(1, $resourceId, PDO::PARAM_INT);
    // TODO: Execute the query
    $stmt->execute();
    // TODO: Fetch the result as an associative array
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    // TODO: Check if resource exists
    // If yes, return success response with resource data
    // If no, return error response with 404 status
    if ($resource) {
        // sendResponse(true, 'Resource retrieved successfully', $resource);
        sendResponse( $resource);
    } else {
        // sendResponse(false, 'Resource not found', [], 404);
        sendResponse([], 404);
    }
}


/**
 * Function: Create a new resource
 * Method: POST
 * 
 * Required JSON Body:
 *   - title: Resource title (required)
 *   - description: Resource description (optional)
 *   - link: URL to the resource (required)
 * 
 * Response:
 *   - success: true/false
 *   - message: Success or error message
 *   - id: ID of created resource (on success)
 */
function createResource($db, $data) {
    // TODO: Validate required fields
    // Check if title and link are provided and not empty
    // If any required field is missing, return error response with 400 status
    if (empty($data['title']) || empty($data['link'])) {
        // sendResponse(false, 'Title and link are required', [], 400);
        sendResponse([], 400);
        return;
    }
    // if (!$db || !$data ) throw new Exception('ID required');
    if (!$db || !$data ) 
        throw new Exception('ID required');
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    // Validate URL format for link using filter_var with FILTER_VALIDATE_URL
    // If URL is invalid, return error response with 400 status
    $title = trim($data['title']);
    $description = isset($data['description']) ? trim($data['description']) : '';
    $link = trim($data['link']);
    if (!filter_var($link, FILTER_VALIDATE_URL)) {
        // sendResponse(false, 'Invalid URL format for link', [], 400);
        sendResponse([], 400);
        return;
    }
    
    // TODO: Set default value for description if not provided
    // Use empty string as default
    $description = $description ?? '';
    
    // TODO: Prepare INSERT query
    // INSERT INTO resources (title, description, link) VALUES (?, ?, ?)
    $query = 'INSERT INTO resources (title, description, link) VALUES (?, ?, ?)';
    $stmt = $db->prepare($query);   
    // TODO: Bind parameters
    // Bind title, description, and link
    $stmt->bindParam(1, $title, PDO::PARAM_STR);
    $stmt->bindParam(2, $description, PDO::PARAM_STR);
    $stmt->bindParam(3, $link, PDO::PARAM_STR);
    // TODO: Execute the query
    $executeResult = $stmt->execute();
    // TODO: Check if insert was successful
    // If yes, get the last inserted ID using $db->lastInsertId()
    // Return success response with 201 status and the new resource ID
    // If no, return error response with 500 status
    if ($executeResult && $stmt->rowCount() > 0) {
        $newId = $db->lastInsertId();
        // sendResponse(true, 'Resource created successfully', ['id' => $newId], 201);
        sendResponse( ['id' => $newId], 201);
    } else {
        // sendResponse(false, 'Failed to create resource', [], 500);
        sendResponse([], 500);
    }
}


/**
 * Function: Update an existing resource
 * Method: PUT
 * 
 * Required JSON Body:
 *   - id: The resource's database ID (required)
 *   - title: Updated resource title (optional)
 *   - description: Updated description (optional)
 *   - link: Updated URL (optional)
 * 
 * Response:
 *   - success: true/false
 *   - message: Success or error message
 */
function updateResource($db, $data) {
    // TODO: Validate that resource ID is provided
    // If not, return error response with 400 status
    if (empty($data['id'])) {
        // sendResponse(false, 'Resource ID is required for update', [], 400);
        sendResponse([], 400);
        return;
    }
    // TODO: Check if resource exists
    // Prepare and execute a SELECT query to find the resource by id
    // If not found, return error response with 404 status
    $resourceId = $data['id'];
    $checkSql = 'SELECT id FROM resources WHERE id = ?';
    $checkStmt = $db->prepare($checkSql);
    $checkStmt ->execute([$resourceId]);
    if ($checkStmt->rowCount() === 0) {
        // sendResponse(false, 'Resource not found', [], 404);
        sendResponse([], 404);
        return;
    }
    // TODO: Build UPDATE query dynamically based on provided fields
    // Initialize empty arrays for fields to update and values
    // Check which fields are provided (title, description, link)
    // Add each provided field to the update arrays
    $fields = [];
    $values = [];
    if (isset($data['title'])) {
        $fields[] = 'title = ?';
        $values[] = trim($data['title']);
    }
    if (isset($data['description'])) {
        $fields[] = 'description = ?';
        $values[] = trim($data['description']);
    }
    if (isset($data['link'])) {

        $fields[] = 'link = ?';
        $values[] = trim($data['link']);
    }
    // TODO: If no fields to update, return error response with 400 status
    if (empty($fields)) {
        // sendResponse(false, 'No fields provided for update', [], 400);
        sendResponse([], 400);
        return;
    }
    // TODO: If link is being updated, validate URL format
    // Use filter_var with FILTER_VALIDATE_URL
    // If invalid, return error response with 400 status
    if (isset($data['link'])) {
        $link = trim($data['link']);
        if (!filter_var($link, FILTER_VALIDATE_URL)) {
            // sendResponse(false, 'Invalid URL format for link', [], 400);
            sendResponse([], 400);
            return;
        }
    }
    // TODO: Build the complete UPDATE SQL query
    // UPDATE resources SET field1 = ?, field2 = ? WHERE id = ?
    $updateSql = 'UPDATE resources SET' . implode(',',$fields) . ' WHERE id = ?';
    // TODO: Prepare the query
    $stmt = $db->prepare($updateSql);
    // TODO: Bind parameters dynamically
    // Bind all update values, then bind the resource ID at the end
    foreach ($values as $index => $value) {
        $stmt->bindValue($index + 1, $value);
    }
    // TODO: Execute the query
    $executeResult = $stmt->execute([$resourceId]);
    // TODO: Check if update was successful
    // If yes, return success response with 200 status
    // If no, return error response with 500 status
    if ($executeResult) {
        // sendResponse(true, 'Resource updated successfully', [], 200);
        sendResponse([], 200);
    } else {
        // sendResponse(false, 'Failed to update resource', [], 500);
        sendResponse([], 500);
    }   
}


/**
 * Function: Delete a resource
 * Method: DELETE
 * 
 * Parameters:
 *   - $resourceId: The resource's database ID
 * 
 * Response:
 *   - success: true/false
 *   - message: Success or error message
 * 
 * Note: This should also delete all associated comments
 */
function deleteResource($db, $resourceId) {
    // TODO: Validate that resource ID is provided and is numeric
    // If not, return error response with 400 status
     if (empty($resourceId) || !is_numeric($resourceId)) {
        // sendResponse(false, 'Invalid or missing resource ID', [], 400);
        sendResponse([], 400);
        return ;
    }

    // TODO: Check if resource exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    $checkSql = 'SELECT id FROM resources WHERE id = ?';
    $checkStmt = $db->prepare($checkSql);   
    $checkStmt ->execute([$resourceId]);

    if ($checkStmt->rowCount() === 0) {
        // sendResponse(false, 'Resource not found', [], 404);
        sendResponse([], 404);
        return;
    }

    // TODO: Begin a transaction (for data integrity)
    // Use $db->beginTransaction()
    $db->beginTransaction();
    
    try {
        // TODO: First, delete all associated comments
        // Prepare DELETE query for comments table
        // DELETE FROM comments WHERE resource_id = ?
        $deleteCommentsSql = 'DELETE FROM comments WHERE resource_id = ?';
        $deleteCommentsStmt = $db->prepare($deleteCommentsSql);

        // TODO: Bind resource_id and execute
        $deleteCommentsStmt->execute([$resourceId]);

        // TODO: Then, delete the resource
        // Prepare DELETE query for resources table
        // DELETE FROM resources WHERE id = ?
        $deleteResourceSql = 'DELETE FROM resources WHERE id = ?';
        $deleteResourceStmt = $db->prepare($deleteResourceSql);
        
        // TODO: Bind resource_id and execute
        $deleteResourceStmt->execute([$resourceId]);

        // TODO: Commit the transaction
        // Use $db->commit()
        $db->commit();

        // TODO: Return success response with 200 status
        // sendResponse(true, 'Resource and associated comments deleted successfully', [], 200);
        sendResponse([], 200);

    } catch (Exception $e) {
        // TODO: Rollback the transaction on error
        // Use $db->rollBack()
        $db->rollBack();
        // TODO: Return error response with 500 status
        // sendResponse(false, 'Failed to delete resource: ' . $e->getMessage(), [], 500); 
        sendResponse([], 500);
    }
}


// ============================================================================
// COMMENT FUNCTIONS
// ============================================================================

/**
 * Function: Get all comments for a specific resource
 * Method: GET with action=comments
 * 
 * Query Parameters:
 *   - resource_id: The resource's database ID (required)
 * 
 * Response:
 *   - success: true/false
 *   - data: Array of comment objects
 */
function getCommentsByResourceId($db, $resourceId) {
    // TODO: Validate that resource_id is provided and is numeric
    // If not, return error response with 400 status
    if (empty($resourceId) || !is_numeric($resourceId)) {
        sendResponse(false, 'Invalid or missing resource ID', [], 400);
        return ;
    }
    // TODO: Prepare SQL query to select comments for the resource
    // SELECT id, resource_id, author, text, created_at 
    // FROM comments 
    // WHERE resource_id = ? 
    // ORDER BY created_at ASC
    $sql = 'SELECT id, resource_id, author, text, created_at FROM comments WHERE resource_id = ? ORDER BY created_at ASC';
    // TODO: Bind the resource_id parameter
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $resourceId, PDO::PARAM_INT);    
    // TODO: Execute the query
    $executeResult = $stmt->execute([$resourceId]);
    // TODO: Fetch all results as an associative array
    $commnt = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // TODO: Return success response with comments data
    // Even if no comments exist, return empty array (not an error)
    if ($commnt) {
        // sendResponse(true, 'Comments retrieved successfully', $commnt);
        sendResponse( $commnt);
    } else {
        // sendResponse(true, 'No comments found for this resource', [], 200);
        sendResponse( [], 200);
    }
}


/**
 * Function: Create a new comment
 * Method: POST with action=comment
 * 
 * Required JSON Body:
 *   - resource_id: The resource's database ID (required)
 *   - author: Name of the comment author (required)
 *   - text: Comment text content (required)
 * 
 * Response:
 *   - success: true/false
 *   - message: Success or error message
 *   - id: ID of created comment (on success)
 */
function createComment($db, $data) {
    // TODO: Validate required fields
    // Check if resource_id, author, and text are provided and not empty
    // If any required field is missing, return error response with 400 status
    if (empty($data['resource_id']) || empty($data['author']) || empty($data['text'])) {
        // sendResponse(false, 'resource_id, author, and text are required', [], 400);
        sendResponse([], 400);
        return;
    }

    // TODO: Validate that resource_id is numeric
    // If not, return error response with 400 status
    if (!is_numeric($data['resource_id'])) {
        // sendResponse(false, 'Invalid resource_id', [], 400);
        sendResponse([], 400);
        return;
    }

    // TODO: Check if the resource exists
    // Prepare and execute SELECT query on resources table
    // If resource not found, return error response with 404 status
    $resourceId = $data['resource_id'];
    $checkSql = 'SELECT id FROM resources WHERE id = ?';
    $checkStmt = $db->prepare($checkSql);
    $checkStmt ->execute([$resourceId]);

    if ($checkStmt->rowCount() === 0) {
        // sendResponse(false, 'Resource not found', [], 404);
        sendResponse([], 404);
        return;
    }

    // TODO: Sanitize input data
    // Trim whitespace from author and text
    $author = trim($data['author']);
    $text = trim($data['text']);

    // TODO: Prepare INSERT query
    // INSERT INTO comments (resource_id, author, text) VALUES (?, ?, ?)
    $insertSql = 'INSERT INTO comments (resource_id, author, text) VALUES (?, ?, ?)';
    $stmt = $db->prepare($insertSql);   

    // TODO: Bind parameters
    // Bind resource_id, author, and text
    $stmt->bindParam(1, $resourceId, PDO::PARAM_INT);
    $stmt->bindParam(2, $author, PDO::PARAM_STR);
    $stmt->bindParam(3, $text, PDO::PARAM_STR);

    // TODO: Execute the query
    $executeResult = $stmt->execute();
    // TODO: Check if insert was successful
    // If yes, get the last inserted ID using $db->lastInsertId()
    // Return success response with 201 status and the new comment ID
    // If no, return error response with 500 status
    if ($executeResult && $stmt->rowCount() > 0) {
        $newId = $db->lastInsertId();
        // sendResponse(true, 'Comment created successfully', ['id' => $newId], 201);
        sendResponse( ['id' => $newId], 201);
    } else {
        // sendResponse(false, 'Failed to create comment', [], 500);
        sendResponse([], 500);
    }
}


/**
 * Function: Delete a comment
 * Method: DELETE with action=delete_comment
 * 
 * Query Parameters or JSON Body:
 *   - comment_id: The comment's database ID (required)
 * 
 * Response:
 *   - success: true/false
 *   - message: Success or error message
 */
function deleteComment($db, $commentId) {
    // TODO: Validate that comment_id is provided and is numeric
    // If not, return error response with 400 status
    if (empty($commentId) || !is_numeric($commentId)) {
        // sendResponse(false, 'Invalid or missing comment ID', [], 400);
        sendResponse([], 400);
        return ;
    }
    // TODO: Check if comment exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    $checkSql = 'SELECT id FROM comments WHERE id = ?';
    $checkStmt = $db->prepare($checkSql);
    $checkStmt ->execute([$commentId]);

    if ($checkStmt->rowCount() === 0) {
        // sendResponse(false, 'Comment not found', [], 404);
        sendResponse([], 404);
        return;
    }

    // TODO: Prepare DELETE query
    // DELETE FROM comments WHERE id = ?
    $deleteQuery = 'DELETE FROM comments WHERE id = ?';
    $stmt = $db->prepare($deleteQuery);

    // TODO: Bind the comment_id parameter
    $stmt->bindParam(1, $commentId, PDO::PARAM_INT);

    // TODO: Execute the query
    $executeResult = $stmt->execute();

    // TODO: Check if delete was successful
    // If yes, return success response with 200 status
    // If no, return error response with 500 status
    if ($executeResult) {
        // sendResponse(true, 'Comment deleted successfully', [], 200);
        sendResponse([], 200);
    } else {
        // sendResponse(false, 'Failed to delete comment', [], 500);
        sendResponse([], 500);
    }

}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Route the request based on HTTP method and action parameter
    
    if ($method === 'GET') {
        // TODO: Check the action parameter to determine which function to call
       
        // If action is 'comments', get comments for a resource
        // TODO: Check if action === 'comments'
        // Get resource_id from query parameters
        // Call getCommentsByResourceId()

        if($action === 'comments'){
            if (isset($_GET['resource_id']))
                getCommentsByResourceId($db, $_GET['resource_id']);
            else
                // sendResponse(false, 'resource_id parameter is required', [], 400);
                sendResponse([], 400);
        }
        else if (isset($_GET['id'])){
            getResourceById($db, $_GET['id']);
        }
        else
            getAllResources($db);   

        // If id parameter exists, get single resource
        // TODO: Check if 'id' parameter exists in $_GET
        // Call getResourceById()
        
        // Otherwise, get all resources
        // TODO: Call getAllResources()
        
    } elseif ($method === 'POST') {
        // TODO: Check the action parameter to determine which function to call
        
        // If action is 'comment', create a new comment
        // TODO: Check if action === 'comment'
        // Call createComment()
        if($action === 'comment')
            createComment($db, $inputData);
        else
            createResource($db, $inputData);
        // Otherwise, create a new resource
        // TODO: Call createResource()
        
    } elseif ($method === 'PUT') {
        // TODO: Update a resource
        // Call updateResource()
        if($action === 'comments')
            updateResource($db, $inputData);
        else
            // sendResponse(false, 'Invalid action for PUT method', [], 400);
            sendResponse([], 400);
        
    } elseif ($method === 'DELETE') {
        // TODO: Check the action parameter to determine which function to call
        
        // If action is 'delete_comment', delete a comment
        // TODO: Check if action === 'delete_comment'
        // Get comment_id from query parameters or request body
        // Call deleteComment()
        if ($action === 'delete_comment') {
            if (isset($_GET['comment_id']))
                deleteComment($db, $_GET['comment_id']);
            else
                // sendResponse(false, 'comment_id parameter is required', [], 400);
                sendResponse([], 400);
        }else
            deleteResource($db, $resource_id);
        // Otherwise, delete a resource
        // TODO: Get resource id from query parameter or request body
        // Call deleteResource()
        
    } else {
        // TODO: Return error for unsupported methods
        // Set HTTP status to 405 (Method Not Allowed)
        // Return JSON error message using sendResponse()
        // sendResponse(false, 'Method Not Allowed', [], 405);
        sendResponse([], 405);
    }
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    // Log the error message (optional, use error_log())
    error_log($e->getMessage());
    // Return generic error response with 500 status
    // Do NOT expose detailed error messages to the client in production
    // sendResponse(false, 'Database error' , [], 500);
    sendResponse([], 500);
    
} catch (Exception $e) {
    // TODO: Handle general errors
    // Log the error message (optional)
    error_log($e->getMessage());
    // Return error response with 500 status    
    // sendResponse(false, 'Server error: ' . $e->getMessage(), [], 500);
    sendResponse([], 500);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response
 * 
 * @param array $data - Data to send (should include 'success' key)
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code using http_response_code()
    http_response_code($statusCode);
    // TODO: Ensure data is an array
    // If not, wrap it in an array
    header('Content-Type: application/json');
    if (!is_array($data))
        $data = ['data' => $data];
    // TODO: Echo JSON encoded data
    // Use JSON_PRETTY_PRINT for readability (optional)
    echo json_encode($data, JSON_PRETTY_PRINT);
    // TODO: Exit to prevent further execution
    exit;
}


/**
 * Helper function to validate URL format
 * 
 * @param string $url - URL to validate
 * @return bool - True if valid, false otherwise
 */
function validateUrl($url) {
    // TODO: Use filter_var with FILTER_VALIDATE_URL
    // Return true if valid, false otherwise
    return filter_var($url, FILTER_VALIDATE_URL) !== false;    
}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace using trim()
    $data = trim($data);
    // TODO: Strip HTML tags using strip_tags()
    $data = strip_tags($data);
    // TODO: Convert special characters using htmlspecialchars()
    // Use ENT_QUOTES to escape both double and single quotes
    $data = htmlspecialchars($data , ENT_QUOTES ,"UTF-8" );
    // TODO: Return sanitized data
    return $data ;
}


/**
 * Helper function to validate required fields
 * 
 * @param array $data - Data array to validate
 * @param array $requiredFields - Array of required field names
 * @return array - Array with 'valid' (bool) and 'missing' (array of missing fields)
 */
function validateRequiredFields($data, $requiredFields) {
    // TODO: Initialize empty array for missing fields
    $missing =[];
    // TODO: Loop through required fields
    // Check if each field exists in data and is not empty
    // If missing or empty, add to missing fields array
    foreach($requiredFields as $field )
        if(empty($data[$field]))
            $missing[]=$field;
    // TODO: Return result array
    // ['valid' => (count($missing) === 0), 'missing' => $missing]
    return ['valid' => (count($missing) === 0), 'missing' => $missing];
}

?>
