<?php
// login.php - Simple admin login (for access restriction demo)
// Default: admin / admin123
session_start();
if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
    header("Location: index.php");
    exit;
}
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = $_POST["username"] ?? "";
    $pass = $_POST["password"] ?? "";
    if ($user === "admin" && $pass === "admin123") {
        $_SESSION["admin_logged_in"] = true;
        $_SESSION["admin_user"] = "admin";
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials. Try admin / admin123";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login - Event Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:480px">
  <div class="card shadow">
    <div class="card-header bg-dark text-white"><h5 class="mb-0">Admin Login</h5></div>
    <div class="card-body">
      <p class="text-muted small">Practical 12: Event Management CRUD<br>Default: <code>admin / admin123</code></p>
      <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3"><label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required></div>
        <button class="btn btn-dark w-100">Login</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
