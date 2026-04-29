<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARPMS - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card" id="authCard">
            <div class="login-logo">
                <i class="fa-solid fa-graduation-cap"></i>
                <h1>ARPMS</h1>
            </div>
            <p class="login-subtitle">Assist Research Project Management System</p>
            
            <div class="login-tabs-pill">
                <div class="login-tab-pill active" data-tab="login">Login</div>
                <div class="login-tab-pill" data-tab="register">Register</div>
            </div>
            
            <!-- Login Form -->
            <div id="loginForm">
                <form action="auth/login_process.php" method="POST">
                    <div class="login-field">
                        <label>Email</label>
                        <input type="email" name="email" class="login-input" placeholder="Enter your email" autocomplete="off" required>
                    </div>
                    
                    <div class="login-field">
                        <label>Password</label>
                        <input type="password" name="password" class="login-input" placeholder="Enter your password" autocomplete="off" required>
                    </div>
                    
                    <div class="login-footer">
                        <a href="#" class="forgot-pw">Forgot password</a>
                    </div>
                    
                    <button type="submit" class="btn-login-blue">Login</button>
                </form>
            </div>

            <!-- Register Form (Image 5) -->
            <div id="registerForm" style="display: none;">
                <form action="auth/register_process.php" method="POST">
                    <div class="login-field">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="login-input" placeholder="Enter your full name" required>
                    </div>

                    <div class="login-field">
                        <label>Email</label>
                        <input type="email" name="email" class="login-input" placeholder="Enter your email" required>
                    </div>

                    <div class="login-field">
                        <label>Request Role</label>
                        <select name="role" class="select-premium" style="width: 100%; border: none; background: #F3F4F6;" required>
                            <option value="" disabled selected>Select your role</option>
                            <option value="researcher">Researcher</option>
                            <option value="coordinator">Coordinator</option>
                        </select>
                    </div>

                    <div class="login-field">
                        <label>Department (Optional)</label>
                        <input type="text" name="department" class="login-input" placeholder="Enter your department">
                    </div>

                    <div class="login-field">
                        <label>Password</label>
                        <input type="password" name="password" class="login-input" placeholder="Enter your password" required>
                    </div>

                    <div class="login-field">
                        <label>Confirm password</label>
                        <input type="password" name="confirm_password" class="login-input" placeholder="Confirm your password" required>
                    </div>

                    <button type="submit" class="btn-login-blue" style="margin-top: 12px;">Register</button>
                </form>
            </div>
            
            <?php if(isset($_GET['error'])): ?>
                <div style="margin-top: 20px; padding: 12px; background-color: #FEF2F2; color: #DC2626; border-radius: 8px; font-size: 13px; font-weight: 500;">
                    <?php 
                    if($_GET['error'] == 'invalid_credentials') echo "Invalid email or password.";
                    else if($_GET['error'] == 'password_mismatch') echo "Passwords do not match.";
                    else if($_GET['error'] == 'email_exists') echo "Email already registered.";
                    else if($_GET['error'] == 'pending_approval') echo "Your account is pending approval.";
                    else echo "An error occurred. Please try again.";
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['success'])): ?>
                <div style="margin-top: 20px; padding: 12px; background-color: #F0FDF4; color: #16A34A; border-radius: 8px; font-size: 13px; font-weight: 500;">
                    Registration request sent. Please wait for admin approval.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.login-tab-pill').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.login-tab-pill').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                if(tab.dataset.tab === 'login') {
                    document.getElementById('loginForm').style.display = 'block';
                    document.getElementById('registerForm').style.display = 'none';
                } else {
                    document.getElementById('loginForm').style.display = 'none';
                    document.getElementById('registerForm').style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>
