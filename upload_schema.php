<?php
session_start();

// Include the schema parser module
require_once __DIR__ . '/modules/schema_parser.php';

// Check if a file was actually uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['schema_file'])) {

    $file = $_FILES['schema_file'];
    $allowed_extensions = ['sql', 'txt'];

    // 1. Validate Upload Errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE) {
            die("Error: File exceeds server upload limit. Please upload structure-only DDL.");
        }
        die("Error uploading file. Code: " . $file['error']);
    }

    // 2. Validate File Extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        die("Error: Invalid file format. Only .sql and .txt files are allowed.");
    }

    // 3. Validate File Size (Expanded to 50MB to handle large database dumps)
    if ($file['size'] > 50 * 1024 * 1024) {
        die("Error: File is too large. Maximum allowed size is 50MB.");
    }

    // 4. Read file content from temporary directory
    $tmp_path = $file['tmp_name'];
    $sql_content = file_get_contents($tmp_path);

    if ($sql_content !== false) {

        // 5. Parse tables, columns, primary keys, and foreign keys
        $parsedSchema = parseSQLSchema($sql_content);

        if (empty($parsedSchema)) {
            die("Error: No valid CREATE TABLE statements detected in the uploaded file.");
        }

        // 6. Compress schema into ultra-compact format for AI token efficiency
        $compactSchema = compressSchemaForAI($parsedSchema);

        // 7. Store results in Session for the UI badges and AI Prompt
        $_SESSION['schema'] = $compactSchema;
        $_SESSION['parsed_schema_array'] = $parsedSchema;
        $_SESSION['schema_filename'] = $file['name'];

        // 8. Redirect back to dashboard with success message
        header("Location: dashboard.php?upload=success");
        exit;
    } else {
        die("Error: Could not read the uploaded file.");
    }
} else {
    die("Error: No file uploaded.");
}
?>