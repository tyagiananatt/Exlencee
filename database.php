<?php
// Database configuration
$servername = "sql310.infinityfree.com";
$username = "if0_40054126";
$password = "1p4N2CwtBw";
$dbname = "if0_40054126_exlence";

// Check if debug mode is enabled
$show_messages = isset($_GET['debug']) && $_GET['debug'] === 'true';

// Disable output buffering if not in debug mode
if (!$show_messages) {
    ob_start();
}

// First, create the database if it doesn't exist
try {
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    $conn->close();
} catch (Exception $e) {
    if ($show_messages) {
        die("Error: " . $e->getMessage());
    }
}

// Now connect to the created database
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset utf8mb4: " . $conn->error);
    }

    // Drop existing books table if it exists
    $sql = "DROP TABLE IF EXISTS books";
    if (!$conn->query($sql) && $show_messages) {
        throw new Exception("Error dropping books table: " . $conn->error);
    }

    // Create books table with updated structure
    $sql = "CREATE TABLE books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        description TEXT,
        content TEXT,
        cover_url VARCHAR(255),
        file_url VARCHAR(255),
        file_type VARCHAR(20) DEFAULT 'text',
        category VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (!$conn->query($sql) && $show_messages) {
        throw new Exception("Error creating books table: " . $conn->error);
    }

    // Create users table with role field
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        role VARCHAR(20) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($sql) && $show_messages) {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    // Create user_time_logs table
    $sql = "CREATE TABLE IF NOT EXISTS user_time_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        login_time DATETIME NOT NULL,
        logout_time DATETIME DEFAULT NULL,
        time_spent INT DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if (!$conn->query($sql) && $show_messages) {
        throw new Exception("Error creating user_time_logs table: " . $conn->error);
    }

    // Create sessions table
    $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if (!$conn->query($sql) && $show_messages) {
        throw new Exception("Error creating sessions table: " . $conn->error);
    }

    // Add role column to users table if it doesn't exist
    $check_role = "SHOW COLUMNS FROM users LIKE 'role'";
    $result = $conn->query($check_role);
    if ($result->num_rows === 0) {
        $add_role = "ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'";
        if (!$conn->query($add_role) && $show_messages) {
            throw new Exception("Error adding role column: " . $conn->error);
        }
    }

    // Create subjects table
    $sql = "CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        category VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($sql) && $show_messages) {
        throw new Exception("Error creating subjects table: " . $conn->error);
    }

    // Create user_subject_progress table
    $sql = "CREATE TABLE IF NOT EXISTS user_subject_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject_id INT NOT NULL,
        time_spent INT DEFAULT 0,
        progress_percentage FLOAT DEFAULT 0,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    )";

    if (!$conn->query($sql) && $show_messages) {
        throw new Exception("Error creating user_subject_progress table: " . $conn->error);
    }

    // Insert default subjects if they don't exist
    $default_subjects = [
        ['Programming', 'Learn various programming languages and concepts', 'Technical'],
        ['Web Development', 'Master web technologies and frameworks', 'Technical'],
        ['Data Structures', 'Understanding fundamental data structures', 'Technical'],
        ['Algorithms', 'Learn problem-solving and algorithmic thinking', 'Technical'],
        ['Database Management', 'Master database concepts and SQL', 'Technical']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO subjects (name, description, category) VALUES (?, ?, ?)");
    foreach ($default_subjects as $subject) {
        $stmt->bind_param("sss", $subject[0], $subject[1], $subject[2]);
        $stmt->execute();
    }
    $stmt->close();

    // Create community_posts table if it doesn't exist
    $sql_community_posts = "CREATE TABLE IF NOT EXISTS community_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        votes INT DEFAULT 0,
        likes INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";

    if (!$conn->query($sql_community_posts) && $show_messages) {
        throw new Exception("Error creating community_posts table: " . $conn->error);
    }

    // Create community_replies table
    $sql = "CREATE TABLE IF NOT EXISTS community_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        if ($show_messages) {
            echo "community_replies table created successfully<br>";
        }
    } else {
        if ($show_messages) {
            echo "Error creating community_replies table: " . $conn->error . "<br>";
        }
    }

    // Create post_likes table if it doesn't exist
    $sql_post_likes = "CREATE TABLE IF NOT EXISTS post_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES community_posts(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_like (post_id, user_id)
    )";

    if (!$conn->query($sql_post_likes) && $show_messages) {
        throw new Exception("Error creating post_likes table: " . $conn->error);
    }

    // Add likes column to community_posts table if it doesn't exist
    $sql_add_likes = "ALTER TABLE community_posts ADD COLUMN IF NOT EXISTS likes INT DEFAULT 0";
    if (!$conn->query($sql_add_likes) && $show_messages) {
        throw new Exception("Error adding likes column: " . $conn->error);
    }

    // If debug mode is enabled, show success message
    if ($show_messages) {
        echo "All database tables have been created successfully!";
    }

    // Clear output buffer if not in debug mode
    if (!$show_messages) {
        ob_end_clean();
    }

} catch (Exception $e) {
    if ($show_messages) {
        die("Error: " . $e->getMessage());
    }
    if (!$show_messages) {
        ob_end_clean();
    }
}

// Don't close the connection here as it will be used by other files
?> 