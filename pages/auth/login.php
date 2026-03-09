<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Vendora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Shared Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Logo -->
    <link rel="icon" href="assets/image/logo.png">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <img src="assets/image/logo.png" alt="Vendora Logo" style="height: 60px; margin-bottom: 12px;">
    
        </div>

        <!-- Title -->
        <div class="auth-title">
            <h2>Welcome Back</h2>
            <p>Sign in to your Vendora account</p>
        </div>

        <form class="auth-form" id="loginForm">
            <div class="floating-group">
                <input type="text" name="username" class="floating-control" placeholder=" " required>
                <label class="floating-label"><i class="fa-solid fa-user" style="margin-right: 8px;"></i> Username</label>
            </div>

            <div class="floating-group">
                <input type="password" name="password" class="floating-control" id="loginPassword" placeholder=" " required>
                <label class="floating-label"><i class="fa-solid fa-lock" style="margin-right: 8px;"></i> Password</label>
                <div class="input-group-append" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%);">
                    <button type="button" class="btn-icon" onclick="togglePassword('loginPassword')" style="width:34px;height:34px;font-size:0.85rem;background:transparent;box-shadow:none;border:none;">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-row mb-16">
                <label class="form-check">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="?page=forgot_password" style="font-size:0.83rem; font-weight:600;">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block" id="loginBtn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Sign In
            </button>
        </form>

        <div class="auth-footer">
            <p>Vendora POS v1.0 &middot; &copy; <?= date('Y') ?></p>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    var input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const loginBtn = $('#loginBtn');
        const originalBtnContent = loginBtn.html();
        
        // Show loading state
        loginBtn.html('<i class="fas fa-spinner fa-spin"></i> Signing in...').attr('disabled', true);
        
        $.ajax({
            url: 'controllers/AuthController.php',
            type: 'POST',
            data: $(this).serialize() + '&action=login',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = '?page=dashboard';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: response.message,
                        confirmButtonColor: '#ff69b4'
                    });
                    loginBtn.html(originalBtnContent).attr('disabled', false);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong. Please try again later.',
                    confirmButtonColor: '#ff69b4'
                });
                loginBtn.html(originalBtnContent).attr('disabled', false);
            }
        });
    });
});
</script>
</body>
</html>
