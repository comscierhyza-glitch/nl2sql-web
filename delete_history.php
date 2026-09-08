<?php
session_start();
require_once 'connection/database.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = (int)$_GET['id'];
    $userId = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM query_history WHERE id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

// 📌 I-clear ang last state aron dili mogawas ang green badge pagkahuman og delete
unset($_SESSION['last_nlp']);

header("Location: dashboard.php");
exit();