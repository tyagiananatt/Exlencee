<?php
// Database configuration
$db_host = 'sql310.infinityfree.com';     // Database host
$db_user = 'if0_40054126';         // Database user
$db_password = '1p4N2CwtBw';         // Database password
$db_name = 'if0_40054126_exlence';   // Database name

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?> 