<?php
session_start();

// Handle schema removal action
if (isset($_POST['remove_schema'])) {
    unset($_SESSION['schema'], $_SESSION['parsed_schema_array'], $_SESSION['schema_filename'], $_SESSION['ai_cache']);
    header("Location: dashboard.php");
    exit;
}

// Guard against uploads exceeding PHP core post_max_size directive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    die("Error: The uploaded file exceeds the server post_max_size directive in php.ini.");
}

require_once __DIR__ . '/modules/schema_parser.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['schema_file'])) {
    $file = $_FILES['schema_file'];
    $allowed_extensions = ['sql', 'txt'];

    // 1. Validate file upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE) {
            die("Error: The file exceeds upload_max_filesize configured in php.ini.");
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            die("Error: No file was selected for upload.");
        }
        die("Error uploading file. Status code: " . $file['error']);
    }

    // 2. Validate file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        die("Error: Invalid file format. Only .sql and .txt files are allowed.");
    }

    // 3. Read uploaded temporary file stream
    $sql_content = file_get_contents($file['tmp_name']);

    if ($sql_content !== false) {
        $parsedSchema = parseSQLSchema($sql_content);

        if (empty($parsedSchema)) {
            die("Error: No valid CREATE TABLE statements detected in the uploaded file.");
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
        die("Error: Unable to read the uploaded file.");
    }
} else {
    die("Error: No file uploaded.");
}
?>