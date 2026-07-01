<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Store - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); width: 100%; max-width: 360px; }
        h2 { text-align: center; margin-bottom: 24px; color: #333333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #555555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #cccccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .btn-submit { width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-submit:hover { background-color: #0056b3; }
        .error-message { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; text-align: center; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>School Store Login</h2>

    <!-- Checks URL query parameters for authorization errors -->
    <?php if (isset($_GET['error'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <form action="authenticate.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus autocomplete="off">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn-submit">Login</button>
    </form>
</div>

</body>
</html>
