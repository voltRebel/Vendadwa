<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — Ntɛm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <img src="assets/image/logo.png" alt="Ntɛm Logo" style="height: 60px; margin-bottom: 12px;">
            <h1>Ntɛm</h1>
        </div>

        <!-- Title -->
        <div class="auth-title">
            <h2>Forgot Password? 💌</h2>
            <p>Enter your email and we'll send you a reset link</p>
        </div>

        <!-- Form -->
        <form class="auth-form" action="#" method="POST">
            <div class="floating-group">
                <input type="email" class="floating-control" placeholder=" " required>
                <label class="floating-label"><i class="fa-solid fa-envelope" style="margin-right: 8px;"></i> Email Address</label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">
                <i class="fa-solid fa-paper-plane"></i>
                Send Reset Link
            </button>
        </form>

        <div class="auth-footer">
            <p><a href="?page=login"><i class="fa-solid fa-arrow-left"></i> Back to Login</a></p>
        </div>
    </div>
</div>
</body>
</html>
