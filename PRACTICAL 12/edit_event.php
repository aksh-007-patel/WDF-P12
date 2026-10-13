<?php
require_once "auth.php";
require_once "config.php";

$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);
if ($id <= 0) { header("Location: index.php?err=" . urlencode("Invalid event ID.")); exit; }

// Fetch existing record
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$event) { header("Location: index.php?err=" . urlencode("Event not found.")); exit; }

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $category = trim($_POST["category"] ?? "General");
    $status = $_POST["status"] ?? "Open";
    if (!in_array($status, ["Open", "Closed"])) $status = "Open";

    if ($title === "" || $event_date === "" || $venue === "") {
        $error = "Title, Event Date and Venue are required.";
    } else {
        [$posterName, $fileErr] = handle_poster_upload($_FILES["poster"] ?? null, $event["poster"]);
        if ($fileErr) {
            $error = $fileErr;
        } else {
            $u = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, venue=?, category=?, status=?, poster=? WHERE id=?");
            $u->bind_param("sssssssi", $title, $description, $event_date, $venue, $category, $status, $posterName, $id);
            if ($u->execute()) {
                audit_log($conn, $id, "UPDATE", "Event '$title' updated.");
                header("Location: index.php?msg=" . urlencode("Event updated successfully."));
                exit;
            } else {
                $error = "Database error: " . $conn->error;
            }
            $u->close();
        }
    }
    // refresh displayed values on error
    $event = array_merge($event, ["title" => $title ?? "", "description" => $description ?? "",
        "event_date" => $event_date ?? "", "venue" => $venue ?? "",
        "category" => $category ?? "", "status" => $status ?? "Open"]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Event - Practical 12</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width:700px">
  <h4>Edit Event #<?= $id ?></h4>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <div class="card shadow-sm"><div class="card-body">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="mb-3"><label class="form-label">Title *</label>
      <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($event["title"]) ?>"></div>
    <div class="mb-3"><label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($event["description"]) ?></textarea></div>
    <div class="row">
      <div class="col-md-6 mb-3"><label class="form-label">Event Date *</label>
        <input type="date" name="event_date" class="form-control" required value="<?= htmlspecialchars($event["event_date"]) ?>"></div>
      <div class="col-md-6 mb-3"><label class="form-label">Venue *</label>
        <input type="text" name="venue" class="form-control" required value="<?= htmlspecialchars($event["venue"]) ?>"></div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3"><label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($event["category"]) ?>"></div>
      <div class="col-md-6 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="Open" <?= $event["status"] === "Open" ? "selected" : "" ?>>Open</option>
          <option value="Closed" <?= $event["status"] === "Closed" ? "selected" : "" ?>>Closed</option>
        </select></div>
    </div>
    <div class="mb-3"><label class="form-label">Current Poster</label><br>
      <?php if ($event["poster"] && is_file(UPLOAD_DIR . basename($event["poster"]))): ?>
        <img src="uploads/<?= htmlspecialchars(basename($event["poster"])) ?>" width="150" class="rounded border mb-2">
      <?php else: ?><span class="text-muted small">No poster uploaded.</span><?php endif; ?><br>
      <label class="form-label mt-2">Replace Poster (optional)</label>
      <input type="file" name="poster" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
      <div class="form-text">Leave empty to keep existing poster. Allowed: JPG, JPEG, PNG, GIF, WEBP | Max 2 MB</div></div>
    <button class="btn btn-warning">Update Event</button>
    <a href="index.php" class="btn btn-secondary">Back</a>
  </form>
  </div></div>
</div>
</body>
</html>
