<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cosmetics Inventory Management System - Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fff0f5;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin-top: 80px;
            background: #fff0f5;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(214, 51, 132, 0.15);
            border: 2px solid #f8bbd0;
        }
        .login-title {
            font-weight: bold;
            color: #d63384;
            letter-spacing: 1px;
        }
        .btn-pink {
            background-color: #d63384;
            color: white;
            border-radius: 30px;
            font-weight: 500;
            box-shadow: 0 2px 8px #f8bbd0;
            border: none;
        }
        .btn-pink:hover {
            background-color: #c2185b;
            color: #fff;
        }
        .form-label {
            color: #d63384;
            font-weight: 500;
        }
        .form-control:focus {
            border-color: #d63384;
            box-shadow: 0 0 0 0.2rem #f8bbd0;
        }
        .alert-danger {
            background: #f8bbd0;
            color: #d63384;
            border: none;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            border-radius: 20px;
            border: 1.5px solid #f8bbd0;
        }
        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #d63384;
        }
        .login-container {
            transition: box-shadow 0.3s;
        }
        .login-container:hover {
            box-shadow: 0 16px 40px rgba(214, 51, 132, 0.18);
        }
        .login-title::after {
            content: ' ♡';
            color: #f06292;
            font-size: 1.2em;
        }
    </style>
</head>
<body>  
    


<div class="container d-flex justify-content-center">
    
        <div class="row align-items-center" style="margin-top:80px;">
            <div class="col-auto d-n    one d-md-block">
                <img src="data:image/png;base64,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" alt="Lipstick" style="height:120px; margin-right:20px;">
            </div>
            <div class="col">
                
                <div class="login-container">
                    <h3 class="text-center login-title mb-4">Admin Login</h3>
                    <form action="auth.php" method="POST">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger py-2">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-pink w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
