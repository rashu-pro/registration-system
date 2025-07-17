<?php
// Configuration
$host = 'localhost';
$dbname = 'rs_flacofy';
$username = 'root';
$password = '';

// Create connection without specifying the database
$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Check if database exists
$db_check = $conn->query("SHOW DATABASES LIKE '$dbname'");
if ($db_check->num_rows == 0) {
    // Doesn't exist → Create it
    if ($conn->query("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "Database '$dbname' created successfully.<br>";
    } else {
        die("Failed to create database '$dbname': " . $conn->error);
    }
}

// Now select the database
$conn->select_db($dbname);

// Set MySQL session timezone to Asia/Dhaka
$conn->query("SET time_zone = '+06:00'");

// Set PHP timezone to Asia/Dhaka
date_default_timezone_set('Asia/Dhaka');

// Email Configuration
const SMTP_USER = 'flacofy0@gmail.com';
const SMTP_PASS = 'wdth zexq eymz tuvn';
