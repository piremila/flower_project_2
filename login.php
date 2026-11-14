<?php
/**
 * login.php — Handles login form submission AND renders the login page.
 *
 * GET  → Renders the login form (same design as login.html).
 * POST → Verifies credentials, starts session, redirects.
 *
 * After successful login the user is redirected to j.html (the flower shop).
 * logout.php destroys the session.
 */

session_start();
require_once __DIR__ . '/db.php';

/* ──────────────────────────────────────────────────────────────
   POST HANDLER — credential verification
   ────────────────────────────────────────────────────────────── */
$loginError   = '';
$loginSuccess = '';

// Show "account created" banner when coming from signup.php
if (isset($_GET['registered'])) {
    $loginSuccess = 'Account created successfully! Please sign in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    $errorMsg = '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Please enter a valid email address.';
    } elseif ($password === '') {
        $errorMsg = 'Please enter your password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name, password FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email']= $email;

            header('Location: j.html');
            exit;
        } else {
            $errorMsg = 'Invalid email address or password. Please try again.';
        }
    }

    $loginError = htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8');
}

/* ──────────────────────────────────────────────────────────────
   PAGE RENDER — identical design to original login.html
   ────────────────────────────────────────────────────────────── */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | R.K Fresh Flowers</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:25px;
    background:#f3f0e9;
    font-family:'Poppins',sans-serif;
    color:#293b2d;
}

.login-container {
    width:100%;
    max-width:1050px;
    min-height:630px;
    display:grid;
    grid-template-columns:1fr 1fr;
    background:#fffdf9;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 20px 70px rgba(40,55,42,0.13);
}

.image-panel {
    position:relative;
    min-height:630px;
    background:
        linear-gradient(to top,rgba(24,45,29,0.75),rgba(24,45,29,0.02) 75%),
        url("k.jpeg") center/cover no-repeat;
    display:flex;
    align-items:flex-end;
    padding:45px;
}

.image-text { color:white; max-width:380px; }
.image-text .small-title { font-size:12px; letter-spacing:3px; text-transform:uppercase; margin-bottom:16px; }
.image-text h1 { font-family:'Playfair Display',serif; font-size:43px; line-height:1.2; margin-bottom:15px; }
.image-text p  { font-size:13px; line-height:1.9; color:#f5eee8; }

.form-panel { padding:48px 55px; display:flex; flex-direction:column; justify-content:center; }

.brand { display:flex; align-items:center; justify-content:center; gap:11px; margin-bottom:42px; }
.brand span { font-family:'Playfair Display',serif; font-size:21px; font-weight:600; color:#34543a; }

.form-heading { margin-bottom:27px; }
.form-heading h2 { font-family:'Playfair Display',serif; font-size:34px; font-weight:600; margin-bottom:9px; }
.form-heading p  { color:#888d83; font-size:12px; line-height:1.8; }

.social-buttons { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:22px; }
.social-btn {
    height:45px;
    display:flex;
    justify-content:center;
    align-items:center;
    gap:9px;
    background:white;
    border:1px solid #e3e5dc;
    border-radius:8px;
    color:#454b43;
    text-decoration:none;
    font-size:12px;
    cursor:pointer;
    transition:.25s;
}
.social-btn:hover { background:#f4f7f0; border-color:#8ba28b; }
.social-btn img   { width:18px; height:18px; object-fit:contain; }

.divider {
    display:flex;
    align-items:center;
    gap:12px;
    color:#a1a398;
    font-size:10px;
    margin:5px 0 23px;
}
.divider::before,.divider::after { content:""; flex:1; height:1px; background:#e7e8e1; }

.input-group { margin-bottom:19px; }
.input-group label { display:block; margin-bottom:8px; font-size:12px; font-weight:500; color:#3b4a3c; }
.input-wrap { position:relative; }
.input-wrap input {
    width:100%;
    height:49px;
    padding:0 14px;
    border:1px solid #dfe2d9;
    border-radius:8px;
    background:#fff;
    outline:none;
    color:#293b2d;
    font-size:12px;
    transition:.25s;
}
.input-wrap input:focus { border-color:#789477; box-shadow:0 0 0 3px rgba(120,148,119,.12); }
.password-input { padding-right:65px !important; }
.toggle-password {
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    border:none;
    background:none;
    color:#648064;
    font-size:10px;
    font-weight:600;
    cursor:pointer;
}

.form-options { display:flex; justify-content:space-between; align-items:center; gap:10px; margin:2px 0 23px; font-size:10px; }
.remember { display:flex; align-items:center; gap:7px; color:#747a70; }
.remember input { accent-color:#486a4c; }
.form-options a,.signup a { color:#527454; text-decoration:none; font-weight:600; }

.signin-btn {
    width:100%;
    height:49px;
    border:none;
    border-radius:8px;
    background:#3e6044;
    color:white;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    transition:.25s;
}
.signin-btn:hover { background:#2b4931; transform:translateY(-1px); }

.signup { text-align:center; margin-top:25px; color:#858a80; font-size:11px; }

/* Alert messages */
.alert {
    display:block;
    margin-bottom:15px;
    padding:10px 13px;
    border-radius:7px;
    font-size:11px;
    line-height:1.6;
}
.alert-error   { background:#f9e8e6; color:#8e3838; border:1px solid #f0c5c0; }
.alert-success { background:#e8f5ec; color:#2d6a42; border:1px solid #b8dfc5; }

@media (max-width:800px) {
    body { padding:15px; }
    .login-container { max-width:500px; grid-template-columns:1fr; }
    .image-panel { min-height:260px; padding:28px; }
    .image-text h1 { font-size:30px; }
    .form-panel { padding:35px 30px; }
    .brand { margin-bottom:28px; }
}
@media (max-width:400px) {
    .form-panel { padding:28px 20px; }
    .form-heading h2 { font-size:29px; }
    .brand span { font-size:18px; }
    .image-panel { min-height:220px; }
}
</style>
</head>
<body>

<div class="login-container">

    <!-- LEFT FLOWER IMAGE -->
    <div class="image-panel">
        <div class="image-text">
            <div class="small-title">Welcome to our garden</div>
            <h1>Flowers make every moment beautiful.</h1>
            <p>Discover fresh flowers, thoughtful gifts and beautiful bouquets made to brighten someone's day.</p>
        </div>
    </div>

    <!-- RIGHT LOGIN FORM -->
    <div class="form-panel">

        <div class="brand">
            <span>R.K Fresh Flowers</span>
        </div>

        <div class="form-heading">
            <h2>Welcome Back</h2>
            <p>Sign in to find your perfect flowers.</p>
        </div>

        <div class="social-buttons">
            <button type="button" class="social-btn" onclick="socialNotice('Google')">
                <img src="Google.png" alt="Google"> Google
            </button>
            <button type="button" class="social-btn" onclick="socialNotice('Apple')">
                <img src="Apple.png" alt="Apple"> Apple
            </button>
        </div>

        <div class="divider">OR CONTINUE WITH EMAIL</div>

        <?php if ($loginError): ?>
            <div class="alert alert-error" role="alert" id="message"><?= $loginError ?></div>
        <?php endif; ?>

        <?php if ($loginSuccess): ?>
            <div class="alert alert-success" role="status" id="message"><?= htmlspecialchars($loginSuccess, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php">

            <div class="input-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        autocomplete="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="password-input"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-password" id="togglePassword">SHOW</button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember">
                    <input type="checkbox" id="remember" name="remember"> Remember me
                </label>
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit" class="signin-btn" id="signinBtn">
                Sign In &nbsp; →
            </button>

        </form>

        <p class="signup">
            New here?
            <a href="accound.html">Create an account</a>
        </p>

    </div>
</div>

<script>
const passwordInput  = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', function () {
    const showing = passwordInput.type === 'password';
    passwordInput.type      = showing ? 'text' : 'password';
    togglePassword.textContent = showing ? 'HIDE' : 'SHOW';
});

function socialNotice(provider) {
    const box = document.getElementById('message') || document.createElement('div');
    box.id        = 'message';
    box.className = 'alert alert-error';
    box.textContent = provider + ' sign-in is a design placeholder. Connect an authentication service to enable it.';
    if (!box.parentNode) {
        document.getElementById('loginForm').before(box);
    }
}
</script>
</body>
</html>
