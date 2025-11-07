<?php
require_once 'config.php';

// WARNING: run this once from the browser (http://localhost/bookhaven/create_admin.php)
// then delete the file for security.

$email = 'admin@bookhaven.com';
$password = 'admin123';
$name = 'Administrator';

$conn = getDBConnection();

$hashed = password_hash($password, PASSWORD_DEFAULT);

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $id = $row['id'];
    $upd = $conn->prepare("UPDATE users SET password = ?, role = 'admin', name = ? WHERE id = ?");
    $upd->bind_param('ssi', $hashed, $name, $id);
    if ($upd->execute()) {
        echo "Updated existing user with admin role and new password.\n";
    } else {
        echo "Failed to update user: " . $conn->error;
    }
    $upd->close();
} else {
    $ins = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, '', ?, 'admin')");
    $ins->bind_param('sss', $name, $email, $hashed);
    if ($ins->execute()) {
        echo "Created new admin user. Email: $email, Password: $password\n";
    } else {
        echo "Failed to create admin user: " . $conn->error;
    }
    $ins->close();
}

$stmt->close();
$conn->close();

echo "\nImportant: remove or rename this file after use to avoid a security risk.";
