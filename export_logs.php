<?php
session_start();
require_once 'connection/database.php';

// 1. Restrict access to authenticated admins only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Set headers for CSV file download
$filename = "audit_logs_" . date('Y-m-d_H-i') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 3. Open output stream
$output = fopen('php://output', 'w');

// 4. Set CSV Column Headers
fputcsv($output, ['ID', 'User', 'Role', 'Natural Language Prompt', 'Generated SQL', 'Timestamp']);

// 📌 Pahibalo sa VS Code Linter aron mawala ang red error sa $conn
/** @var mysqli $conn */

// 5. Fetch records from query_history and users tables
$query = "SELECT qh.id, u.username, u.role, qh.natural_language, qh.generated_sql, qh.created_at 
          FROM query_history qh 
          LEFT JOIN users u ON qh.user_id = u.id 
          ORDER BY qh.id DESC";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['id'],
            !empty($row['username']) ? $row['username'] : 'Guest',
            !empty($row['role']) ? ucfirst($row['role']) : 'Guest',
            $row['natural_language'],
            $row['generated_sql'],
            $row['created_at'] ?? 'N/A'
        ]);
    }
}

fclose($output);
exit();
?>