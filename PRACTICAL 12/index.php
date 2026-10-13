<?php
require_once "auth.php";
require_once "config.php";

// Filters: category + open/closed (Intermediate extension)
$filter_category = $_GET["category"] ?? "";
$filter_status = $_GET["status"] ?? "";

$where = [];
$params = [];
$types = "";
if ($filter_category !== "") { $where[] = "category = ?"; $params[] = $filter_category; $types .= "s"; }
if ($filter_status !== "")   { $where[] = "status = ?";   $params[] = $filter_status;   $types .= "s"; }

$sql = "SELECT * FROM events";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY event_date ASC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Category list for filter
$cats = $conn->query("SELECT DISTINCT category FROM events ORDER BY category");
$msg = $_GET["msg"] ?? "";
$err = $_GET["err"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Event Management - Practical 12</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <span class="navbar-brand">Event Management (Practical 12)</span>
    <span class="text-light small">Admin: <?= htmlspecialchars($_SESSION["admin_user"] ?? "admin") ?> |
      <a href="logout.php" class="text-warning">Logout</a></span>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>All Events</h4>
    <a href="add_event.php" class="btn btn-primary">+ Add Event</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <!-- Filter bar (Intermediate: category + open/closed filter) -->
  <form class="row g-2 mb-3" method="get">
    <div class="col-md-4">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <?php while ($c = $cats->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($c["category"]) ?>" <?= $filter_category === $c["category"] ? "selected" : "" ?>>
            <?= htmlspecialchars($c["category"]) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select">
        <option value="">Open + Closed</option>
        <option value="Open" <?= $filter_status === "Open" ? "selected" : "" ?>>Open</option>
        <option value="Closed" <?= $filter_status === "Closed" ? "selected" : "" ?>>Closed</option>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-secondary w-100">Filter</button></div>
    <div class="col-md-2"><a href="index.php" class="btn btn-outline-secondary w-100">Reset</a></div>
  </form>

  <div class="card shadow-sm">
    <div class="table-responsive">
    <table class="table table-striped table-hover mb-0 align-middle">
      <thead class="table-dark">
        <tr><th>#</th><th>Poster</th><th>Title</th><th>Date</th><th>Venue</th><th>Category</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No events found.</td></tr>
      <?php endif; ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row["id"] ?></td>
          <td>
            <?php if ($row["poster"] && is_file(UPLOAD_DIR . basename($row["poster"]))): ?>
              <img src="uploads/<?= htmlspecialchars(basename($row["poster"])) ?>" width="70" height="50" style="object-fit:cover" class="rounded border">
            <?php else: ?><span class="text-muted small">No poster</span><?php endif; ?>
          </td>
          <td><strong><?= htmlspecialchars($row["title"]) ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars(substr($row["description"] ?? "", 0, 60)) ?></small></td>
          <td><?= htmlspecialchars($row["event_date"]) ?></td>
          <td><?= htmlspecialchars($row["venue"]) ?></td>
          <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row["category"]) ?></span></td>
          <td>
            <span class="badge <?= $row["status"] === "Open" ? "bg-success" : "bg-secondary" ?>">
              <?= htmlspecialchars($row["status"]) ?></span>
          </td>
          <td class="text-nowrap">
            <a href="edit_event.php?id=<?= $row["id"] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="delete_event.php?id=<?= $row["id"] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Delete this event?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div>
  <p class="text-muted small mt-3">Upload rules: JPG / JPEG / PNG / GIF / WEBP only, max 2 MB. Failure messages are shown on add/edit.</p>
</div>
</body>
</html>
