<?php
require_once "auth.php";
require_once "config.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $category = trim($_POST["category"] ?? "General");
    $status = $_POST["status"] ?? "Open";
    if (!in_array($status, ["Open", "Closed"])) $status = "Open";

    // Basic server-side validation
    if ($title === "" || $event_date === "" || $venue === "") {
        $error = "Title, Event Date and Venue are required.";
    } else {
        // File validation via helper
        [$posterName, $fileErr] = handle_poster_upload($_FILES["poster"] ?? null, null);
        if ($fileErr) {
            $error = $fileErr; // Q3/Q4: upload failure message displayed
        } else {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, venue, category, status, poster) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $title, $description, $event_date, $venue, $category, $status, $posterName);
            if ($stmt->execute()) {
                $eid = $stmt->insert_id;
                audit_log($conn, $eid, "CREATE", "Event '$title' created.");
                header("Location: index.php?msg=" . urlencode("Event added successfully."));
                exit;
            } else {
                $error = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Event - Practical 12</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width:700px">
  <h4>Add New Event</h4>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <div class="card shadow-sm"><div class="card-body">
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3"><label class="form-label">Title *</label>
      <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST["title"] ?? "") ?>"></div>
    <div class="mb-3"><label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST["description"] ?? "") ?></textarea></div>
    <div class="row">
      <div class="col-md-6 mb-3"><label class="form-label">Event Date *</label>
        <input type="date" name="event_date" class="form-control" required value="<?= htmlspecialchars($_POST["event_date"] ?? "") ?>"></div>
      <div class="col-md-6 mb-3"><label class="form-label">Venue *</label>
        <input type="text" name="venue" class="form-control" required value="<?= htmlspecialchars($_POST["venue"] ?? "") ?>"></div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3"><label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" placeholder="Technical / Cultural / Career..." value="<?= htmlspecialchars($_POST["category"] ?? "") ?>"></div>
      <div class="col-md-6 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="Open">Open</option>
          <option value="Closed">Closed</option>
        </select></div>
    </div>
    <div class="mb-3"><label class="form-label">Event Poster (optional)</label>
      <input type="file" name="poster" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
      <div class="form-text">Allowed: JPG, JPEG, PNG, GIF, WEBP | Max size: 2 MB</div></div>
    <button class="btn btn-primary">Save Event</button>
    <a href="index.php" class="btn btn-secondary">Back</a>
  </form>
  </div></div>
</div>
</body>
</html>
