<?php
/**
 * noontech - Signup Page
 * Mimics OpenAI's official "Create your account" registration interface.
 */
session_start();
include 'includes/db.php';
require_once 'send_otp.php';

// If user is already logged in, redirect to home
if (isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit;
}

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'signup') {
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error_msg = "Please fill in all inputs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } elseif (strlen($username) < 3) {
        $error_msg = "Username must be at least 3 characters.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters.";
    } else {
        if ($conn && empty($db_connection_error)) {
            // Check if user already exists
            $check_sql = "SELECT id FROM users WHERE email = ?";
            $check_stmt = $conn->prepare($check_sql);
            if ($check_stmt) {
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_stmt->store_result();

                if ($check_stmt->num_rows > 0) {
                    $error_msg = "An account with this email already exists.";
                } else {
                    $check_stmt->close();

                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $otp = (string) random_int(100000, 999999);

                    $sql = "INSERT INTO users (email, username, password_hash, is_verified, otp_code, otp_expires_at, created_at) VALUES (?, ?, ?, 0, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param("ssss", $email, $username, $hash, $otp);
                        $stmt->execute();

                        // Dispatch email
                        sendOTP($email, $otp);

                        header("Location: verify.php?email=" . urlencode($email));
                        exit;
                    } else {
                        $error_msg = "Database error: unable to prepare registration statement.";
                    }
                }
                $check_stmt->close();
            } else {
                $error_msg = "Database error: unable to check email existence.";
            }
        } else {
            $error_msg = "Database connection error. Try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create your account - noontech</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Logo Mark -->
            <div class="auth-logo">
                <a href="index.php"
                    style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        style="color: var(--text-primary);">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </a>
            </div>

            <h1 class="auth-title">Create your account</h1>
            <p class="auth-subtitle">Join noontech to create and configure projects</p>

            <?php if (!empty($error_msg)): ?>
                <div
                    style="background-color: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px; border-radius: var(--radius-md); font-size: 13.5px; margin-bottom: 20px; text-align: left;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <!-- Standard HTML Form -->
            <form action="signup.php" method="POST" class="auth-form">
                <input type="hidden" name="action" value="signup">

                <div class="form-group">
                    <label for="regUsername" class="form-label">Username</label>
                    <input type="text" id="regUsername" name="username" class="form-input"
                        placeholder="choose a username" required autocomplete="username" minlength="3"
                        value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="regEmail" class="form-label">Email Address</label>
                    <input type="email" id="regEmail" name="email" class="form-input" placeholder="you@domain.com"
                        required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="regPassword" class="form-label">Password</label>
                    <input type="password" id="regPassword" name="password" class="form-input" placeholder="••••••••"
                        required>
                </div>

                <div class="form-group">
                    <label for="regConfirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" id="regConfirmPassword" name="confirm_password" class="form-input"
                        placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">Continue</button>
            </form>

            <p class="auth-footer-text">
                Already have an account? <a href="login.php">Log in</a>
            </p>

            <p style="margin-top: 24px; font-size: 11px; color: var(--text-tertiary);">
                <a href="index.php" style="color: inherit; text-decoration: underline;">Return to Homepage</a>
            </p>
        </div>
    </div>

</body>

</html>