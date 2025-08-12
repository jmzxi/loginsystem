<?php
// audit_log.php
require_once 'config.php';

function logLoginAttempt($pdo, $userId, $username, $email, $outcome) {
    $transactionNumber = uniqid('txn_', true);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $now = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO login_records (transaction_number, user_id, last_name, first_name, username, email, alias_user_defined, alias_machine_defined, user_type, initial_login_date, login_at, ip_address, user_agent, outcome)
      SELECT ?, u.id, u.last_name, u.first_name, u.username, u.email, u.alias_user_defined, u.alias_machine_defined, u.user_type, u.registered_at, ?, ?, ?, ?
      FROM users u WHERE u.id = ?");

    $stmt->execute([$transactionNumber, $now, $ip, $userAgent, $outcome, $userId]);
}
