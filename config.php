<?php
session_start();

// ----------------------------------------------------
// 1. DATABASE CONFIGURATION (ALL CREDENTIALS ARE NOW SET)
// ----------------------------------------------------
// Your InfinityFree Credentials:
define('DB_HOST', 'sql310.infinityfree.com');       // MySQL Hostname
define('DB_USER', 'if0_40054126');           // MySQL Username
define('DB_PASS', '1p4N2CwtBw');             // MySQL Password
define('DB_NAME', 'if0_40054126_exlence');   // Full Database Name

// General Configuration
define('SITE_NAME', 'Excellence');
define('USERS_TABLE', 'users'); // Matches your created table name

// ----------------------------------------------------
// 2. DATABASE CONNECTION FUNCTION
// ----------------------------------------------------

/**
 * Establishes a connection to the MySQL database.
 * @return mysqli|false The database connection object or false on failure.
 */
function connectDB() {
    // The @ suppresses the default PHP error messages for a cleaner output
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        // Log the detailed error (for developer access only)
        error_log("Database Connection Failed: " . $conn->connect_error);

        // Display a generic error to the public
        die("<h1>Database Connection Error</h1><p>The system is currently unable to connect to the database. Please check back shortly.</p>");
    }

    // Set character set for proper data handling
    $conn->set_charset("utf8mb4");
    return $conn;
}


// ----------------------------------------------------
// 3. DATABASE USER FUNCTIONS (For Sign-up and Login)
// ----------------------------------------------------

/**
 * Adds a new user to the 'users' table.
 * @return bool True on success, false on failure (e.g., username already exists).
 */
function addUser($username, $email, $password) {
    $conn = connectDB();
    if (!$conn) return false;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare statement to prevent SQL injection and insert data
    $stmt = $conn->prepare("INSERT INTO " . USERS_TABLE . " (username, email, password) VALUES (?, ?, ?)");
    
    if ($stmt === false) {
        error_log("addUser SQL Prepare Error: " . $conn->error);
        $conn->close();
        return false;
    }

    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    $success = $stmt->execute();
    
    if (!$success) {
        error_log("addUser Execute Error: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Authenticates a user against the 'users' table.
 * @return array|false The user's row data (id, username, role) on success, false otherwise.
 */
function authenticateUser($username, $password) {
    $conn = connectDB();
    if (!$conn) return false;

    // Select user details
    $stmt = $conn->prepare("SELECT id, password, role FROM " . USERS_TABLE . " WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            $stmt->close();
            $conn->close();
            
            // Return essential user data to be stored in the session
            return ['id' => $user['id'], 'username' => $username, 'role' => $user['role']];
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

// ----------------------------------------------------
// 4. SESSION & HELPER FUNCTIONS
// ----------------------------------------------------

function isLoggedIn() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Clear session data before redirecting for security
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit();
    }
}