<?php
session_start();
require_once 'connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$username || !$password) {
        $error = 'Korisničko ime i lozinka su obavezni!';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: administracija.php');
            exit;
        } else {
            $error = 'Neispravno korisničko ime ili lozinka!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava - El Confidencial</title>
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Georgia', serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-title {
            font-size: 32px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 25px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            width: 100%;
            background: #2c5aa0;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #1a3a6b;
        }
        .btn-secondary {
            width: 100%;
            box-sizing: border-box;
            background: #6c757d;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            transition: background 0.3s;
            text-align: center;
            display: block;
            text-decoration: none;
        }
        .btn-secondary:hover {
            background: #495057;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-title">Prijava</div>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Korisničko ime</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Lozinka</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Prijavi se</button>
        </form>
        <a href="index.php" class="btn-secondary">Natrag na početnu</a>
    </div>
</body>
</html>
