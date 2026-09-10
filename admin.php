<?php
session_start();
require_once 'connection/database.php';

// -------------------------------------------------------------------------
// 1. SECURITY & ROLE CHECK (Admin Only Access)
// -------------------------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$checkAdmin = mysqli_query($conn, "SELECT role FROM users WHERE id = $userId");
$userRole = mysqli_fetch_assoc($checkAdmin)['role'] ?? 'user';

if ($userRole !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// -------------------------------------------------------------------------
// 2. ACTION HANDLERS (Delete User / Delete Query Log)
// -------------------------------------------------------------------------
if (isset($_GET['delete_user'])) {
    $delUserId = (int)$_GET['delete_user'];
    if ($delUserId !== $userId) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $delUserId");
    }
    header("Location: admin.php");
    exit();
}

if (isset($_GET['delete_log'])) {
    $delLogId = (int)$_GET['delete_log'];
    mysqli_query($conn, "DELETE FROM query_history WHERE id = $delLogId");
    header("Location: admin.php");
    exit();
}

// -------------------------------------------------------------------------
// 3. STATS & ANALYTICS DATA
// -------------------------------------------------------------------------
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'] ?? 0;
$totalQueries = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM query_history"))['count'] ?? 0;
$totalGuestQueries = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM query_history WHERE user_id IS NULL"))['count'] ?? 0;

// -------------------------------------------------------------------------
// 4. FETCH USERS & GLOBAL AUDIT LOGS
// -------------------------------------------------------------------------
$usersQuery = mysqli_query($conn, "SELECT id, username, email, role, created_at FROM users ORDER BY id DESC");

$logsQuery = mysqli_query($conn, "
    SELECT q.id, q.natural_language, q.generated_sql, q.created_at, u.username 
    FROM query_history q 
    LEFT JOIN users u ON q.user_id = u.id 
    ORDER BY q.id DESC LIMIT 50
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>NL2SQL | System Administration Panel</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .admin-header {
            background: #0f172a;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .card-table {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }

        .card-table h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.1rem;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .scrollable-table-wrapper {
            overflow-y: auto;
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
            width: 100%;
            -webkit-overflow-scrolling: touch;
        }

        .scroll-hint {
            display: none;
        }

        .users-scroll {
            max-height: 240px;
        }

        .logs-scroll {
            max-height: 380px;
        }

        .scrollable-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollable-table-wrapper::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .scrollable-table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }

        th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        tr:hover {
            background: #f8fafc;
        }

        .badge-admin {
            background: #dbeafe;
            color: #1e40af;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-user {
            background: #f1f5f9;
            color: #475569;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .btn-delete {
            color: #ef4444;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .btn-delete:hover {
            background: #fef2f2;
        }

        /* =======================================================
           MOBILE VIEWPORT OPTIMIZATIONS (EDGE-TO-EDGE FIT)
        ======================================================= */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                padding: 12px 14px !important;
                gap: 12px !important;
            }

            .admin-header>div {
                width: 100% !important;
                display: flex !important;
                flex-wrap: wrap !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 8px !important;
            }

            .admin-header h2 {
                font-size: 1.05rem !important;
            }

            /* Gipagamyan ang kilid aron dili mausik ang screen sa cellphone */
            .admin-container {
                padding: 10px 8px !important;
                margin: 10px auto !important;
                width: 100% !important;
            }

            .stats-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
                margin-bottom: 16px !important;
            }

            .stat-card {
                padding: 14px 14px !important;
                border-radius: 8px !important;
                gap: 12px !important;
            }

            .stat-icon {
                width: 44px !important;
                height: 44px !important;
                font-size: 1.25rem !important;
            }

            /* Gi-adjust ang card padding gikan 20px ngadto sa 10px */
            .card-table {
                padding: 14px 10px !important;
                border-radius: 8px !important;
                margin-bottom: 16px !important;
            }

            .card-table h3 {
                font-size: 0.95rem !important;
                margin-bottom: 10px !important;
            }

            /* Compact table cells */
            table {
                min-width: 500px !important;
            }

            th,
            td {
                padding: 8px 10px !important;
                font-size: 0.78rem !important;
            }

            .scroll-hint {
                display: flex !important;
                align-items: center;
                gap: 6px;
                font-size: 0.72rem;
                color: #64748b;
                background: #f1f5f9;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 5px 8px;
                margin-bottom: 8px;
            }

            .admin-header a,
            .admin-header button {
                font-size: 0.78rem !important;
                padding: 5px 10px !important;
            }
        }
    </style>
</head>

<body>

    <!-- ADMIN HEADER -->
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-user-shield" style="font-size: 1.3rem; color: #3b82f6;"></i>
            <h2 style="margin: 0; font-size: 1.15rem;">NL2SQL Admin Control Center</h2>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 0.85rem; color: #cbd5e1;">Logged in: <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong></span>
            <div style="display: flex; gap: 8px;">
                <a href="dashboard.php" style="background: #2563eb; color: white; text-decoration: none; padding: 5px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-terminal"></i> Workspace
                </a>
                <a href="logout.php" style="background: #ef4444; color: white; text-decoration: none; padding: 5px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">Logout</a>
            </div>
        </div>
    </div>

    <div class="admin-container">

        <!-- STATS CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Total Registered Users</h4>
                    <span style="font-size: 1.4rem; font-weight: 700; color: #0f172a;"><?= $totalUsers ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #ecfdf5; color: #10b981;">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Total Queries Processed</h4>
                    <span style="font-size: 1.4rem; font-weight: 700; color: #0f172a;"><?= $totalQueries ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">
                    <i class="fas fa-user-secret"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Guest Queries Processed</h4>
                    <span style="font-size: 1.4rem; font-weight: 700; color: #0f172a;"><?= $totalGuestQueries ?></span>
                </div>
            </div>
        </div>

        <!-- USER MANAGEMENT TABLE (SCROLLABLE) -->
        <div class="card-table">
            <h3><i class="fas fa-users-cog text-primary"></i> User Management</h3>

            <p class="scroll-hint"><i class="fas fa-arrows-left-right"></i> Swipe left/right to view all columns</p>
            <div class="scrollable-table-wrapper users-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($usersQuery)): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="<?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                        <?= strtoupper($u['role'] ?? 'USER') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                        <a href="admin.php?delete_user=<?= $u['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-style: italic; font-size: 0.75rem;">(Current)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- GLOBAL QUERY AUDIT LOG TABLE (SCROLLABLE) -->
        <div class="card-table">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-list-alt text-primary"></i> Global System Query Audit Logs
                </h3>

                <a href="export_logs.php" style="background-color: #10b981; color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>

            <p class="scroll-hint"><i class="fas fa-arrows-left-right"></i> Swipe left/right to view all columns</p>
            <div class="scrollable-table-wrapper logs-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Natural Language Statement</th>
                            <th>Generated SQL</th>
                            <th>Timestamp</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($logsQuery) > 0): ?>
                            <?php while ($log = mysqli_fetch_assoc($logsQuery)): ?>
                                <tr>
                                    <td><?= $log['id'] ?></td>
                                    <td>
                                        <?php if (!empty($log['username'])): ?>
                                            <strong><?= htmlspecialchars($log['username']) ?></strong>
                                        <?php else: ?>
                                            <span style="background: #f1f5f9; color: #64748b; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-user-secret"></i> Guest
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($log['natural_language']) ?>
                                    </td>
                                    <td style="font-family: monospace; color: #2563eb; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($log['generated_sql']) ?>
                                    </td>
                                    <td><?= $log['created_at'] ?></td>
                                    <td>
                                        <a href="admin.php?delete_log=<?= $log['id'] ?>" class="btn-delete" onclick="return confirm('Delete this log entry?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px;">No query logs recorded in system database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>

</html>