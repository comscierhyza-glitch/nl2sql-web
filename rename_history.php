<?php
session_start();
require_once 'connection/database.php';

if (isset($_SESSION['user_id']) && isset($_POST['id']) && isset($_POST['title'])) {
    $historyId = (int)$_POST['id'];
    $userId = (int)$_SESSION['user_id'];
    $newTitle = trim($_POST['title']);

    if (!empty($newTitle)) {
        // I-update ang 'title' column sa database
        $stmt = $conn->prepare("UPDATE query_history SET title = ? WHERE id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("sii", $newTitle, $historyId, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }
}

header("Location: dashboard.php");
exit();
?>