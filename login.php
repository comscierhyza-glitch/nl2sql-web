<?php
session_start();
$host = 'localhost';
$db = 'sqlg1_db';
$user = 'root';
$pass = '';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $userRecord = $stmt->fetch();

            if ($userRecord && password_verify($password, $userRecord['password'])) {
                $_SESSION['user_id'] = $userRecord['id'];
                $_SESSION['username'] = $userRecord['username'];
                $_SESSION['fullname'] = $userRecord['fullname'];
                $_SESSION['role'] = $userRecord['role'] ?? 'user'; // I-save ang role sa session

                // Role-based Redirection
                if (isset($userRecord['role']) && $userRecord['role'] === 'admin') {
                    header("Location: admin.php"); // o admin.php (depende sa ngalan sa imong admin file)
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "Database Connection Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - NL2SQL Workspace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            height: 100vh;
            background: #f8fafc;
            color: #1e293b;
            overflow: hidden;
        }

        .auth-brand {
            flex: 1;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
        }

        .auth-brand h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .auth-brand h1 i {
            color: #3b82f6;
        }

        .auth-brand p {
            font-size: 1.1rem;
            color: #94a3b8;
            max-width: 450px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            font-size: 0.95rem;
            color: #cbd5e1;
        }

        .feature-list div {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature-list i {
            color: #10b981;
        }

        .auth-container {
            width: 480px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            box-shadow: -10px 0 25px rgba(0, 0, 0, 0.03);
            z-index: 10;
        }

        .auth-header h2 {
            font-size: 1.75rem;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .auth-header p {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .auth-footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .auth-brand {
                display: none;
            }

            .auth-container {
                width: 100%;
                height: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="auth-brand">
        <h1><i class="fas fa-terminal"></i> NL2SQL Workspace</h1>
        <p>Transform English statements into enterprise-ready SQL queries instantly using advanced AI and schema-aware normalization.</p>
        <div class="feature-list">
            <div><i class="fas fa-check-circle"></i> Real-time Natural Language Translation</div>
            <div><i class="fas fa-check-circle"></i> Dynamic Database Schema Import (.sql)</div>
            <div><i class="fas fa-check-circle"></i> Explainable AI (XAI) Query Insights</div>
        </div>
    </div>

    <div class="auth-container">
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p>Please enter your details to sign in.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email or Username</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username or email" required>
                </div>
            </div>

            <!-- PASSWORD FIELD WITH FORGOT LINK -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="font-size: 0.85rem; color: #475569; font-weight: 600;">Password</label>
                    <a href="forgot_password.php" style="font-size: 0.8rem; color: #2563eb; text-decoration: none; font-weight: 500;">Forgot password?</a>
                </div>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 10px 10px 10px 38px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.9rem;">
                </div>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create account</a>
        </div>
    </div>

</body>

</html>