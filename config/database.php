<?php // config/database.php

// IMPORTANT: This file now reads credentials from environment variables,
// which are expected to be set by the Docker Compose environment.

// --- Database Credentials from Environment Variables ---
// Use getenv() to read environment variables. Provide default values (e.g., null or 'localhost')
// as fallbacks, although they should ideally always be set in a Docker environment via .env.
$dbHost = getenv('DB_HOST') ?: 'db'; // Default to 'db' which is the service name in docker-compose
$dbName = getenv('DB_NAME') ?: null;
$dbUser = getenv('DB_USER') ?: null;
$dbPass = getenv('DB_PASS') ?: null;
$dbPort = getenv('DB_PORT') ?: '3306'; // Default MySQL port
$dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';

// Check if essential variables are set
if (!$dbName || !$dbUser || !$dbPass) {
    error_log("Database configuration environment variables (DB_NAME, DB_USER, DB_PASS) are not fully set.");
    // You might want to die here or handle this more gracefully depending on the context.
    // For development, it's useful to know they are missing.
    // Check if the script is running in a CLI environment or web server
    if (php_sapi_name() !== 'cli') {
         // Only output error to browser if not running in CLI
         // Removed the echo statement below that caused the "headers already sent" error
         // echo "Error: Database environment variables are not configured. Check your .env file and docker-compose setup.";
    }
    die("Error: Database environment variables not configured. Check logs."); // Terminate script execution regardless, provide hint in message
}

// --- PDO Configuration ---

// Data Source Name (DSN) for PDO
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

// Options for PDO connection
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Make the default fetch be an associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation mode for real prepared statements
];

// --- Database Connection Function ---

/**
 * Establishes a database connection using PDO.
 * Reads credentials from environment variables.
 *
 * @return PDO PDO database connection object.
 * @throws PDOException If the connection fails. Logs error and terminates script.
 */
function connect_db(): PDO {
    // Use variables defined above in this script's scope
    global $dsn, $dbUser, $dbPass, $options;

    try {
        // Create a new PDO instance
        $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        // error_log("Database connection successful to host: " . (getenv('DB_HOST') ?: 'db')); // Optional success log
        return $pdo;
    } catch (\PDOException $e) {
        // Log the detailed error message server-side (important for production)
        error_log("Database Connection Error: " . $e->getMessage() . " (DSN: " . $dsn . ", User: " . $dbUser . ")");

        // Terminate the script. Avoid showing detailed exception messages to end-users.
        // The redirect logic in submit_feedback.php will handle showing a generic error message.
        // Removed the echo statement below that caused the "headers already sent" error
        // if (php_sapi_name() !== 'cli') {
        //     echo "Database connection failed. Please check configuration or contact support. Error details have been logged.";
        // }
        die("Database connection failed. Check logs."); // Terminate script execution, provide hint in message

        // Alternatively, re-throw the exception if you have higher-level error handling
        // throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

?>
