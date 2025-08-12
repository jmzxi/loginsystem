<?php
// config.php

// MySQL connection
$host = 'localhost';
$db = 'loginModule';
$user = 'root';
$pass = 'jmzxi.0811';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// SMTP email config for PHPMailer
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_AUTH', true);
define('SMTP_USERNAME', 'loginmodule2025@gmail,com');
define('SMTP_PASSWORD', 'loginModule@.2025');
define('SMTP_SECURE', 'tls'); // or 'ssl'
define('SMTP_PORT', 587);
define('EMAIL_FROM', 'loginmodule2025.com');
define('EMAIL_FROM_NAME', 'Login Module');

// Google Client ID: PUT YOUR CLIENT ID HERE
define('GOOGLE_CLIENT_ID', '312625154111-gcs2kqgiuus4clbdir005crl9nuahgqf.apps.googleusercontent.com');
