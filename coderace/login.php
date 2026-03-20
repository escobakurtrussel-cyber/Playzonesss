<?php
session_start();
include 'db.php';

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$user'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['xp'] = $row['xp'];
            $_SESSION['max_level'] = $row['max_level'];
            header("Location: dashboard.php");
        } else { $error = "Invalid password!"; }
    } else { $error = "User not found!"; }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>LearnQuest | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #F7FCF5; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .auth-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,68,27,0.05); width: 100%; max-width: 400px; border: 1px solid #E5F5E0; }
        .btn-auth { background: #238845; color: white; border-radius: 12px; padding: 12px; width: 100%; border: none; font-weight: bold; }
        .btn-auth:hover { background: #00441B; }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #A1D99B; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2 class="fw-bold text-center mb-1" style="color: #00441B;">LearnQuest</h2>
        <p class="text-center text-muted mb-4">Master logic, one block at a time.</p>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if(isset($_GET['msg'])) echo "<div class='alert alert-success'>".$_GET['msg']."</div>"; ?>
        <form method="POST">
            <div class="mb-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <button type="submit" name="login" class="btn-auth mb-3">LOGIN</button>
            <p class="text-center small">No account yet? <a href="register.php" class="text-success fw-bold">Sign up</a></p>
        </form>
    </div>
</body>
</html>