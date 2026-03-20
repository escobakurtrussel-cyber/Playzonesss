<?php
// Dapat ay pangalan ng FILE ang i-include, hindi pangalan ng database
include 'db.php'; 

if (isset($_POST['register'])) {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, xp, max_level) VALUES ('$user', '$pass', 0, 1)";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?msg=Success");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>LearnQuest | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #F7FCF5; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .auth-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,68,27,0.05); width: 100%; max-width: 400px; border: 1px solid #E5F5E0; }
        .btn-auth { background: #00441B; color: white; border-radius: 12px; padding: 12px; width: 100%; border: none; font-weight: bold; }
        .btn-auth:hover { background: #238845; }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #A1D99B; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2 class="fw-bold text-center mb-4" style="color: #00441B;">Create Account</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <button type="submit" name="register" class="btn-auth mb-3">REGISTER</button>
            <p class="text-center small">Already have an account? <a href="login.php" class="text-success fw-bold">Login here</a></p>
        </form>
    </div>
</body>
</html>