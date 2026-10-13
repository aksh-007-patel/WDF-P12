<?php
// config.php - Database connection
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "event_db";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Upload settings (file validation)
define("UPLOAD_DIR", __DIR__ . "/uploads/");
define("MAX_FILE_SIZE", 2 * 1024 * 1024); // 2 MB
$ALLOWED_EXTS = ["jpg", "jpeg", "png", "gif", "webp"];
$ALLOWED_MIMES = ["image/jpeg", "image/png", "image/gif", "image/webp"];

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Helper: write audit log
function audit_log($conn, $event_id, $action, $details = "") {
    $stmt = $conn->prepare("INSERT INTO audit_log (event_id, action, details) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iss", $event_id, $action, $details);
        $stmt->execute();
        $stmt->close();
    }
}

// Helper: validate + store poster, returns [filename|null, error|null]
// $file = $_FILES['poster'], $oldFile = existing poster to delete on replace
function handle_poster_upload($file, $oldFile = null) {
    global $ALLOWED_EXTS, $ALLOWED_MIMES;

    // No file uploaded -> keep old / null (poster is optional)
    if (!isset($file) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return [$oldFile, null];
    }
    if ($file["error"] !== UPLOAD_ERR_OK) {
        return [null, "File upload failed (error code " . $file["error"] . ")."];
    }
    // 1. Size validation
    if ($file["size"] > MAX_FILE_SIZE) {
        return [null, "File too large. Maximum allowed size is 2 MB."];
    }
    if ($file["size"] === 0) {
        return [null, "Empty file is not allowed."];
    }
    // 2. Extension validation
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, $ALLOWED_EXTS)) {
        return [null, "Invalid file type. Only JPG, JPEG, PNG, GIF, WEBP allowed."];
    }
    // 3. MIME + real image validation (server-side)
    $mime = mime_content_type($file["tmp_name"]);
    if (!in_array($mime, $ALLOWED_MIMES)) {
        return [null, "Invalid file content. Upload a valid image file."];
    }
    $imgInfo = @getimagesize($file["tmp_name"]);
    if ($imgInfo === false) {
        return [null, "Uploaded file is not a valid image."];
    }
    // 4. Secure renaming to avoid collisions + directory traversal
    $newName = "event_" . date("Ymd_His") . "_" . uniqid() . "." . $ext;
    $dest = UPLOAD_DIR . $newName;
    if (!move_uploaded_file($file["tmp_name"], $dest)) {
        return [null, "Failed to save uploaded file."];
    }
    // Delete old poster on successful replace
    if ($oldFile && $oldFile !== $newName) {
        $oldPath = UPLOAD_DIR . basename($oldFile);
        if (is_file($oldPath)) @unlink($oldPath);
    }
    return [$newName, null];
}
?>
