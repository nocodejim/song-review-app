<?php // public/submit_feedback.php (Final v1)

// --- Security Check: Ensure POST Request ---
// This script should only process data submitted via POST from the form.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Log the attempt for security monitoring.
    error_log("Warning: Attempted access to submit_feedback.php via method: " . $_SERVER["REQUEST_METHOD"]);
    // Redirect back to the form with an error. Avoid revealing script existence.
    header("Location: index.php?status=error&code=invalid_request");
    exit; // Stop script execution immediately.
}

// --- Include Database Configuration ---
// This will define the connect_db() function and connection parameters.
// If connect_db() fails, it will log the error and terminate the script.
require_once __DIR__ . '/../config/database.php';

// --- Input Definitions & Validation Rules ---
// Define expected inputs and allowed values for robustness.
$allowed_song_ids = ['help_me']; // Expand this array if more songs are added
$allowed_choices = ['v3', 'v4']; // Allowed version identifiers

// --- Retrieve and Validate Form Input ---
// Use filter_input for safer retrieval compared to direct $_POST access.

// 1. Song ID (from hidden form input)
// Validate it exists and is one of the allowed song identifiers.
$song_id = filter_input(INPUT_POST, 'song_id');
if (!$song_id || !in_array($song_id, $allowed_song_ids, true)) { // Use strict comparison
    error_log("Validation Error: Invalid or missing song_id submitted: " . ($song_id ?? 'NULL'));
    header("Location: index.php?status=error&code=invalid_song");
    exit;
}

// 2. Song Choice (from radio buttons)
// Validate it exists and is one of the allowed version choices.
$song_choice = filter_input(INPUT_POST, 'song_choice');
if (!$song_choice || !in_array($song_choice, $allowed_choices, true)) { // Use strict comparison
    error_log("Validation Error: Invalid or missing song_choice submitted: '" . ($song_choice ?? 'NULL') . "' for song_id: " . $song_id);
    // Use a specific status code for invalid user input vs. general errors
    header("Location: index.php?status=invalid");
    exit;
}

// 3. Feedback Text (from textarea)
// Sanitize the input to prevent XSS if this text is ever displayed back to users.
// FILTER_SANITIZE_FULL_SPECIAL_CHARS encodes special characters like < > & ".
$feedback_text_raw = filter_input(INPUT_POST, 'feedback_text', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
// Trim whitespace and treat purely whitespace input as empty.
$feedback_text = ($feedback_text_raw !== null) ? trim($feedback_text_raw) : null;
// Convert empty string to NULL for database storage if the field is nullable.
if ($feedback_text === '') {
    $feedback_text = null;
}
// Optional: Add length validation if needed.
// define('MAX_FEEDBACK_LENGTH', 5000);
// if ($feedback_text !== null && mb_strlen($feedback_text) > MAX_FEEDBACK_LENGTH) {
//     error_log("Validation Error: Feedback text exceeded maximum length for song_id: " . $song_id);
//     header("Location: index.php?status=error&code=too_long");
//     exit;
// }


// --- Database Interaction ---
$pdo = null; // Initialize PDO variable outside try block
$stmt = null; // Initialize Statement variable outside try block

try {
    // 1. Establish database connection using the function from database.php
    $pdo = connect_db(); // This function handles its own connection errors.

    // 2. Prepare the SQL INSERT statement with placeholders (?)
    // Using prepared statements is CRUCIAL to prevent SQL injection vulnerabilities.
    $sql = "INSERT INTO feedback (song_id, chosen_version, feedback_text, submitted_at) VALUES (?, ?, ?, NOW())";
    // Note: Added NOW() directly in SQL for the timestamp.

    $stmt = $pdo->prepare($sql);

    // 3. Bind the validated and sanitized values to the placeholders.
    // This securely associates the PHP variables with the placeholders in the SQL.
    // Explicitly setting parameter types (PDO::PARAM_STR, PDO::PARAM_NULL) adds type safety.
    $stmt->bindParam(1, $song_id, PDO::PARAM_STR);
    $stmt->bindParam(2, $song_choice, PDO::PARAM_STR);

    // Bind feedback text as NULL if it's empty, otherwise bind as a string.
    if ($feedback_text === null) {
        $stmt->bindParam(3, $feedback_text, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(3, $feedback_text, PDO::PARAM_STR);
    }

    // 4. Execute the prepared statement.
    $success = $stmt->execute(); // Returns true on success, false on failure (if ERRMODE_SILENT/WARNING) or throws exception (if ERRMODE_EXCEPTION)

    // 5. Handle Success or Failure
    if ($success) {
        // Log successful insertion (optional, good for tracking)
        // error_log("Feedback successfully inserted for song_id: " . $song_id . ", choice: " . $song_choice);
        // Redirect back to the main page with a success status.
        header("Location: index.php?status=success");
        exit; // Terminate script after redirect header.
    } else {
        // This block might only be reached if PDO::ATTR_ERRMODE is not PDO::ERRMODE_EXCEPTION.
        // It's good practice to include it for robustness.
        $errorInfo = $stmt->errorInfo(); // Get detailed error info from the statement
        error_log("Database Execute Failed for song_id: " . $song_id . " - SQLSTATE[" . $errorInfo[0] . "] Error Code: " . $errorInfo[1] . " Message: " . $errorInfo[2]);
        header("Location: index.php?status=error&code=db_execute");
        exit;
    }

} catch (PDOException $e) {
    // --- Catch Database Errors (e.g., from prepare() or execute() if ERRMODE_EXCEPTION is set) ---
    // Log the detailed error message server-side.
    error_log("Database PDOException in submit_feedback.php during execution: " . $e->getMessage() . " (Code: " . $e->getCode() . ") - SQL attempted: " . $sql);

    // Redirect the user to the main page with a generic database error code.
    header("Location: index.php?status=error&code=db_error");
    exit; // Terminate script.

} finally {
    // --- Cleanup ---
    // Ensure database statement and connection resources are released,
    // although PHP usually handles this automatically at script end.
    // Explicitly nullifying can help in complex scripts or with persistent connections.
    $stmt = null;
    $pdo = null;
    // error_log("Database connection closed in submit_feedback.php finally block."); // Optional log
}

?>
