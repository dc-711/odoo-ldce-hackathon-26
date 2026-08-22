<?php

// Database configuration
$host = "localhost";
$username = "root";
$password = "nikkosan007";
$database = "globetrotter";

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character encoding
$conn->set_charset("utf8mb4");

?>