<?php
// logout.php
require_once 'helpers.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
    $stmt->execute([$userId]);
}

$_SESSION = [];
session_destroy();

setcookie('remember_series', '', time() - 3600, '/', '', false, true);
setcookie('remember_token', '', time() - 3600, '/', '', false, true);

header('Location: ../start-login.html');
exit;
