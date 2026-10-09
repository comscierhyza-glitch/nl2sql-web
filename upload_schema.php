<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?restricted=upload");
    exit;
}

// Handle schema removal action
if (isset($_POST['remove_schema'])) {
    unset($_SESSION['schema'], $_SESSION['parsed_schema_array'], $_SESSION['schema_filename'], $_SESSION['ai_cache']);
    header("Location: dashboard.php");
    exit;
}

// Guard against uploads exceeding PHP core post_max_size directive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    header("Location: dashboard.php?error=" . urlencode("The uploaded file exceeds the server post_max_size directive (.user.ini)."));
    exit;
}

require_once __DIR__ . '/modules/schema_parser.php';

// Dawata bisan 'schema_file' o 'sql_file' ang name sa input form
$file = $_FILES['schema_file'] ?? $_FILES['sql_file'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $file !== null) {
    $allowed_extensions = ['sql', 'txt'];

    // 1. Validate file upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE) {
            header("Location: dashboard.php?error=" . urlencode("The file exceeds the maximum upload limit configured in your server settings (.user.ini)."));
            exit;
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            header("Location: dashboard.php?error=" . urlencode("Please select a file before clicking upload."));
            exit;
        }
        header("Location: dashboard.php?error=" . urlencode("Error uploading file. Status code: " . $file['error']));
        exit;
    }

    // 2. Validate file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        header("Location: dashboard.php?error=" . urlencode("Invalid file format. Only .sql and .txt files are allowed."));
        exit;
    }

    // 3. Read uploaded temporary file stream
    $sql_content = file_get_contents($file['tmp_name']);

    if ($sql_content !== false) {
        $parsedSchema = parseSQLSchema($sql_content);

        if (empty($parsedSchema)) {
            header("Location: dashboard.php?error=" . urlencode("No valid CREATE TABLE statements detected in the uploaded file."));
            exit;
        }

        $compactSchema = compressSchemaForAI($parsedSchema);

        // Store active schema in session
        $_SESSION['schema'] = $compactSchema;
        $_SESSION['parsed_schema_array'] = $parsedSchema;
        $_SESSION['schema_filename'] = $file['name'];
        unset($_SESSION['ai_cache']); // Invalidate previous query cache

        header("Location: dashboard.php?upload=success");
        exit;
    } else {
        header("Location: dashboard.php?error=" . urlencode("Unable to read the uploaded file."));
        exit;
    }
} else {
    header("Location: dashboard.php?error=" . urlencode("No file was selected for upload."));
    exit;
}
