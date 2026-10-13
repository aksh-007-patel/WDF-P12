<?php
require_once "auth.php";
require_once "config.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) { header("Location: index.php?err=" . urlencode("Invalid event ID.")); exit; }

// Get poster filename to delete file as well
$stmt = $conn->prepare("SELECT title, poster FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$row) { header("Location: index.php?err=" . urlencode("Event not found.")); exit; }

$del = $conn->prepare("DELETE FROM events WHERE id = ?");
$del->bind_param("i", $id);
if ($del->execute()) {
    if ($row["poster"]) {
        $p = UPLOAD_DIR . basename($row["poster"]);
        if (is_file($p)) @unlink($p);
    }
    audit_log($conn, $id, "DELETE", "Event '" . $row["title"] . "' deleted.");
    header("Location: index.php?msg=" . urlencode("Event deleted successfully."));
} else {
    header("Location: index.php?err=" . urlencode("Delete failed: " . $conn->error));
}
exit;
?>
