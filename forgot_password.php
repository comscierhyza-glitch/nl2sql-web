<?php
session_start();
require_once 'connection/database.php';

/** @var mysqli $conn */
global $conn;

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($newPassword) || empty($confirmPassword)) {
        $message = "Please fill in all required fields.";
        $messageType = "danger";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "New passwords do not match.";
        $messageType = "danger";
    } else {
        // 1. Verify if username and email match in DB
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $userId = $user['id'];

                // 2. Hash new password & update database
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($updateStmt) {
                    $updateStmt->bind_param("si", $hashedPassword, $userId);
                    if ($updateStmt->execute()) {
                        $message = "Password reset successfully! You can now log in with your new password.";
                        $messageType = "success";
                    } else {
                        $message = "Failed to update password. Please try again.";
                        $messageType = "danger";
                    }
                    $updateStmt->close();
                }
            } else {
                $message = "Account verification failed. Username and email combination not found.";
                $messageType = "danger";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NL2SQL | Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .reset-card {
            background: white;
            width: 100%;
            max-width: 420px;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        .alert {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 18px;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            color: #475569;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .input-box {
            position: relative;
        }

        .input-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .input-box input {
            width: 100%;
            padding: 10px 10px 10px 38px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        .btn-reset {
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            padding: 11px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.95rem;
            margin-top: 10px;
        }

        .btn-reset:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>

    <div class="reset-card">
        <div style="text-align: center; margin-bottom: 25px;">
            <i class="fas fa-key" style="font-size: 2.2rem; color: #2563eb; margin-bottom: 10px;"></i>
            <h2 style="margin: 0; color: #0f172a; font-size: 1.3rem;">Reset Password</h2>
            <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.85rem;">Verify your identity to create a new password</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label>Username</label>
                <div class="input-box">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" required placeholder="Enter your username">
                </div>
            </div>

            <div class="form-group">
                <label>Registered Email Address</label>
                <div class="input-box">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" required placeholder="Enter your email address">
                </div>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <div class="input-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="new_password" required placeholder="••••••••">
                </div>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="input-box">
                    <i class="fas fa-check-circle"></i>
                    <input type="password" name="confirm_password" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn-reset">Update Password</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: #64748b; text-decoration: none; font-size: 0.85rem;">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

</body>

</html>