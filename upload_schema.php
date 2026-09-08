<?php
session_start();

// Check if a file was actually uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['schema_file'])) {
    
    $file = $_FILES['schema_file'];
    $allowed_extensions = ['sql', 'txt'];
    
    // 1. Validate File Extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        die("Error: Invalid file format. Only .sql files are allowed.");
    }
    
    // 2. Validate File Size (Limit to 2MB to prevent abuse)
    if ($file['size'] > 2 * 1024 * 1024) {
        die("Error: File is too large. Maximum size is 2MB.");
    }

    // 3. Read the file contents from the system's secure temp directory
    $tmp_path = $file['tmp_name'];
    $sql_content = file_get_contents($tmp_path);

    if ($sql_content !== false) {
        
        // 4. STRIP SENSITIVE DATA: Remove all INSERT statements using Regex
        // This regex looks for "INSERT INTO" and removes everything until the semicolon
        $clean_schema = preg_replace('/INSERT\s+INTO\s+.*?;/is', '', $sql_content);
        
        // Optional: Remove SQL comments to save AI token limits
        $clean_schema = preg_replace('/--.*$/m', '', $clean_schema);
        $clean_schema = preg_replace('/\/\*.*?\*\//s', '', $clean_schema);
        
        // Remove excess blank lines
        $clean_schema = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $clean_schema);

        // 5. Save the cleaned schema to the session for the AI Prompt to use
        $_SESSION['schema'] = trim($clean_schema);

        // NOTE: PHP automatically deletes the temp file after the script ends,
        // so no need for manual unlink() when using $_FILES['tmp_name'].

        // Redirect back to dashboard with success message
        header("Location: dashboard.php?upload=success");
        exit;
    } else {
        die("Error: Could not read the uploaded file.");
    }
} else {
    die("Error: No file uploaded.");
}
?>