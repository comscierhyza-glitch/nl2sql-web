<?php
$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    echo '<div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 20px; border-radius: 10px; text-align: center;">
            <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: 10px;"></i>
            <h3>Guest Mode Active</h3>
            <p>Please <a href="login.php" style="font-weight: 700; text-decoration: underline; color: #2563eb;">Log In</a> to view and manage your account settings.</p>
          </div>';
    return;
}

$userId = (int)$_SESSION['user_id'];
$statusMsg = "";
$statusType = "";

// 1. HANDLE PROFILE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $selectedDialect = trim($_POST['default_dialect'] ?? 'MySQL');

    if (!empty($newUsername) && !empty($newEmail)) {
        if (isset($conn)) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssi", $newUsername, $newEmail, $userId);
                if ($stmt->execute()) {
                    $_SESSION['username'] = $newUsername;
                    $_SESSION['selected_dialect'] = $selectedDialect;
                    $statusMsg = "Profile settings successfully updated!";
                    $statusType = "success";
                } else {
                    $statusMsg = "Failed to update profile. Email or username might already exist.";
                    $statusType = "error";
                }
                $stmt->close();
            }
        }
    } else {
        $statusMsg = "Username and Email fields are required.";
        $statusType = "error";
    }
}

// 2. FETCH CURRENT USER DATA GIKAN SA DATABASE
$currentUser = [
    'username' => $_SESSION['username'] ?? '',
    'email' => '',
    'role' => $userRole ?? 'user'
];

if (isset($conn)) {
    $stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $currentUser = $row;
        }
        $stmt->close();
    }
}

$activeDialect = $_SESSION['selected_dialect'] ?? 'MySQL';
?>

<div style="max-width: 750px; margin: 0 auto;">

    <!-- HEADER TITLE -->
    <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin: 0 0 6px 0; font-size: 1.35rem; color: #1e293b; font-weight: 700;">
                <i class="fas fa-cog" style="color: #2563eb;"></i> Account & Workspace Settings
            </h2>
            <p style="margin: 0; font-size: 0.88rem; color: #64748b;">
                Manage your credentials, role privileges, and default SQL dialect preference.
            </p>
        </div>
        <a href="dashboard.php" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; padding: 7px 14px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fas fa-arrow-left"></i> Back to Workspace
        </a>
    </div>

    <!-- ALERT NOTIFICATION -->
    <?php if (!empty($statusMsg)): ?>
        <div style="padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 8px; <?= $statusType === 'success' ? 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' : 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;' ?>">
            <i class="fas <?= $statusType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($statusMsg) ?>
        </div>
    <?php endif; ?>

    <!-- PROFILE SETTINGS CARD -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 26px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">

        <form method="POST">

            <!-- 1. USERNAME -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; font-size: 0.88rem; color: #334155; margin-bottom: 6px;">
                    Username / Full Name
                </label>
                <input type="text" name="username" value="<?= htmlspecialchars($currentUser['username']) ?>" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 0.92rem; box-sizing: border-box; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'">
            </div>

            <!-- 2. EMAIL ADDRESS -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; font-size: 0.88rem; color: #334155; margin-bottom: 6px;">
                    Email Address
                </label>
                <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 0.92rem; box-sizing: border-box; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'">
            </div>

            <!-- 3. ROLE / PRIVILEGE (CLEAN READ-ONLY) -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; font-size: 0.88rem; color: #334155; margin-bottom: 6px;">
                    Account Privilege / Role
                </label>
                <div style="display: flex; align-items: center; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <span style="font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; <?= strtolower($currentUser['role'] ?? '') === 'admin' ? 'background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;' : 'background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1;' ?>">
                        <i class="fas <?= strtolower($currentUser['role'] ?? '') === 'admin' ? 'fa-shield-alt' : 'fa-user' ?>"></i> <?= htmlspecialchars($currentUser['role'] ?? 'User') ?>
                    </span>
                </div>
            </div>

            <!-- 4. DEFAULT TARGET DIALECT -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 600; font-size: 0.88rem; color: #334155; margin-bottom: 6px;">
                    Default Target SQL Dialect
                </label>
                <select name="default_dialect" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 0.92rem; background: white; color: #1e293b; font-weight: 500; outline: none; cursor: pointer;">
                    <option value="MySQL" <?= $activeDialect === 'MySQL' ? 'selected' : '' ?>>MySQL / MariaDB</option>
                    <option value="PostgreSQL" <?= $activeDialect === 'PostgreSQL' ? 'selected' : '' ?>>PostgreSQL</option>
                    <option value="SQLite" <?= $activeDialect === 'SQLite' ? 'selected' : '' ?>>SQLite</option>
                    <option value="Microsoft SQL Server" <?= $activeDialect === 'Microsoft SQL Server' ? 'selected' : '' ?>>MS SQL Server</option>
                </select>
                <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px; display: block;">This dialect will automatically be selected on new sessions.</small>
            </div>

            <!-- 5. SUBMIT BUTTON -->
            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" name="update_profile" style="background: #2563eb; color: white; border: none; padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2); transition: all 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>

        </form>

    </div>

</div>