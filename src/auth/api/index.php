<?php
/**
 * Authentication Handler for Login Form
 * 
 * This PHP script handles user authentication via POST requests from the Fetch API.
 * It validates credentials against a MySQL database using PDO,
 * creates sessions, and returns JSON responses.
 */

// --- Session Management ---
session_start();

// --- Set Response Headers ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// --- Check Request Method ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse([
        'success' => false,
        'message' => 'Invalid request method. Only POST is allowed.'
    ], 405);
}

// --- Get POST Data ---
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// --- Validate fields ---
if (!isset($data) || !isset($data['email']) || !isset($data['password'])) {
    sendResponse([
        'success' => false,
        'message' => 'Email and password are required.'
    ], 400);
}

$email = trim($data['email']);
$password = $data['password'];

// --- Server-side validation ---
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse([
        'success' => false,
        'message' => 'Invalid email format.'
    ], 400);
}

if (strlen($password) < 8) {
    sendResponse([
        'success' => false,
        'message' => 'Password must be at least 8 characters long.'
    ], 400);
}

// --- Load students.json ---
$dataFile = __DIR__ . "/../../students.json";

if (!file_exists($dataFile)) {
    sendResponse([
        "success" => false,
        "message" => "Student data file not found."
    ], 500);
}

$students = json_decode(file_get_contents($dataFile), true);

if (!is_array($students)) {
    sendResponse([
        "success" => false,
        "message" => "Student data is corrupted."
    ], 500);
}

// --- Find user ---
$foundUser = null;
foreach ($students as $student) {
    if (strcasecmp($student["email"], $email) === 0) {
        $foundUser = $student;
        break;
    }
}

if (!$foundUser || !isset($foundUser["password"])) {
    sendResponse([
        "success" => false,
        "message" => "Invalid email or password."
    ], 401);
}

// --- Verify password ---
if (!password_verify($password, $foundUser["password"])) {
    sendResponse([
        "success" => false,
        "message" => "Invalid email or password."
    ], 401);
}

// --- Successful login ---
$_SESSION["logged_in"]  = true;
$_SESSION["user_id"]    = $foundUser["id"];
$_SESSION["user_name"]  = $foundUser["name"];
$_SESSION["user_email"] = $foundUser["email"];

sendResponse([
    "success" => true,
    "message" => "Login successful.",
    "user" => [
        "id"    => $foundUser["id"],
        "name"  => $foundUser["name"],
        "email" => $foundUser["email"]
    ]
], 200);

?>
